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