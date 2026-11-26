<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('janzu_session');
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(dirname(__DIR__));
$basePath = '';
if ($documentRoot && $projectRoot && str_starts_with(strtolower($projectRoot), strtolower($documentRoot))) {
    $basePath = '/' . trim(str_replace('\\', '/', substr($projectRoot, strlen($documentRoot))), '/');
    if ($basePath === '/') {
        $basePath = '';
    }
}
define('BASE', $basePath);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE . '/' . ltrim($path, '/');
}

function dinero(int|float|string $amount): string
{
    return '$' . number_format((float) $amount, 0, ',', '.');
}

function ir(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function volver_a(string $fallback): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $parts = $referer !== '' ? parse_url($referer) : false;
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $refererHost = strtolower((string) ($parts['host'] ?? $host));
    $path = (string) ($parts['path'] ?? '');
    $prefix = BASE === '' ? '/' : BASE . '/';

    if ($parts && $refererHost === $host && str_starts_with($path, $prefix)) {
        $target = $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
        header('Location: ' . $target);
        exit;
    }
    ir($fallback);
}

function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}

function csrf_ok(): void
{
    $token = (string) ($_POST['csrf'] ?? '');
    if ($token === '' || !hash_equals(csrf(), $token)) {
        http_response_code(419);
        exit('La sesión venció. Recarga la página e inténtalo de nuevo.');
    }
}

function aviso(?string $message = null, string $type = 'ok'): ?array
{
    if ($message !== null) {
        $_SESSION['aviso'] = [$type, $message];
        return null;
    }
    $flash = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $flash;
}

function usuario(): ?array
{
    return $_SESSION['user'] ?? null;
}

function exigir_login(): void
{
    if (!usuario()) {
        aviso('Inicia sesión para continuar.', 'error');
        ir('auth/login.php');
    }
}

function exigir_admin(): void
{
    if (!usuario() || (usuario()['rol'] ?? '') !== 'admin') {
        aviso('Esta sección requiere una cuenta administradora.', 'error');
        ir('auth/login.php');
    }
}

function carrito(): array
{
    if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    $clean = [];
    foreach ($_SESSION['carrito'] as $id => $quantity) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
        if ($id && $id > 0 && $quantity && $quantity > 0) {
            $clean[$id] = min($quantity, 99);
        }
    }
    $_SESSION['carrito'] = $clean;
    return $clean;
}

function carrito_cantidad(): int
{
    return array_sum(carrito());
}

function carrito_detalle(): array
{
    $cart = carrito();
    if (!$cart) {
        return [[], 0];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = db()->prepare(
        "SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id " .
        "WHERE p.activo = 1 AND p.id IN ($placeholders)"
    );
    $query->execute($ids);

    $items = [];
    $total = 0;
    foreach ($query->fetchAll() as $product) {
        $quantity = min((int) $cart[$product['id']], (int) $product['stock']);
        if ($quantity < 1) {
            continue;
        }
        $product['cant'] = $quantity;
        $product['sub'] = $quantity * (int) $product['precio'];
        $total += $product['sub'];
        $items[] = $product;
    }
    return [$items, $total];
}

function foto_src(array $product): string
{
    $filename = basename((string) ($product['imagen'] ?? ''));
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $file = __DIR__ . '/../assets/img/productos/' . $filename;

    if ($filename !== '' && in_array($extension, $allowed, true) && is_file($file)) {
        return url('assets/img/productos/' . rawurlencode($filename));
    }
    return url('assets/img/cap-placeholder.svg');
}

function foto(array $product, string $class = ''): string
{
    return '<img class="foto ' . e($class) . '" src="' . e(foto_src($product)) . '" alt="Gorra ' .
        e($product['nombre'] ?? 'JANZU') . '" loading="lazy" width="640" height="480">';
}

function tarjeta(array $product): string
{
    $id = (int) $product['id'];
    $stock = (int) $product['stock'];
    $html = '<article class="product-card">';
    $html .= '<a class="product-image" href="' . e(url('producto.php?id=' . $id)) . '">';
    $html .= foto($product);
    if (!empty($product['destacado'])) {
        $html .= '<span class="product-label">Selección JANZU</span>';
    }
    if ($stock < 1) {
        $html .= '<span class="product-label product-label-muted">Agotada</span>';
    }
    $html .= '</a><div class="product-info">';
    $html .= '<div class="product-category">' . e($product['categoria'] ?? 'Colección') . '</div>';
    $html .= '<a class="product-title" href="' . e(url('producto.php?id=' . $id)) . '"><h3>' . e($product['nombre']) . '</h3></a>';
    if (!empty($product['descripcion'])) {
        $description = trim((string) $product['descripcion']);
        if (mb_strlen($description) > 118) {
            $description = mb_substr($description, 0, 115) . '…';
        }
        $html .= '<p class="product-copy">' . e($description) . '</p>';
    }
    $html .= '<div class="product-buy"><strong>' . dinero($product['precio']) . '</strong>';
    if ($stock > 0) {
        $html .= '<form method="post" action="' . e(url('carrito.php')) . '" data-cart-add>' . csrf_campo();
        $html .= '<input type="hidden" name="accion" value="agregar"><input type="hidden" name="id" value="' . $id . '">';
        $html .= '<input type="hidden" name="cantidad" value="1"><button class="button button-small" type="submit">Añadir</button></form>';
    } else {
        $html .= '<span class="stock-note">Sin existencias</span>';
    }
    $html .= '</div></div></article>';
    return $html;
}

function cart_payload(): array
{
    [$items, $total] = carrito_detalle();
    $payload = [];
    foreach ($items as $item) {
        $payload[] = [
            'id' => (int) $item['id'],
            'name' => (string) $item['nombre'],
            'quantity' => (int) $item['cant'],
            'stock' => (int) $item['stock'],
            'price' => (int) $item['precio'],
            'subtotal' => (int) $item['sub'],
            'image' => foto_src($item),
            'productUrl' => url('producto.php?id=' . (int) $item['id']),
        ];
    }
    return [
        'items' => $payload,
        'count' => array_sum(array_column($payload, 'quantity')),
        'total' => (int) $total,
    ];
}

function responder_carrito(bool $ok, string $message, string $fallback): void
{
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message] + cart_payload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    aviso($message, $ok ? 'ok' : 'error');
    volver_a($fallback);
}
