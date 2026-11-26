<?php
require_once __DIR__ . '/../includes/funciones.php';
exigir_login();
$currentUser = usuario();
$section = (string) ($_GET['s'] ?? 'perfil');
if (!in_array($section, ['perfil', 'pedidos'], true)) {
    $section = 'perfil';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    if (($_POST['accion'] ?? '') === 'actualizar_clave') {
        $currentPassword = (string) ($_POST['clave_actual'] ?? '');
        $newPassword = (string) ($_POST['clave_nueva'] ?? '');
        $confirmation = (string) ($_POST['confirmar_clave'] ?? '');
        $passwordQuery = db()->prepare('SELECT password FROM usuarios WHERE id = ? LIMIT 1');
        $passwordQuery->execute([$currentUser['id']]);
        $storedPassword = (string) $passwordQuery->fetchColumn();
        if (!password_verify($currentPassword, $storedPassword)) {
            aviso('La contraseña actual no coincide.', 'error');
            ir('cliente/index.php?s=perfil');
        }
        if (strlen($newPassword) < 10 || strlen($newPassword) > 120 || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[A-Za-z]/', $newPassword)) {
            aviso('La nueva contraseña debe tener al menos 10 caracteres e incluir una letra y un número.', 'error');
            ir('cliente/index.php?s=perfil');
        }
        if (!hash_equals($newPassword, $confirmation)) {
            aviso('La confirmación no coincide con la nueva contraseña.', 'error');
            ir('cliente/index.php?s=perfil');
        }
        db()->prepare('UPDATE usuarios SET password = ? WHERE id = ?')->execute([password_hash($newPassword, PASSWORD_DEFAULT), $currentUser['id']]);
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        csrf();
        aviso('Actualizamos tu contraseña.');
        ir('cliente/index.php?s=perfil');
    }
    $name = trim((string) ($_POST['nombre'] ?? ''));
    $phone = trim((string) ($_POST['telefono'] ?? ''));
    $address = trim((string) ($_POST['direccion'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || ($phone !== '' && !preg_match('/^[0-9 +()\-]{7,20}$/', $phone)) || mb_strlen($address) > 200) {
        aviso('Revisa tu nombre, teléfono y dirección antes de guardar.', 'error');
        ir('cliente/index.php');
    }
    db()->prepare('UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?')
        ->execute([$name, $phone ?: null, $address ?: null, $currentUser['id']]);
    $_SESSION['user'] = array_merge($currentUser, ['nombre' => $name, 'telefono' => $phone, 'direccion' => $address]);
    aviso('Guardamos tus datos.');
    ir('cliente/index.php');
}

$titulo = 'Mi cuenta';
require __DIR__ . '/../includes/header.php';
?>
<section class="shell section">
    <div class="account-head"><div><span class="eyebrow">JANZU / Mi cuenta</span><h1>Hola, <?= e($currentUser['nombre']) ?>.</h1><p class="lead">Aquí encuentras tus datos y el estado de tus pedidos.</p></div><a class="text-link" href="<?= e(url('tienda.php')) ?>">Volver a la colección <span aria-hidden="true">→</span></a></div>
    <nav class="tabs" aria-label="Mi cuenta"><a class="<?= $section === 'perfil' ? 'on' : '' ?>" href="<?= e(url('cliente/index.php?s=perfil')) ?>">Mis datos</a><a class="<?= $section === 'pedidos' ? 'on' : '' ?>" href="<?= e(url('cliente/index.php?s=pedidos')) ?>">Mis pedidos</a></nav>

    <?php if ($section === 'pedidos'):
        $orders = db()->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado DESC, id DESC');
        $orders->execute([$currentUser['id']]);
        $orderList = $orders->fetchAll();
        $lines = db()->prepare('SELECT i.cantidad, i.precio, p.nombre FROM pedido_items i JOIN productos p ON p.id = i.producto_id WHERE i.pedido_id = ? ORDER BY i.id');
        ?>
        <?php if (!$orderList): ?><div class="empty-state"><span class="eyebrow">Aún no tienes pedidos</span><h2>Tu primera selección te espera.</h2><a class="button button-dark" href="<?= e(url('tienda.php')) ?>">Explorar colección <span aria-hidden="true">→</span></a></div>
        <?php else: foreach ($orderList as $order): $lines->execute([$order['id']]); ?>
            <article class="order-card">
                <div class="order-card-head"><div><span class="eyebrow">Pedido #<?= (int) $order['id'] ?></span><h3><?= e(date('d/m/Y', strtotime($order['creado']))) ?></h3></div><span class="status-tag status-<?= e($order['estado']) ?>"><?= e(ucfirst($order['estado'])) ?></span></div>
                <p class="muted">Entrega: <?= e($order['direccion']) ?> · Tel. <?= e($order['telefono']) ?></p>
                <ul class="order-lines"><?php foreach ($lines->fetchAll() as $line): ?><li><span><?= (int) $line['cantidad'] ?> × <?= e($line['nombre']) ?></span><strong><?= dinero((int) $line['precio'] * (int) $line['cantidad']) ?></strong></li><?php endforeach; ?></ul>
                <div class="summary-line"><span>Total</span><strong><?= dinero($order['total']) ?></strong></div>
            </article>
        <?php endforeach; endif; ?>
    <?php else: ?>
        <form method="post" class="form-card form-shell">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="guardar_perfil">
            <div class="form-field"><label for="nombre">Nombre completo</label><input id="nombre" name="nombre" value="<?= e($currentUser['nombre']) ?>" autocomplete="name" maxlength="100" required></div>
            <div class="form-field"><label for="email">Correo electrónico</label><input id="email" value="<?= e($currentUser['email']) ?>" disabled></div>
            <div class="form-field"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" type="tel" value="<?= e($currentUser['telefono'] ?? '') ?>" autocomplete="tel" maxlength="20"></div>
            <div class="form-field"><label for="direccion">Dirección de entrega</label><input id="direccion" name="direccion" value="<?= e($currentUser['direccion'] ?? '') ?>" autocomplete="street-address" maxlength="200"></div>
            <button class="button button-dark" type="submit">Guardar mis datos <span aria-hidden="true">→</span></button>
        </form>
        <form method="post" class="form-card form-shell">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="actualizar_clave">
            <h2>Cambiar contraseña</h2>
            <div class="form-field"><label for="clave_actual">Contraseña actual</label><input id="clave_actual" name="clave_actual" type="password" autocomplete="current-password" required></div>
            <div class="form-field"><label for="clave_nueva">Nueva contraseña</label><input id="clave_nueva" name="clave_nueva" type="password" minlength="10" maxlength="120" autocomplete="new-password" required><span class="form-note">Al menos 10 caracteres, con una letra y un número.</span></div>
            <div class="form-field"><label for="confirmar_clave">Confirmar nueva contraseña</label><input id="confirmar_clave" name="confirmar_clave" type="password" minlength="10" maxlength="120" autocomplete="new-password" required></div>
            <button class="button button-outline" type="submit">Guardar nueva contraseña</button>
        </form>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
