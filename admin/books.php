<?php
require_once __DIR__ . '/../includes/ui.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$db = get_db();

// Simple create/update with transaction demo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = trim($_POST['title'] ?? '');
	$author = trim($_POST['author'] ?? '');
	$picture = trim($_POST['picture'] ?? '');
	$cat = (int)($_POST['category_id'] ?? 0);
	$year = (int)($_POST['published_year'] ?? 0);
	if ($title && $author && $cat > 0) {
		try {
			$db->pdo()->beginTransaction();
			$db->run('INSERT INTO books(title,picture,author,category_id,published_year) VALUES(:t,:p,:a,:c,:y)', [
				't' => $title, 'p' => $picture, 'a' => $author, 'c' => $cat, 'y' => $year ?: null,
			]);
			$db->pdo()->commit();
		} catch (Throwable $e) {
			$db->pdo()->rollBack();
		}
	}
}

// Delete
if (($_GET['action'] ?? '') === 'delete') {
	$bid = (int)($_GET['id'] ?? 0);
	if ($bid > 0) { $db->run('DELETE FROM books WHERE book_id = :id', ['id' => $bid]); }
	header('Location: books.php'); exit;
}

$cats = $db->run('SELECT cat_id, cat_name FROM categories ORDER BY cat_name')->fetchAll();
$rows = $db->run('SELECT b.book_id, b.title, c.cat_name FROM books b INNER JOIN categories c ON c.cat_id = b.category_id ORDER BY b.title')->fetchAll();

render_header('Admin · Books', 'Transactions, INSERT, DELETE');

echo '<div class="mb-4"><a href="' . e(base_url('/admin/index.php')) . '" class="text-sm text-blue-700 hover:underline">← Back to Dashboard</a></div>';

echo '<form method="post" class="bg-white border rounded p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-2 card">';

echo '<input name="title" placeholder="Title" class="border rounded px-2 py-2" required />';

echo '<input name="author" placeholder="Author" class="border rounded px-2 py-2" required />';

echo '<input name="picture" placeholder="Image URL" class="border rounded px-2 py-2" />';

echo '<select name="category_id" class="border rounded px-2 py-2">';
foreach ($cats as $c) echo '<option value="' . (int)$c['cat_id'] . '">' . e($c['cat_name']) . '</option>';

echo '</select>';

echo '<input name="published_year" type="number" placeholder="Year" class="border rounded px-2 py-2" />';

echo '<div class="md:col-span-5">';

echo '<button class="px-3 py-2 rounded btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow" data-sql="INSERT INTO books(...) VALUES(...) (inside TRANSACTION)">Add Book</button>';

echo '</div></form>';


echo '<div class="bg-white border rounded card overflow-hidden">';

echo '<table class="w-full">';

echo '<tr class="text-left border-b bg-gray-50"><th class="p-2">Title</th><th class="p-2">Category</th><th class="p-2">Actions</th></tr>';

foreach ($rows as $r) {
	echo '<tr class="border-b"><td class="p-2">' . e($r['title']) . '</td><td class="p-2">' . e($r['cat_name']) . '</td>';
	$delSql = 'DELETE FROM books WHERE book_id = ?';
	echo '<td class="p-2"><a class="text-red-600 hover:underline" data-sql="' . e($delSql) . '" href="?action=delete&id=' . (int)$r['book_id'] . '" onclick="return confirm(\'Delete?\')">Delete</a></td></tr>';
}

echo '</table>';

echo '</div>';

sql_info_panel('Admin Books queries', [
	'INSERT INTO books(...) VALUES(...) within BEGIN/COMMIT',
	'SELECT b... INNER JOIN categories ...',
	'DELETE FROM books WHERE book_id = :id',
]);

render_footer();
?>


