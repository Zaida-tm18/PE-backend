-- Esquema de base de datos: Sistema de Gestión Veterinaria (PostgreSQL)
-- Se ejecuta automáticamente al iniciar el contenedor de Postgres (docker-entrypoint-initdb.d)

CREATE TYPE rol_usuario AS ENUM ('admin', 'veterinario', 'recepcionista');

CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol rol_usuario NOT NULL DEFAULT 'recepcionista',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mascotas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100) DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    propietario_nombre VARCHAR(150) NOT NULL,
    propietario_telefono VARCHAR(20) DEFAULT NULL,
    usuario_id INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mascota_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices útiles para búsquedas frecuentes
CREATE INDEX idx_mascotas_especie ON mascotas(especie);
CREATE INDEX idx_mascotas_propietario ON mascotas(propietario_nombre);

-- Postgres no tiene "ON UPDATE CURRENT_TIMESTAMP" como MySQL, así que se
-- emula con un trigger que actualiza actualizado_en en cada UPDATE.
CREATE OR REPLACE FUNCTION actualizar_timestamp_mascota()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_mascotas_actualizado_en
BEFORE UPDATE ON mascotas
FOR EACH ROW
EXECUTE FUNCTION actualizar_timestamp_mascota();
