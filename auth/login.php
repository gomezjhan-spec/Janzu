<?php
require_once __DIR__ . '/../includes/funciones.php';
$email = '';
if (usuario()) {
    ir(usuario()['rol'] === 'admin' ? 'admin/index.php' : 'cliente/index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $query = db()->prepare('SELECT id, nombre, email, password, telefono, direccion, rol FROM usuarios WHERE email = ? LIMIT 1');
    $query->execute([$email]);
    $account = $query->fetch();

    if ($account && password_verify($password, $account['password'])) {
        if (password_needs_rehash($account['password'], PASSWORD_DEFAULT)) {
            db()->prepare('UPDATE usuarios SET password = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $account['id']]);
        }
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        unset($account['password']);
        $_SESSION['user'] = $account;
        csrf();
        aviso('Hola, ' . $account['nombre'] . '. Ya estás dentro de JANZU.');
        ir($account['rol'] === 'admin' ? 'admin/index.php' : 'cliente/index.php');
    }
    aviso('El correo o la contraseña no coinciden. Revisa tus datos e inténtalo de nuevo.', 'error');
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/../includes/header.php';
?>
<section class="shell section form-shell">
    <span class="eyebrow">JANZU / Tu cuenta</span><h1>Qué bueno verte.</h1><p class="lead">Entra para revisar tus pedidos, guardar tus datos y seguir el estado de cada entrega.</p>
    <form method="post" class="form-card">
        <?= csrf_campo() ?>
        <div class="form-field"><label for="email">Correo electrónico</label><input id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" maxlength="150" required></div>
        <div class="form-field"><label for="password">Contraseña</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="button button-dark" type="submit">Entrar a mi cuenta <span aria-hidden="true">→</span></button>
        <p class="form-links">¿Aún no tienes cuenta? <a href="<?= e(url('auth/registro.php')) ?>">Crea una aquí.</a></p>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
