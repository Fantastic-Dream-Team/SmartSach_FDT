-- =============================================================================
-- ESQUEMA COMPLETO Y LIMPIO DE BASE DE DATOS: SmartSACH (Versión 1.9.2)
-- =============================================================================

-- Habilitar la extensión espacial PostGIS para manejar coordenadas geográficas
CREATE EXTENSION IF NOT EXISTS postgis;

-- Limpieza ordenada de tablas existentes para evitar conflictos de claves foráneas
DROP TRIGGER IF EXISTS tr_actualizar_rastreo_camion ON public.camiones_rastreo;
DROP TRIGGER IF EXISTS tr_activar_usuario_tras_suscripcion ON public.suscripciones;
DROP TRIGGER IF EXISTS tr_activar_suscripcion_inicial ON public.usuarios;

DROP FUNCTION IF EXISTS public.fn_alerta_proximidad_sach();
DROP FUNCTION IF EXISTS public.fn_activar_usuario_por_suscripcion();
DROP FUNCTION IF EXISTS public.fn_activar_suscripcion_inicial();

DROP TABLE IF EXISTS public.noticias CASCADE;
DROP TABLE IF EXISTS public.reportes_incidencias CASCADE;
DROP TABLE IF EXISTS public.notificaciones CASCADE;
DROP TABLE IF EXISTS public.camiones_rastreo CASCADE;
DROP TABLE IF EXISTS public.pagos CASCADE;
DROP TABLE IF EXISTS public.suscripciones CASCADE;
DROP TABLE IF EXISTS public.rutas CASCADE;
DROP TABLE IF EXISTS public.ubicaciones_servicio CASCADE;
DROP TABLE IF EXISTS public.usuarios CASCADE;
DROP TABLE IF EXISTS public.spatial_ref_sys CASCADE;

-- =============================================================================
-- 1. TABLAS PRINCIPALES (ENTIDADES FUERTES)
-- =============================================================================

-- Tabla de Usuarios del Sistema (Clientes, Empleados y Administradores)
CREATE TABLE public.usuarios (
    usuario_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    auth_id uuid UNIQUE, -- Vinculación con Supabase Auth
    nombre character varying NOT NULL,
    apellido character varying NOT NULL,
    cedula character varying NOT NULL UNIQUE,
    telefono character varying,
    direccion character varying,
    correo_electronico character varying NOT NULL UNIQUE,
    estado_verificacion character varying DEFAULT 'pendiente'::character varying 
        CHECK (estado_verificacion::text = ANY (ARRAY['pendiente'::character varying, 'activo'::character varying, 'suspendido'::character varying]::text[])),
    fecha_registro timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    rol character varying DEFAULT 'Cliente'::character varying,
    CONSTRAINT usuarios_pkey PRIMARY KEY (usuario_id)
);

-- Tabla de Rutas de Recolección de Basura
CREATE TABLE public.rutas (
    ruta_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    nombre_ruta character varying NOT NULL,
    zona_sector character varying,
    horario_estimado character varying,
    estado_ruta character varying DEFAULT 'activa'::character varying 
        CHECK (estado_ruta::text = ANY (ARRAY['activa'::character varying, 'mantenimiento'::character varying, 'inactiva'::character varying]::text[])),
    CONSTRAINT rutas_pkey PRIMARY KEY (ruta_id)
);

-- =============================================================================
-- 2. TABLAS RELACIONALES Y SERVICIOS GEOGRÁFICOS
-- =============================================================================

