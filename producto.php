<?php
require_once __DIR__ . '/includes/funciones.php';
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$query = db()->prepare(
    'SELECT p.*, c.nombre AS categoria, c.slug AS categoria_slug FROM productos p ' .
    'JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.activo = 1 LIMIT 1'
);
$query->execute([$productId]);
$product = $query->fetch();
if (!$product) {
    aviso('Esa gorra no está disponible en este momento.', 'error');
    ir('tienda.php');
}
$titulo = $product['nombre'];
require __DIR__ . '/includes/header.php';
?>
<section class="shell section">
    <nav class="breadcrumb" aria-label="Ruta"><a href="<?= e(url()) ?>">Inicio</a><span>·</span><a href="<?= e(url('tienda.php')) ?>">Colección</a><span>·</span><a href="<?= e(url('tienda.php?cat=' . rawurlencode($product['categoria_slug']))) ?>"><?= e($product['categoria']) ?></a></nav>
    <div class="product-detail">
        <div class="product-detail-visual"><?= foto($product) ?></div>
        <div class="product-detail-copy">
            <span class="eyebrow">JANZU / <?= e($product['categoria']) ?></span>
            <h1><?= e($product['nombre']) ?></h1>
            <div class="product-detail-price"><span><?= dinero($product['precio']) ?></span><small>Pesos colombianos (COP)</small></div>
            <p class="product-description"><?= nl2br(e($product['descripcion'])) ?></p>
            <?php if ((int) $product['stock'] > 0): ?>
                <span class="stock-state">Disponible · <?= (int) $product['stock'] ?> unidades</span>
                <form class="add-product-form" method="post" action="<?= e(url('carrito.php')) ?>" data-cart-add>
                    <?= csrf_campo() ?><input type="hidden" name="accion" value="agregar"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                    <label for="cantidad">Unidades<input id="cantidad" type="number" name="cantidad" value="1" min="1" max="<?= (int) $product['stock'] ?>" required></label>
                    <button class="button button-dark" type="submit">Añadir a la bolsa <span aria-hidden="true">→</span></button>
                </form>
            <?php else: ?><span class="stock-state out">Agotada por ahora</span><?php endif; ?>
            <p class="product-meta">Unidades disponibles actualizadas desde inventario. El envío se coordina al confirmar el pedido.</p>
            <div class="details-list">
                <details><summary>Sobre esta pieza</summary><p><?= nl2br(e($product['descripcion'])) ?></p></details>
                <details><summary>Tu pedido</summary><p>Al confirmar, guardamos las unidades disponibles y podrás seguir el estado desde tu cuenta. El pago y el envío se coordinan contigo.</p></details>
                <details><summary>¿Tienes una pregunta?</summary><p><a href="<?= e(url('contacto.php')) ?>">Escríbenos desde aquí</a> y te ayudaremos con los detalles de esta pieza.</p></details>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
