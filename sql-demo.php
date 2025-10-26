<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';

render_header('SQL Demonstrations', 'Explore all SQL operations: Joins, Subqueries, Aggregates, Set Operations, CTEs & More');

$db = get_db();

// Track which demo to show
$demo = $_GET['demo'] ?? '';
$results = [];
$sqlExecuted = '';

?>

<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg p-8 mb-8 shadow-xl">
	<h1 class="text-3xl font-bold mb-4">🎯 Interactive SQL Demonstrations</h1>
	<p class="text-lg opacity-90">Click any button below to execute and view results of different SQL operations</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
	
	<!-- Basic SELECT + WHERE -->
	<a href="?demo=select_where" class="group bg-white hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">📋</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-blue-600">SELECT + WHERE</h3>
		<p class="text-sm text-gray-600">Basic queries with filtering</p>
	</a>

	<!-- JOINS -->
	<a href="?demo=joins" class="group bg-white hover:bg-green-50 border-2 border-gray-200 hover:border-green-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🔗</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-green-600">INNER/LEFT/RIGHT JOINS</h3>
		<p class="text-sm text-gray-600">Combining data from tables</p>
	</a>

	<!-- Aggregates -->
	<a href="?demo=aggregates" class="group bg-white hover:bg-purple-50 border-2 border-gray-200 hover:border-purple-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">📊</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-purple-600">Aggregates</h3>
		<p class="text-sm text-gray-600">COUNT, AVG, MAX, MIN, SUM</p>
	</a>

	<!-- GROUP BY + HAVING -->
	<a href="?demo=groupby" class="group bg-white hover:bg-orange-50 border-2 border-gray-200 hover:border-orange-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">📦</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-orange-600">GROUP BY + HAVING</h3>
		<p class="text-sm text-gray-600">Grouping and filtering groups</p>
	</a>

	<!-- Subqueries -->
	<a href="?demo=subqueries" class="group bg-white hover:bg-red-50 border-2 border-gray-200 hover:border-red-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🔍</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-red-600">Subqueries</h3>
		<p class="text-sm text-gray-600">Nested SELECT, IN, EXISTS</p>
	</a>

	<!-- Set Operations -->
	<a href="?demo=set_ops" class="group bg-white hover:bg-teal-50 border-2 border-gray-200 hover:border-teal-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🔀</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-teal-600">Set Operations</h3>
		<p class="text-sm text-gray-600">UNION, INTERSECT, EXCEPT</p>
	</a>

	<!-- CTEs -->
	<a href="?demo=cte" class="group bg-white hover:bg-pink-50 border-2 border-gray-200 hover:border-pink-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🎭</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-pink-600">CTEs (WITH)</h3>
		<p class="text-sm text-gray-600">Common Table Expressions</p>
	</a>

	<!-- Views -->
	<a href="?demo=views" class="group bg-white hover:bg-indigo-50 border-2 border-gray-200 hover:border-indigo-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">👁️</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-indigo-600">Views</h3>
		<p class="text-sm text-gray-600">Virtual tables from queries</p>
	</a>

	<!-- Stored Procedures -->
	<a href="?demo=procedures" class="group bg-white hover:bg-yellow-50 border-2 border-gray-200 hover:border-yellow-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">⚙️</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-yellow-600">Stored Procedures</h3>
		<p class="text-sm text-gray-600">Callable database functions</p>
	</a>

	<!-- Functions -->
	<a href="?demo=functions" class="group bg-white hover:bg-cyan-50 border-2 border-gray-200 hover:border-cyan-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🔧</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-cyan-600">Functions</h3>
		<p class="text-sm text-gray-600">Custom SQL functions</p>
	</a>

	<!-- Triggers -->
	<a href="?demo=triggers" class="group bg-white hover:bg-rose-50 border-2 border-gray-200 hover:border-rose-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">⚡</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-rose-600">Triggers</h3>
		<p class="text-sm text-gray-600">Automatic event handlers</p>
	</a>

	<!-- CASE Statements -->
	<a href="?demo=case" class="group bg-white hover:bg-lime-50 border-2 border-gray-200 hover:border-lime-500 rounded-lg p-6 transition-all hover:shadow-lg transform hover:-translate-y-1">
		<div class="text-3xl mb-3">🔀</div>
		<h3 class="font-bold text-lg mb-2 group-hover:text-lime-600">CASE Statements</h3>
		<p class="text-sm text-gray-600">Conditional logic in SQL</p>
	</a>

