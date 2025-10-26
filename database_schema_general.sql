-- Schema SQL para AdoptMe - Sprint 1
-- Base de datos: adoptme

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni CHAR(8) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefono CHAR(9) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    distrito VARCHAR(100),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_dni (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de preferencias de usuario (HU-003)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    especie_preferida VARCHAR(50),
    tamano_preferido VARCHAR(50),
    edad_preferida VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pref (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de códigos de verificación (HU-001 CA-002)
CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones (HU-002 TR-005)
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token TEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_session (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mascotas (para futuras funcionalidades)
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100),
    edad INT,
    tamano VARCHAR(50),
    sexo ENUM('Macho', 'Hembra'),
    descripcion TEXT,
    foto_url VARCHAR(255),
    estado ENUM('Disponible', 'En proceso', 'Adoptado') DEFAULT 'Disponible',
    refugio_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_especie (especie),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de adopciones
CREATE TABLE IF NOT EXISTS adoptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_adoptions (user_id),
    INDEX idx_pet_adoptions (pet_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mensajes (chat)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_conversation (sender_id, receiver_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo para desarrollo (opcional)
-- INSERT INTO pets (name, especie, raza, edad, tamano, sexo, descripcion, foto_url) VALUES
-- ('Luna', 'Perro', 'Mestizo', 2, 'Mediano', 'Hembra', 'Perrita muy cariñosa y juguetona', 'https://example.com/luna.jpg'),
-- ('Michi', 'Gato', 'Siamés', 1, 'Pequeño', 'Macho', 'Gatito tranquilo y dormilón', 'https://example.com/michi.jpg');
-- Schema SQL para AdoptMe - Sprint 1
-- Base de datos: adoptme

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni CHAR(8) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefono CHAR(9) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    distrito VARCHAR(100),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_dni (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de preferencias de usuario (HU-003)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    especie_preferida VARCHAR(50),
    tamano_preferido VARCHAR(50),
    edad_preferida VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pref (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de códigos de verificación (HU-001 CA-002)
CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones (HU-002 TR-005)
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token TEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_session (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mascotas (para futuras funcionalidades)
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100),
    edad INT,
    tamano VARCHAR(50),
    sexo ENUM('Macho', 'Hembra'),
    descripcion TEXT,
    foto_url VARCHAR(255),
    estado ENUM('Disponible', 'En proceso', 'Adoptado') DEFAULT 'Disponible',
    refugio_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_especie (especie),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de adopciones
CREATE TABLE IF NOT EXISTS adoptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_adoptions (user_id),
    INDEX idx_pet_adoptions (pet_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mensajes (chat)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_conversation (sender_id, receiver_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo para desarrollo (opcional)
-- INSERT INTO pets (name, especie, raza, edad, tamano, sexo, descripcion, foto_url) VALUES
-- ('Luna', 'Perro', 'Mestizo', 2, 'Mediano', 'Hembra', 'Perrita muy cariñosa y juguetona', 'https://example.com/luna.jpg'),
-- ('Michi', 'Gato', 'Siamés', 1, 'Pequeño', 'Macho', 'Gatito tranquilo y dormilón', 'https://example.com/michi.jpg');

-- Actualización de la base de datos para Sprint 2 - HU-004: Catálogo de Mascotas
-- Ejecutar después de database_schema.sql

-- Agregar columnas necesarias para el catálogo (HU-004)
ALTER TABLE pets 
ADD COLUMN IF NOT EXISTS is_urgent TINYINT(1) DEFAULT 0 COMMENT 'Mascotas urgentes aparecen primero',
ADD COLUMN IF NOT EXISTS priority INT DEFAULT 0 COMMENT 'Prioridad para ordenamiento (mayor = más urgente)',
ADD COLUMN IF NOT EXISTS distrito VARCHAR(100) COMMENT 'Ubicación de la mascota',
ADD INDEX IF NOT EXISTS idx_priority (priority DESC, created_at DESC),
ADD INDEX IF NOT EXISTS idx_urgent (is_urgent, priority DESC);

-- Datos de ejemplo para desarrollo - Sprint 2 (HU-004)
-- Mascotas con diferentes características para probar filtros y paginación

INSERT INTO pets (name, especie, raza, edad, tamano, sexo, descripcion, foto_url, distrito, is_urgent, priority, estado) VALUES
-- Mascotas URGENTES (CA-002: aparecen primero)
('Luna', 'Perro', 'Mestizo', 2, 'Mediano', 'Hembra', 
 'Perrita muy cariñosa y juguetona. Rescatada de las calles, necesita hogar urgente.', 
 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400', 
 'San Miguel', 1, 100, 'Disponible'),

('Rocky', 'Perro', 'Labrador', 5, 'Grande', 'Macho', 
 'Perro adulto muy tranquilo y obediente. Busca familia urgentemente.', 
 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400', 
 'Surco', 1, 95, 'Disponible'),

('Michi', 'Gato', 'Siamés', 1, 'Pequeño', 'Macho', 
 'Gatito muy juguetón y cariñoso. Necesita adopción urgente por mudanza.', 
 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 
 'Miraflores', 1, 90, 'Disponible'),

-- Mascotas DISPONIBLES (sin urgencia)
('Bella', 'Perro', 'Golden Retriever', 3, 'Grande', 'Hembra', 
 'Perra muy amigable con niños. Le encanta jugar en el parque.', 
 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400', 
 'La Molina', 0, 50, 'Disponible'),

('Max', 'Perro', 'Bulldog Francés', 4, 'Pequeño', 'Macho', 
 'Perro tranquilo ideal para departamentos. Muy compañero.', 
 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=400', 
 'San Isidro', 0, 45, 'Disponible'),

('Pelusa', 'Gato', 'Persa', 2, 'Pequeño', 'Hembra', 
 'Gatita muy dulce y tranquila. Le gusta dormir y que la mimen.', 
 'https://images.unsplash.com/photo-1543852786-1cf6624b9987?w=400', 
 'Jesús María', 0, 40, 'Disponible'),

('Toby', 'Perro', 'Beagle', 1, 'Mediano', 'Macho', 
 'Cachorro muy activo y curioso. Necesita familia con tiempo para jugar.', 
 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?w=400', 
 'Pueblo Libre', 0, 35, 'Disponible'),

('Nala', 'Gato', 'Mestizo', 6, 'Pequeño', 'Hembra', 
 'Gata adulta muy independiente pero cariñosa. Ideal para personas tranquilas.', 
 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400', 
 'Lince', 0, 30, 'Disponible'),

('Choco', 'Perro', 'Pastor Alemán', 3, 'Grande', 'Macho', 
 'Perro guardián leal y protector. Necesita espacio amplio.', 
 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400', 
 'Comas', 0, 25, 'Disponible'),

('Simba', 'Gato', 'Naranja Tabby', 1, 'Pequeño', 'Macho', 
 'Gatito juguetón y aventurero. Le encanta explorar.', 
 'https://images.unsplash.com/photo-1615789591457-74a63395c990?w=400', 
 'San Borja', 0, 20, 'Disponible'),

-- Más mascotas para probar paginación (CA-003: mínimo 6 resultados)
('Laika', 'Perro', 'Husky Siberiano', 2, 'Grande', 'Hembra', 
 'Perra muy activa que ama correr. Necesita dueño deportista.', 
 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?w=400', 
 'Los Olivos', 0, 15, 'Disponible'),

('Garfield', 'Gato', 'Naranja Pelo Corto', 4, 'Mediano', 'Macho', 
 'Gato tranquilo que ama comer y dormir. Muy cariñoso.', 
 'https://images.unsplash.com/photo-1529257414772-1960b7bea4eb?w=400', 
 'Magdalena', 0, 10, 'Disponible');

-- Verificación de datos insertados
-- SELECT COUNT(*) as total_mascotas FROM pets WHERE estado = 'Disponible';
-- SELECT * FROM pets WHERE is_urgent = 1 ORDER BY priority DESC;
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


-- Sprint 3: Agregar roles de usuario y tabla de solicitudes de adopción
-- Ejecutar en orden

-- FASE 1: Agregar columna role a users
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' 
AFTER is_verified;

-- Crear usuario administrador por defecto
-- Email: admin@adoptme.com
-- Password: Admin123!
INSERT INTO users (nombres, apellidos, dni, email, telefono, password_hash, role, is_verified) 
VALUES (
    'Admin', 
    'Sistema', 
    '00000001', 
    'admin@adoptme.com', 
    '999999999', 
    '$2y$10$YourHashedPasswordHere', 
    'admin', 
    1
) ON DUPLICATE KEY UPDATE role = 'admin', is_verified = 1;

-- Si ya existe un usuario, convertirlo en admin (opcional)
-- UPDATE users SET role = 'admin', is_verified = 1 WHERE id = 1;

-- Verificar que se agregó correctamente
SELECT id, nombres, email, role FROM users WHERE role = 'admin';