-- Tabla de Ubicaciones asociadas a los Clientes (Puntos de recolección geográficos)
CREATE TABLE public.ubicaciones_servicio (
    ubicacion_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    usuario_id integer NOT NULL,
    nombre_referencia character varying,
    coordenadas_gps geometry(Point, 4326) NOT NULL, -- Uso de PostGIS para precisión GPS
    descripcion_direccion text,
    foto_url character varying,
    fecha_creacion timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ubicaciones_servicio_pkey PRIMARY KEY (ubicacion_id),
    CONSTRAINT fk_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabla de Suscripciones de Clientes a las Rutas y Ubicaciones
CREATE TABLE public.suscripciones (
    suscripcion_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    usuario_id integer NOT NULL,
    ubicacion_id integer NOT NULL,
    ruta_id integer NOT NULL,
    fecha_activacion date,
    proximo_vencimiento date,
    estado_pago character varying DEFAULT 'al_dia'::character varying 
        CHECK (estado_pago::text = ANY (ARRAY['al_dia'::character varying, 'moroso'::character varying]::text[])),
    estado_suscripcion character varying DEFAULT 'activa'::character varying,
    CONSTRAINT suscripciones_pkey PRIMARY KEY (suscripcion_id),
    CONSTRAINT suscripciones_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE,
    CONSTRAINT suscripciones_ubicacion_id_fkey FOREIGN KEY (ubicacion_id) REFERENCES public.ubicaciones_servicio(ubicacion_id) ON DELETE CASCADE,
    CONSTRAINT suscripciones_ruta_id_fkey FOREIGN KEY (ruta_id) REFERENCES public.rutas(ruta_id) ON DELETE RESTRICT
);

-- =============================================================================
-- 3. TABLAS DE TRANSACCIONES, RASTREO Y COMUNICACIÓN
-- =============================================================================

-- Tabla de Pagos de Suscripción
CREATE TABLE public.pagos (
    pago_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    suscripcion_id integer NOT NULL,
    monto numeric NOT NULL,
    fecha_pago timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    metodo_pago character varying,
    comprobante_url character varying,
    CONSTRAINT pagos_pkey PRIMARY KEY (pago_id),
    CONSTRAINT pagos_suscripcion_id_fkey FOREIGN KEY (suscripcion_id) REFERENCES public.suscripciones(suscripcion_id) ON DELETE CASCADE
);

-- Tabla de Rastreo GPS en Tiempo Real de los Camiones Recolectores
CREATE TABLE public.camiones_rastreo (
    camion_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    ruta_id integer NOT NULL,
    placa_vehiculo character varying NOT NULL UNIQUE,
    latitud numeric NOT NULL,
    longitud numeric NOT NULL,
    ultima_actualizacion timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT camiones_rastreo_pkey PRIMARY KEY (camion_id),
    CONSTRAINT camiones_rastreo_ruta_id_fkey FOREIGN KEY (ruta_id) REFERENCES public.rutas(ruta_id) ON DELETE CASCADE
);

-- Tabla de Notificaciones Internas para Usuarios
CREATE TABLE public.notificaciones (
    notificacion_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    usuario_id integer NOT NULL,
    titulo character varying NOT NULL,
    mensaje text NOT NULL,
    tipo_notificacion character varying DEFAULT 'sistema'::character varying 
        CHECK (tipo_notificacion::text = ANY (ARRAY['pago'::character varying, 'ruta'::character varying, 'sistema'::character varying, 'incidencia'::character varying]::text[])),
    leido boolean DEFAULT false,
    fecha_envio timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT notificaciones_pkey PRIMARY KEY (notificacion_id),
    CONSTRAINT notificaciones_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabla de Reportes e Incidencias creados por los Usuarios
CREATE TABLE public.reportes_incidencias (
    reporte_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    usuario_id integer NOT NULL,
    ubicacion_id integer NOT NULL,
    tipo_incidencia character varying NOT NULL 
        CHECK (tipo_incidencia::text = ANY (ARRAY['no_paso_camion'::character varying, 'mala_atencion'::character varying, 'desperficio_en_via'::character varying, 'otro'::character varying]::text[])),
    descripcion text,
    estado_reporte character varying DEFAULT 'abierto'::character varying 
        CHECK (estado_reporte::text = ANY (ARRAY['abierto'::character varying, 'en_proceso'::character varying, 'resuelto'::character varying, 'cerrado'::character varying]::text[])),
    fecha_reporte timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT reportes_incidencias_pkey PRIMARY KEY (reporte_id),
    CONSTRAINT reportes_incidencias_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE,
    CONSTRAINT reportes_incidencias_ubicacion_id_fkey FOREIGN KEY (ubicacion_id) REFERENCES public.ubicaciones_servicio(ubicacion_id) ON DELETE CASCADE
);

-- Tabla de Noticias Normalizada (3FN - Relacionada con autores internos y rutas específicas)
CREATE TABLE public.noticias (
    noticia_id integer GENERATED ALWAYS AS IDENTITY NOT NULL,
    titulo character varying NOT NULL,
    contenido text NOT NULL,
    fecha_publicacion timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    creado_por integer, -- Llave foránea que apunta al usuario administrador creador (3FN)
    ruta_id integer,    -- Llave foránea opcional para segmentar noticias por rutas de recolección
    CONSTRAINT noticias_pkey PRIMARY KEY (noticia_id),
    CONSTRAINT fk_noticias_autor FOREIGN KEY (creado_por) REFERENCES public.usuarios(usuario_id) ON DELETE SET NULL,
    CONSTRAINT fk_noticias_ruta FOREIGN KEY (ruta_id) REFERENCES public.rutas(ruta_id) ON DELETE SET NULL
);

-- =============================================================================
-- 4. FUNCIONES DE AUTOMATIZACIÓN (TRIGGERS)
-- =============================================================================

-- Trigger A: Cambia el estado de verificación del usuario a 'activo' al registrar una suscripción (ruta)
CREATE OR REPLACE FUNCTION public.fn_activar_usuario_por_suscripcion()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE public.usuarios
    SET estado_verificacion = 'activo'
    WHERE usuario_id = NEW.usuario_id
      AND estado_verificacion = 'pendiente';

    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger B: Genera la suscripción inicial con Ruta 1 por defecto al activar un usuario si este NO tiene ya una suscripción
CREATE OR REPLACE FUNCTION public.fn_activar_suscripcion_inicial()
RETURNS TRIGGER AS $$
BEGIN
    -- Comprobar transiciones de estado
    IF OLD.estado_verificacion = 'pendiente' AND NEW.estado_verificacion = 'activo' THEN
        -- EVITAR RECURSIVIDAD Y DUPLICACIONES: 
        -- Solo insertamos si el usuario no tiene ninguna suscripción registrada previamente.
        IF NOT EXISTS (SELECT 1 FROM public.suscripciones WHERE usuario_id = NEW.usuario_id) THEN
            INSERT INTO public.suscripciones (usuario_id, ubicacion_id, ruta_id, fecha_activacion, proximo_vencimiento, estado_pago)
            SELECT 
                NEW.usuario_id, 
                ub.ubicacion_id, 
                1, -- Ruta 1 por defecto del sistema
                CURRENT_DATE, 
                (CURRENT_DATE + INTERVAL '30 days'), 
                'al_dia'::character varying
            FROM public.ubicaciones_servicio ub 
            WHERE ub.usuario_id = NEW.usuario_id 
            LIMIT 1;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger C: Actualiza la fecha/hora de actualización cuando cambia la posición GPS del camión
CREATE OR REPLACE FUNCTION public.fn_alerta_proximidad_sach()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.latitud <> OLD.latitud OR NEW.longitud <> OLD.longitud THEN
        NEW.ultima_actualizacion = CURRENT_TIMESTAMP;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- =============================================================================
-- 5. VINCULACIÓN DE DISPARADORES A LAS TABLAS
-- =============================================================================

-- Vincular Trigger A a la tabla suscripciones (AFTER INSERT)
CREATE TRIGGER tr_activar_usuario_tras_suscripcion
    AFTER INSERT ON public.suscripciones
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_activar_usuario_por_suscripcion();

-- Vincular Trigger B a la tabla usuarios (BEFORE UPDATE)
CREATE TRIGGER tr_activar_suscripcion_inicial
    BEFORE UPDATE ON public.usuarios
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_activar_suscripcion_inicial();

-- Vincular Trigger C a la tabla camiones_rastreo (BEFORE UPDATE)
CREATE TRIGGER tr_actualizar_rastreo_camion
    BEFORE UPDATE ON public.camiones_rastreo
    FOR EACH ROW
    EXECUTE FUNCTION public.fn_alerta_proximidad_sach();