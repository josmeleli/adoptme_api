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
