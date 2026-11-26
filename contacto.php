<?php
require_once __DIR__ . '/includes/funciones.php';
$fields = ['nombre' => '', 'email' => '', 'asunto' => '', 'mensaje' => ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_ok();
    foreach ($fields as $key => $_) {
        $fields[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    if (mb_strlen($fields['nombre']) < 2 || mb_strlen($fields['nombre']) > 100) {
        $errors['nombre'] = 'Escribe un nombre de entre 2 y 100 caracteres.';
    }
    if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($fields['email']) > 150) {
        $errors['email'] = 'Escribe un correo válido.';
    }
    if (mb_strlen($fields['asunto']) < 3 || mb_strlen($fields['asunto']) > 120) {
        $errors['asunto'] = 'Resume el asunto en 3 a 120 caracteres.';
    }
    if (mb_strlen($fields['mensaje']) < 10 || mb_strlen($fields['mensaje']) > 5000) {
        $errors['mensaje'] = 'Escribe un mensaje de entre 10 y 5.000 caracteres.';
    }
    if (!$errors) {
        db()->prepare('INSERT INTO mensajes (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)')
            ->execute(array_values($fields));
        aviso('Recibimos tu mensaje. Lo revisaremos desde el panel de JANZU.');
        ir('contacto.php');
    }
}
$titulo = 'Contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="shell section form-shell">
    <span class="eyebrow">JANZU / Hablemos</span><h1>Cuéntanos qué tienes en mente.</h1><p class="lead">¿Tienes una pregunta, buscas una pieza en particular o quieres explorar una personalización? Escríbenos y revisaremos tu mensaje desde nuestro equipo.</p>
    <form method="post" class="form-card">
        <?= csrf_campo() ?>
        <div class="form-field"><label for="nombre">Nombre</label><input id="nombre" name="nombre" value="<?= e($fields['nombre']) ?>" autocomplete="name" maxlength="100" required><?php if (isset($errors['nombre'])): ?><p class="field-error" role="alert"><?= e($errors['nombre']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="<?= e($fields['email']) ?>" autocomplete="email" maxlength="150" required><?php if (isset($errors['email'])): ?><p class="field-error" role="alert"><?= e($errors['email']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="asunto">Asunto</label><input id="asunto" name="asunto" value="<?= e($fields['asunto']) ?>" maxlength="120" required><?php if (isset($errors['asunto'])): ?><p class="field-error" role="alert"><?= e($errors['asunto']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="mensaje">Mensaje</label><textarea id="mensaje" name="mensaje" maxlength="5000" required><?= e($fields['mensaje']) ?></textarea><?php if (isset($errors['mensaje'])): ?><p class="field-error" role="alert"><?= e($errors['mensaje']) ?></p><?php endif; ?></div>
        <button class="button button-dark" type="submit">Enviar mensaje <span aria-hidden="true">→</span></button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
