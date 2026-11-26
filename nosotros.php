<?php
$titulo = 'La marca';
require __DIR__ . '/includes/header.php';
?>
<section class="shell section">
    <nav class="breadcrumb" aria-label="Ruta"><a href="<?= e(url()) ?>">Inicio</a><span>·</span><strong>La marca</strong></nav>
    <div class="page-intro"><span class="eyebrow">JANZU / Cali, Colombia</span><h1>El detalle que completa el día.</h1><p class="lead">Creemos que una gorra puede ser muchas cosas a la vez: una pieza útil, una forma de expresarte y el inicio de una buena conversación.</p></div>
    <div class="editorial-band brand-feature">
        <div class="editorial-block"><span class="eyebrow">Nuestra mirada</span><h2>Una marca cercana, una colección con intención.</h2><p>JANZU nace en Cali con una idea sencilla: reunir gorras para diferentes estilos y momentos, con una experiencia clara desde que eliges una pieza hasta que recibes tu pedido.</p><p>Nos interesa cuidar cada paso: mostrar bien lo que ofrecemos, hablar con transparencia y estar disponibles cuando necesitas ayuda.</p></div>
        <div class="editorial-image"><img src="<?= e(url('assets/img/productos/ny-negra.jpg')) ?>" alt="Gorra negra de la colección JANZU" loading="lazy"></div>
    </div>
</section>
<section class="shell section">
    <span class="eyebrow">Lo que nos guía</span><h2>Una forma de hacer las cosas.</h2>
    <div class="category-grid">
        <article class="category-card"><div><span class="eyebrow">01 / Propósito</span><h3>Encontrar la tuya.</h3><p>Ofrecer piezas que sumen comodidad y carácter a la vida cotidiana, con opciones para distintas edades y preferencias.</p></div><span class="category-link">Misión</span></article>
        <article class="category-card"><div><span class="eyebrow">02 / Camino</span><h3>Crecer con identidad.</h3><p>Construir una marca colombiana reconocida por su estilo, la atención cercana y una colección que evoluciona con sus clientes.</p></div><span class="category-link">Visión</span></article>
        <article class="category-card"><div><span class="eyebrow">03 / Principios</span><h3>Claro y cercano.</h3><p>Mostrar información útil, cuidar la disponibilidad y acompañarte en cada consulta y pedido.</p></div><span class="category-link">Nuestra promesa</span></article>
    </div>
</section>
<section class="shell section narrow">
    <span class="eyebrow">El equipo</span><h2>Hecho en equipo.</h2><p>JANZU es un proyecto académico desarrollado por Juan Daniel Gamboa, Jhan Franco Villegas, Juan José Paz y Antonella Insuasti, desde la Universidad del Valle, Sede Norte del Cauca.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