</div>

<?php

// Execute selected demonstration
if ($demo) {
	echo '<div class="bg-white border-2 border-gray-200 rounded-lg shadow-xl overflow-hidden mb-8">';
	
	switch ($demo) {
		case 'select_where':
			echo '<div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">📋 SELECT with WHERE Clause</h2>';
			echo '</div><div class="p-6">';
			
			$sqlExecuted = "SELECT book_id, title, author, published_year, price 
FROM books 
WHERE published_year >= 2000 AND price < 20
ORDER BY published_year DESC, title ASC
LIMIT 10";
			$stmt = $db->run($sqlExecuted);
			$results = $stmt->fetchAll();
			
			echo '<h3 class="font-bold mb-2">Books published after 2000 with price under $20</h3>';
			displayResults($results, $sqlExecuted);
			echo '</div>';
			break;

		case 'joins':
			echo '<div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🔗 JOINS Demonstration</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			// INNER JOIN
			echo '<div class="border-l-4 border-green-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">INNER JOIN</h3>';
			$sql1 = "SELECT b.book_id, b.title, b.author, c.cat_name, COUNT(r.review_id) as review_count
FROM books b
INNER JOIN categories c ON b.category_id = c.cat_id
LEFT JOIN reviews r ON r.book_id = b.book_id
GROUP BY b.book_id, b.title, b.author, c.cat_name
ORDER BY review_count DESC
LIMIT 5";
			$results1 = $db->run($sql1)->fetchAll();
			displayResults($results1, $sql1);
			echo '</div>';
			
			// LEFT JOIN
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">LEFT JOIN (Books with/without reviews)</h3>';
			$sql2 = "SELECT b.book_id, b.title, COUNT(r.review_id) as review_count,
CASE WHEN COUNT(r.review_id) = 0 THEN 'No reviews yet' ELSE CONCAT(COUNT(r.review_id), ' reviews') END as status
FROM books b
LEFT JOIN reviews r ON r.book_id = b.book_id
GROUP BY b.book_id, b.title
ORDER BY review_count ASC
LIMIT 5";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			// RIGHT JOIN simulation (MySQL doesn't fully support RIGHT JOIN, we'll use LEFT JOIN reversed)
			echo '<div class="border-l-4 border-purple-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Multi-table JOIN</h3>';
			$sql3 = "SELECT u.name as user_name, b.title as book_title, r.rating, r.comment, r.review_date
FROM reviews r
INNER JOIN users u ON r.user_id = u.user_id
INNER JOIN books b ON r.book_id = b.book_id
ORDER BY r.review_date DESC
LIMIT 8";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'aggregates':
			echo '<div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">📊 Aggregate Functions</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-purple-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">COUNT, AVG, MAX, MIN, SUM</h3>';
			$sql = "SELECT 
	COUNT(*) as total_books,
	COUNT(DISTINCT author) as unique_authors,
	COUNT(DISTINCT category_id) as categories_used,
	AVG(price) as avg_price,
	MAX(price) as max_price,
	MIN(price) as min_price,
	SUM(stock) as total_inventory,
	AVG(published_year) as avg_year,
	MIN(published_year) as oldest_year,
	MAX(published_year) as newest_year
FROM books";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-indigo-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Review Statistics</h3>';
			$sql2 = "SELECT 
	COUNT(*) as total_reviews,
	AVG(rating) as avg_rating,
	MAX(rating) as max_rating,
	MIN(rating) as min_rating,
	SUM(helpful_count) as total_helpful,
	COUNT(DISTINCT user_id) as unique_reviewers,
	COUNT(DISTINCT book_id) as books_reviewed
FROM reviews";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'groupby':
			echo '<div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">📦 GROUP BY and HAVING</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-orange-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Books per Category (GROUP BY)</h3>';
			$sql = "SELECT c.cat_name, c.icon,
	COUNT(b.book_id) as book_count,
	AVG(b.price) as avg_price,
	SUM(b.stock) as total_stock
FROM categories c
LEFT JOIN books b ON b.category_id = c.cat_id
GROUP BY c.cat_id, c.cat_name, c.icon
ORDER BY book_count DESC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-red-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Categories with 2+ books (HAVING)</h3>';
			$sql2 = "SELECT c.cat_name,
	COUNT(b.book_id) as book_count,
	AVG(b.price) as avg_price
FROM categories c
INNER JOIN books b ON b.category_id = c.cat_id
GROUP BY c.cat_id, c.cat_name
HAVING COUNT(b.book_id) >= 2
ORDER BY book_count DESC";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '<div class="border-l-4 border-pink-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Top Reviewers (HAVING avg_rating >= 4)</h3>';
			$sql3 = "SELECT u.name, u.email,
	COUNT(r.review_id) as review_count,
	AVG(r.rating) as avg_rating,
	MIN(r.rating) as min_rating,
	MAX(r.rating) as max_rating
FROM users u
INNER JOIN reviews r ON r.user_id = u.user_id
GROUP BY u.user_id, u.name, u.email
HAVING AVG(r.rating) >= 4 AND COUNT(r.review_id) >= 2
ORDER BY review_count DESC, avg_rating DESC";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'subqueries':
			echo '<div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🔍 Subqueries (Nested SELECT, IN, EXISTS)</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-red-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Books with above-average price (Subquery in WHERE)</h3>';
			$sql = "SELECT book_id, title, author, price,
	(SELECT AVG(price) FROM books) as avg_price,
	price - (SELECT AVG(price) FROM books) as price_diff
FROM books
WHERE price > (SELECT AVG(price) FROM books)
ORDER BY price DESC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Books IN categories with 2+ books (IN subquery)</h3>';
			$sql2 = "SELECT b.book_id, b.title, c.cat_name
FROM books b
INNER JOIN categories c ON b.category_id = c.cat_id
WHERE b.category_id IN (
	SELECT category_id 
	FROM books 
	GROUP BY category_id 
	HAVING COUNT(*) >= 2
)
ORDER BY c.cat_name, b.title";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '<div class="border-l-4 border-green-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Books that have reviews (EXISTS)</h3>';
			$sql3 = "SELECT b.book_id, b.title, b.author
FROM books b
WHERE EXISTS (
	SELECT 1 FROM reviews r WHERE r.book_id = b.book_id
)
ORDER BY b.title
LIMIT 8";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '<div class="border-l-4 border-purple-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Books with NO reviews (NOT EXISTS)</h3>';
			$sql4 = "SELECT b.book_id, b.title, b.author, b.published_year
FROM books b
WHERE NOT EXISTS (
	SELECT 1 FROM reviews r WHERE r.book_id = b.book_id
)
ORDER BY b.published_year DESC";
			$results4 = $db->run($sql4)->fetchAll();
			displayResults($results4, $sql4);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'set_ops':
			echo '<div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🔀 Set Operations (UNION, INTERSECT, EXCEPT)</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-teal-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">UNION - Combine books and their status</h3>';
			$sql = "SELECT book_id, title, 'Has Reviews' as status, 
	(SELECT COUNT(*) FROM reviews WHERE book_id = b.book_id) as count
FROM books b
WHERE EXISTS (SELECT 1 FROM reviews r WHERE r.book_id = b.book_id)

UNION

SELECT book_id, title, 'No Reviews' as status, 0 as count
FROM books b
WHERE NOT EXISTS (SELECT 1 FROM reviews r WHERE r.book_id = b.book_id)

ORDER BY status DESC, title ASC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">UNION ALL - Users and their activity types</h3>';
			$sql2 = "SELECT u.name, 'Review' as activity_type, COUNT(*) as count
FROM users u
INNER JOIN reviews r ON u.user_id = r.user_id
GROUP BY u.user_id, u.name

UNION ALL

SELECT u.name, 'Favorite' as activity_type, COUNT(*) as count
FROM users u
INNER JOIN favorites f ON u.user_id = f.user_id
GROUP BY u.user_id, u.name

ORDER BY name, activity_type";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '<div class="border-l-4 border-purple-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">INTERSECT simulation - Books that are both reviewed AND favorited</h3>';
			$sql3 = "SELECT DISTINCT b.book_id, b.title, b.author
FROM books b
WHERE b.book_id IN (SELECT book_id FROM reviews)
  AND b.book_id IN (SELECT book_id FROM favorites)
ORDER BY b.title";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '<div class="border-l-4 border-red-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">EXCEPT simulation - Books favorited but NOT reviewed</h3>';
			$sql4 = "SELECT DISTINCT b.book_id, b.title, b.author
FROM books b
INNER JOIN favorites f ON b.book_id = f.book_id
WHERE b.book_id NOT IN (SELECT book_id FROM reviews)
ORDER BY b.title";
			$results4 = $db->run($sql4)->fetchAll();
			displayResults($results4, $sql4);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'cte':
			echo '<div class="bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🎭 Common Table Expressions (WITH / CTE)</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-pink-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Single CTE - Book statistics</h3>';
			$sql = "WITH BookStats AS (
	SELECT b.book_id, b.title, b.author, b.price,
		COUNT(r.review_id) as review_count,
		AVG(r.rating) as avg_rating
	FROM books b
	LEFT JOIN reviews r ON r.book_id = b.book_id
	GROUP BY b.book_id, b.title, b.author, b.price
)
SELECT book_id, title, author, price, review_count, 
	ROUND(avg_rating, 2) as avg_rating,
	CASE 
		WHEN avg_rating >= 4.5 THEN 'Excellent'
		WHEN avg_rating >= 3.5 THEN 'Good'
		WHEN avg_rating >= 2.5 THEN 'Average'
		WHEN avg_rating IS NOT NULL THEN 'Poor'
		ELSE 'Not Rated'
	END as category
FROM BookStats
ORDER BY avg_rating DESC, review_count DESC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-purple-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Multiple CTEs - Complex analysis</h3>';
			$sql2 = "WITH 
CategoryStats AS (
	SELECT category_id, COUNT(*) as book_count
	FROM books
	GROUP BY category_id
),
ReviewStats AS (
	SELECT book_id, COUNT(*) as review_count, AVG(rating) as avg_rating
	FROM reviews
	GROUP BY book_id
)
SELECT b.book_id, b.title, c.cat_name,
	cs.book_count as books_in_category,
	COALESCE(rs.review_count, 0) as reviews,
	COALESCE(ROUND(rs.avg_rating, 2), 0) as rating
FROM books b
INNER JOIN categories c ON b.category_id = c.cat_id
INNER JOIN CategoryStats cs ON cs.category_id = b.category_id
LEFT JOIN ReviewStats rs ON rs.book_id = b.book_id
ORDER BY rating DESC, reviews DESC
LIMIT 10";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'views':
			echo '<div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">👁️ Using Views</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-indigo-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">v_book_ratings (Book ratings view)</h3>';
			$sql = "SELECT * FROM v_book_ratings ORDER BY avg_rating DESC, review_count DESC LIMIT 10";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">v_top_books (Top rated books view)</h3>';
			$sql2 = "SELECT * FROM v_top_books LIMIT 8";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '<div class="border-l-4 border-green-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">v_user_activity (User activity view)</h3>';
			$sql3 = "SELECT * FROM v_user_activity ORDER BY total_reviews DESC, total_favorites DESC";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'procedures':
			echo '<div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">⚙️ Stored Procedures</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-yellow-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">CALL sp_get_book_stats(1)</h3>';
			$sql = "CALL sp_get_book_stats(1)";
			$stmt = $db->run($sql);
			$results = $stmt->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-orange-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">CALL sp_bulk_update_ratings() - Uses LOOP and CASE</h3>';
			$sql2 = "CALL sp_bulk_update_ratings()";
			$stmt2 = $db->run($sql2);
			$results2 = $stmt2->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'functions':
			echo '<div class="bg-gradient-to-r from-cyan-500 to-cyan-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🔧 Custom Functions</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-cyan-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">fn_get_user_review_count() - Count reviews per user</h3>';
			$sql = "SELECT u.user_id, u.name, u.email,
	fn_get_user_review_count(u.user_id) as review_count
FROM users u
ORDER BY review_count DESC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">fn_calculate_book_score() - Custom scoring algorithm</h3>';
			$sql2 = "SELECT b.book_id, b.title, b.author,
	ROUND(fn_calculate_book_score(b.book_id), 2) as book_score,
	(SELECT COUNT(*) FROM reviews WHERE book_id = b.book_id) as review_count,
	(SELECT AVG(rating) FROM reviews WHERE book_id = b.book_id) as avg_rating
FROM books b
ORDER BY book_score DESC
LIMIT 10";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'triggers':
			echo '<div class="bg-gradient-to-r from-rose-500 to-rose-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">⚡ Triggers & Audit Logs</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-rose-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Recommendation Audit Log (trg_recommendations_after_insert)</h3>';
			$sql = "SELECT * FROM rec_audit ORDER BY created_at DESC LIMIT 10";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-pink-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Activity Log (trg_reviews_after_insert)</h3>';
			$sql2 = "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '</div>';
			break;

		case 'case':
			echo '<div class="bg-gradient-to-r from-lime-500 to-lime-600 text-white px-6 py-4">';
			echo '<h2 class="text-2xl font-bold">🔀 CASE Statements (Conditional Logic)</h2>';
			echo '</div><div class="p-6 space-y-6">';
			
			echo '<div class="border-l-4 border-lime-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Book Price Categories</h3>';
			$sql = "SELECT book_id, title, price,
	CASE 
		WHEN price >= 40 THEN 'Premium'
		WHEN price >= 20 THEN 'Standard'
		WHEN price >= 10 THEN 'Budget'
		ELSE 'Bargain'
	END as price_category,
	CASE
		WHEN stock > 20 THEN 'In Stock'
		WHEN stock > 10 THEN 'Low Stock'
		WHEN stock > 0 THEN 'Very Low'
		ELSE 'Out of Stock'
	END as stock_status
FROM books
ORDER BY price DESC";
			$results = $db->run($sql)->fetchAll();
			displayResults($results, $sql);
			echo '</div>';
			
			echo '<div class="border-l-4 border-green-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Book Age Categories</h3>';
			$sql2 = "SELECT book_id, title, author, published_year,
	YEAR(CURRENT_DATE) - published_year as age,
	CASE 
		WHEN YEAR(CURRENT_DATE) - published_year < 5 THEN 'New Release'
		WHEN YEAR(CURRENT_DATE) - published_year < 15 THEN 'Recent'
		WHEN YEAR(CURRENT_DATE) - published_year < 30 THEN 'Modern'
		WHEN YEAR(CURRENT_DATE) - published_year < 50 THEN 'Vintage'
		ELSE 'Classic'
	END as age_category
FROM books
ORDER BY published_year DESC";
			$results2 = $db->run($sql2)->fetchAll();
			displayResults($results2, $sql2);
			echo '</div>';
			
			echo '<div class="border-l-4 border-blue-500 pl-4">';
			echo '<h3 class="font-bold text-lg mb-2">Review Quality Assessment</h3>';
			$sql3 = "SELECT u.name as reviewer, b.title as book, r.rating,
	CASE r.rating
		WHEN 5 THEN '⭐⭐⭐⭐⭐ Excellent'
		WHEN 4 THEN '⭐⭐⭐⭐ Very Good'
		WHEN 3 THEN '⭐⭐⭐ Good'
		WHEN 2 THEN '⭐⭐ Fair'
		WHEN 1 THEN '⭐ Poor'
		ELSE 'Unrated'
	END as rating_display,
	CASE 
		WHEN LENGTH(r.comment) > 100 THEN 'Detailed Review'
		WHEN LENGTH(r.comment) > 30 THEN 'Standard Review'
		WHEN r.comment IS NOT NULL THEN 'Brief Review'
		ELSE 'No Comment'
	END as review_length
FROM reviews r
INNER JOIN users u ON r.user_id = u.user_id
INNER JOIN books b ON r.book_id = b.book_id
ORDER BY r.review_date DESC
LIMIT 10";
			$results3 = $db->run($sql3)->fetchAll();
			displayResults($results3, $sql3);
			echo '</div>';
			
			echo '</div>';
			break;
	}
	
	echo '</div>';
}

function displayResults($results, $sql) {
	// Display SQL
	echo '<div class="mb-4 bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-x-auto">';
	echo '<pre>' . e($sql) . '</pre>';
	echo '</div>';
	
	// Display results
	if (empty($results)) {
		echo '<div class="text-gray-500 italic">No results found.</div>';
		return;
	}
	
	echo '<div class="overflow-x-auto">';
	echo '<table class="min-w-full border border-gray-300">';
	echo '<thead class="bg-gray-100"><tr>';
	
	// Headers
	foreach (array_keys($results[0]) as $col) {
		echo '<th class="border border-gray-300 px-4 py-2 text-left font-semibold">' . e($col) . '</th>';
	}
	echo '</tr></thead><tbody>';
	
	// Rows
	foreach ($results as $row) {
		echo '<tr class="hover:bg-gray-50">';
		foreach ($row as $val) {
			echo '<td class="border border-gray-300 px-4 py-2">' . e($val ?? 'NULL') . '</td>';
		}
		echo '</tr>';
	}
	
	echo '</tbody></table></div>';
	echo '<div class="mt-2 text-sm text-gray-600">Rows returned: ' . count($results) . '</div>';
}

render_footer();
?>