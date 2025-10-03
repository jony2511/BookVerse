<?php
require_once __DIR__ . '/../includes/ui.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['cat_name'] ?? '');
	if ($name) { $db->run('INSERT INTO categories(cat_name) VALUES(:n)', ['n' => $name]); }
}

if (($_GET['action'] ?? '') === 'delete') {
	$cid = (int)($_GET['id'] ?? 0);
	if ($cid > 0) { $db->run('DELETE FROM categories WHERE cat_id = :id', ['id' => $cid]); }
	header('Location: categories.php'); exit;
}

$rows = $db->run('SELECT c.cat_id, c.cat_name, COUNT(b.book_id) AS num_books FROM categories c LEFT JOIN books b ON b.category_id = c.cat_id GROUP BY c.cat_id, c.cat_name ORDER BY c.cat_name')->fetchAll();

render_header('Admin · Categories', 'LEFT JOIN, GROUP BY, DELETE CASCADE demonstration');

echo '<form method="post" class="bg-white border rounded p-4 mb-6 flex gap-2">';
echo '<input name="cat_name" placeholder="Category name" class="border rounded px-2 py-2" required />';
echo '<button class="px-3 py-2 bg-blue-600 text-white rounded" title="INSERT INTO categories(cat_name) VALUES (?)">Add</button>';
echo '</form>';

echo '<table class="w-full bg-white border rounded">';
echo '<tr class="text-left border-b"><th class="p-2">Name</th><th class="p-2">Books</th><th class="p-2">Actions</th></tr>';
foreach ($rows as $r) {
	echo '<tr class="border-b"><td class="p-2">' . e($r['cat_name']) . '</td><td class="p-2">' . (int)$r['num_books'] . '</td>';
	echo '<td class="p-2"><a class="text-red-600" title="DELETE FROM categories WHERE cat_id = ? (books cascade)" href="?action=delete&id=' . (int)$r['cat_id'] . '" onclick="return confirm(\'Delete?\')">Delete</a></td></tr>';
}
echo '</table>';

sql_info_panel('Admin Categories queries', [
	'SELECT ... LEFT JOIN books ... GROUP BY ...',
	'INSERT INTO categories(cat_name) VALUES(:n)',
	'DELETE FROM categories WHERE cat_id = :id (books cascade via FK)',
]);

render_footer();
?>


