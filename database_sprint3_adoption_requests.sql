-- FASE 2: Crear tabla de solicitudes de adopción (adoption_requests)
-- Sprint 3 - HU-005

CREATE TABLE IF NOT EXISTS adoption_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    
    -- Pantalla 1: Información Personal
    nombres_completos VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono CHAR(9) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    direccion_completa TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    distrito VARCHAR(100) NOT NULL,
    
    -- Pantalla 2: Información del Hogar
    tipo_vivienda VARCHAR(50) NOT NULL COMMENT 'Propia, Alquilada, Familiar',
    propietario_acepta_mascotas VARCHAR(20) NOT NULL COMMENT 'Sí, No, Soy propietario',
    miembros_familia INT NOT NULL,
    hay_ninos VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    alergias_familia VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    
    -- Pantalla 3: Experiencia con Mascotas
    tiene_otras_mascotas VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    descripcion_otras_mascotas TEXT NULL,
    experiencia_previa TEXT NOT NULL,
    tiempo_sola_mascota VARCHAR(100) NOT NULL,
    tiene_veterinario VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    presupuesto_mensual VARCHAR(100) NOT NULL,
    
    -- Pantalla 4: Motivación para Adoptar
    motivacion_adopcion TEXT NOT NULL,
    conocimiento_raza TEXT NOT NULL,
    dispuesto_entrenar VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    compromiso_largo_plazo VARCHAR(2) NOT NULL COMMENT 'Sí, No',
    
    -- Estado y seguimiento
    status ENUM('pendiente', 'en_revision', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    notas_admin TEXT NULL COMMENT 'Notas del administrador sobre la solicitud',
    fecha_revision TIMESTAMP NULL,
    revisado_por INT NULL COMMENT 'ID del admin que revisó',
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (revisado_por) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Índices para optimizar consultas
    INDEX idx_user_pet (user_id, pet_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC),
    INDEX idx_pet_status (pet_id, status)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Solicitudes de adopción - Sprint 3 HU-005';

-- Verificar que se creó correctamente
DESCRIBE adoption_requests;
