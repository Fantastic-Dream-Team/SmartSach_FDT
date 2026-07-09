-- ==========================================
-- SCRIPT LIMPIO Y OPTIMIZADO PARA SMARTSACH
-- INTEGRADO CON SUPABASE AUTH DIRECTO Y POSTGIS
-- ==========================================

-- Habilitar extensión para geolocalización
CREATE EXTENSION IF NOT EXISTS postgis;

-- ==========================================
-- 1. TABLA DE USUARIOS (CON ROL)
-- ==========================================
CREATE TABLE public.usuarios (
    usuario_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    auth_id UUID UNIQUE, 
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20), 
    direccion VARCHAR(50), 
    correo_electronico VARCHAR(100) NOT NULL UNIQUE,
    rol VARCHAR(20) DEFAULT 'Cliente' CHECK (rol IN ('Cliente', 'Conductor', 'Gestor')),
    estado_verificacion VARCHAR(20) CHECK (estado_verificacion IN ('pendiente', 'activo', 'suspendido')) DEFAULT 'pendiente',
    fecha_registro TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_usuarios_correo ON public.usuarios(correo_electronico);
CREATE INDEX idx_usuarios_cedula ON public.usuarios(cedula);
CREATE INDEX idx_usuarios_rol ON public.usuarios(rol);

-- ==========================================
-- 2. TABLA DE UBICACIONES (CON GEOGRAPHY)
-- ==========================================
CREATE TABLE public.ubicaciones_servicio (
    ubicacion_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre_referencia VARCHAR(50),
    coordenadas_gps GEOGRAPHY(POINT, 4326) NOT NULL, 
    descripcion_direccion TEXT,
    foto_url VARCHAR(255),
    fecha_creacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE
);

-- ==========================================
-- 3. TABLA DE RUTAS (CON DATOS INICIALES)
-- ==========================================
CREATE TABLE public.rutas (
    ruta_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre_ruta VARCHAR(100) NOT NULL,
    zona_sector VARCHAR(100),
    horario_estimado VARCHAR(100),
    estado_ruta VARCHAR(20) CHECK (estado_ruta IN ('activa', 'mantenimiento', 'inactiva')) DEFAULT 'activa'
);

-- Insertar 3 rutas predefinidas (según el documento)
INSERT INTO public.rutas (nombre_ruta, zona_sector, horario_estimado) VALUES
('Ruta David Este', 'David Este', '08:00 - 12:00'),
('Ruta David Centro', 'David Centro', '07:00 - 11:00'),
('Ruta Algarrobos', 'Algarrobos', '09:00 - 13:00')
ON CONFLICT (ruta_id) DO NOTHING;

-- ==========================================
-- 4. SUSCRIPCIONES
-- ==========================================
CREATE TABLE public.suscripciones (
    suscripcion_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE,
    ubicacion_id INT NOT NULL REFERENCES public.ubicaciones_servicio(ubicacion_id) ON DELETE CASCADE,
    ruta_id INT NOT NULL REFERENCES public.rutas(ruta_id),
    fecha_activacion DATE,
    proximo_vencimiento DATE,
    estado_pago VARCHAR(20) CHECK (estado_pago IN ('al_dia', 'moroso')) DEFAULT 'al_dia'
);

-- ==========================================
-- 5. HISTORIAL DE PAGOS
-- ==========================================
CREATE TABLE public.pagos (
    pago_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    suscripcion_id INT NOT NULL REFERENCES public.suscripciones(suscripcion_id) ON DELETE CASCADE,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    metodo_pago VARCHAR(50),
    comprobante_url VARCHAR(255)
);

