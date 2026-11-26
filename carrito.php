<?php
require_once __DIR__ . '/includes/funciones.php';

if (isset($_GET['mini']) && $_GET['mini'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(cart_payload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    $cart = carrito();
    $action = isset($_POST['quitar']) ? 'quitar' : (string) ($_POST['accion'] ?? '');

    if ($action === 'agregar') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $quantity = filter_var($_POST['cantidad'] ?? 1, FILTER_VALIDATE_INT) ?: 0;
        $query = db()->prepare('SELECT id, stock FROM productos WHERE id = ? AND activo = 1 LIMIT 1');
        $query->execute([$id]);
        $product = $query->fetch();
        if (!$product || (int) $product['stock'] < 1) {
            responder_carrito(false, 'Esta gorra no tiene unidades disponibles.', 'tienda.php');
        }
        if ($quantity < 1) {
            responder_carrito(false, 'Elige al menos una unidad.', 'producto.php?id=' . $id);
        }
        $current = (int) ($cart[$id] ?? 0);
        $cart[$id] = min($current + $quantity, (int) $product['stock'], 99);
        $_SESSION['carrito'] = $cart;
        responder_carrito(true, 'Añadimos la gorra a tu bolsa.', 'tienda.php');
    }

    if ($action === 'actualizar') {
        $updates = $_POST['cant'] ?? [];
        if (isset($_POST['id'], $_POST['cantidad'])) {
            $updates[(int) $_POST['id']] = $_POST['cantidad'];
        }
        if (is_array($updates)) {
            foreach ($updates as $rawId => $rawQuantity) {
                $id = filter_var($rawId, FILTER_VALIDATE_INT) ?: 0;
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
                if ($id < 1 || $quantity === false) {
                    continue;
                }
                if ($quantity < 1) {
                    unset($cart[$id]);
                    continue;
                }
                $stockQuery = db()->prepare('SELECT stock FROM productos WHERE id = ? AND activo = 1 LIMIT 1');
                $stockQuery->execute([$id]);
                $stock = $stockQuery->fetchColumn();
                if ($stock === false || (int) $stock < 1) {
                    unset($cart[$id]);
                    continue;
                }
                $cart[$id] = min($quantity, (int) $stock, 99);
            }
        }
        $_SESSION['carrito'] = $cart;
        responder_carrito(true, 'Tu bolsa está actualizada.', 'carrito.php');
    }

    if ($action === 'quitar') {
        $id = filter_var($_POST['quitar'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        if ($id > 0) {
            unset($cart[$id]);
        }
        $_SESSION['carrito'] = $cart;
        responder_carrito(true, 'Quitamos la gorra de tu bolsa.', 'carrito.php');
    }

    if ($action === 'pedir') {
        exigir_login();
        $address = trim((string) ($_POST['direccion'] ?? ''));
        $phone = trim((string) ($_POST['telefono'] ?? ''));
        if (mb_strlen($address) < 8 || mb_strlen($address) > 200 || !preg_match('/^[0-9 +()\-]{7,20}$/', $phone)) {
            aviso('Escribe una dirección completa y un teléfono válido para la entrega.', 'error');
            ir('carrito.php');
        }
        if (!$cart) {
            aviso('Tu bolsa está vacía.', 'error');
            ir('tienda.php');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            ksort($cart, SORT_NUMERIC);
            $lines = [];
            $total = 0;
            $lock = $pdo->prepare('SELECT id, nombre, precio, stock FROM productos WHERE id = ? AND activo = 1 FOR UPDATE');
            foreach ($cart as $id => $quantity) {
                $lock->execute([(int) $id]);
                $product = $lock->fetch();
                if (!$product || (int) $product['stock'] < (int) $quantity) {
                    throw new RuntimeException('stock');
                }
                $quantity = min((int) $quantity, 99);
                $lineTotal = $quantity * (int) $product['precio'];
                $lines[] = ['id' => (int) $product['id'], 'quantity' => $quantity, 'price' => (int) $product['precio']];
                $total += $lineTotal;
            }
            if (!$lines) {
                throw new RuntimeException('empty');
            }

            $pdo->prepare('UPDATE usuarios SET direccion = ?, telefono = ? WHERE id = ?')->execute([$address, $phone, usuario()['id']]);
            $pdo->prepare('INSERT INTO pedidos (usuario_id, total, direccion, telefono) VALUES (?, ?, ?, ?)')
                ->execute([usuario()['id'], $total, $address, $phone]);
            $orderId = (int) $pdo->lastInsertId();
            $insertLine = $pdo->prepare('INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)');
            $deductStock = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($lines as $line) {
                $deductStock->execute([$line['quantity'], $line['id'], $line['quantity']]);
                if ($deductStock->rowCount() !== 1) {
                    throw new RuntimeException('stock');
                }
                $insertLine->execute([$orderId, $line['id'], $line['quantity'], $line['price']]);
            }
            $pdo->commit();
            $_SESSION['user']['direccion'] = $address;
            $_SESSION['user']['telefono'] = $phone;
            $_SESSION['carrito'] = [];
            aviso('Pedido #' . $orderId . ' creado. Podrás seguir su estado desde tu cuenta.');
            ir('cliente/index.php?s=pedidos');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (!$exception instanceof RuntimeException) {
                error_log('[JANZU] Order creation failed: ' . $exception->getMessage());
            }
            aviso('No pudimos completar el pedido. Revisa la disponibilidad de las gorras e inténtalo de nuevo.', 'error');
            ir('carrito.php');
        }
    }

    responder_carrito(false, 'No reconocimos esa acción.', 'carrito.php');
}

[$items, $total] = carrito_detalle();
$currentUser = usuario();
$titulo = 'Tu bolsa';
require __DIR__ . '/includes/header.php';
?>
<section class="shell section">
    <nav class="breadcrumb" aria-label="Ruta"><a href="<?= e(url()) ?>">Inicio</a><span>·</span><strong>Tu bolsa</strong></nav>
    <div class="section-heading"><div><span class="eyebrow">JANZU / Tu selección</span><h1>Tu bolsa.</h1></div><a class="text-link" href="<?= e(url('tienda.php')) ?>">Seguir explorando <span aria-hidden="true">→</span></a></div>

    <?php if (!$items): ?>
        <div class="empty-state"><span class="eyebrow">Por ahora, está vacía</span><h2>Tu próxima pieza empieza aquí.</h2><p>Explora la colección y añade las gorras que quieras guardar en tu bolsa.</p><a class="button button-dark" href="<?= e(url('tienda.php')) ?>">Explorar colección <span aria-hidden="true">→</span></a></div>
    <?php else: ?>
        <div class="cart-layout">
            <div>
                <form class="cart-list" method="post" action="<?= e(url('carrito.php')) ?>" data-cart-update-form>
                    <?= csrf_campo() ?><input type="hidden" name="accion" value="actualizar">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-row" data-cart-row data-item-id="<?= (int) $item['id'] ?>" data-unit-price="<?= (int) $item['precio'] ?>">
                            <a class="cart-row-image" href="<?= e(url('producto.php?id=' . (int) $item['id'])) ?>"><?= foto($item) ?></a>
                            <div class="cart-row-product"><h2 class="cart-row-name"><a href="<?= e(url('producto.php?id=' . (int) $item['id'])) ?>"><?= e($item['nombre']) ?></a></h2><p class="cart-row-meta"><?= e($item['categoria']) ?> · <?= dinero($item['precio']) ?> por unidad</p></div>
                            <span class="cart-price"><?= dinero($item['precio']) ?></span>
                            <label class="cart-quantity"><span class="field-label">Unidades</span><input type="number" name="cant[<?= (int) $item['id'] ?>]" value="<?= (int) $item['cant'] ?>" min="0" max="<?= (int) $item['stock'] ?>" aria-label="Unidades de <?= e($item['nombre']) ?>" data-cart-quantity></label>
                            <div class="cart-subtotal" data-row-subtotal><?= dinero($item['sub']) ?></div>
                            <button class="remove-button" type="submit" name="quitar" value="<?= (int) $item['id'] ?>" aria-label="Quitar <?= e($item['nombre']) ?> de la bolsa" data-cart-remove>×</button>
                        </div>
                    <?php endforeach; ?>
                    <div class="cart-update"><button class="button button-outline" type="submit">Actualizar bolsa</button><p class="cart-update-status" data-cart-update-status></p></div>
                </form>
            </div>
            <aside class="cart-summary" aria-labelledby="summary-heading">
                <h2 id="summary-heading">Resumen</h2>
                <div class="summary-line"><span>Unidades</span><strong data-cart-page-count><?= carrito_cantidad() ?></strong></div>
                <div class="summary-line"><span>Subtotal</span><strong data-cart-page-total><?= dinero($total) ?></strong></div>
                <p class="summary-note">El total mostrado corresponde a las piezas. El envío y el pago se coordinan contigo al confirmar.</p>
                <?php if ($currentUser): ?>
                    <form method="post" action="<?= e(url('carrito.php')) ?>">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="pedir">
                        <div class="form-field"><label for="direccion">Dirección de entrega</label><input id="direccion" name="direccion" value="<?= e($currentUser['direccion'] ?? '') ?>" minlength="8" maxlength="200" autocomplete="street-address" required></div>
                        <div class="form-field"><label for="telefono">Teléfono de contacto</label><input id="telefono" name="telefono" value="<?= e($currentUser['telefono'] ?? '') ?>" type="tel" inputmode="tel" autocomplete="tel" required></div>
                        <button class="button button-dark button-full" type="submit">Crear pedido <span aria-hidden="true">→</span></button>
                    </form>
                <?php else: ?>
                    <a class="button button-dark button-full" href="<?= e(url('auth/login.php')) ?>">Inicia sesión para pedir <span aria-hidden="true">→</span></a>
                    <p class="summary-note">¿Primera vez en JANZU? <a href="<?= e(url('auth/registro.php')) ?>">Crea tu cuenta aquí.</a></p>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
