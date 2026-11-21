<?php
require_once __DIR__ . '/includes/init.php';
$next = $_GET['next'] ?? $_POST['next'] ?? 'index.php';
// Only allow local redirects
if (!preg_match('/^[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9_=&\-]*)?$/', $next)) $next = 'index.php';
if (is_logged_in()) { header('Location: ' . $next); exit; }

$error = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $stmt = db()->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . $next); exit;
        }
        $error = 'Email or password is incorrect.';
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h3 sn-h mb-3">Welcome back</h1>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="sn-panel">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="mb-3"><label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required></div>
      <div class="mb-3"><label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" required></div>
      <button class="btn sn-btn w-100">Log in</button>
      <p class="mt-3 mb-0 small">New here? <a href="signup.php">Create an account</a></p>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
