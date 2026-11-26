(() => {
    const body = document.body;
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const primaryNav = document.querySelector('[data-primary-nav]');
    const cartToggle = document.querySelector('[data-cart-toggle]');
    const cartDrawer = document.querySelector('[data-cart-drawer]');
    const drawerItems = document.querySelector('[data-cart-drawer-items]');
    const drawerTotal = document.querySelector('[data-cart-drawer-total]');
    const toast = document.querySelector('[data-toast]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let lastFocus = null;
    let toastTimer = null;
    let cartState = { items: [], count: 0, total: 0 };

    const money = value => '$' + new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(Number(value || 0));

    function showToast(message) {
        if (!toast || !message) return;
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    function updateCounts(count) {
        document.querySelectorAll('[data-cart-count]').forEach(node => { node.textContent = String(count); });
        if (cartToggle) cartToggle.setAttribute('aria-label', `Abrir bolsa con ${count} unidades`);
        const pageCount = document.querySelector('[data-cart-page-count]');
        if (pageCount) pageCount.textContent = String(count);
    }

    function node(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    }

    function renderDrawer() {
        if (!drawerItems) return;
        drawerItems.replaceChildren();
        if (!cartState.items.length) {
            const empty = node('div', 'drawer-empty');
            empty.append(node('span', 'eyebrow', 'Tu bolsa está vacía'));
            empty.append(node('h3', '', 'Empieza por una pieza que te guste.'));
            empty.append(node('p', '', 'Explora la colección y guarda tus favoritas aquí.'));
            drawerItems.append(empty);
        } else {
            cartState.items.forEach(item => {
                const row = node('article', 'drawer-item');
                const imageLink = node('a', 'drawer-item-image');
                imageLink.href = item.productUrl;
                const image = node('img');
                image.src = item.image;
                image.alt = `Gorra ${item.name}`;
                image.loading = 'lazy';
                imageLink.append(image);

                const info = node('div', 'drawer-item-info');
                const title = node('h3', 'drawer-item-name');
                const productLink = node('a', '', item.name);
                productLink.href = item.productUrl;
                title.append(productLink);
                info.append(title, node('p', 'drawer-item-price', `${money(item.price)} · ${money(item.subtotal)}`));
                const quantity = node('div', 'drawer-quantity');
                const decrease = node('button', '', '−');
                decrease.type = 'button';
                decrease.setAttribute('aria-label', `Quitar una unidad de ${item.name}`);
                decrease.dataset.cartDelta = '-1';
                decrease.dataset.itemId = String(item.id);
                const count = node('span', '', String(item.quantity));
                const increase = node('button', '', '+');
                increase.type = 'button';
                increase.setAttribute('aria-label', `Añadir una unidad de ${item.name}`);
                increase.dataset.cartDelta = '1';
                increase.dataset.itemId = String(item.id);
                increase.disabled = item.quantity >= item.stock;
                quantity.append(decrease, count, increase);
                info.append(quantity);

                const remove = node('button', 'drawer-remove', '×');
                remove.type = 'button';
                remove.setAttribute('aria-label', `Quitar ${item.name} de la bolsa`);
                remove.dataset.cartRemoveId = String(item.id);
                row.append(imageLink, info, remove);
                drawerItems.append(row);
            });
        }
        if (drawerTotal) drawerTotal.textContent = money(cartState.total);
        updateCounts(cartState.count);
    }

    async function requestCart(data, endpoint) {
        if (!data.has('csrf')) data.set('csrf', csrfToken);
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (Array.isArray(result.items)) {
                cartState = result;
                renderDrawer();
                updateCartPage(result);
            }
            if (!response.ok || !result.ok) showToast(result.message || 'No pudimos actualizar tu bolsa.');
            else if (result.message) showToast(result.message);
            return result;
        } catch (error) {
            showToast('No fue posible conectar con la tienda. Inténtalo de nuevo.');
            return null;
        }
    }

    async function loadCart() {
        if (!cartToggle) return;
        const endpoint = new URL(cartToggle.href, window.location.href);
        endpoint.search = '';
        endpoint.searchParams.set('mini', '1');
        try {
            const response = await fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                cartState = await response.json();
                renderDrawer();
            }
        } catch (error) {
            if (drawerItems) drawerItems.replaceChildren(node('p', 'drawer-loading', 'La bolsa no está disponible en este momento.'));
        }
    }

    function openDrawer() {
        if (!cartDrawer) return;
        lastFocus = document.activeElement;
        cartDrawer.removeAttribute('inert');
        document.querySelectorAll('.site-header, main, .customize-strip, .site-footer, .skip-link').forEach(element => element.setAttribute('inert', ''));
        body.classList.add('drawer-open');
        cartDrawer.setAttribute('aria-hidden', 'false');
        cartDrawer.focus();
        loadCart();
    }

    function closeDrawer() {
        if (!cartDrawer) return;
        body.classList.remove('drawer-open');
        cartDrawer.setAttribute('aria-hidden', 'true');
        cartDrawer.setAttribute('inert', '');
        document.querySelectorAll('.site-header, main, .customize-strip, .site-footer, .skip-link').forEach(element => element.removeAttribute('inert'));
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    if (menuToggle && primaryNav) {
        menuToggle.addEventListener('click', () => {
            const open = menuToggle.getAttribute('aria-expanded') !== 'true';
            menuToggle.setAttribute('aria-expanded', String(open));
            primaryNav.classList.toggle('open', open);
        });
        primaryNav.addEventListener('click', event => {
            if (event.target.closest('a')) {
                menuToggle.setAttribute('aria-expanded', 'false');
                primaryNav.classList.remove('open');
            }
        });
    }

    if (cartToggle && cartDrawer) {
        cartToggle.addEventListener('click', event => {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            openDrawer();
        });
        document.querySelectorAll('[data-cart-close]').forEach(element => element.addEventListener('click', closeDrawer));
        document.addEventListener('keydown', event => {
            if (!body.classList.contains('drawer-open')) return;
            if (event.key === 'Escape') {
                closeDrawer();
                return;
            }
            if (event.key === 'Tab') {
                const focusable = [...cartDrawer.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')];
                if (!focusable.length) {
                    event.preventDefault();
                    cartDrawer.focus();
                    return;
                }
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && (document.activeElement === first || document.activeElement === cartDrawer)) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    document.addEventListener('submit', async event => {
        const form = event.target.closest('[data-cart-add]');
        if (!form) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;
        const result = await requestCart(new FormData(form), form.action);
        if (button) button.disabled = false;
        if (result?.ok && body.classList.contains('drawer-open')) renderDrawer();
    });

    document.addEventListener('click', async event => {
        const deltaButton = event.target.closest('[data-cart-delta]');
        const removeButton = event.target.closest('[data-cart-remove-id]');
        const updateButton = deltaButton || removeButton;
        if (!updateButton) return;
        const id = updateButton.dataset.itemId || updateButton.dataset.cartRemoveId;
        const item = cartState.items.find(entry => String(entry.id) === String(id));
        if (!item) return;
        const nextQuantity = removeButton ? 0 : item.quantity + Number(deltaButton.dataset.cartDelta);
        const data = new FormData();
        data.set('accion', nextQuantity < 1 ? 'quitar' : 'actualizar');
        data.set(nextQuantity < 1 ? 'id' : 'id', String(id));
        data.set('cantidad', String(nextQuantity));
        if (nextQuantity < 1) data.set('quitar', String(id));
        updateButton.disabled = true;
        await requestCart(data, cartToggle.href);
        updateButton.disabled = false;
    });

    function updateCartPage(payload) {
        const pageTotal = document.querySelector('[data-cart-page-total]');
        if (pageTotal) pageTotal.textContent = money(payload.total);
        const pageCount = document.querySelector('[data-cart-page-count]');
        if (pageCount) pageCount.textContent = String(payload.count);
    }

    const cartForm = document.querySelector('[data-cart-update-form]');
    if (cartForm) {
        const updateStatus = document.querySelector('[data-cart-update-status]');
        const checkoutButton = document.querySelector('.cart-summary button[type="submit"]');
        cartForm.addEventListener('input', event => {
            if (!event.target.matches('[data-cart-quantity]')) return;
            let total = 0;
            let count = 0;
            cartForm.querySelectorAll('[data-cart-row]').forEach(row => {
                const input = row.querySelector('[data-cart-quantity]');
                const quantity = Math.max(0, Number(input.value || 0));
                const subtotal = quantity * Number(row.dataset.unitPrice || 0);
                total += subtotal;
                count += quantity;
                const rowSubtotal = row.querySelector('[data-row-subtotal]');
                if (rowSubtotal) rowSubtotal.textContent = money(subtotal);
            });
            updateCartPage({ total, count });
            if (updateStatus) updateStatus.textContent = 'Hay cambios por guardar. Actualiza la bolsa antes de crear el pedido.';
            if (checkoutButton) checkoutButton.disabled = true;
        });
        cartForm.addEventListener('submit', event => {
            const removeButton = event.submitter;
            if (removeButton?.hasAttribute('data-cart-remove')) return;
            if (checkoutButton) checkoutButton.disabled = false;
        });
    }

    document.querySelectorAll('[data-flash]').forEach(message => setTimeout(() => message.remove(), 8000));
    loadCart();
})();
