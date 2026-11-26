# Lista de aceptación JANZU

Prueba en una copia de la base de datos, con Apache y MySQL activos. Anota resultado y fecha antes de la presentación.

## Tienda y bolsa

- [ ] Inicio, navegación, fotos y pie cargan sin avisos de PHP.
- [ ] En celular, el menú abre y cierra; las páginas no requieren desplazamiento horizontal.
- [ ] Búsqueda, categoría, precios, disponibilidad, orden y paginación muestran resultados coherentes.
- [ ] Añadir desde una tarjeta no cambia de página; el contador aumenta por unidades.
- [ ] Añadir varias unidades desde el detalle respeta el inventario.
- [ ] La bolsa lateral y la página de bolsa muestran cantidades, precios por unidad, subtotales y total correctos.
- [ ] Cambiar o quitar una unidad actualiza el estado; cantidades mayores al inventario se limitan.
- [ ] Con la sesión cerrada se puede llenar la bolsa, pero se pide iniciar sesión para crear el pedido.
- [ ] Una dirección corta, un teléfono inválido, un token vencido o un producto agotado no crean el pedido.
- [ ] Un pedido válido aparece en la cuenta y en administración; el inventario disminuye exactamente por las unidades pedidas.

## Cuenta y administración

- [ ] Registro valida correo, teléfono y contraseña; un correo repetido se rechaza.
- [ ] Inicio de sesión correcto e incorrecto devuelve mensajes claros; salir requiere el formulario protegido.
- [ ] Perfil y cambio de contraseña requieren CSRF y actualizan la cuenta correcta.
- [ ] El panel solo abre para una cuenta con rol `admin`.
- [ ] El panel muestra conteos y ventas; productos permiten crear, editar, destacar, ocultar y volver a mostrar.
- [ ] La carga admite JPG, PNG y WEBP válidos de hasta 2 MB; otros formatos se rechazan.
- [ ] Cancelar un pedido pendiente o confirmado devuelve las unidades al inventario una sola vez. Un pedido enviado o entregado no se puede cancelar desde el panel.
- [ ] Reabrir un pedido cancelado vuelve a comprobar el inventario y no permite stock negativo.
- [ ] Las cuentas de cliente, pedidos y mensajes muestran los registros esperados; un mensaje puede marcarse como leído.

## Compatibilidad y presentación

- [ ] Con una copia de respaldo, las dos migraciones se ejecutan sin borrar usuarios, productos, pedidos ni mensajes.
- [ ] Ejecutar de nuevo una migración no duplica cambios ni altera descripciones personalizadas.
- [ ] `database/janzu.sql` sirve para una instalación nueva y no contiene `DROP TABLE`.
- [ ] Imágenes, formularios, navegación y diálogos funcionan con teclado y con movimiento reducido.
- [ ] Cambia cualquier contraseña de demostración que ya exista antes de mostrar la tienda en una red compartida.
