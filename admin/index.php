<?php
require_once __DIR__ . '/../includes/ui.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$db = get_db();

$qBooks = 'SELECT COUNT(*) AS c FROM books';
$qReviews = 'SELECT COUNT(*) AS c FROM reviews';
$qUsers = "SELECT COUNT(*) AS c FROM users WHERE role='user'";
$qTopCats = "SELECT c.cat_name, COUNT(b.book_id) AS num_books FROM categories c LEFT JOIN books b ON b.category_id = c.cat_id GROUP BY c.cat_id, c.cat_name ORDER BY num_books DESC LIMIT 5";

$books = (int)$db->run($qBooks)->fetch()['c'];
$reviews = (int)$db->run($qReviews)->fetch()['c'];
$users = (int)$db->run($qUsers)->fetch()['c'];
$topCats = $db->run($qTopCats)->fetchAll();

render_header('Admin · Dashboard', 'Key statistics with GROUP BY and aggregates');

echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">';
echo '<div class="bg-white border rounded p-4 card shadow-sm" data-sql="' . e($qBooks) . '"><div class="text-sm text-gray-500">Books</div><div class="text-2xl font-semibold">' . $books . '</div></div>';
echo '<div class="bg-white border rounded p-4 card shadow-sm" data-sql="' . e($qReviews) . '"><div class="text-sm text-gray-500">Reviews</div><div class="text-2xl font-semibold">' . $reviews . '</div></div>';
echo '<div class="bg-white border rounded p-4 card shadow-sm" data-sql="' . e($qUsers) . '"><div class="text-sm text-gray-500">Users</div><div class="text-2xl font-semibold">' . $users . '</div></div>';
echo '</div>';

echo '<div class="bg-white border rounded p-4 card shadow-sm">';
echo '<div class="font-semibold mb-3">Top Categories</div>';
echo '<table class="w-full text-sm">';
echo '<tr class="text-left border-b"><th class="p-2">Category</th><th class="p-2">Books</th></tr>';
foreach ($topCats as $r) {
	echo '<tr class="border-b"><td class="p-2">' . e($r['cat_name']) . '</td><td class="p-2">' . (int)$r['num_books'] . '</td></tr>';
}
echo '</table>';
echo '</div>';

echo '<div class="mt-6 flex gap-3">';
echo '<a href="' . e(base_url('/admin/books.php')) . '" class="px-3 py-2 rounded btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow" data-sql="SELECT * FROM books ORDER BY title">Manage Books</a>';
echo '<a href="' . e(base_url('/admin/categories.php')) . '" class="px-3 py-2 rounded btn bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-700 hover:to-teal-700 shadow" data-sql="SELECT * FROM categories ORDER BY cat_name">Manage Categories</a>';
echo '</div>';

sql_info_panel('Admin Dashboard queries', [
	$qBooks,
	$qReviews,
	$qUsers,
	$qTopCats,
]);

render_footer();
?>


