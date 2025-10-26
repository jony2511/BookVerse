<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$db = get_db();

// Example WITH (CTE) to compute top-rated books and join to recs
$sql = <<<SQL
WITH top_books AS (
  SELECT b.book_id, b.title, COALESCE(AVG(r.rating),0) AS avg_rating
  FROM books b
  LEFT JOIN reviews r ON r.book_id = b.book_id
  GROUP BY b.book_id, b.title
  HAVING AVG(r.rating) >= 4 OR COUNT(r.review_id) >= 2
)
SELECT rec.rec_id, rec.reason, rec.created_date, b.title, tb.avg_rating, u.name AS recommender
FROM recommendations rec
INNER JOIN books b ON b.book_id = rec.book_id
LEFT JOIN users u ON u.user_id = rec.user_id
LEFT JOIN top_books tb ON tb.book_id = rec.book_id
ORDER BY rec.created_date DESC
SQL;

$rows = $db->run($sql)->fetchAll();

render_header('Recommendations', '');

echo '<div class="space-y-3">';
foreach ($rows as $r) {
	echo '<div class="bg-white border rounded p-3">';
	echo '<div class="font-medium">' . e($r['title']) . ' · ⭐ ' . number_format((float)($r['avg_rating'] ?? 0),1) . '</div>';
	echo '<div class="text-sm text-gray-600">' . e($r['reason']) . '</div>';
	echo '<div class="text-xs text-gray-500">By: ' . e($r['recommender'] ?? 'Admin/Editor') . ' · ' . e($r['created_date']) . '</div>';
	echo '</div>';
}
echo '</div>';

sql_info_panel('Recommendations queries', [
	$sql,
]);

render_footer();
?>


