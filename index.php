<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';

render_header('Home', 'Discover books, reviews, and recommendations.');

$db = get_db();

// Featured books with average ratings (LEFT JOIN + GROUP BY)
$sql = "SELECT b.book_id, b.title, b.author, b.picture, COALESCE(AVG(r.rating),0) AS avg_rating\nFROM books b\nLEFT JOIN reviews r ON r.book_id = b.book_id\nGROUP BY b.book_id, b.title, b.author, b.picture\nORDER BY avg_rating DESC, b.title ASC\nLIMIT 8";
$stmt = $db->run($sql);
$books = $stmt->fetchAll();

echo '<section><div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
foreach ($books as $bk) {
	echo '<a href="' . e(base_url('/book.php?id=' . $bk['book_id'])) . '" class="bg-white border rounded-lg overflow-hidden hover:shadow">';
	echo '<img class="w-full h-40 object-cover" src="' . e($bk['picture']) . '" alt="' . e($bk['title']) . '">';
	echo '<div class="p-3">';
	echo '<div class="font-medium">' . e($bk['title']) . '</div>';
	echo '<div class="text-xs text-gray-500">' . e($bk['author']) . '</div>';
	echo '<div class="text-sm mt-1">⭐ ' . number_format((float)$bk['avg_rating'], 1) . '</div>';
	echo '</div></a>';
}
echo '</div></section>';

sql_info_panel('Home page queries', [
	"Featured books with AVG rating:\n" . $sql,
]);

render_footer();
?>


