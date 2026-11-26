<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli') {
    // Keep the smoke test independent from Laragon's DOCUMENT_ROOT and session path.
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
    ini_set('session.save_path', sys_get_temp_dir());
}

require_once __DIR__ . '/../includes/funciones.php';

$checks = 0;
$failures = [];
$assert = static function (bool $condition, string $label) use (&$checks, &$failures): void {
    $checks++;
    if (!$condition) {
        $failures[] = $label;
    }
};

$assert(e('<script>') === '&lt;script&gt;', 'La salida HTML debe escapar etiquetas.');
$assert(dinero(45000) === '$45.000', 'El precio debe mostrarse con separador colombiano.');
$assert(url('tienda.php') === '/tienda.php', 'Las rutas deben resolver desde la raíz del proyecto en CLI.');
$assert(str_contains(csrf_campo(), 'name="csrf"'), 'Los formularios deben incluir el token CSRF.');

$_SESSION['carrito'] = ['2' => '5', '4' => '0', 'bad' => 8, '-5' => 3, '6' => '100'];
$cleanCart = carrito();
$assert($cleanCart === [2 => 5, 6 => 99], 'El carrito debe aceptar IDs y cantidades válidos y limitar cada línea.');
$assert(carrito_cantidad() === 104, 'El contador debe sumar las unidades, no las líneas.');

$photoPath = foto_src(['id' => 1, 'nombre' => 'Prueba', 'imagen' => 'la-azul.jpg']);
$assert(str_contains($photoPath, 'assets/img/productos/la-azul.jpg'), 'La foto local debe resolverse dentro del catálogo.');
$fallbackPath = foto_src(['id' => 2, 'nombre' => 'Prueba', 'imagen' => '../database/janzu.sql']);
$assert(str_contains($fallbackPath, 'assets/img/cap-placeholder.svg'), 'Una ruta de imagen no válida debe usar el recurso neutro.');
$card = tarjeta(['id' => 7, 'nombre' => 'Prueba urbana', 'categoria' => 'Adultos', 'descripcion' => 'Una descripción suficientemente larga para validar la tarjeta.', 'precio' => 52000, 'stock' => 3, 'destacado' => 0, 'imagen' => null]);
$assert(str_contains($card, 'data-cart-add') && str_contains($card, 'action="/carrito.php"'), 'Las tarjetas deben enviar el alta a la bolsa sin forzar navegación.');
$catalogSource = file_get_contents(__DIR__ . '/../tienda.php');
$assert(is_string($catalogSource) && str_contains($catalogSource, 'LIMIT ? OFFSET ?') && !str_contains($catalogSource, 'LIMIT :limit OFFSET :offset'), 'La consulta del catálogo debe usar parámetros PDO de un solo tipo.');

if ($failures) {
    fwrite(STDERR, "Fallaron " . count($failures) . " de $checks comprobaciones:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK: $checks comprobaciones básicas pasaron.\n";
