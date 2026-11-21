<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);
if (!is_logged_in()) json_out(['error' => 'Please log in to shortlist a PG.'], 401);
require_csrf();

$pid = (int)($_POST['property_id'] ?? 0);
$chk = db()->prepare('SELECT 1 FROM properties WHERE id = ?');
$chk->execute([$pid]);
if (!$chk->fetchColumn()) json_out(['error' => 'Property not found.'], 404);

$uid = current_user_id();
$del = db()->prepare('DELETE FROM interested_users WHERE user_id = ? AND property_id = ?');
$del->execute([$uid, $pid]);
if ($del->rowCount() > 0) {
    json_out(['interested' => false]);
}
$ins = db()->prepare('INSERT INTO interested_users (user_id, property_id) VALUES (?, ?)');
$ins->execute([$uid, $pid]);
json_out(['interested' => true]);
