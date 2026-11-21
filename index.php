<?php
require_once __DIR__ . '/includes/init.php';
$cities = db()->query('SELECT DISTINCT city FROM properties ORDER BY city')->fetchAll(PDO::FETCH_COLUMN);
$maxPrice = (int)db()->query('SELECT MAX(price) FROM properties')->fetchColumn();
$pageTitle = 'Find a PG';
require __DIR__ . '/includes/header.php';
?>
<section class="sn-hero">
  <h1 class="sn-h">A room near campus, without the broker.</h1>
  <p class="lead mb-0">Filter by city, budget and who it&rsquo;s for. Shortlist the places you like.</p>
</section>

<form id="filters" class="sn-filters row g-3 align-items-end" autocomplete="off">
  <div class="col-12 col-md-4">
    <label for="f-city" class="form-label">City</label>
    <select id="f-city" name="city" class="form-select">
      <option value="">All cities</option>
      <?php foreach ($cities as $c): ?><option><?= e($c) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-4">
    <label for="f-price" class="form-label">Max budget: <strong id="price-label">&#8377;<?= $maxPrice ?></strong> / month</label>
    <input type="range" id="f-price" name="max_price" class="form-range" min="5000" max="<?= $maxPrice ?>" step="500" value="<?= $maxPrice ?>">
  </div>
  <div class="col-8 col-md-3">
    <label for="f-gender" class="form-label">Suitable for</label>
    <select id="f-gender" name="gender" class="form-select">
      <option value="">Anyone</option>
      <option value="male">Boys</option>
      <option value="female">Girls</option>
      <option value="unisex">Unisex</option>
    </select>
  </div>
  <div class="col-4 col-md-1 d-grid">
    <button type="reset" id="f-reset" class="btn btn-outline-secondary">Reset</button>
  </div>
</form>

<div class="d-flex justify-content-between align-items-center mt-4 mb-2">
  <h2 class="h5 mb-0" id="result-count" aria-live="polite">Loading&hellip;</h2>
  <div id="spinner" class="spinner-border spinner-border-sm text-secondary d-none" role="status"><span class="visually-hidden">Loading</span></div>
</div>
<div id="listing" class="row g-4"></div>
<noscript><p class="alert alert-warning">Please enable JavaScript to browse listings.</p></noscript>
<?php require __DIR__ . '/includes/footer.php'; ?>