-- ==========================================
-- 6. RASTREO DE CAMIONES
-- ==========================================
CREATE TABLE public.camiones_rastreo (
    camion_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    ruta_id INT NOT NULL REFERENCES public.rutas(ruta_id) ON DELETE CASCADE,
    placa_vehiculo VARCHAR(20) NOT NULL UNIQUE,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    ultima_actualizacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 7. SISTEMA DE NOTIFICACIONES
-- ==========================================
CREATE TABLE public.notificaciones (
    notificacion_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo_notificacion VARCHAR(20) CHECK (tipo_notificacion IN ('pago', 'ruta', 'sistema', 'incidencia')) DEFAULT 'sistema',
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 8. REPORTES E INCIDENCIAS
-- ==========================================
CREATE TABLE public.reportes_incidencias (
    reporte_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES public.usuarios(usuario_id) ON DELETE CASCADE,
    ubicacion_id INT NOT NULL REFERENCES public.ubicaciones_servicio(ubicacion_id) ON DELETE CASCADE,
    tipo_incidencia VARCHAR(30) CHECK (tipo_incidencia IN ('no_paso_camion', 'mala_atencion', 'desperdicio_en_via', 'otro')) NOT NULL,
    descripcion TEXT,
    estado_reporte VARCHAR(20) CHECK (estado_reporte IN ('abierto', 'en_proceso', 'resuelto', 'cerrado')) DEFAULT 'abierto',
    fecha_reporte TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- FUNCIONES Y TRIGGERS
-- ==========================================

-- TRIGGER 1: Sincronización Automática de Registro (CON ROL)
CREATE OR REPLACE FUNCTION public.fn_sincronizar_auth_usuario()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.usuarios (
        auth_id,
        nombre, 
        apellido, 
        cedula, 
        telefono,    
        direccion,   
        correo_electronico, 
        rol,
        estado_verificacion
    )
    VALUES (
        NEW.id, 
        COALESCE(NEW.raw_user_meta_data->>'nombre', 'Usuario'), 
        COALESCE(NEW.raw_user_meta_data->>'apellido', 'Nuevo'),   
        COALESCE(NEW.raw_user_meta_data->>'cedula', '0-000-0000'), 
        NEW.raw_user_meta_data->>'telefono', 
        SUBSTRING(COALESCE(NEW.raw_user_meta_data->>'direccion', '') FROM 1 FOR 50), 
        NEW.email,
        COALESCE(NEW.raw_user_meta_data->>'rol', 'Cliente'), -- <-- CLAVE: extrae el rol
        'pendiente'
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

DROP TRIGGER IF EXISTS tr_on_auth_user_created ON auth.users;
CREATE TRIGGER tr_on_auth_user_created
AFTER INSERT ON auth.users
FOR EACH ROW EXECUTE FUNCTION public.fn_sincronizar_auth_usuario();

-- TRIGGER 2: Asignación Automática de Ruta según Ubicación (usando PostGIS)
CREATE OR REPLACE FUNCTION public.fn_asignar_ruta_por_ubicacion()
RETURNS TRIGGER AS $$
DECLARE
    v_ruta_id INT;
BEGIN
    -- Encontrar la ruta más cercana a las coordenadas GPS del usuario
    -- (Asume que las rutas tienen un punto de referencia almacenado en alguna parte,
    --  por simplicidad, asigna la ruta 1 (David Centro) como predeterminada si no se encuentra)
    SELECT ruta_id INTO v_ruta_id FROM public.rutas WHERE ruta_id = 1;
    
    -- Insertar suscripción inicial con la ruta asignada
    INSERT INTO public.suscripciones (usuario_id, ubicacion_id, ruta_id, fecha_activacion, proximo_vencimiento, estado_pago)
    VALUES (
        NEW.usuario_id, 
        NEW.ubicacion_id, 
        COALESCE(v_ruta_id, 1), 
        CURRENT_DATE, 
        (CURRENT_DATE + INTERVAL '30 days'), 
        'moroso'
    );
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS tr_asignar_ruta_por_ubicacion ON public.ubicaciones_servicio;
CREATE TRIGGER tr_asignar_ruta_por_ubicacion
AFTER INSERT ON public.ubicaciones_servicio
FOR EACH ROW EXECUTE FUNCTION public.fn_asignar_ruta_por_ubicacion();

-- TRIGGER 3: Actualización de metadatos de Camión
CREATE OR REPLACE FUNCTION public.fn_alerta_proximidad_sach()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.latitud <> OLD.latitud OR NEW.longitud <> OLD.longitud THEN
        NEW.ultima_actualizacion = CURRENT_TIMESTAMP;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS tr_alerta_proximidad_sach ON public.camiones_rastreo;
CREATE TRIGGER tr_alerta_proximidad_sach
BEFORE UPDATE ON public.camiones_rastreo
FOR EACH ROW EXECUTE FUNCTION public.fn_alerta_proximidad_sach();

-- ==========================================
-- PROCEDIMIENTOS ALMACENADOS
-- ==========================================

-- PROCEDIMIENTO: Procesar Pagos
CREATE OR REPLACE PROCEDURE public.sp_procesar_pago_sach(
    p_suscripcion_id INT,
    p_monto DECIMAL(10,2),
    p_metodo VARCHAR(50)
)
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO public.pagos (suscripcion_id, monto, metodo_pago)
    VALUES (p_suscripcion_id, p_monto, p_metodo);

    UPDATE public.suscripciones 
    SET proximo_vencimiento = (proximo_vencimiento + INTERVAL '30 days'),
        estado_pago = 'al_dia'
    WHERE suscripcion_id = p_suscripcion_id;
END;
$$;

-- ==========================================
-- VISTAS
-- ==========================================

-- VISTA: Paz y Salvo Financiero
CREATE OR REPLACE VIEW public.vista_paz_y_salvo_usuarios AS
SELECT 
    u.cedula,
    (u.nombre || ' ' || u.apellido) AS cliente,
    s.proximo_vencimiento,
    CASE 
        WHEN s.proximo_vencimiento >= CURRENT_DATE THEN 'PAZ Y SALVO'
        ELSE 'EN MORA'
    END AS estado_financiero,
    (s.proximo_vencimiento - CURRENT_DATE) AS dias_para_vencimiento
FROM public.usuarios u
JOIN public.suscripciones s ON u.usuario_id = s.usuario_id;