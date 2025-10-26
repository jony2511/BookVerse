<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';

// Check if database is initialized
try {
    $db = get_db();
    // Quick check if books table has the new schema
    $stmt = $db->run("SHOW COLUMNS FROM books LIKE 'price'");
    if (!$stmt->fetch()) {
        // Database not initialized with new schema
        header('Location: setup.html');
        exit;
    }
} catch (PDOException $e) {
    // Database doesn't exist or connection failed
    header('Location: setup.html');
    exit;
}

render_header('Welcome to BookVerse', 'Your comprehensive book review and discovery platform');

?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white rounded-xl p-10 mb-10 shadow-2xl">
	<div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
		<div>
			<h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to BookVerse</h1>
			<p class="text-xl mb-6 opacity-90">Discover, review, and share your favorite books with our community</p>
			<div class="flex gap-4">
				<a href="<?php echo base_url('/books.php'); ?>" class="px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg transform hover:-translate-y-0.5">
					Browse Books
				</a>
				<a href="<?php echo base_url('/sql-demo.php'); ?>" class="px-6 py-3 bg-indigo-800 text-white rounded-lg font-semibold hover:bg-indigo-900 transition-colors shadow-lg transform hover:-translate-y-0.5">
					SQL Demos
				</a>
			</div>
		</div>
		<div class="hidden md:block text-center">
			<div class="text-8xl">📚</div>
			<div class="text-lg mt-4 opacity-75">Complete SQL Operations Demo</div>
		</div>
	</div>
</div>

<!-- Stats Cards -->
<?php
$statsSQL = "SELECT 
	(SELECT COUNT(*) FROM books) as total_books,
	(SELECT COUNT(*) FROM users) as total_users,
	(SELECT COUNT(*) FROM reviews) as total_reviews,
	(SELECT COUNT(*) FROM categories) as total_categories,
	(SELECT AVG(rating) FROM reviews) as avg_rating
";
$stats = $db->run($statsSQL)->fetch();
?>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10">
	<div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-5 shadow-lg transform hover:scale-105 transition-transform" data-sql="<?php echo e($statsSQL); ?>">
		<div class="text-3xl mb-2">📚</div>
		<div class="text-2xl font-bold"><?php echo $stats['total_books']; ?></div>
		<div class="text-sm opacity-90">Books</div>
	</div>
	<div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-5 shadow-lg transform hover:scale-105 transition-transform" data-sql="<?php echo e($statsSQL); ?>">
		<div class="text-3xl mb-2">👥</div>
		<div class="text-2xl font-bold"><?php echo $stats['total_users']; ?></div>
		<div class="text-sm opacity-90">Users</div>
	</div>
	<div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-5 shadow-lg transform hover:scale-105 transition-transform" data-sql="<?php echo e($statsSQL); ?>">
		<div class="text-3xl mb-2">⭐</div>
		<div class="text-2xl font-bold"><?php echo $stats['total_reviews']; ?></div>
		<div class="text-sm opacity-90">Reviews</div>
	</div>
	<div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg p-5 shadow-lg transform hover:scale-105 transition-transform" data-sql="<?php echo e($statsSQL); ?>">
		<div class="text-3xl mb-2">📁</div>
		<div class="text-2xl font-bold"><?php echo $stats['total_categories']; ?></div>
		<div class="text-sm opacity-90">Categories</div>
	</div>
	<div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg p-5 shadow-lg transform hover:scale-105 transition-transform" data-sql="<?php echo e($statsSQL); ?>">
		<div class="text-3xl mb-2">🌟</div>
		<div class="text-2xl font-bold"><?php echo number_format($stats['avg_rating'], 1); ?></div>
		<div class="text-sm opacity-90">Avg Rating</div>
	</div>
</div>

