-- Script de creación y actualización de base de datos para Smartsach (PostgreSQL) - Refactorizado a Zonas y Noticias

-- Eliminar tablas si existen para reiniciar limpiamente en orden de dependencias
DROP TABLE IF EXISTS comentarios_reportes CASCADE;
DROP TABLE IF EXISTS reportes CASCADE;
DROP TABLE IF EXISTS pagos CASCADE;
DROP TABLE IF EXISTS rutas CASCADE;
DROP TABLE IF EXISTS noticias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS zonas CASCADE;

-- 1. Tabla de Zonas de Recolección
CREATE TABLE zonas (
    id SERIAL PRIMARY KEY,
    nombre_zona VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado VARCHAR(20) DEFAULT 'inactiva' CHECK (estado IN ('inactiva', 'en_ruta', 'finalizada'))
);

-- 2. Tabla de Usuarios (Soporte para roles y asignación de zona para conductores)
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'cliente' CHECK (rol IN ('cliente', 'gestor', 'conductor')),
    zona_id INT DEFAULT NULL, -- Zona asignada si es conductor
    foto_perfil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_zona FOREIGN KEY (zona_id) REFERENCES zonas(id) ON DELETE SET NULL
);

-- 3. Tabla de Rutas / Direcciones de Clientes (Casas asociadas a una zona de recolección)
CREATE TABLE rutas (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    zona_id INT DEFAULT NULL, -- Zona a la que pertenece la casa del cliente
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    costo DECIMAL(10, 2) DEFAULT 15.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rutas FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_zona_rutas FOREIGN KEY (zona_id) REFERENCES zonas(id) ON DELETE SET NULL
);

-- 4. Tabla de Pagos
CREATE TABLE pagos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pagado', 'Pendiente')),
    fecha_pago TIMESTAMP DEFAULT NULL,
    referencia VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_pagos FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 5. Tabla de Reportes (Con control de lectura para el gestor)
CREATE TABLE reportes (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta_id INT,
    descripcion TEXT NOT NULL,
    foto_url VARCHAR(255) DEFAULT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'En Proceso', 'Resuelto')),
    visto_gestor BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_reportes FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ruta_reportes FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE SET NULL
);

-- 6. Tabla de Comentarios de Reportes (Hilos de conversación)
CREATE TABLE comentarios_reportes (
    id SERIAL PRIMARY KEY,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_comentarios FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    CONSTRAINT fk_usuario_comentarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 7. Tabla de Noticias (Blog / Novedades de Reciclaje)
CREATE TABLE noticias (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    autor_id INT DEFAULT NULL,
    CONSTRAINT fk_autor_noticia FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Índices de Base de Datos
CREATE INDEX idx_usuarios_zona ON usuarios(zona_id);
CREATE INDEX idx_rutas_zona ON rutas(zona_id);
CREATE INDEX idx_pagos_usuario ON pagos(usuario_id);
CREATE INDEX idx_reportes_usuario ON reportes(usuario_id);
CREATE INDEX idx_comentarios_reporte ON comentarios_reportes(reporte_id);
CREATE INDEX idx_noticias_fecha ON noticias(fecha_publicacion DESC);

-- ==========================================
-- INSERTAR DATOS INICIALES DE PRUEBA
-- Contraseña por defecto para todos: '123456'
-- password_hash BCRYPT: '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq'
-- ==========================================

-- Zonas iniciales
INSERT INTO zonas (nombre_zona, descripcion, estado) VALUES
('David Centro', 'Casco viejo de David, zona comercial y residencial densa', 'inactiva'),
('David Sur', 'Barriadas del sector sur, San Cristóbal y alrededores', 'inactiva'),
('Boquete Centro', 'Bajo Boquete, zona residencial y turística', 'inactiva');

-- Usuarios por rol (conductores vinculados a zonas)
INSERT INTO usuarios (nombre, email, password, rol, zona_id) VALUES
('Fabian Cliente', 'cliente@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'cliente', NULL),
('Fabian Gestor', 'gestor@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'gestor', NULL),
('Pedro Conductor David', 'conductor1@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'conductor', 1), -- David Centro
('Juan Conductor Sur', 'conductor2@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'conductor', 2); -- David Sur

-- Clientes secundarios con casas en David Centro para multipunto (Pedro)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Vecino David 1', 'vecino1@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'cliente'),
('Vecino David 2', 'vecino2@smartsach.com', '$2y$10$USk.QCJibNQW/O4cnW7meeHVTtw68qCnjAHo7ZAyHj5c.WT7xhuKq', 'cliente');

-- Viviendas en David Centro (Zona ID: 1)
-- Coordenadas cercanas en David Centro para simular paradas reales
INSERT INTO rutas (usuario_id, zona_id, nombre, descripcion, latitud, longitud, costo) VALUES
(1, 1, 'Casa Principal David', 'Frente al Parque Miguel de Cervantes Saavedra', 8.428670, -82.428750, 15.00), -- Paz y salvo (Pago pendiente de 40.00 abajo)
(5, 1, 'Casa Vecino 1', 'Calle C Norte, David', 8.431200, -82.429500, 15.00),
(6, 1, 'Casa Vecino 2', 'Avenida 3era Este, David', 8.427100, -82.425100, 15.00);

-- Pagos de prueba
INSERT INTO pagos (usuario_id, monto, estado, fecha_pago, referencia) VALUES
(1, 15.00, 'Pagado', '2026-06-01 08:30:00', 'REF-109283'),
(5, 15.00, 'Pagado', '2026-06-01 09:30:00', 'REF-109284'); -- Vecino 1 Paz y Salvo

-- Vecino 2 está Moroso (tiene una deuda pendiente de 15.00)
INSERT INTO pagos (usuario_id, monto, estado) VALUES
(6, 15.00, 'Pendiente');

-- Noticias iniciales
INSERT INTO noticias (titulo, contenido, autor_id) VALUES
('Horarios de Lluvia en Chiriquí', 'Debido a la temporada lluviosa, se solicita a los ciudadanos asegurar bien los contenedores para evitar derrames y contaminación.', 2),
('Manual del Buen Reciclador', 'Separe sus botellas plásticas transparentes (PET-1) del cartón corrugado. Ambos materiales deben entregarse limpios y aplastados.', 2);
