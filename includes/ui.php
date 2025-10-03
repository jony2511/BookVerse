<?php
require_once __DIR__ . '/db.php';

function base_url(string $path = ''): string {
	$cfg = require __DIR__ . '/../config.php';
	return rtrim($cfg['app']['base_url'], '/') . '/' . ltrim($path, '/');
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function render_header(string $title, string $subtitle = ''): void {
	$full = 'BookVerse' . ($title ? ' · ' . $title : '');
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1" />';
	echo '<title>' . e($full) . '</title>';
	echo '<script src="https://cdn.tailwindcss.com"></script>';
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
	echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">';
	echo '<style>body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji"}</style>';
	echo '</head><body class="bg-gray-50 text-gray-900">';
	echo '<header class="bg-white border-b"><div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">';
	echo '<a href="' . e(base_url('/')) . '" class="text-xl font-semibold">BookVerse</a>';
	echo '<nav class="space-x-4 text-sm">';
	echo '<a class="hover:text-blue-600" href="' . e(base_url('/books.php')) . '">Books</a>';
	echo '<a class="hover:text-blue-600" href="' . e(base_url('/favorites.php')) . '">Favorites</a>';
	echo '<a class="hover:text-blue-600" href="' . e(base_url('/recommendations.php')) . '">Recommendations</a>';
	echo '<a class="hover:text-blue-600" href="' . e(base_url('/sql-search.php')) . '">SQL Index</a>';
	require_once __DIR__ . '/auth.php';
	if ($u = current_user()) {
		echo '<span class="text-gray-400">|</span>';
		echo '<span class="text-sm">' . e($u['name'] ?? $u['email']) . '</span>';
		echo '<a class="hover:text-red-600" href="' . e(base_url('/logout.php')) . '">Logout</a>';
		if (($u['role'] ?? 'user') === 'admin') {
			echo '<a class="hover:text-blue-600" href="' . e(base_url('/admin/books.php')) . '">Admin</a>';
		}
	} else {
		echo '<a class="hover:text-blue-600" href="' . e(base_url('/login.php')) . '">Login</a>';
		echo '<a class="hover:text-blue-600" href="' . e(base_url('/register.php')) . '">Register</a>';
	}
	echo '</nav></div></header>';
	echo '<main class="max-w-6xl mx-auto px-4 py-8">';
	if ($title) {
		echo '<div class="mb-6"><h1 class="text-2xl font-semibold">' . e($title) . '</h1>';
		if ($subtitle) echo '<p class="text-gray-600">' . e($subtitle) . '</p>';
		echo '</div>';
	}
}

function render_footer(): void {
	echo '</main>';
	echo '<footer class="border-t bg-white"><div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-500">';
	echo '© ' . date('Y') . ' BookVerse';
	echo '</div></footer>';
	echo '</body></html>';
}

function sql_info_panel(string $title, array $queries): void {
	echo '<section class="mt-8 bg-white border rounded-lg p-4">';
	echo '<h3 class="font-semibold mb-2">SQL Used: ' . e($title) . '</h3>';
	echo '<div class="space-y-2 text-xs font-mono">';
	foreach ($queries as $q) {
		echo '<pre class="whitespace-pre-wrap bg-gray-50 p-2 rounded border text-[11px]">' . e($q) . '</pre>';
	}
	echo '</div></section>';
}

function sql_button(string $label, string $sqlPreview, string $classes = 'px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700'): void {
	$tooltip = e($sqlPreview);
	echo '<button class="' . e($classes) . '" title="' . $tooltip . '">' . e($label) . '</button>';
}
?>