<!-- Featured Books Section -->
<div class="mb-10">
	<div class="flex items-center justify-between mb-6">
		<div>
			<h2 class="text-3xl font-bold text-gray-800">🏆 Top Rated Books</h2>
			<p class="text-gray-600">Using LEFT JOIN, GROUP BY, AVG aggregate</p>
		</div>
		<button onclick="toggleSQL('featured-sql')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow">
			Show SQL
		</button>
	</div>

	<?php
	// Featured books with average ratings (LEFT JOIN + GROUP BY + AVG)
	$sql = "SELECT b.book_id, b.title, b.author, b.picture, b.price,
			COALESCE(AVG(r.rating), 0) AS avg_rating,
			COUNT(r.review_id) AS review_count
			FROM books b
			LEFT JOIN reviews r ON r.book_id = b.book_id
			GROUP BY b.book_id, b.title, b.author, b.picture, b.price
			ORDER BY avg_rating DESC, review_count DESC, b.title ASC
			LIMIT 8";
	$stmt = $db->run($sql);
	$books = $stmt->fetchAll();
	?>

	<div id="featured-sql" class="hidden mb-4 p-4 bg-gray-900 text-green-400 rounded-lg">
		<div class="font-semibold mb-2 text-white">SQL Query (LEFT JOIN + GROUP BY + AVG + COUNT):</div>
		<pre class="text-xs overflow-x-auto"><?php echo e($sql); ?></pre>
	</div>

	<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
		<?php foreach ($books as $bk): ?>
			<a href="<?php echo e(base_url('/book.php?id=' . $bk['book_id'])); ?>" 
			   class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl hover:border-indigo-500 transform hover:-translate-y-2 transition-all duration-300"
			   data-sql="<?php echo e($sql); ?>">
				<div class="relative">
					<img class="w-full h-52 object-cover" src="<?php echo e($bk['picture']); ?>" alt="<?php echo e($bk['title']); ?>">
					<div class="absolute top-2 right-2 bg-yellow-400 text-gray-900 px-2 py-1 rounded-full text-sm font-bold shadow-lg">
						⭐ <?php echo number_format((float)$bk['avg_rating'], 1); ?>
					</div>
				</div>
				<div class="p-4">
					<h3 class="font-bold text-gray-900 mb-1 line-clamp-2"><?php echo e($bk['title']); ?></h3>
					<p class="text-sm text-gray-600 mb-2"><?php echo e($bk['author']); ?></p>
					<div class="flex justify-between items-center">
						<span class="text-lg font-bold text-indigo-600">$<?php echo number_format($bk['price'], 2); ?></span>
						<span class="text-xs text-gray-500"><?php echo $bk['review_count']; ?> reviews</span>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<!-- Categories Section -->
<div class="mb-10">
	<div class="flex items-center justify-between mb-6">
		<div>
			<h2 class="text-3xl font-bold text-gray-800">📁 Browse by Category</h2>
			<p class="text-gray-600">Using GROUP BY with COUNT and AVG</p>
		</div>
		<button onclick="toggleSQL('category-sql')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow">
			Show SQL
		</button>
	</div>

	<?php
	$catSQL = "SELECT c.cat_id, c.cat_name, c.icon, c.description,
				COUNT(b.book_id) as book_count,
				AVG(b.price) as avg_price
				FROM categories c
				LEFT JOIN books b ON b.category_id = c.cat_id
				GROUP BY c.cat_id, c.cat_name, c.icon, c.description
				ORDER BY book_count DESC, c.cat_name ASC";
	$categories = $db->run($catSQL)->fetchAll();
	?>

	<div id="category-sql" class="hidden mb-4 p-4 bg-gray-900 text-green-400 rounded-lg">
		<div class="font-semibold mb-2 text-white">SQL Query (LEFT JOIN + GROUP BY + COUNT + AVG):</div>
		<pre class="text-xs overflow-x-auto"><?php echo e($catSQL); ?></pre>
	</div>

	<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
		<?php foreach ($categories as $cat): ?>
			<a href="<?php echo base_url('/books.php?cat=' . $cat['cat_id']); ?>" 
			   class="group bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-lg p-6 hover:border-purple-500 hover:shadow-xl transition-all transform hover:-translate-y-1"
			   data-sql="<?php echo e($catSQL); ?>">
				<div class="text-5xl mb-3 group-hover:scale-110 transition-transform"><?php echo $cat['icon'] ?: '📁'; ?></div>
				<h3 class="font-bold text-lg mb-1 group-hover:text-purple-600"><?php echo e($cat['cat_name']); ?></h3>
				<p class="text-sm text-gray-600 mb-3"><?php echo e($cat['description'] ?: 'Explore books'); ?></p>
				<div class="flex justify-between text-xs">
					<span class="text-gray-500"><?php echo $cat['book_count']; ?> books</span>
					<span class="text-indigo-600 font-semibold">Avg $<?php echo number_format($cat['avg_price'] ?? 0, 2); ?></span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<!-- Recent Reviews Section -->
