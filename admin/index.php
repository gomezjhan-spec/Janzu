<?php
require_once __DIR__ . '/../includes/funciones.php';
exigir_admin();
$pdo = db();
$sections = ['resumen' => 'Resumen', 'productos' => 'Productos', 'pedidos' => 'Pedidos', 'clientes' => 'Clientes', 'mensajes' => 'Mensajes'];
$section = (string) ($_GET['s'] ?? 'resumen');
if (!isset($sections[$section])) {
    $section = 'resumen';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    $action = (string) ($_POST['accion'] ?? '');

    if ($action === 'guardar_producto') {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $name = trim((string) ($_POST['nombre'] ?? ''));
        $description = trim((string) ($_POST['descripcion'] ?? ''));
        $price = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT);
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
        $categoryId = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $color = (string) ($_POST['color'] ?? '#d8d0c4');
        $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 120) $errors[] = 'El nombre debe tener entre 3 y 120 caracteres.';
        if (mb_strlen($description) < 20 || mb_strlen($description) > 5000) $errors[] = 'La descripción debe tener entre 20 y 5.000 caracteres.';
        if ($price === false || $price < 1 || $price > 999999999) $errors[] = 'Escribe un precio válido en pesos colombianos.';
        if ($stock === false || $stock < 0 || $stock > 100000) $errors[] = 'Escribe una cantidad de inventario válida.';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $color = '#d8d0c4';
        $categoryQuery = $pdo->prepare('SELECT id FROM categorias WHERE id = ?');
        $categoryQuery->execute([$categoryId]);
        if (!$categoryQuery->fetchColumn()) $errors[] = 'Elige una categoría disponible.';

        $existing = null;
        if ($id > 0) {
            $existingQuery = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
            $existingQuery->execute([$id]);
            $existing = $existingQuery->fetch();
            if (!$existing) $errors[] = 'No encontramos ese producto.';
        }

        $imageName = $existing['imagen'] ?? null;
        if (isset($_POST['quitar_imagen'])) $imageName = null;
        $upload = $_FILES['imagen'] ?? null;
        if (!$errors && $upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen debe pesar 2 MB o menos y cargarse correctamente.';
            } elseif (is_uploaded_file($upload['tmp_name'])) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload['tmp_name']);
                $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $imageInfo = @getimagesize($upload['tmp_name']);
                if (!isset($types[$mime]) || !$imageInfo || $imageInfo[0] < 80 || $imageInfo[1] < 80 || $imageInfo[0] > 6000 || $imageInfo[1] > 6000) {
                    $errors[] = 'Usa una imagen JPG, PNG o WEBP válida de al menos 80 × 80 píxeles.';
                } else {
                    $imageName = 'product-' . bin2hex(random_bytes(12)) . '.' . $types[$mime];
                    $destination = __DIR__ . '/../assets/img/productos/' . $imageName;
                    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                        $errors[] = 'No pudimos guardar la imagen. Revisa los permisos de la carpeta de productos.';
                    }
                }
            } else {
                $errors[] = 'No pudimos verificar la imagen cargada.';
            }
        }

        if ($errors) {
            aviso(implode(' ', $errors), 'error');
            ir('admin/index.php?s=productos' . ($id ? '&editar=' . $id : ''));
        }

        $values = [(int) $categoryId, $name, $description, (int) $price, (int) $stock, $color,
            isset($_POST['destacado']) ? 1 : 0, isset($_POST['activo']) ? 1 : 0, $imageName];
        if ($id > 0) {
            $pdo->prepare('UPDATE productos SET categoria_id = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, color = ?, destacado = ?, activo = ?, imagen = ? WHERE id = ?')
                ->execute([...$values, $id]);
            aviso('Actualizamos el producto.');
        } else {
            $pdo->prepare('INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, color, destacado, activo, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute($values);
            aviso('Creamos el producto.');
        }
        ir('admin/index.php?s=productos');
    }

    if ($action === 'visibilidad') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        if ($id > 0) {
            $pdo->prepare('UPDATE productos SET activo = IF(activo = 1, 0, 1) WHERE id = ?')->execute([$id]);
            aviso('Actualizamos la visibilidad del producto.');
        }
        ir('admin/index.php?s=productos');
    }

    if ($action === 'estado' && in_array($_POST['estado'] ?? '', ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'], true)) {
        $orderId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        if ($orderId > 0) {
            $pdo->beginTransaction();
            try {
                $orderQuery = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ? FOR UPDATE');
                $orderQuery->execute([$orderId]);
                $oldStatus = $orderQuery->fetchColumn();
                $newStatus = (string) $_POST['estado'];
                if ($oldStatus === false) throw new RuntimeException('missing');
                $allowedTransitions = [
                    'pendiente' => ['pendiente', 'confirmado', 'cancelado'],
                    'confirmado' => ['confirmado', 'enviado', 'cancelado'],
                    'enviado' => ['enviado', 'entregado'],
                    'entregado' => ['entregado'],
                    'cancelado' => ['cancelado', 'pendiente'],
                ];
                if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) throw new RuntimeException('transition');
                if ($oldStatus !== $newStatus && $newStatus === 'cancelado') {
                    $orderLines = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ? ORDER BY producto_id FOR UPDATE');
                    $orderLines->execute([$orderId]);
                    $restore = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
                    foreach ($orderLines->fetchAll() as $line) $restore->execute([(int) $line['cantidad'], (int) $line['producto_id']]);
                } elseif ($oldStatus === 'cancelado' && $newStatus !== 'cancelado') {
                    $orderLines = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ? ORDER BY producto_id FOR UPDATE');
                    $orderLines->execute([$orderId]);
                    $deduct = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?');
                    foreach ($orderLines->fetchAll() as $line) {
                        $quantity = (int) $line['cantidad'];
                        $deduct->execute([$quantity, (int) $line['producto_id'], $quantity]);
                        if ($deduct->rowCount() !== 1) throw new RuntimeException('stock');
                    }
                }
                $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$newStatus, $orderId]);
                $pdo->commit();
                aviso('Actualizamos el estado del pedido #' . $orderId . '.');
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                if (!$exception instanceof RuntimeException) error_log('[JANZU] Order state update failed: ' . $exception->getMessage());
                aviso('No se puede aplicar ese cambio o el inventario ya no alcanza. Revisa el pedido e inténtalo de nuevo.', 'error');
            }
        }
        ir('admin/index.php?s=pedidos');
    }

    if ($action === 'leido') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        if ($id > 0) $pdo->prepare('UPDATE mensajes SET leido = 1 WHERE id = ?')->execute([$id]);
        aviso('Marcamos el mensaje como leído.');
        ir('admin/index.php?s=mensajes');
    }
}

