<?php
require_once __DIR__ . '/../includes/init.php';

$city   = trim($_GET['city'] ?? '');
$max    = (int)($_GET['max_price'] ?? 0);
$gender = $_GET['gender'] ?? '';

$sql = 'SELECT p.id, p.name, p.city, p.area, p.price, p.gender, p.rating, p.image,
               (i.user_id IS NOT NULL) AS interested
        FROM properties p
        LEFT JOIN interested_users i ON i.property_id = p.id AND i.user_id = :uid
        WHERE 1=1';
$params = [':uid' => current_user_id()];

if ($city !== '')   { $sql .= ' AND p.city = :city';   $params[':city'] = $city; }
if ($max > 0)       { $sql .= ' AND p.price <= :max';  $params[':max'] = $max; }
if (in_array($gender, ['male', 'female', 'unisex'], true)) {
    $sql .= ' AND p.gender = :gender'; $params[':gender'] = $gender;
}
$sql .= ' ORDER BY p.rating DESC, p.price ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
foreach ($rows as &$r) {
    $r['id'] = (int)$r['id']; $r['price'] = (int)$r['price'];
    $r['rating'] = (float)$r['rating']; $r['interested'] = (bool)$r['interested'];
}
json_out(['properties' => $rows, 'logged_in' => is_logged_in()]);
