<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$db = get_db();
$bookId = (int)($_POST['book_id'] ?? 0);
$user = current_user();
if ($bookId > 0 && $user) {
	$sql = 'INSERT INTO favorites(user_id, book_id) VALUES(:u,:b) ON DUPLICATE KEY UPDATE date_added = CURRENT_TIMESTAMP';
	$db->run($sql, ['u' => $user['user_id'], 'b' => $bookId]);
}
header('Location: book.php?id=' . $bookId);
exit;
?>


