<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';

$db = get_db();

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);

$where = [];
$params = [];
if ($q !== '') { $where[] = '(b.title LIKE :q OR b.author LIKE :q)'; $params['q'] = "%$q%"; }
if ($cat > 0) { $where[] = 'b.category_id = :cat'; $params['cat'] = $cat; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT b.book_id, b.title, b.author, b.picture, c.cat_name, COALESCE(v.avg_rating,0) AS avg_rating\nFROM books b\nINNER JOIN categories c ON c.cat_id = b.category_id\nLEFT JOIN v_book_ratings v ON v.book_id = b.book_id\n$whereSql\nORDER BY b.title ASC";
$stmt = $db->run($sql, $params);
$books = $stmt->fetchAll();

render_header('Books', 'Browse all books with JOINs and filters');

// category list for filter
$cats = $db->run('SELECT cat_id, cat_name FROM categories ORDER BY cat_name')->fetchAll();

echo '<form method="get" class="mb-6 flex gap-2">';
echo '<input name="q" value="' . e($q) . '" placeholder="Search title/author" class="border rounded px-3 py-2 w-48" />';
echo '<select name="cat" class="border rounded px-3 py-2"><option value="0">All categories</option>';
foreach ($cats as $c) {
	$sel = $cat === (int)$c['cat_id'] ? ' selected' : '';
	echo '<option value="' . (int)$c['cat_id'] . '"' . $sel . '>' . e($c['cat_name']) . '</option>';
}
echo '</select>';
$tooltip = Database::captureSqlForTooltip($sql, $params);
echo '<button class="px-3 py-2 rounded btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow" data-sql="' . e($tooltip) . '">Filter</button>';
echo '</form>';

echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
foreach ($books as $bk) {
	echo '<a href="' . e(base_url('/book.php?id=' . $bk['book_id'])) . '" class="bg-white border rounded-lg overflow-hidden card shadow-sm">';
	echo '<img class="w-full h-44 object-cover" src="' . e($bk['picture']) . '" alt="' . e($bk['title']) . '">';
	echo '<div class="p-3">';
	echo '<div class="font-medium">' . e($bk['title']) . '</div>';
	echo '<div class="text-xs text-gray-500">' . e($bk['author']) . ' · ' . e($bk['cat_name']) . '</div>';
	echo '<div class="text-sm mt-1">⭐ ' . number_format((float)$bk['avg_rating'], 1) . '</div>';
	echo '</div></a>';
}
echo '</div>';

// sql_info_panel('Books listing queries', [
// 	"List with INNER JOIN and LEFT JOIN view:\n" . $tooltip,
// ]);

render_footer();
?>


