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
	echo '<style>body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,\"Segoe UI\",Roboto,Helvetica,Arial,\"Apple Color Emoji\",\"Segoe UI Emoji\"} .page-enter{opacity:0; transform: translateY(6px);} .page-enter-active{opacity:1; transform: translateY(0); transition: all .35s ease;} .card{transition:transform .25s ease, box-shadow .25s ease;} .card:hover{transform: translateY(-2px) scale(1.01);} .btn{transition: all .2s ease;} .btn:active{transform: translateY(1px);} .tooltip-sql{position:fixed; z-index:50; max-width:640px; background:white; border:1px solid #e5e7eb; border-radius:.5rem; box-shadow:0 10px 25px rgba(0,0,0,.12);} .tooltip-sql pre{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, \"Liberation Mono\", \"Courier New\", monospace; font-size:12px; white-space:pre-wrap; padding:.75rem; background:#f9fafb; border-radius:.5rem; margin:.5rem;} .gradient-header{background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 50%, #7c3aed 100%);} .nav-link{color:rgba(255,255,255,.9)} .nav-link:hover{color:#fff; text-decoration:underline; text-underline-offset:4px}</style>';
	echo '</head><body class="bg-gray-50 text-gray-900">';
	echo '<header class="gradient-header"><div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">';
	echo '<a href="' . e(base_url('/')) . '" class="text-xl font-semibold text-white">BookVerse</a>';
	echo '<nav class="space-x-4 text-sm">';
	echo '<a class="nav-link" href="' . e(base_url('/books.php')) . '">Books</a>';
	echo '<a class="nav-link" href="' . e(base_url('/favorites.php')) . '">Favorites</a>';
	echo '<a class="nav-link" href="' . e(base_url('/recommendations.php')) . '">Recommendations</a>';
//	echo '<a class="nav-link" href="' . e(base_url('/sql-search.php')) . '">SQL Index</a>';
	require_once __DIR__ . '/auth.php';
	if ($u = current_user()) {
		echo '<span class="nav-link">|</span>';
		echo '<span class="text-sm text-white/90">' . e($u['name'] ?? $u['email']) . '</span>';
		echo '<a class="nav-link" href="' . e(base_url('/logout.php')) . '">Logout</a>';
		if (($u['role'] ?? 'user') === 'admin') {
			echo '<a class="nav-link" href="' . e(base_url('/admin/index.php')) . '">Admin</a>';
		}
	} else {
		echo '<a class="nav-link" href="' . e(base_url('/login.php')) . '">Login</a>';
		echo '<a class="nav-link" href="' . e(base_url('/register.php')) . '">Register</a>';
	}
	echo '</nav></div></header>';
	echo '<main class="max-w-6xl mx-auto px-4 py-8 page-enter page-enter-active">';
	if ($title) {
		echo '<div class="mb-6"><h1 class="text-2xl font-semibold">' . e($title) . '</h1>';
		if ($subtitle) echo '<p class="text-gray-600">' . e($subtitle) . '</p>';
		echo '</div>';
	}
	// Global tooltip container and script
	echo '<div id="sql-tooltip" class="tooltip-sql hidden"></div>';
	echo <<<'SCRIPT'
<script>
(function(){
  const tip=document.getElementById("sql-tooltip");
  function showTip(text,x,y){
    if(!text) return;
    tip.innerHTML = "<div class=\"p-2\"><div class=\"text-xs font-semibold px-2 pt-1 text-gray-700\">SQL Preview</div><pre class=\"border\">"+String(text).replace(/</g,"&lt;")+"</pre></div>";
    tip.style.left=(x+12)+"px";
    tip.style.top=(y+12)+"px";
    tip.classList.remove("hidden");
  }
  function hideTip(){ tip.classList.add("hidden"); }
  document.addEventListener("mousemove",function(e){
    const el=e.target.closest('[data-sql],[title]');
    if(!el){ hideTip(); return; }
    const sql=el.getAttribute('data-sql')||el.getAttribute('title');
    if(sql && /select|insert|update|delete|create|call|with|view|join|group by|having|limit|order by/i.test(sql)){
      showTip(sql,e.clientX,e.clientY);
    } else { hideTip(); }
  });
})();
</script>
SCRIPT;
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

function sql_button(string $label, string $sqlPreview, string $classes = 'px-3 py-2 rounded btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow'):
	void {
	$tooltip = $sqlPreview;
	echo '<button class="' . e($classes) . '" data-sql="' . e($tooltip) . '" title="' . e($tooltip) . '">' . e($label) . '</button>';
}
?>


