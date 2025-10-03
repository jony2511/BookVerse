<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$db = get_db();

$user = current_user();
$bookId = (int)($_POST['book_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($bookId > 0) {
	try {
		$db->run('CALL sp_add_review(:u,:b,:r,:c)', [
			'u' => $user['user_id'],
			'b' => $bookId,
			'r' => $rating,
			'c' => $comment,
		]);
	} catch (Throwable $e) {
		// For simplicity, ignore; could flash error in session
	}
}
header('Location: book.php?id=' . $bookId);
exit;
?>


