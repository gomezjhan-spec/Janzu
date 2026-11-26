<?php
require_once __DIR__ . '/includes/funciones.php';

$categories = db()->query('SELECT * FROM categorias ORDER BY id')->fetchAll();
$categorySlug = trim((string) ($_GET['cat'] ?? ''));
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
$sort = (string) ($_GET['orden'] ?? 'destacados');
$minPrice = isset($_GET['min']) && ctype_digit((string) $_GET['min']) ? (int) $_GET['min'] : null;
$maxPrice = isset($_GET['max']) && ctype_digit((string) $_GET['max']) ? (int) $_GET['max'] : null;
$inStock = (string) ($_GET['disponible'] ?? '') === '1';
$page = max(1, min(10000, (int) ($_GET['pagina'] ?? 1)));
$perPage = 12;
$orders = [
    'destacados' => 'p.destacado DESC, p.id DESC',
    'nuevos' => 'p.id DESC',
    'precio-menor' => 'p.precio ASC, p.id DESC',
    'precio-mayor' => 'p.precio DESC, p.id DESC',
    'nombre' => 'p.nombre ASC',
];
if (!isset($orders[$sort])) {
    $sort = 'destacados';
}

$where = ['p.activo = 1'];
$params = [];
if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($search !== '') {
    $where[] = '(p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($minPrice !== null) {
    $where[] = 'p.precio >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $where[] = 'p.precio <= ?';
    $params[] = $maxPrice;
}
if ($inStock) {
    $where[] = 'p.stock > 0';
}
$whereSql = implode(' AND ', $where);

$countQuery = db()->prepare("SELECT COUNT(*) FROM productos p JOIN categorias c ON c.id = p.categoria_id WHERE $whereSql");
$countQuery->execute($params);
$totalProducts = (int) $countQuery->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$productsQuery = db()->prepare(
    "SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id " .
    "WHERE $whereSql ORDER BY {$orders[$sort]} LIMIT :limit OFFSET :offset"
);
foreach ($params as $index => $value) {
    $productsQuery->bindValue($index + 1, $value);
}
$productsQuery->bindValue(':limit', $perPage, PDO::PARAM_INT);
$productsQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$productsQuery->execute();
$products = $productsQuery->fetchAll();

$selectedCategory = null;
foreach ($categories as $category) {
    if ($category['slug'] === $categorySlug) {
        $selectedCategory = $category;
        break;
    }
}
$titulo = $selectedCategory ? 'Gorras ' . $selectedCategory['nombre'] : 'La colección';
require __DIR__ . '/includes/header.php';
?>
<section class="shell section">
    <nav class="breadcrumb" aria-label="Ruta"><a href="<?= e(url()) ?>">Inicio</a><span>·</span><strong><?= e($titulo) ?></strong></nav>
    <div class="page-intro"><span class="eyebrow">JANZU / Colección</span><h1><?= e($titulo) ?></h1><p><?= e($selectedCategory['descripcion'] ?? 'Encuentra una silueta que encaje con tu manera de llevar el día.') ?></p></div>

    <form class="catalog-controls" method="get" action="<?= e(url('tienda.php')) ?>" role="search">
        <div class="filter-group">
            <div class="filter-field"><label for="q">Buscar</label><input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Nombre o detalle"></div>
            <div class="filter-field"><label for="cat">Categoría</label><select id="cat" name="cat"><option value="">Toda la colección</option><?php foreach ($categories as $category): ?><option value="<?= e($category['slug']) ?>" <?= $categorySlug === $category['slug'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="filter-field"><label for="min">Precio desde</label><input id="min" name="min" type="number" min="0" step="1000" value="<?= $minPrice !== null ? $minPrice : '' ?>" placeholder="$ 0"></div>
            <div class="filter-field"><label for="max">Precio hasta</label><input id="max" name="max" type="number" min="0" step="1000" value="<?= $maxPrice !== null ? $maxPrice : '' ?>" placeholder="Sin límite"></div>
            <label class="check-field"><input type="checkbox" name="disponible" value="1" <?= $inStock ? 'checked' : '' ?>> Disponibles ahora</label>
        </div>
        <div class="filter-group">
            <div class="filter-field"><label for="orden-select">Ordenar por</label><select id="orden-select" name="orden"><option value="destacados" <?= $sort === 'destacados' ? 'selected' : '' ?>>Selección JANZU</option><option value="nuevos" <?= $sort === 'nuevos' ? 'selected' : '' ?>>Más recientes</option><option value="precio-menor" <?= $sort === 'precio-menor' ? 'selected' : '' ?>>Precio: menor a mayor</option><option value="precio-mayor" <?= $sort === 'precio-mayor' ? 'selected' : '' ?>>Precio: mayor a menor</option><option value="nombre" <?= $sort === 'nombre' ? 'selected' : '' ?>>Nombre</option></select></div>
            <button class="button button-dark" type="submit">Aplicar filtros</button>
        </div>
    </form>

    <div class="section-heading"><span class="catalog-result"><?= $totalProducts ?> <?= $totalProducts === 1 ? 'pieza' : 'piezas' ?></span>
        <?php if ($categorySlug !== '' || $search !== '' || $minPrice !== null || $maxPrice !== null || $inStock): ?><a class="text-link" href="<?= e(url('tienda.php')) ?>">Quitar filtros <span aria-hidden="true">×</span></a><?php endif; ?>
    </div>
    <?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product) { echo tarjeta($product); } ?></div>
    <?php else: ?><div class="empty-state"><span class="eyebrow">Sin resultados</span><h2>No encontramos esa selección.</h2><p>Prueba con otra búsqueda o quita los filtros para explorar todo el catálogo.</p><a class="button button-dark" href="<?= e(url('tienda.php')) ?>">Ver toda la colección</a></div><?php endif; ?>

    <?php if ($totalPages > 1): $queryArgs = $_GET; unset($queryArgs['pagina']); ?>
        <nav class="pagination" aria-label="Páginas del catálogo">
            <?php if ($page > 1): $queryArgs['pagina'] = $page - 1; ?><a href="<?= e(url('tienda.php?' . http_build_query($queryArgs))) ?>" aria-label="Página anterior">←</a><?php endif; ?>
            <?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): $queryArgs['pagina'] = $number; ?>
                <?php if ($number === $page): ?><span class="current" aria-current="page"><?= $number ?></span><?php else: ?><a href="<?= e(url('tienda.php?' . http_build_query($queryArgs))) ?>"><?= $number ?></a><?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): $queryArgs['pagina'] = $page + 1; ?><a href="<?= e(url('tienda.php?' . http_build_query($queryArgs))) ?>" aria-label="Página siguiente">→</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
