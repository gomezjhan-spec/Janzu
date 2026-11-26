-- JANZU · Mejora los textos originales de muestra sin tocar textos ya editados.
-- También asocia dos fotos locales cuando coinciden los nombres de la muestra original.
USE janzu;

UPDATE productos
SET descripcion = 'Silueta de visera curva en gabardina de algodón con un tono índigo fácil de combinar. Su cierre posterior regulable permite un ajuste cómodo para acompañarte en recorridos, planes al aire libre y días de sol.'
WHERE nombre = 'Clásica Índigo'
  AND descripcion = 'Gorra de visera curva en gabardina de algodón. Cierre ajustable en la parte trasera.';

UPDATE productos
SET descripcion = 'Una pieza de visera plana y corona estructurada para sumar un gesto urbano a tus combinaciones. El ajuste tipo snapback facilita graduarla y llevarla cómoda durante el día.'
WHERE nombre = 'Urbana Negra'
  AND descripcion = 'Visera plana, ajuste snapback y corona estructurada. Combina con todo.';

UPDATE productos
SET descripcion = 'Diseñada para los días activos, combina una estructura ligera con paneles que permiten mayor circulación de aire. La banda interior ayuda a mantener una sensación cómoda durante caminatas y actividades cotidianas.'
WHERE nombre = 'Deportiva Aire'
  AND descripcion = 'Tela liviana y transpirable con banda interna que absorbe el sudor.';

UPDATE productos
SET descripcion = 'El frente estructurado conserva la forma y los paneles posteriores de malla favorecen la ventilación. Su tono arena aporta un acabado sobrio que funciona con prendas claras y oscuras.'
WHERE nombre = 'Trucker Arena'
  AND descripcion = 'Frente firme y malla trasera para días de mucho calor.';

UPDATE productos
SET descripcion = 'Una silueta llamativa en fucsia con bordado frontal y ajuste regulable. El acabado aporta un punto de color a combinaciones sencillas y está pensado para destacar sin perder comodidad.'
WHERE nombre = 'Fucsia Neón'
  AND descripcion = 'Color intenso con bordado frontal. Para destacar en cualquier plan.';

UPDATE productos
SET descripcion = 'Visera plana, tono morado profundo y costuras reforzadas para una pieza urbana que mantiene su forma. El cierre ajustable permite adaptarla a diferentes preferencias de uso.'
WHERE nombre = 'Morada Street'
  AND descripcion = 'Visera plana en tono morado, con costuras reforzadas.';

UPDATE productos
SET descripcion = 'Una gorra ligera de cinco paneles con un tono verde lima que aporta energía a tu conjunto. Su correa posterior permite graduar el ajuste para llevarla durante tus planes diarios.'
WHERE nombre = 'Verde Lima'
  AND descripcion = 'Gorra de 5 paneles, ligera y con ajuste de correa.';

UPDATE productos
SET descripcion = 'Pensada para acompañar a los más pequeños, combina algodón suave, visera flexible y cierre de velcro para ajustar con facilidad. Una opción práctica para paseos, juegos y días al aire libre.'
WHERE nombre = 'Mini Sol'
  AND descripcion = 'Algodón suave, visera flexible y cierre de velcro fácil de ajustar.';

UPDATE productos
SET descripcion = 'Una pieza infantil de tela fresca y tacto ligero, creada para sumar sombra y comodidad durante las actividades al aire libre. Su sistema regulable facilita encontrar un ajuste apropiado.'
WHERE nombre = 'Mini Cielo'
  AND descripcion = 'Con protección solar y tela fresca para jugar al aire libre.';

UPDATE productos
SET descripcion = 'Tamaño infantil, tono coral y costuras interiores suaves para ayudar a evitar roces. Una gorra cómoda y sencilla de ajustar para acompañar las salidas y los juegos de cada día.'
WHERE nombre = 'Mini Coral'
  AND descripcion = 'Tamaño infantil, costuras sin relieve para que no incomode.';

UPDATE productos
SET imagen = 'la-azul.jpg'
WHERE nombre = 'Clásica Índigo' AND (imagen IS NULL OR imagen = '');

UPDATE productos
SET imagen = 'la-negra.jpg'
WHERE nombre = 'Urbana Negra' AND (imagen IS NULL OR imagen = '');
