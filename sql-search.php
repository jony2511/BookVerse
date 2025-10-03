<?php
require_once __DIR__ . '/includes/ui.php';

render_header('SQL Keyword Index', 'Search where SQL concepts are demonstrated.');

$map = [
	'CREATE DATABASE' => ['init_db.php'],
	'CREATE TABLE' => ['init_db.php'],
	'FOREIGN KEY' => ['init_db.php'],
	'ON DELETE CASCADE' => ['init_db.php'],
	'VIEW' => ['init_db.php','index.php','books.php','book.php'],
	'WITH' => ['recommendations.php'],
	'CTE' => ['recommendations.php'],
	'STORED PROCEDURE' => ['init_db.php','book.php','review_add.php'],
	'TRIGGER' => ['init_db.php'],
	'JOIN' => ['index.php','books.php','book.php','favorites.php','recommendations.php'],
	'LEFT JOIN' => ['index.php','books.php','book.php','recommendations.php'],
	'INNER JOIN' => ['books.php','book.php','favorites.php','recommendations.php'],
	'GROUP BY' => ['index.php','books.php','recommendations.php'],
	'HAVING' => ['recommendations.php'],
	'AGGREGATE' => ['index.php','books.php'],
	'AVG' => ['index.php','books.php','recommendations.php'],
	'COUNT' => ['init_db.php','recommendations.php'],
	'LIKE' => ['books.php'],
	'ORDER BY' => ['index.php','books.php','favorites.php','recommendations.php'],
	'LIMIT' => ['index.php'],
	'UNIQUE' => ['init_db.php'],
	'ON DUPLICATE KEY UPDATE' => ['book.php','favorite_toggle.php'],
	'TRANSACTION' => ['init_db.php'],
	'SIGNAL' => ['init_db.php'],
];

$q = strtoupper(trim($_GET['q'] ?? ''));

echo '<form method="get" class="mb-4 flex gap-2">';
echo '<input name="q" value="' . e($q) . '" placeholder="e.g. JOIN, GROUP BY, TRIGGER" class="border rounded px-3 py-2 w-64" />';
echo '<button class="px-3 py-2 bg-blue-600 text-white rounded">Search</button>';
echo '</form>';

if ($q !== '') {
	$results = [];
	foreach ($map as $key => $files) {
		if (str_contains($key, $q)) { $results[$key] = $files; }
	}
	if (!$results) echo '<div class="text-sm text-gray-600">No matches.</div>';
	foreach ($results as $key => $files) {
		echo '<div class="mb-2"><div class="font-semibold">' . e($key) . '</div>';
		echo '<div class="text-sm text-gray-600">Files: ' . e(implode(', ', $files)) . '</div></div>';
	}
} else {
	echo '<div class="text-sm text-gray-600">Enter a SQL keyword or concept to find related pages.</div>';
}

render_footer();
?>