$titulo = 'Administración';
require __DIR__ . '/../includes/header.php';
?>
<section class="shell section admin-layout">
    <nav class="admin-side" aria-label="Administración">
        <?php foreach ($sections as $key => $label): ?><a class="<?= $section === $key ? 'on' : '' ?>" href="<?= e(url('admin/index.php?s=' . $key)) ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <div class="admin-main">
        <span class="eyebrow">JANZU / Administración</span><h1><?= e($sections[$section]) ?>.</h1>

        <?php if ($section === 'resumen'):
            $stats = [
                'Productos activos' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo = 1')->fetchColumn(),
                'Clientes registrados' => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn(),
                'Pedidos pendientes' => (int) $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn(),
                'Ventas registradas' => dinero($pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado <> 'cancelado'")->fetchColumn()),
                'Stock por reponer' => (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= 5')->fetchColumn(),
                'Mensajes sin leer' => (int) $pdo->query('SELECT COUNT(*) FROM mensajes WHERE leido = 0')->fetchColumn(),
            ];
            $recentOrders = $pdo->query('SELECT p.id, p.total, p.estado, p.creado, u.nombre AS cliente FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.creado DESC, p.id DESC LIMIT 8')->fetchAll();
            ?>
            <div class="stat-grid"><?php foreach ($stats as $label => $value): ?><div class="stat-card"><strong><?= e($value) ?></strong><span><?= e($label) ?></span></div><?php endforeach; ?></div>
            <div class="admin-section-heading"><h2>Pedidos recientes</h2><a class="text-link" href="<?= e(url('admin/index.php?s=pedidos')) ?>">Ver todos <span aria-hidden="true">→</span></a></div>
            <?php if (!$recentOrders): ?><div class="empty-state"><p>Aún no hay pedidos para mostrar.</p></div><?php else: ?>
                <div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Total</th></tr></thead><tbody>
                <?php foreach ($recentOrders as $order): ?><tr><td>#<?= (int) $order['id'] ?></td><td><?= e($order['cliente']) ?></td><td><?= e(date('d/m/Y H:i', strtotime($order['creado']))) ?></td><td><span class="status-tag status-<?= e($order['estado']) ?>"><?= e(ucfirst($order['estado'])) ?></span></td><td><?= dinero($order['total']) ?></td></tr><?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>

        <?php elseif ($section === 'productos'):
            $categories = $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
            $editing = null;
            if (isset($_GET['editar'])) {
                $editQuery = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
                $editQuery->execute([(int) $_GET['editar']]);
                $editing = $editQuery->fetch() ?: null;
            }
            $formProduct = $editing ?? ['id' => 0, 'categoria_id' => $categories[0]['id'] ?? 0, 'nombre' => '', 'descripcion' => '', 'precio' => '', 'stock' => '', 'color' => '#d8d0c4', 'destacado' => 0, 'activo' => 1, 'imagen' => null];
            ?>
            <div class="admin-section-heading"><h2><?= $editing ? 'Editar pieza' : 'Añadir una pieza' ?></h2><?php if ($editing): ?><a class="text-link" href="<?= e(url('admin/index.php?s=productos')) ?>">Cancelar edición</a><?php endif; ?></div>
            <form method="post" enctype="multipart/form-data" class="form-card admin-form">
                <?= csrf_campo() ?><input type="hidden" name="accion" value="guardar_producto"><input type="hidden" name="id" value="<?= (int) $formProduct['id'] ?>">
                <div class="form-grid">
                    <div class="form-field"><label for="nombre">Nombre de la pieza</label><input id="nombre" name="nombre" value="<?= e($formProduct['nombre']) ?>" maxlength="120" required></div>
                    <div class="form-field"><label for="categoria_id">Categoría</label><select id="categoria_id" name="categoria_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $formProduct['categoria_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label for="precio">Precio (COP)</label><input id="precio" name="precio" type="number" min="1" max="999999999" step="1000" value="<?= e($formProduct['precio']) ?>" required></div>
                    <div class="form-field"><label for="stock">Unidades en inventario</label><input id="stock" name="stock" type="number" min="0" max="100000" value="<?= e($formProduct['stock']) ?>" required></div>
                    <div class="form-field"><label for="color">Tono principal</label><input id="color" name="color" type="color" value="<?= e($formProduct['color']) ?>"></div>
                    <div class="form-field"><label for="imagen">Fotografía (JPG, PNG o WEBP · máximo 2 MB)</label><input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp"><?php if (!empty($formProduct['imagen'])): ?><span class="form-note">Foto actual: <?= e($formProduct['imagen']) ?></span><label class="field-label"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la foto actual</label><?php endif; ?></div>
                </div>
                <div class="form-field"><label for="descripcion">Descripción completa</label><textarea id="descripcion" name="descripcion" minlength="20" maxlength="5000" required><?= e($formProduct['descripcion']) ?></textarea><span class="form-note">Cuenta la silueta, el material, el ajuste y para qué momentos la recomiendas.</span></div>
                <div class="check-row"><label><input type="checkbox" name="destacado" value="1" <?= $formProduct['destacado'] ? 'checked' : '' ?>> Selección JANZU</label><label><input type="checkbox" name="activo" value="1" <?= $formProduct['activo'] ? 'checked' : '' ?>> Visible en la tienda</label></div>
                <button class="button button-dark" type="submit">Guardar pieza <span aria-hidden="true">→</span></button>
            </form>
            <div class="admin-section-heading"><h2>Inventario</h2><span class="status-note">Los productos con pedidos se pueden ocultar sin perder su historial.</span></div>
            <?php $products = $pdo->query('SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id ORDER BY p.id DESC')->fetchAll(); ?>
            <?php if (!$products): ?><div class="empty-state"><p>Tu catálogo aparecerá aquí.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Pieza</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                <?php foreach ($products as $product): ?><tr><td><?= e($product['nombre']) ?></td><td><?= e($product['categoria']) ?></td><td><?= dinero($product['precio']) ?></td><td><?= (int) $product['stock'] ?></td><td><?= $product['activo'] ? 'Visible' : 'Oculta' ?></td><td><div class="inline-form"><a href="<?= e(url('admin/index.php?s=productos&editar=' . (int) $product['id'])) ?>">Editar</a><form method="post" class="inline-form"><?= csrf_campo() ?><input type="hidden" name="accion" value="visibilidad"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="text-button" type="submit"><?= $product['activo'] ? 'Ocultar' : 'Mostrar' ?></button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div><?php endif; ?>

        <?php elseif ($section === 'pedidos'):
            $orders = $pdo->query('SELECT p.*, u.nombre AS cliente, u.email FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.creado DESC, p.id DESC')->fetchAll();
            $lineQuery = $pdo->prepare('SELECT i.cantidad, i.precio, p.nombre FROM pedido_items i JOIN productos p ON p.id = i.producto_id WHERE i.pedido_id = ? ORDER BY i.id');
            ?>
            <?php if (!$orders): ?><div class="empty-state"><p>Los pedidos creados aparecerán aquí.</p></div><?php else: foreach ($orders as $order): $lineQuery->execute([$order['id']]); ?>
                <article class="admin-order"><div class="order-card-head"><div><span class="eyebrow">Pedido #<?= (int) $order['id'] ?> · <?= e(date('d/m/Y H:i', strtotime($order['creado']))) ?></span><h3><?= e($order['cliente']) ?></h3><p><?= e($order['email']) ?></p></div><strong><?= dinero($order['total']) ?></strong></div>
                    <address><?= e($order['direccion']) ?> · <?= e($order['telefono']) ?></address>
                    <ul class="order-lines"><?php foreach ($lineQuery->fetchAll() as $line): ?><li><span><?= (int) $line['cantidad'] ?> × <?= e($line['nombre']) ?></span><strong><?= dinero((int) $line['precio'] * (int) $line['cantidad']) ?></strong></li><?php endforeach; ?></ul>
                    <?php $statusOptions = ['pendiente' => ['pendiente', 'confirmado', 'cancelado'], 'confirmado' => ['confirmado', 'enviado', 'cancelado'], 'enviado' => ['enviado', 'entregado'], 'entregado' => ['entregado'], 'cancelado' => ['cancelado', 'pendiente']][$order['estado']] ?? [$order['estado']]; ?>
                    <form method="post" class="inline-form"><?= csrf_campo() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int) $order['id'] ?>"><label class="field-label" for="estado-<?= (int) $order['id'] ?>">Estado</label><select id="estado-<?= (int) $order['id'] ?>" name="estado"><?php foreach ($statusOptions as $state): ?><option value="<?= e($state) ?>" <?= $state === $order['estado'] ? 'selected' : '' ?>><?= e(ucfirst($state)) ?></option><?php endforeach; ?></select><button class="button button-small" type="submit">Guardar estado</button></form>
                </article>
            <?php endforeach; endif; ?>

        <?php elseif ($section === 'clientes'):
            $customers = $pdo->query("SELECT u.id, u.nombre, u.email, u.telefono, u.creado, COUNT(p.id) AS pedidos, COALESCE(SUM(CASE WHEN p.estado <> 'cancelado' THEN p.total ELSE 0 END), 0) AS total FROM usuarios u LEFT JOIN pedidos p ON p.usuario_id = u.id WHERE u.rol = 'cliente' GROUP BY u.id ORDER BY u.creado DESC")->fetchAll();
            ?>
            <?php if (!$customers): ?><div class="empty-state"><p>Los clientes registrados aparecerán aquí.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Correo</th><th>Teléfono</th><th>Pedidos</th><th>Total histórico</th><th>Registro</th></tr></thead><tbody><?php foreach ($customers as $customer): ?><tr><td><?= e($customer['nombre']) ?></td><td><a href="mailto:<?= e($customer['email']) ?>"><?= e($customer['email']) ?></a></td><td><?= e($customer['telefono'] ?? '—') ?></td><td><?= (int) $customer['pedidos'] ?></td><td><?= dinero($customer['total']) ?></td><td><?= e(date('d/m/Y', strtotime($customer['creado']))) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>

        <?php else:
            $messages = $pdo->query('SELECT * FROM mensajes ORDER BY leido ASC, creado DESC, id DESC')->fetchAll();
            ?>
            <?php if (!$messages): ?><div class="empty-state"><p>Los mensajes recibidos aparecerán aquí.</p></div><?php else: foreach ($messages as $message): ?>
                <article class="message-card <?= $message['leido'] ? '' : 'unread' ?>"><div class="order-card-head"><h3><?= e($message['asunto']) ?></h3><span class="status-note"><?= e(date('d/m/Y H:i', strtotime($message['creado']))) ?></span></div><p class="message-meta"><?= e($message['nombre']) ?> · <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></p><p class="message-copy"><?= e($message['mensaje']) ?></p><?php if (!$message['leido']): ?><form method="post"><?= csrf_campo() ?><input type="hidden" name="accion" value="leido"><input type="hidden" name="id" value="<?= (int) $message['id'] ?>"><button class="button button-small" type="submit">Marcar como leído</button></form><?php endif; ?></article>
            <?php endforeach; endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
