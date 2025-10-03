<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
$db = get_db();
$user = current_user();

$sql = "SELECT b.book_id, b.title, b.author, b.picture, f.date_added\nFROM favorites f\nINNER JOIN books b ON b.book_id = f.book_id\nWHERE f.user_id = :u\nORDER BY f.date_added DESC";
$rows = $db->run($sql, ['u' => $user['user_id']])->fetchAll();

render_header('Your Favorites');

echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
foreach ($rows as $bk) {
	echo '<a href="' . e(base_url('/book.php?id=' . $bk['book_id'])) . '" class="bg-white border rounded-lg overflow-hidden hover:shadow">';
	echo '<img class="w-full h-40 object-cover" src="' . e($bk['picture']) . '" alt="' . e($bk['title']) . '">';
	echo '<div class="p-3">';
	echo '<div class="font-medium">' . e($bk['title']) . '</div>';
	echo '<div class="text-xs text-gray-500">' . e($bk['author']) . '</div>';
	echo '</div></a>';
}
echo '</div>';

sql_info_panel('Favorites queries', [
	Database::captureSqlForTooltip($sql, ['u' => $user['user_id']]),
]);

render_footer();
?>


