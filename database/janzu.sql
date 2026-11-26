-- JANZU · Instalación nueva para Laragon / MySQL / HeidiSQL.
-- No contiene DROP TABLE. Los registros existentes no se borran ni se reemplazan.
CREATE DATABASE IF NOT EXISTS janzu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE janzu;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    direccion VARCHAR(200) NULL,
    rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    creado TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL,
    descripcion VARCHAR(200) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT NOT NULL AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,0) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    color VARCHAR(7) NOT NULL DEFAULT '#d8d0c4',
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    imagen VARCHAR(120) NULL,
    creado TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_productos_activo_categoria (activo, categoria_id, id),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    total DECIMAL(10,0) NOT NULL,
    estado ENUM('pendiente','confirmado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    direccion VARCHAR(200) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    creado TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pedidos_estado_creado (estado, creado),
    KEY idx_pedidos_usuario_creado (usuario_id, creado),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_items (
    id INT NOT NULL AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,0) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pedido_items_pedido (pedido_id),
    KEY idx_pedido_items_producto (producto_id),
    CONSTRAINT fk_pedido_items_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
    CONSTRAINT fk_pedido_items_producto FOREIGN KEY (producto_id) REFERENCES productos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    asunto VARCHAR(120) NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    creado TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mensajes_leido_creado (leido, creado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Los INSERT IGNORE agregan las piezas de muestra una sola vez y respetan IDs existentes.
INSERT IGNORE INTO categorias (id, nombre, slug, descripcion) VALUES
    (1, 'Adultos', 'adultos', 'Siluetas clásicas, urbanas y deportivas para todos los días.'),
    (2, 'Jóvenes', 'jovenes', 'Diseños con carácter para quienes marcan su propio ritmo.'),
    (3, 'Niños', 'ninos', 'Piezas cómodas y fáciles de ajustar para los más pequeños.');

INSERT IGNORE INTO productos (id, categoria_id, nombre, descripcion, precio, stock, color, destacado, activo, imagen) VALUES
    (1, 1, 'Clásica Índigo', 'Silueta de visera curva en gabardina de algodón con un tono índigo fácil de combinar. Su cierre posterior regulable permite un ajuste cómodo para acompañarte en recorridos, planes al aire libre y días de sol.', 45000, 29, '#1e3a8a', 1, 1, 'la-azul.jpg'),
    (2, 1, 'Urbana Negra', 'Una pieza de visera plana y corona estructurada para sumar un gesto urbano a tus combinaciones. El ajuste tipo snapback facilita graduarla y llevarla cómoda durante el día.', 52000, 25, '#111827', 1, 1, 'la-negra.jpg'),
    (3, 1, 'Deportiva Aire', 'Diseñada para los días activos, combina una estructura ligera con paneles que permiten mayor circulación de aire. La banda interior ayuda a mantener una sensación cómoda durante caminatas y actividades cotidianas.', 48000, 40, '#0ea5e9', 0, 1, NULL),
    (4, 1, 'Trucker Arena', 'El frente estructurado conserva la forma y los paneles posteriores de malla favorecen la ventilación. Su tono arena aporta un acabado sobrio que funciona con prendas claras y oscuras.', 44000, 22, '#d6b98c', 0, 1, NULL),
    (5, 2, 'Fucsia Neón', 'Una silueta llamativa en fucsia con bordado frontal y ajuste regulable. El acabado aporta un punto de color a combinaciones sencillas y está pensado para destacar sin perder comodidad.', 49000, 18, '#e91e8c', 1, 1, NULL),
    (6, 2, 'Morada Street', 'Visera plana, tono morado profundo y costuras reforzadas para una pieza urbana que mantiene su forma. El cierre ajustable permite adaptarla a diferentes preferencias de uso.', 51000, 20, '#7c3aed', 1, 1, NULL),
    (7, 2, 'Verde Lima', 'Una gorra ligera de cinco paneles con un tono verde lima que aporta energía a tu conjunto. Su correa posterior permite graduar el ajuste para llevarla durante tus planes diarios.', 46000, 15, '#84cc16', 0, 1, NULL),
    (8, 3, 'Mini Sol', 'Pensada para acompañar a los más pequeños, combina algodón suave, visera flexible y cierre de velcro para ajustar con facilidad. Una opción práctica para paseos, juegos y días al aire libre.', 32000, 35, '#f59e0b', 1, 1, NULL),
    (9, 3, 'Mini Cielo', 'Una pieza infantil de tela fresca y tacto ligero, creada para sumar sombra y comodidad durante las actividades al aire libre. Su sistema regulable facilita encontrar un ajuste apropiado.', 33000, 28, '#38bdf8', 0, 1, NULL),
    (10, 3, 'Mini Coral', 'Tamaño infantil, tono coral y costuras interiores suaves para ayudar a evitar roces. Una gorra cómoda y sencilla de ajustar para acompañar las salidas y los juegos de cada día.', 31000, 24, '#fb7185', 0, 1, NULL);
