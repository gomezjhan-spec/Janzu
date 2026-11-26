</main>
<section class="customize-strip" aria-label="Personalización">
    <div class="shell customize-inner">
        <div><span class="eyebrow">Una idea propia</span><h2>Tu diseño también tiene lugar aquí.</h2></div>
        <a class="button button-light" href="<?= e(url('contacto.php')) ?>">Hablemos de tu proyecto <span aria-hidden="true">↗</span></a>
    </div>
</section>
<footer class="site-footer">
    <div class="shell footer-main">
        <div class="footer-brand">
            <a class="wordmark wordmark-light" href="<?= e(url()) ?>"><span>JANZU</span><small>HEADWEAR COLLECTION</small></a>
            <p>Una selección de gorras para moverte a tu manera, todos los días.</p>
        </div>
        <div><h2 class="footer-heading">Explora</h2><a href="<?= e(url('tienda.php')) ?>">Toda la colección</a><a href="<?= e(url('tienda.php?cat=adultos')) ?>">Adultos</a><a href="<?= e(url('tienda.php?cat=jovenes')) ?>">Jóvenes</a><a href="<?= e(url('tienda.php?cat=ninos')) ?>">Niños</a></div>
        <div><h2 class="footer-heading">JANZU</h2><a href="<?= e(url('nosotros.php')) ?>">Nuestra historia</a><a href="<?= e(url('contacto.php')) ?>">Contacto</a><a href="<?= e(url('auth/registro.php')) ?>">Crear cuenta</a></div>
        <div><h2 class="footer-heading">Estamos en</h2><p>Cali, Valle del Cauca<br>Colombia</p><a class="footer-contact" href="<?= e(url('contacto.php')) ?>">Escríbenos <span aria-hidden="true">↗</span></a></div>
    </div>
    <div class="shell footer-bottom"><span>© <?= date('Y') ?> JANZU</span><span>HEADWEAR FOR EVERY DAY</span></div>
</footer>

<div class="drawer-backdrop" data-cart-backdrop data-cart-close></div>
<aside class="cart-drawer" id="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" aria-hidden="true" tabindex="-1" inert data-cart-drawer>
    <div class="drawer-header"><div><span class="eyebrow">JANZU / TU BOLSA</span><h2 id="cart-drawer-title">Tu selección</h2></div><button class="drawer-close" type="button" aria-label="Cerrar bolsa" data-cart-close>×</button></div>
    <div class="drawer-items" data-cart-drawer-items><p class="drawer-loading">Cargando tu bolsa…</p></div>
    <div class="drawer-footer"><div class="drawer-total"><span>Subtotal</span><strong data-cart-drawer-total>$0</strong></div><p>El envío se coordina al confirmar tu pedido.</p><a class="button button-dark button-full" href="<?= e(url('carrito.php')) ?>">Revisar bolsa <span aria-hidden="true">→</span></a><a class="drawer-continue" href="<?= e(url('tienda.php')) ?>">Seguir explorando</a></div>
</aside>
<div class="toast" aria-live="polite" aria-atomic="true" data-toast></div>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
