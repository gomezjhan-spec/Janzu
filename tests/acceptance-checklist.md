# Protocolo de aceptación de JANZU

Completa este protocolo sobre la versión que se entregará. Las pruebas que crean pedidos, cambian inventario, usuarios o mensajes deben ejecutarse en una copia de base de datos. No marques una casilla hasta registrar el resultado observado.

## Registro de ejecución

- Versión / commit: `[ ]`
- Fecha y hora: `[ ]`
- Responsable de prueba: `[ ]`
- Sistema operativo: `[ ]`
- PHP y extensiones: `[ ]`
- MySQL/MariaDB: `[ ]`
- Navegador y versión: `[ ]`
- URL y tamaño de pantalla: `[ ]`
- Base de pruebas / respaldo: `[ ]`

## 4.1 Pruebas funcionales

| ID | Módulo y escenario | Preparación y pasos | Respuesta esperada | Observado / veredicto |
|---|---|---|---|---|
| CP01 | Inicio | Abrir la página principal. | Cargan navegación, marca, destacados e imágenes sin avisos PHP. | `[ ]` |
| CP02 | Búsqueda | Buscar un nombre que exista y luego una cadena sin coincidencia. | Coincidencias correctas; estado vacío con forma de continuar. | `[ ]` |
| CP03 | Filtros | Combinar categoría, precio, disponibilidad y orden. | Cada resultado cumple filtros; el orden corresponde a selección. | `[ ]` |
| CP04 | Paginación | Cargar suficientes productos y cambiar de página. | No hay mezcla de filtros y orden entre páginas; no hay error PDO. | `[ ]` |
| CP05 | Detalle | Abrir producto activo, inexistente e inactivo. | Activo muestra datos; otros estados se manejan sin error técnico. | `[ ]` |
| CP06 | Añadir a bolsa | Añadir desde tarjeta y detalle. | Contador aumenta por unidades; subtotal y bolsa coinciden. | `[ ]` |
| CP07 | Límite de stock | Intentar añadir más unidades que las disponibles. | Sistema informa límite; no confirma unidades que no existen. | `[ ]` |
| CP08 | Actualizar / quitar bolsa | Cambiar cantidades y eliminar un artículo. | Totales se recalculan; producto retirado desaparece. | `[ ]` |
| CP09 | Visitante y pedido | Con sesión cerrada, preparar bolsa e intentar crear pedido. | Puede preparar la bolsa; se solicita autenticación para confirmar. | `[ ]` |
| CP10 | Pedido válido | En base aislada, usar cliente, dirección y teléfono válidos. | Se registra pedido y detalle juntos; inventario disminuye una vez. | `[ ]` |
| CP11 | Pedido inválido | Probar bolsa vacía, dirección corta, teléfono inválido y stock agotado. | Cada caso muestra motivo y no deja pedido parcial. | `[ ]` |
| CP12 | Registro | Probar datos válidos, correo inválido y duplicado. | Válido crea cliente; inválidos se explican; correo duplicado no se inserta. | `[ ]` |
| CP13 | Acceso / salida | Probar clave correcta e incorrecta; salir con token. | Acceso correcto asigna rol; error no revela existencia; salir cierra sesión. | `[ ]` |
| CP14 | Perfil / contraseña | Guardar perfil; probar clave actual errada, confirmación distinta y clave válida. | Sólo datos válidos se guardan; la clave requiere validación actual. | `[ ]` |
| CP15 | Cuenta e historial | Iniciar como cliente A y abrir pedidos; cambiar id o URL a pedido de cliente B. | Sólo se presentan pedidos propios. | `[ ]` |
| CP16 | Contacto | Enviar campos válidos, correo mal formado y texto con etiquetas HTML. | Válido queda en mensajes; inválido se rechaza; HTML se muestra escapado. | `[ ]` |
| CP17 | Acceso a administración | Abrir panel como visitante, cliente y administrador. | Sólo administrador autorizado ve datos y acciones del panel. | `[ ]` |
| CP18 | Catálogo administrativo | Crear y editar producto; cambiar destacado/activo; probar errores de precio/stock. | Se guarda estado válido y el catálogo refleja activo/inactivo. | `[ ]` |
| CP19 | Carga de imagen | Probar JPG, PNG, WEBP <=2 MB, extensión inválida y archivo >2 MB. | Sólo tipos válidos y tamaño admitido se guardan. | `[ ]` |
| CP20 | Cambio de pedido | En base aislada, cancelar pedido y repetir cancelación. | Stock se reintegra una sola vez; estado coincide. | `[ ]` |
| CP21 | Reapertura | Cancelar pedido, reducir stock y volver a confirmar. | No reabre sin stock; no deja valor negativo ni cambio parcial. | `[ ]` |
| CP22 | Mensajes admin | Abrir mensaje y marcarlo leído. | Estado se actualiza; se mantiene el texto recibido. | `[ ]` |

