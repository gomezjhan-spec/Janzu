<?php
require_once __DIR__ . '/../includes/funciones.php';
$data = ['nombre' => '', 'email' => '', 'telefono' => ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    foreach ($data as $key => $_) {
        $data[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $data['email'] = mb_strtolower($data['email']);
    $password = (string) ($_POST['password'] ?? '');

    if (mb_strlen($data['nombre']) < 2 || mb_strlen($data['nombre']) > 100) {
        $errors['nombre'] = 'Escribe un nombre de entre 2 y 100 caracteres.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
        $errors['email'] = 'Escribe un correo válido de hasta 150 caracteres.';
    }
    if ($data['telefono'] !== '' && !preg_match('/^[0-9 +()\-]{7,20}$/', $data['telefono'])) {
        $errors['telefono'] = 'Escribe un teléfono válido con 7 a 20 caracteres.';
    }
    if (strlen($password) < 10 || strlen($password) > 120 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) {
        $errors['password'] = 'Usa al menos 10 caracteres, con una letra y un número.';
    }

    if (!$errors) {
        $exists = db()->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $exists->execute([$data['email']]);
        if ($exists->fetchColumn()) {
            $errors['email'] = 'Ese correo ya tiene una cuenta. Prueba iniciando sesión.';
        }
    }

    if (!$errors) {
        try {
            db()->prepare('INSERT INTO usuarios (nombre, email, telefono, password) VALUES (?, ?, ?, ?)')
                ->execute([$data['nombre'], $data['email'], $data['telefono'] ?: null, password_hash($password, PASSWORD_DEFAULT)]);
            aviso('Tu cuenta quedó lista. Ya puedes iniciar sesión.');
            ir('auth/login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors['email'] = 'Ese correo ya tiene una cuenta. Prueba iniciando sesión.';
            } else {
                throw $exception;
            }
        }
    }
}

$titulo = 'Crear cuenta';
require __DIR__ . '/../includes/header.php';
?>
<section class="shell section form-shell">
    <span class="eyebrow">JANZU / Bienvenido</span><h1>Una cuenta a tu medida.</h1><p class="lead">Guarda tus datos de entrega y consulta el recorrido de tus pedidos en un solo lugar.</p>
    <form method="post" class="form-card">
        <?= csrf_campo() ?>
        <div class="form-field"><label for="nombre">Nombre completo</label><input id="nombre" name="nombre" value="<?= e($data['nombre']) ?>" autocomplete="name" minlength="2" maxlength="100" required><?php if (isset($errors['nombre'])): ?><p class="field-error" role="alert"><?= e($errors['nombre']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="<?= e($data['email']) ?>" autocomplete="email" maxlength="150" required><?php if (isset($errors['email'])): ?><p class="field-error" role="alert"><?= e($errors['email']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="telefono">Teléfono <span class="muted">(opcional)</span></label><input id="telefono" name="telefono" type="tel" value="<?= e($data['telefono']) ?>" autocomplete="tel" maxlength="20"><?php if (isset($errors['telefono'])): ?><p class="field-error" role="alert"><?= e($errors['telefono']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="10" maxlength="120" required><?php if (isset($errors['password'])): ?><p class="field-error" role="alert"><?= e($errors['password']) ?></p><?php endif; ?></div>
        <p class="form-note">Debe tener al menos 10 caracteres e incluir una letra y un número.</p>
        <button class="button button-dark" type="submit">Crear mi cuenta <span aria-hidden="true">→</span></button>
        <p class="form-links">¿Ya tienes cuenta? <a href="<?= e(url('auth/login.php')) ?>">Inicia sesión.</a></p>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
