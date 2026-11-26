-- JANZU · Esquema aditivo. Ejecutar en la base janzu con HeidiSQL.
-- Puede ejecutarse más de una vez; no elimina tablas ni registros.
USE janzu;

SET @schema_name = DATABASE();
SET @has_imagen = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'imagen'
);
SET @sql = IF(@has_imagen = 0,
    'ALTER TABLE productos ADD COLUMN imagen VARCHAR(120) NULL AFTER activo',
    'SELECT 1'
);
PREPARE janzu_stmt FROM @sql;
EXECUTE janzu_stmt;
DEALLOCATE PREPARE janzu_stmt;

SET @has_product_index = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'productos' AND INDEX_NAME = 'idx_productos_activo_categoria'
);
SET @sql = IF(@has_product_index = 0,
    'CREATE INDEX idx_productos_activo_categoria ON productos (activo, categoria_id, id)',
    'SELECT 1'
);
PREPARE janzu_stmt FROM @sql;
EXECUTE janzu_stmt;
DEALLOCATE PREPARE janzu_stmt;

SET @has_order_index = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'pedidos' AND INDEX_NAME = 'idx_pedidos_estado_creado'
);
SET @sql = IF(@has_order_index = 0,
    'CREATE INDEX idx_pedidos_estado_creado ON pedidos (estado, creado)',
    'SELECT 1'
);
PREPARE janzu_stmt FROM @sql;
EXECUTE janzu_stmt;
DEALLOCATE PREPARE janzu_stmt;

SET @has_user_order_index = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'pedidos' AND INDEX_NAME = 'idx_pedidos_usuario_creado'
);
SET @sql = IF(@has_user_order_index = 0,
    'CREATE INDEX idx_pedidos_usuario_creado ON pedidos (usuario_id, creado)',
    'SELECT 1'
);
PREPARE janzu_stmt FROM @sql;
EXECUTE janzu_stmt;
DEALLOCATE PREPARE janzu_stmt;
