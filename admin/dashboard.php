<?php
require_once __DIR__ . '/../includes/ui.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Check admin access
$user = current_user();
if (!$user || ($user['role'] ?? 'user') !== 'admin') {
	header('Location: ' . base_url('/login.php'));
	exit;
}

$db = get_db();

render_header('Admin Panel', 'Comprehensive database management with all SQL operations');

// Get stats
$stats = $db->run("
	SELECT 
		(SELECT COUNT(*) FROM books) as total_books,
		(SELECT COUNT(*) FROM users) as total_users,
		(SELECT COUNT(*) FROM reviews) as total_reviews,
		(SELECT COUNT(*) FROM categories) as total_categories
")->fetch();

?>

<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg p-8 mb-8 shadow-xl">
	<h1 class="text-4xl font-bold mb-2">👑 Admin Dashboard</h1>
	<p class="text-lg opacity-90">Complete database management and SQL operations</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
	<div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-6 shadow-lg transform hover:scale-105 transition-transform">
		<div class="text-4xl mb-2">📚</div>
		<div class="text-3xl font-bold"><?php echo $stats['total_books']; ?></div>
		<div class="text-sm opacity-90">Total Books</div>
	</div>
	
	<div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-6 shadow-lg transform hover:scale-105 transition-transform">
		<div class="text-4xl mb-2">👥</div>
		<div class="text-3xl font-bold"><?php echo $stats['total_users']; ?></div>
		<div class="text-sm opacity-90">Total Users</div>
	</div>
	
	<div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg transform hover:scale-105 transition-transform">
		<div class="text-4xl mb-2">⭐</div>
		<div class="text-3xl font-bold"><?php echo $stats['total_reviews']; ?></div>
		<div class="text-sm opacity-90">Total Reviews</div>
	</div>
	
	<div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg transform hover:scale-105 transition-transform">
		<div class="text-4xl mb-2">📁</div>
		<div class="text-3xl font-bold"><?php echo $stats['total_categories']; ?></div>
		<div class="text-sm opacity-90">Categories</div>
	</div>
</div>

<!-- Management Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
	<a href="<?php echo base_url('/admin/books.php'); ?>" class="group bg-white hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">📖</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-blue-600">Manage Books</h3>
		<p class="text-sm text-gray-600">Add, edit, delete books (DML operations)</p>
	</a>

	<a href="<?php echo base_url('/admin/categories.php'); ?>" class="group bg-white hover:bg-green-50 border-2 border-gray-200 hover:border-green-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">📁</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-green-600">Manage Categories</h3>
		<p class="text-sm text-gray-600">Category CRUD operations</p>
	</a>

	<a href="<?php echo base_url('/sql-demo.php'); ?>" class="group bg-white hover:bg-purple-50 border-2 border-gray-200 hover:border-purple-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">🎯</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-purple-600">SQL Demonstrations</h3>
		<p class="text-sm text-gray-600">All SQL operations with examples</p>
	</a>

	<a href="<?php echo base_url('/init_db.php'); ?>" class="group bg-white hover:bg-orange-50 border-2 border-gray-200 hover:border-orange-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">🔧</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-orange-600">Database Schema</h3>
		<p class="text-sm text-gray-600">View DDL statements & reinitialize</p>
	</a>

	<a href="<?php echo base_url('/sql-search.php'); ?>" class="group bg-white hover:bg-teal-50 border-2 border-gray-200 hover:border-teal-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">🔍</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-teal-600">SQL Index</h3>
		<p class="text-sm text-gray-600">Search where SQL features are used</p>
	</a>

	<a href="<?php echo base_url('/'); ?>" class="group bg-white hover:bg-pink-50 border-2 border-gray-200 hover:border-pink-500 rounded-lg p-6 transition-all hover:shadow-xl transform hover:-translate-y-1">
		<div class="text-4xl mb-3">🏠</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-pink-600">Back to Home</h3>
		<p class="text-sm text-gray-600">Return to main site</p>
	</a>
</div>

<!-- Quick Analytics -->
<div class="bg-white border-2 border-gray-200 rounded-lg shadow-lg overflow-hidden mb-8">
	<div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-4">
		<h2 class="text-2xl font-bold">📊 Quick Analytics</h2>
	</div>
	<div class="p-6">
		<?php
		// Top rated books using VIEW
		$sqlTop = "SELECT * FROM v_top_books LIMIT 5";
		$topBooks = $db->run($sqlTop)->fetchAll();
		?>
		
		<h3 class="text-xl font-semibold mb-4">🏆 Top Rated Books (Using VIEW v_top_books)</h3>
		<div class="overflow-x-auto mb-6">
			<table class="min-w-full border">
				<thead class="bg-gray-100">
					<tr>
						<th class="border px-4 py-2 text-left">Title</th>
						<th class="border px-4 py-2 text-left">Author</th>
						<th class="border px-4 py-2 text-left">Category</th>
						<th class="border px-4 py-2 text-left">Avg Rating</th>
						<th class="border px-4 py-2 text-left">Reviews</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($topBooks as $book): ?>
					<tr class="hover:bg-gray-50">
						<td class="border px-4 py-2 font-medium"><?php echo e($book['title']); ?></td>
						<td class="border px-4 py-2"><?php echo e($book['author']); ?></td>
						<td class="border px-4 py-2"><?php echo e($book['cat_name']); ?></td>
						<td class="border px-4 py-2">⭐ <?php echo number_format($book['avg_rating'], 2); ?></td>
						<td class="border px-4 py-2"><?php echo $book['review_count']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<div class="p-3 bg-gray-50 rounded text-xs mb-6" data-sql="<?php echo e($sqlTop); ?>">
			<strong>SQL:</strong> <code><?php echo e($sqlTop); ?></code>
		</div>

		<?php
		// Category stats using GROUP BY, COUNT, AVG, SUM
		$sqlCats = "SELECT c.cat_name, c.icon,
					COUNT(b.book_id) as book_count,
					AVG(b.price) as avg_price,
					SUM(b.stock) as total_stock,
					(SELECT COUNT(*) FROM reviews r INNER JOIN books b2 ON r.book_id = b2.book_id WHERE b2.category_id = c.cat_id) as total_reviews
					FROM categories c
					LEFT JOIN books b ON b.category_id = c.cat_id
					GROUP BY c.cat_id, c.cat_name, c.icon
					HAVING book_count > 0
					ORDER BY book_count DESC";
		$catStats = $db->run($sqlCats)->fetchAll();
		?>
		
		<h3 class="text-xl font-semibold mb-4">📁 Category Statistics (GROUP BY + HAVING)</h3>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
			<?php foreach ($catStats as $cat): ?>
			<div class="border-2 border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
				<div class="flex items-center justify-between mb-3">
					<div class="flex items-center">
						<span class="text-3xl mr-2"><?php echo $cat['icon'] ?: '📁'; ?></span>
						<div>
							<div class="font-bold"><?php echo e($cat['cat_name']); ?></div>
							<div class="text-xs text-gray-500"><?php echo $cat['book_count']; ?> books</div>
						</div>
					</div>
				</div>
				<div class="grid grid-cols-3 gap-2 text-center text-xs">
					<div class="bg-blue-50 p-2 rounded">
						<div class="text-gray-500">Avg Price</div>
						<div class="font-semibold text-blue-600">$<?php echo number_format($cat['avg_price'], 2); ?></div>
					</div>
					<div class="bg-green-50 p-2 rounded">
						<div class="text-gray-500">Stock</div>
						<div class="font-semibold text-green-600"><?php echo $cat['total_stock']; ?></div>
					</div>
					<div class="bg-purple-50 p-2 rounded">
						<div class="text-gray-500">Reviews</div>
						<div class="font-semibold text-purple-600"><?php echo $cat['total_reviews']; ?></div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		
		<div class="p-3 bg-gray-50 rounded text-xs" data-sql="<?php echo e($sqlCats); ?>">
			<strong>SQL (uses GROUP BY, HAVING, COUNT, AVG, SUM, Subquery):</strong>
			<pre class="mt-2 bg-white p-2 rounded border overflow-x-auto"><?php echo e($sqlCats); ?></pre>
		</div>
	</div>
</div>

<!-- SQL Features Implemented -->
<div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-6 shadow-lg">
	<h3 class="text-xl font-bold text-green-900 mb-4">✅ All SQL Features Implemented</h3>
	<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-green-800">
		<div>
			<h4 class="font-semibold mb-2">DDL Operations:</h4>
			<ul class="space-y-1">
				<li>✓ CREATE DATABASE</li>
				<li>✓ CREATE TABLE</li>
				<li>✓ ALTER TABLE</li>
				<li>✓ DROP TABLE</li>
				<li>✓ CREATE VIEW</li>
				<li>✓ CREATE PROCEDURE</li>
				<li>✓ CREATE FUNCTION</li>
				<li>✓ CREATE TRIGGER</li>
			</ul>
		</div>
		<div>
			<h4 class="font-semibold mb-2">Constraints:</h4>
			<ul class="space-y-1">
				<li>✓ PRIMARY KEY</li>
				<li>✓ FOREIGN KEY</li>
				<li>✓ UNIQUE</li>
				<li>✓ CHECK</li>
				<li>✓ ON DELETE CASCADE</li>
				<li>✓ ON DELETE SET NULL</li>
				<li>✓ ON UPDATE CASCADE</li>
			</ul>
		</div>
		<div>
			<h4 class="font-semibold mb-2">DML Operations:</h4>
			<ul class="space-y-1">
				<li>✓ INSERT</li>
				<li>✓ UPDATE</li>
				<li>✓ DELETE</li>
				<li>✓ SELECT with WHERE</li>
				<li>✓ ORDER BY</li>
				<li>✓ LIMIT</li>
				<li>✓ CASE statements</li>
			</ul>
		</div>
		<div>
			<h4 class="font-semibold mb-2">Joins:</h4>
			<ul class="space-y-1">
				<li>✓ INNER JOIN</li>
				<li>✓ LEFT JOIN</li>
				<li>✓ RIGHT JOIN</li>
				<li>✓ Multiple table joins</li>
			</ul>
		</div>
		<div>
			<h4 class="font-semibold mb-2">Aggregates:</h4>
			<ul class="space-y-1">
				<li>✓ COUNT()</li>
				<li>✓ AVG()</li>
				<li>✓ MAX()</li>
				<li>✓ MIN()</li>
				<li>✓ SUM()</li>
				<li>✓ GROUP BY</li>
				<li>✓ HAVING</li>
			</ul>
		</div>
		<div>
			<h4 class="font-semibold mb-2">Advanced:</h4>
			<ul class="space-y-1">
				<li>✓ Subqueries</li>
				<li>✓ IN operator</li>
				<li>✓ EXISTS</li>
				<li>✓ UNION</li>
				<li>✓ CTEs (WITH)</li>
				<li>✓ Transactions</li>
				<li>✓ IF-ELSE in procedures</li>
				<li>✓ LOOP/CURSOR</li>
			</ul>
		</div>
	</div>
</div>

<?php
render_footer();
?>