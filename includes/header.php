<?php
require_once __DIR__ . '/funciones.php';
$currentUser = usuario();
$titulo = ($titulo ?? 'Headwear collection') . ' | JANZU';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="JANZU Headwear Collection. Gorras seleccionadas para acompañar tu estilo, todos los días.">
    <meta name="theme-color" content="#151515">
    <meta name="csrf-token" content="<?= e(csrf()) ?>">
    <title><?= e($titulo) ?></title>
    <link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main">Saltar al contenido</a>
<div class="announcement">Diseño para llevar todos los días <span aria-hidden="true">·</span> Cali, Colombia</div>
<header class="site-header">
    <div class="shell header-inner">
        <a class="wordmark" href="<?= e(url()) ?>" aria-label="JANZU, inicio">
            <span>JANZU</span><small>HEADWEAR COLLECTION</small>
        </a>
        <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="primary-nav" data-menu-toggle>
            <span></span><span></span>
        </button>
        <nav class="primary-nav" id="primary-nav" aria-label="Principal" data-primary-nav>
            <a href="<?= e(url('tienda.php')) ?>">Colección</a>
            <a href="<?= e(url('tienda.php?cat=adultos')) ?>">Adultos</a>
            <a href="<?= e(url('tienda.php?cat=jovenes')) ?>">Jóvenes</a>
            <a href="<?= e(url('tienda.php?cat=ninos')) ?>">Niños</a>
            <a href="<?= e(url('nosotros.php')) ?>">La marca</a>
            <?php if ($currentUser): ?><form class="nav-logout" method="post" action="<?= e(url('auth/logout.php')) ?>"><?= csrf_campo() ?><button class="text-button" type="submit">Cerrar sesión</button></form><?php endif; ?>
        </nav>
        <div class="header-actions">
            <a class="icon-link account-link" href="<?= e(url($currentUser ? 'cliente/index.php' : 'auth/login.php')) ?>" aria-label="<?= $currentUser ? 'Mi cuenta' : 'Iniciar sesión' ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M4.5 20c.7-3.6 3.2-5.5 7.5-5.5s6.8 1.9 7.5 5.5"></path></svg>
                <span><?= $currentUser ? 'Mi cuenta' : 'Entrar' ?></span>
            </a>
            <?php if ($currentUser && $currentUser['rol'] === 'admin'): ?><a class="admin-shortcut" href="<?= e(url('admin/index.php')) ?>">Panel</a><?php endif; ?>
            <a class="icon-link bag-link" href="<?= e(url('carrito.php')) ?>" aria-label="Abrir bolsa con <?= carrito_cantidad() ?> unidades" data-cart-toggle>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 8V6a3 3 0 0 1 6 0v2"></path></svg>
                <span class="bag-label">Bolsa</span><span class="bag-count" data-cart-count><?= carrito_cantidad() ?></span>
            </a>
        </div>
    </div>
</header>
<?php if ($flash = aviso()): ?>
    <div class="shell flash-wrap"><div class="flash <?= e($flash[0]) ?>" role="status" data-flash><?= e($flash[1]) ?></div></div>
<?php endif; ?>
<main id="main">
