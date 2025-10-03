<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: books.php'); exit; }

$sqlBook = "SELECT b.*, c.cat_name, COALESCE(v.avg_rating,0) AS avg_rating, v.review_count\nFROM books b\nINNER JOIN categories c ON c.cat_id = b.category_id\nLEFT JOIN v_book_ratings v ON v.book_id = b.book_id\nWHERE b.book_id = :id";
$book = $db->run($sqlBook, ['id' => $id])->fetch();
if (!$book) { header('Location: books.php'); exit; }

render_header($book['title'], $book['author'] . ' · ' . $book['cat_name']);

echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-6">';
echo '<div class="md:col-span-1">';
echo '<img class="w-full h-64 object-cover rounded border" src="' . e($book['picture']) . '" alt="' . e($book['title']) . '">';
echo '<div class="mt-3 text-sm">⭐ ' . number_format((float)$book['avg_rating'],1) . ' (' . (int)$book['review_count'] . ' reviews)</div>';
echo '</div>';

echo '<div class="md:col-span-2">';
echo '<div class="space-x-2 mb-4">';
$favPreview = "INSERT INTO favorites(user_id,book_id) VALUES(?, ?) ON DUPLICATE KEY UPDATE date_added = CURRENT_TIMESTAMP";
echo '<form method="post" action="favorite_toggle.php" class="inline">';
echo '<input type="hidden" name="book_id" value="' . (int)$id . '">';
echo '<button class="px-3 py-2 bg-emerald-600 text-white rounded" title="' . e($favPreview) . '">Add/Update Favorite</button>';
echo '</form>';

echo '</div>';
echo '<h3 class="font-semibold mb-2">Reviews</h3>';

// Add review form
if (current_user()) {
	echo '<form method="post" action="review_add.php" class="mb-4 border rounded p-3 bg-white">';
	echo '<input type="hidden" name="book_id" value="' . (int)$id . '">';
	echo '<label class="block text-sm">Rating (1-5)</label><input required type="number" min="1" max="5" name="rating" class="border rounded px-2 py-1 mb-2">';
	echo '<label class="block text-sm">Comment</label><textarea name="comment" class="border rounded px-2 py-1 w-full" rows="2"></textarea>';
	echo '<div class="mt-2">';
	$spPreview = "CALL sp_add_review(:user_id, :book_id, :rating, :comment)";
	echo '<button class="px-3 py-2 bg-blue-600 text-white rounded" title="' . e($spPreview) . '">Submit Review</button>';
	echo '</div>';
	echo '</form>';
} else {
	echo '<div class="text-sm text-gray-600 mb-4">Please <a class="text-blue-600" href="' . e(base_url('/login.php')) . '">login</a> to add a review.</div>';
}

$sqlReviews = "SELECT r.review_id, r.rating, r.comment, r.review_date, u.name\nFROM reviews r\nINNER JOIN users u ON u.user_id = r.user_id\nWHERE r.book_id = :id\nORDER BY r.review_date DESC";
$reviews = $db->run($sqlReviews, ['id' => $id])->fetchAll();

echo '<div class="space-y-3">';
foreach ($reviews as $rv) {
	echo '<div class="bg-white border rounded p-3">';
	echo '<div class="text-sm">⭐ ' . (int)$rv['rating'] . ' · ' . e($rv['name']) . ' · <span class="text-gray-500">' . e($rv['review_date']) . '</span></div>';
	echo '<div class="text-sm mt-1">' . e($rv['comment'] ?? '') . '</div>';
	echo '</div>';
}
echo '</div>';

echo '</div></div>';

sql_info_panel('Book details queries', [
	Database::captureSqlForTooltip($sqlBook, ['id' => $id]),
	Database::captureSqlForTooltip($sqlReviews, ['id' => $id]),
	'Favorite UPSERT using ON DUPLICATE KEY UPDATE',
	'CALL sp_add_review(...) stored procedure',
]);

render_footer();
?>


