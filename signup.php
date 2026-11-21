<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) { header('Location: index.php'); exit; }

$errors = []; $old = ['name' => '', 'email' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { $errors[] = 'Session expired. Please try again.'; }
    $old['name']  = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($old['name'] === '' || mb_strlen($old['name']) > 100) $errors[] = 'Enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9+\- ]{10,15}$/', $old['phone'])) $errors[] = 'Enter a valid phone number (10-15 digits).';
    if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists. Log in instead.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)');
            $stmt->execute([$old['name'], $old['email'], password_hash($pass, PASSWORD_DEFAULT), $old['phone']]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            $_SESSION['user_name'] = $old['name'];
            header('Location: index.php'); exit;
        }
    }
}
$pageTitle = 'Sign up';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h3 sn-h mb-3">Create your account</h1>
    <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" class="sn-panel" novalidate>
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <div class="mb-3"><label class="form-label" for="name">Full name</label>
        <input class="form-control" id="name" name="name" value="<?= e($old['name']) ?>" required></div>
      <div class="mb-3"><label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" value="<?= e($old['email']) ?>" required></div>
      <div class="mb-3"><label class="form-label" for="phone">Phone</label>
        <input class="form-control" type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" required></div>
      <div class="mb-3"><label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" minlength="8" required>
        <div class="form-text">At least 8 characters.</div></div>
      <button class="btn sn-btn w-100">Create account</button>
      <p class="mt-3 mb-0 small">Already registered? <a href="login.php">Log in</a></p>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
