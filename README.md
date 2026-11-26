# JANZU · Headwear Collection

Tienda en línea de gorras en PHP y MySQL, sin frameworks externos. Preparada para Laragon, Apache, MySQL/MariaDB y HeidiSQL.

## Requisitos

- PHP 8.0 o superior, con `PDO MySQL`, `mbstring`, `fileinfo`, sesiones y JSON habilitados.
- MySQL 5.7+ o MariaDB 10.3+ con tablas InnoDB.
- Apache incluido con Laragon.

## Instalación nueva en Laragon

1. Descomprime la carpeta `janzu` en `C:\laragon\www\janzu`.
2. Abre Laragon y selecciona **Iniciar todo**.
3. En HeidiSQL, ejecuta `database/janzu.sql`. El archivo crea la base de datos y agrega el catálogo de muestra. No contiene `DROP TABLE`.
4. Abre `http://janzu.test` o `http://localhost/janzu`.
5. Crea tu cuenta desde **Entrar → Crear una cuenta**.
6. Para habilitar esa cuenta como administradora, selecciona la base `janzu` en HeidiSQL y ejecuta:

   ```sql
   UPDATE usuarios SET rol = 'admin' WHERE email = 'tu-correo@ejemplo.com';
   ```

7. Cierra sesión y vuelve a entrar para abrir **Panel** en la navegación.

No se incluye una contraseña universal de administrador. Usa una cuenta propia y una contraseña fuerte.

## Actualizar una base existente sin perder datos

1. En HeidiSQL, exporta primero la base `janzu` como respaldo SQL.
2. Ejecuta `database/migrations/001_catalogo_y_consultas.sql`.
3. Ejecuta `database/migrations/002_textos_catalogo.sql`.
4. Copia la versión actualizada del proyecto a Laragon. No vuelvas a importar el SQL de instalación sobre tu base existente.

La primera migración añade la columna de fotos y algunos índices solo si faltan. La segunda amplía las descripciones de los productos de muestra únicamente cuando aún tienen el texto original; también asigna dos fotos locales si coinciden los nombres. No elimina tablas ni registros.

Si tu base ya tenía cuentas de muestra, entra a **Mi cuenta → Cambiar contraseña** y actualiza la contraseña antes de presentar el proyecto.

## Conexión a MySQL

La configuración local por defecto apunta a `127.0.0.1`, base `janzu`, usuario `root` y contraseña vacía, que es la configuración habitual de una instalación local de Laragon. Si tu MySQL utiliza otros datos, puedes definir las variables `JANZU_DB_HOST`, `JANZU_DB_NAME`, `JANZU_DB_USER` y `JANZU_DB_PASS`, o crear `config/database.local.php` a partir de `config/database.local.example.php`.

El archivo local de configuración está excluido de Git. No subas contraseñas ni credenciales a un repositorio.

## Qué incluye

- Identidad JANZU en negro `#151515`, marfil `#F7F6F2`, arena `#D8D0C4`, gris `#747474` y blanco.
- Inicio editorial, colecciones por categoría y páginas de producto con descripciones ampliadas.
- Catálogo con búsqueda, categoría, rango de precio, disponibilidad, orden y paginación.
- Bolsa lateral y contador de unidades. Añadir una gorra mantiene al cliente en la página actual.
- Cantidades, subtotales, total, ajuste de inventario y creación transaccional de pedidos.
- Registro, acceso, perfil, cambio de contraseña e historial de pedidos.
- Panel de administración con indicadores, productos, fotos, inventario, ventas, clientes, pedidos y mensajes.
- Formularios con token CSRF, consultas PDO preparadas, escape HTML, control por rol y sesión endurecida.
- Cancelar un pedido devuelve las unidades al inventario; reabrirlo vuelve a comprobar existencias.
- Diseño adaptable a celular, tableta y escritorio; navegación por teclado y soporte de movimiento reducido.

## Imágenes de productos

Desde el panel se pueden subir imágenes JPG, PNG o WEBP de hasta 2 MB. El sistema verifica el tipo y el tamaño real del archivo. Las piezas sin foto usan una ilustración neutra para que el catálogo no se rompa.

## Pruebas

Abre una terminal de Laragon en la carpeta del proyecto y ejecuta:

```powershell
php tests/smoke.php
```

El chequeo valida funciones de salida, precios, rutas, formularios CSRF y saneamiento básico del carrito. Después sigue los flujos de la lista `tests/acceptance-checklist.md` con Apache y MySQL activos.

## Alcance del pago y el envío

El sitio registra pedidos y reserva el inventario. No se conectó una pasarela de pago ni un operador logístico; el pago y el envío se coordinan con el cliente antes de despachar.

## Estructura

```text
admin/                 panel de administración
assets/css, assets/js  estilos e interacción del sitio
assets/img             logotipo auxiliar y fotografías
auth/                  registro, acceso y salida
cliente/               perfil, contraseña e historial
config/                conexión MySQL local
database/              instalación y migraciones SQL
includes/              componentes, sesión y funciones comunes
tests/                 chequeos y lista de aceptación
```
