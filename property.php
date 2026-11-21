<?php
require_once __DIR__ . '/includes/init.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); $pageTitle = 'Not found'; require __DIR__ . '/includes/header.php';
  echo '<div class="alert alert-warning">That property does not exist. <a href="index.php">Back to listings</a></div>';
  require __DIR__ . '/includes/footer.php'; exit; }

$imgs = db()->prepare('SELECT image FROM property_images WHERE property_id = ? ORDER BY id');
$imgs->execute([$id]);
$images = $imgs->fetchAll(PDO::FETCH_COLUMN);
array_unshift($images, $p['image']);

$am = db()->prepare('SELECT a.name FROM amenities a JOIN property_amenities pa ON pa.amenity_id = a.id WHERE pa.property_id = ? ORDER BY a.name');
$am->execute([$id]);
$amenities = $am->fetchAll(PDO::FETCH_COLUMN);

$interested = false;
if (is_logged_in()) {
    $c = db()->prepare('SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?');
    $c->execute([current_user_id(), $id]);
    $interested = (bool)$c->fetchColumn();
}
$icons = ['WiFi'=>'wifi','Meals included'=>'egg-fried','Air conditioning'=>'snow','Laundry'=>'basket','Power backup'=>'lightning-charge','CCTV security'=>'camera-video','Study room'=>'book','Housekeeping'=>'brush'];
$genderLabel = ['male'=>'Boys','female'=>'Girls','unisex'=>'Unisex'][$p['gender']];
$pageTitle = $p['name'];
require __DIR__ . '/includes/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb small">
  <li class="breadcrumb-item"><a href="index.php">Browse PGs</a></li>
  <li class="breadcrumb-item active"><?= e($p['name']) ?></li>
</ol></nav>

<div class="row g-4">
  <div class="col-lg-7">
    <div id="gallery" class="carousel slide sn-gallery" data-bs-ride="false">
      <div class="carousel-inner">
        <?php foreach ($images as $i => $src): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img src="<?= e($src) ?>" class="d-block w-100" alt="<?= e($p['name']) ?> photo <?= $i + 1 ?>" loading="<?= $i ? 'lazy' : 'eager' ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#gallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Previous</span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#gallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>
    </div>
    <div class="d-flex gap-2 mt-2">
      <?php foreach ($images as $i => $src): ?>
        <img src="<?= e($src) ?>" class="sn-thumb" data-bs-target="#gallery" data-bs-slide-to="<?= $i ?>" alt="Show photo <?= $i + 1 ?>">
      <?php endforeach; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <span class="badge sn-badge sn-badge-<?= e($p['gender']) ?>"><?= e($genderLabel) ?></span>
    <h1 class="sn-h h2 mt-2"><?= e($p['name']) ?></h1>
    <p class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($p['area']) ?>, <?= e($p['city']) ?></p>
    <div class="d-flex align-items-baseline gap-3 mb-3">
      <span class="sn-price">&#8377;<?= number_format($p['price']) ?><small>/month</small></span>
      <span class="sn-rating" title="Rating"><i class="bi bi-star-fill"></i> <?= number_format((float)$p['rating'], 1) ?></span>
    </div>
    <button id="interest-btn" class="btn sn-btn btn-lg w-100 <?= $interested ? 'is-on' : '' ?>"
            data-id="<?= $id ?>" aria-pressed="<?= $interested ? 'true' : 'false' ?>">
      <i class="bi <?= $interested ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
      <span><?= $interested ? 'Shortlisted' : 'Shortlist this PG' ?></span>
    </button>
    <h2 class="h5 mt-4">About this PG</h2>
    <p><?= nl2br(e($p['description'])) ?></p>
    <h2 class="h5 mt-4">Amenities</h2>
    <ul class="list-unstyled sn-amenities">
      <?php foreach ($amenities as $a): ?>
        <li><i class="bi bi-<?= e($icons[$a] ?? 'check-circle') ?>"></i> <?= e($a) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
