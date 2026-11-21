<?php
require_once __DIR__ . '/includes/init.php';
if (!is_logged_in()) { header('Location: login.php?next=shortlist.php'); exit; }
$pageTitle = 'My shortlist';
$extraScripts = [
  'https://cdnjs.cloudflare.com/ajax/libs/react/18.3.1/umd/react.production.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.3.1/umd/react-dom.production.min.js',
  'assets/js/shortlist.js',
];
require __DIR__ . '/includes/header.php';
?>
<h1 class="sn-h h2 mb-3">My shortlist</h1>
<!-- React renders the shortlist into this node -->
<div id="shortlist-root"><div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading</span></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