<div class="mb-10">
	<div class="flex items-center justify-between mb-6">
		<div>
			<h2 class="text-3xl font-bold text-gray-800">💬 Recent Reviews</h2>
			<p class="text-gray-600">Using INNER JOIN across multiple tables</p>
		</div>
		<button onclick="toggleSQL('review-sql')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow">
			Show SQL
		</button>
	</div>

	<?php
	$revSQL = "SELECT r.review_id, r.rating, r.comment, r.review_date,
				u.name as user_name, u.email,
				b.book_id, b.title as book_title, b.picture
				FROM reviews r
				INNER JOIN users u ON r.user_id = u.user_id
				INNER JOIN books b ON r.book_id = b.book_id
				ORDER BY r.review_date DESC
				LIMIT 6";
	$reviews = $db->run($revSQL)->fetchAll();
	?>

	<div id="review-sql" class="hidden mb-4 p-4 bg-gray-900 text-green-400 rounded-lg">
		<div class="font-semibold mb-2 text-white">SQL Query (INNER JOIN multiple tables + ORDER BY + LIMIT):</div>
		<pre class="text-xs overflow-x-auto"><?php echo e($revSQL); ?></pre>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
		<?php foreach ($reviews as $rev): ?>
			<div class="bg-white border-2 border-gray-200 rounded-lg p-5 hover:shadow-xl transition-shadow" data-sql="<?php echo e($revSQL); ?>">
				<div class="flex items-start gap-3 mb-3">
					<img src="<?php echo e($rev['picture']); ?>" class="w-16 h-20 object-cover rounded shadow" alt="Book">
					<div class="flex-1">
						<h4 class="font-bold text-sm mb-1"><?php echo e($rev['book_title']); ?></h4>
						<p class="text-xs text-gray-600"><?php echo e($rev['user_name']); ?></p>
						<div class="text-yellow-500 text-sm mt-1">
							<?php for ($i = 1; $i <= 5; $i++): ?>
								<?php echo $i <= $rev['rating'] ? '⭐' : '☆'; ?>
							<?php endfor; ?>
						</div>
					</div>
				</div>
				<p class="text-sm text-gray-700 line-clamp-3"><?php echo e($rev['comment'] ?? 'No comment'); ?></p>
				<div class="text-xs text-gray-400 mt-2"><?php echo date('M d, Y', strtotime($rev['review_date'])); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</div>


<script>
function toggleSQL(id) {
	const el = document.getElementById(id);
	if (el.classList.contains('hidden')) {
		el.classList.remove('hidden');
		el.classList.add('animate-fade-in');
	} else {
		el.classList.add('hidden');
	}
}
</script>

<style>
@keyframes fade-in {
	from { opacity: 0; transform: translateY(-10px); }
	to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
	animation: fade-in 0.3s ease-out;
}
.line-clamp-2 {
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
.line-clamp-3 {
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
</style>

<?php
render_footer();
?>