Para cada caso que no pase, registrar: paso reproducible, dato ficticio usado, resultado actual, resultado esperado, captura anonimizada, severidad, responsable y corrección vinculada a versión.

## 4.2 Validación de enlaces

- [ ] Inicio → Tienda, Nosotros y Contacto.
- [ ] Producto → detalle → añadir a bolsa.
- [ ] Bolsa lateral → página de bolsa y seguir explorando.
- [ ] Registro ↔ Entrar; salir y retorno a navegación pública.
- [ ] Mi cuenta → perfil e historial.
- [ ] Administración → resumen, productos, pedidos, clientes y mensajes.
- [ ] Paginación mantiene búsqueda, filtros y orden.
- [ ] Fotos y recursos CSS/JS responden sin 404.
- [ ] Registrar URL, página de origen, código HTTP, resultado y fecha: `[ ]`.

## 4.3 Validación de resolución y reflujo

| Viewport | Inicio | Catálogo | Detalle | Bolsa | Administración | Desplazamiento horizontal |
|---|---|---|---|---|---|---|
| 1440 x 900 | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| 768 x 1024 | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| 390 x 844 | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |

Adjunta captura PNG por vista, con navegador, fecha y URL. Revisa además zoom 200 %, orientación vertical y lectura por teclado.

## 4.4 Validación de navegadores

| Navegador | Versión / fecha | Flujos revisados | Incidencias / resultado |
|---|---|---|---|
| Chrome | `[ ]` | `[ ]` | `[ ]` |
| Edge | `[ ]` | `[ ]` | `[ ]` |
| Firefox | `[ ]` | `[ ]` | `[ ]` |

## 4.5 Validación de seguridad

- [ ] Rutas de administración protegidas por rol.
- [ ] Formularios POST rechazan token CSRF ausente o inválido.
- [ ] Salidas escapan texto introducido por usuario.
- [ ] Consultas usan parámetros preparados; validar filtros y orden recibidos.
- [ ] Contraseñas se almacenan con hash y no aparecen en el panel.
- [ ] Carga de archivo valida contenido y tamaño.
- [ ] Pedido y cancelación respetan stock con transacción.
- [ ] Repositorio no contiene credenciales, claves, archivos de configuración local ni datos personales reales.
- [ ] Mensajes visibles no muestran consultas SQL, stack traces o rutas del servidor.
- [ ] Describir entorno y capturas de revisión sin usar datos sensibles: `[ ]`.

## 4.6 Evaluación de usabilidad

Realiza las tareas con personas voluntarias y consentimiento informado. Registra códigos anónimos (P01, P02...), no nombres ni correos en el informe.

| Tarea | Éxito sin ayuda | Tiempo | Error / retroceso | Dificultad 1-5 | Comentario anónimo |
|---|---|---|---|---|---|
| Encontrar una gorra por categoría y precio | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| Interpretar detalle, disponibilidad y precio | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| Añadir unidades y explicar subtotal | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| Identificar datos necesarios para crear pedido | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |
| Encontrar el historial de pedidos | `[ ]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |

Participante `[P__]`; fecha `[ ]`; dispositivo y navegador `[ ]`; consentimiento `[ ]`; observaciones `[ ]`; cambio priorizado `[ ]`.

## Evaluación automática del sitio

La guía solicita anexar resultados de W3C/TAW3/Cynthia Says. Ejecutar el validador elegido sobre una URL accesible para la herramienta (o validar HTML exportado), registrar nombre/versión del servicio, URL/fecha, errores y advertencias, y adjuntar el reporte real. Un resultado automático no sustituye las pruebas de teclado, contraste, zoom, lector de pantalla ni usabilidad con personas.

| Evaluador | URL de prueba / fecha | Errores | Advertencias | Archivo adjunto |
|---|---|---:|---:|---|
| `[W3C / TAW3 / Cynthia Says]` | `[ ]` | `[ ]` | `[ ]` | `[ ]` |

## Veredicto de aceptación

- Casos aprobados: `[ ]` / `[ ]`.
- Casos fallidos y defectos abiertos: `[ ]`.
- Riesgo aceptado / limitaciones: `[ ]`.
- Responsable y firma: `[ ]`.
- Fecha de aceptación: `[ ]`.
