<?php
require_once __DIR__ . '/../includes/init.php';
if (!is_logged_in()) json_out(['error' => 'Please log in.'], 401);

$stmt = db()->prepare('SELECT p.id, p.name, p.city, p.area, p.price, p.gender, p.rating, p.image
    FROM interested_users i JOIN properties p ON p.id = i.property_id
    WHERE i.user_id = ? ORDER BY i.created_at DESC');
$stmt->execute([current_user_id()]);
$rows = $stmt->fetchAll();
foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['price'] = (int)$r['price']; $r['rating'] = (float)$r['rating']; }
json_out(['properties' => $rows]);
