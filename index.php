<?php
$titulo = 'Headwear for every day';
require_once __DIR__ . '/includes/funciones.php';
require __DIR__ . '/includes/header.php';

$featured = db()->query(
    "SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id " .
    "WHERE p.activo = 1 ORDER BY p.destacado DESC, p.id DESC LIMIT 6"
)->fetchAll();
$categories = db()->query('SELECT * FROM categorias ORDER BY id')->fetchAll();
?>
<section class="hero">
    <div class="hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">JANZU / Headwear collection</span>
            <h1>La forma más simple de <em>ser tú.</em></h1>
            <p class="lead">Gorras elegidas para acompañar tu ritmo. Siluetas clásicas, detalles cuidados y un estilo que se siente propio.</p>
            <div class="hero-actions">
                <a class="button button-dark" href="<?= e(url('tienda.php')) ?>">Explorar colección <span aria-hidden="true">→</span></a>
                <a class="button button-outline" href="<?= e(url('nosotros.php')) ?>">Conoce JANZU</a>
            </div>
        </div>
        <div class="hero-visual" aria-label="Gorras JANZU en azul y negro">
            <figure class="hero-photo"><span class="hero-note">Everyday essentials · 01</span><img src="<?= e(url('assets/img/productos/la-azul.jpg')) ?>" alt="Gorra azul de visera plana" fetchpriority="high"><figcaption>Color con carácter</figcaption></figure>
            <figure class="hero-photo"><span class="hero-note">Everyday essentials · 02</span><img src="<?= e(url('assets/img/productos/ny-negra.jpg')) ?>" alt="Gorra negra de estilo urbano" fetchpriority="high"><figcaption>Clásicos de siempre</figcaption></figure>
        </div>
    </div>
</section>

<section class="shell section">
    <div class="section-heading"><div><span class="eyebrow">Encuentra tu silueta</span><h2>Elige tu camino.</h2></div><p>Una colección pensada para distintas edades, planes y formas de llevar tu estilo.</p></div>
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="<?= e(url('tienda.php?cat=' . rawurlencode($category['slug']))) ?>">
                <div><span class="eyebrow">Colección / 0<?= (int) $category['id'] ?></span><h3><?= e($category['nombre']) ?></h3><p><?= e($category['descripcion'] ?? 'Diseños para acompañarte todos los días.') ?></p></div>
                <span class="category-link">Ver selección <span aria-hidden="true">↗</span></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="shell section">
    <div class="section-heading"><div><span class="eyebrow">Selección de la casa</span><h2>Piezas para llevar.</h2></div><a class="text-link" href="<?= e(url('tienda.php')) ?>">Ver toda la colección <span aria-hidden="true">→</span></a></div>
    <?php if ($featured): ?><div class="product-grid"><?php foreach ($featured as $product) { echo tarjeta($product); } ?></div>
    <?php else: ?><div class="empty-state"><p>Estamos preparando nuestra primera selección.</p><a class="button button-dark" href="<?= e(url('contacto.php')) ?>">Contáctanos</a></div><?php endif; ?>
</section>

<section class="shell section">
    <div class="editorial-band">
        <div class="editorial-block"><span class="eyebrow">Hecho para tu día</span><h2>Lo esencial también habla de ti.</h2><p>Una buena gorra combina comodidad, carácter y versatilidad. Encuentra ese detalle que acompaña tu rutina desde la mañana hasta el último plan.</p><a class="text-link" href="<?= e(url('nosotros.php')) ?>">Conoce nuestra mirada <span aria-hidden="true">→</span></a></div>
        <div class="editorial-image"><img src="<?= e(url('assets/img/productos/la-negra.jpg')) ?>" alt="Detalle de una gorra negra JANZU" loading="lazy"></div>
    </div>
</section>

<section class="shell section" aria-label="La experiencia JANZU">
    <div class="promise-grid">
        <div class="promise"><span class="promise-mark">01</span><div><h3>Una selección pensada</h3><p>Modelos para distintos estilos y momentos del día.</p></div></div>
        <div class="promise"><span class="promise-mark">02</span><div><h3>Compra con claridad</h3><p>Disponibilidad y total visibles antes de crear tu pedido.</p></div></div>
        <div class="promise"><span class="promise-mark">03</span><div><h3>Atención cercana</h3><p>Cuéntanos qué buscas y te ayudamos a encontrarlo.</p></div></div>
    </div>
</section>

<section class="shell section narrow">
    <span class="eyebrow">Preguntas frecuentes</span><h2>Antes de elegir.</h2>
    <div class="details-list">
        <details><summary>¿Cómo elijo una gorra?</summary><p>En cada producto encontrarás su silueta, material, ajuste y disponibilidad. Si necesitas una recomendación, escríbenos desde la página de contacto.</p></details>
        <details><summary>¿Cómo funciona el pedido?</summary><p>Añade las unidades que quieras a tu bolsa. Al confirmar, guardaremos tu pedido y podrás consultar su estado en tu cuenta. Coordinaremos contigo el pago y el envío.</p></details>
        <details><summary>¿Puedo solicitar una personalización?</summary><p>Sí. Envíanos tu idea, el tipo de gorra y la cantidad aproximada. Revisaremos contigo las opciones disponibles.</p></details>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
