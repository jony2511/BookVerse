<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';

render_header('Initialize Database', 'Complete SQL demonstration: DDL, DML, Constraints, Joins, Aggregates, Subqueries, Views, Procedures, Triggers & More');

$cfg = require __DIR__ . '/config.php';
$pdo = null;
$sqlLog = []; // Track all executed SQL

try {
	// Connect without db for initial create
	$dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $cfg['db']['host'], $cfg['db']['port'], $cfg['db']['charset']);
	$pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);

	$dbName = $cfg['db']['name'];
	
	// ============ DDL: CREATE DATABASE ============
	$sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE DATABASE'] = $sql;
	
	$pdo->exec("USE `$dbName`");

	// ============ DDL: DROP TABLES (if exists) for clean install ============
	$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
	$dropTables = ['favorites', 'recommendations', 'reviews', 'books', 'categories', 'users', 'rec_audit', 'activity_log'];
	foreach ($dropTables as $table) {
		$sql = "DROP TABLE IF EXISTS `$table`";
		$pdo->exec($sql);
	}
	$sqlLog['DDL: DROP TABLES'] = "DROP TABLE IF EXISTS " . implode(', ', $dropTables);
	
	// Drop views, procedures, triggers, functions
	$pdo->exec("DROP VIEW IF EXISTS v_book_ratings");
	$pdo->exec("DROP VIEW IF EXISTS v_top_books");
	$pdo->exec("DROP VIEW IF EXISTS v_user_activity");
	$pdo->exec("DROP PROCEDURE IF EXISTS sp_add_review");
	$pdo->exec("DROP PROCEDURE IF EXISTS sp_get_book_stats");
	$pdo->exec("DROP PROCEDURE IF EXISTS sp_bulk_update_ratings");
	$pdo->exec("DROP FUNCTION IF EXISTS fn_get_user_review_count");
	$pdo->exec("DROP FUNCTION IF EXISTS fn_calculate_book_score");
	$pdo->exec("DROP TRIGGER IF EXISTS trg_recommendations_after_insert");
	$pdo->exec("DROP TRIGGER IF EXISTS trg_reviews_after_insert");
	$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

	// ============ DDL: CREATE TABLES with CONSTRAINTS ============
	
	// Table: users (PRIMARY KEY, UNIQUE, CHECK constraints)
	$sql = <<<SQL
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  profile_pic VARCHAR(255) NULL,
  age INT NULL CHECK (age >= 13 AND age <= 120),
  status ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE users (PK, UNIQUE, CHECK)'] = $sql;

	// Table: categories (PRIMARY KEY, UNIQUE)
	$sql = <<<SQL
CREATE TABLE categories (
  cat_id INT AUTO_INCREMENT PRIMARY KEY,
  cat_name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NULL,
  icon VARCHAR(50) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE categories'] = $sql;

	// Table: books (FOREIGN KEY with CASCADE)
	$sql = <<<SQL
CREATE TABLE books (
  book_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  picture VARCHAR(255) NULL,
  author VARCHAR(150) NOT NULL,
  category_id INT NOT NULL,
  published_year INT NULL CHECK (published_year >= 1000 AND published_year <= 2100),
  isbn VARCHAR(20) NULL UNIQUE,
  pages INT NULL CHECK (pages > 0),
  language VARCHAR(50) NOT NULL DEFAULT 'English',
  description TEXT NULL,
  price DECIMAL(10,2) NULL CHECK (price >= 0),
  stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_books_category FOREIGN KEY (category_id)
    REFERENCES categories(cat_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  INDEX idx_title (title),
  INDEX idx_author (author),
  INDEX idx_category (category_id)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE books (FK, CASCADE, CHECK)'] = $sql;

	// Table: reviews (FOREIGN KEY, CHECK constraint)
	$sql = <<<SQL
CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT NULL,
  helpful_count INT NOT NULL DEFAULT 0 CHECK (helpful_count >= 0),
  review_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  UNIQUE KEY uq_user_book_review (user_id, book_id),
  INDEX idx_book_rating (book_id, rating),
  INDEX idx_review_date (review_date)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE reviews (FK, UNIQUE, CHECK)'] = $sql;

	// Table: recommendations (FK with SET NULL)
	$sql = <<<SQL
CREATE TABLE recommendations (
  rec_id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  user_id INT NULL,
  reason VARCHAR(255) NOT NULL,
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_recs_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT fk_recs_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE SET NULL 
    ON UPDATE CASCADE,
  INDEX idx_priority (priority)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE recommendations (FK, SET NULL)'] = $sql;

	// Table: favorites (UNIQUE composite key)
	$sql = <<<SQL
CREATE TABLE favorites (
  fav_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes TEXT NULL,
  UNIQUE KEY uq_fav_user_book (user_id, book_id),
  CONSTRAINT fk_fav_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT fk_fav_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  INDEX idx_date_added (date_added)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE favorites (UNIQUE composite)'] = $sql;

	// ============ Audit/Log Tables for TRIGGERS ============
	
	// Audit table for recommendations
	$sql = <<<SQL
CREATE TABLE rec_audit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rec_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,
  note VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE rec_audit'] = $sql;

	// Activity log for reviews
	$sql = <<<SQL
CREATE TABLE activity_log (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(50) NOT NULL,
  record_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,
  user_id INT NULL,
  details TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_table_record (table_name, record_id)
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE activity_log'] = $sql;

	// ============ DDL: ALTER TABLE examples ============
	
	// ALTER: Add column
	$sql = "ALTER TABLE books ADD COLUMN IF NOT EXISTS featured TINYINT(1) DEFAULT 0";
	try { $pdo->exec($sql); } catch (Exception $e) {}
	$sqlLog['DDL: ALTER TABLE (ADD COLUMN)'] = $sql;
	
	// ALTER: Modify column
	$sql = "ALTER TABLE users MODIFY COLUMN name VARCHAR(150) NOT NULL";
	try { $pdo->exec($sql); } catch (Exception $e) {}
	$sqlLog['DDL: ALTER TABLE (MODIFY COLUMN)'] = $sql;

	// ============ VIEWS ============
	
	// View 1: Book ratings with aggregates
	$sql = <<<SQL
CREATE VIEW v_book_ratings AS
SELECT 
  b.book_id, 
  b.title, 
  b.author,
  b.category_id,
  c.cat_name,
  COALESCE(AVG(r.rating), 0) AS avg_rating, 
  COUNT(r.review_id) AS review_count,
  MAX(r.rating) AS max_rating,
  MIN(r.rating) AS min_rating,
  SUM(r.helpful_count) AS total_helpful
FROM books b
LEFT JOIN reviews r ON r.book_id = b.book_id
LEFT JOIN categories c ON c.cat_id = b.category_id
GROUP BY b.book_id, b.title, b.author, b.category_id, c.cat_name
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE VIEW (with AVG, COUNT, MAX, MIN, SUM, GROUP BY)'] = $sql;

	// View 2: Top rated books
	$sql = <<<SQL
CREATE VIEW v_top_books AS
SELECT 
  b.book_id,
  b.title,
  b.author,
  c.cat_name,
  AVG(r.rating) AS avg_rating,
  COUNT(r.review_id) AS review_count
FROM books b
INNER JOIN categories c ON c.cat_id = b.category_id
LEFT JOIN reviews r ON r.book_id = b.book_id
GROUP BY b.book_id, b.title, b.author, c.cat_name
HAVING COUNT(r.review_id) >= 1
ORDER BY avg_rating DESC, review_count DESC
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE VIEW (with INNER JOIN, HAVING)'] = $sql;

	// View 3: User activity summary
	$sql = <<<SQL
CREATE VIEW v_user_activity AS
SELECT 
  u.user_id,
  u.name,
  u.email,
  COUNT(DISTINCT r.review_id) AS total_reviews,
  COUNT(DISTINCT f.fav_id) AS total_favorites,
  AVG(r.rating) AS avg_rating_given,
  MAX(r.review_date) AS last_review_date
FROM users u
LEFT JOIN reviews r ON r.user_id = u.user_id
LEFT JOIN favorites f ON f.user_id = u.user_id
GROUP BY u.user_id, u.name, u.email
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE VIEW v_user_activity (user stats)'] = $sql;

	// ============ STORED FUNCTIONS ============
	
	// Function 1: Count user reviews
	$sql = <<<SQL
CREATE FUNCTION fn_get_user_review_count(p_user_id INT)
RETURNS INT
READS SQL DATA
BEGIN
  DECLARE v_count INT DEFAULT 0;
  SELECT COUNT(*) INTO v_count 
  FROM reviews 
  WHERE user_id = p_user_id;
  RETURN v_count;
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE FUNCTION (simple count)'] = $sql;

	// Function 2: Calculate book score with IF-ELSE
	$sql = <<<SQL
CREATE FUNCTION fn_calculate_book_score(p_book_id INT)
RETURNS DECIMAL(10,2)
READS SQL DATA
BEGIN
  DECLARE v_avg_rating DECIMAL(10,2) DEFAULT 0;
  DECLARE v_review_count INT DEFAULT 0;
  DECLARE v_score DECIMAL(10,2) DEFAULT 0;
  
  SELECT AVG(rating), COUNT(*) INTO v_avg_rating, v_review_count
  FROM reviews
  WHERE book_id = p_book_id;
  
  -- Formula: score = avg_rating * (1 + log(review_count + 1))
  IF v_review_count > 0 THEN
    SET v_score = v_avg_rating * (1 + LOG(v_review_count + 1));
  ELSE
    SET v_score = 0;
  END IF;
  
  RETURN v_score;
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE FUNCTION (with IF-ELSE logic)'] = $sql;

	// ============ STORED PROCEDURES ============
	
	// Procedure 1: Add review with transaction and validation
	$sql = <<<SQL
CREATE PROCEDURE sp_add_review(
  IN p_user_id INT, 
  IN p_book_id INT, 
  IN p_rating TINYINT, 
  IN p_comment TEXT
)
BEGIN
  DECLARE v_exists INT DEFAULT 0;
  DECLARE v_err VARCHAR(100);

  -- Validation
  IF p_rating < 1 OR p_rating > 5 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Rating must be between 1 and 5';
  END IF;

  START TRANSACTION;
  
  -- Check user exists
  SELECT COUNT(*) INTO v_exists FROM users WHERE user_id = p_user_id;
  IF v_exists = 0 THEN
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User not found';
  END IF;

  -- Check book exists
  SET v_exists = 0;
  SELECT COUNT(*) INTO v_exists FROM books WHERE book_id = p_book_id;
  IF v_exists = 0 THEN
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Book not found';
  END IF;

  -- Insert or update review
  INSERT INTO reviews(user_id, book_id, rating, comment)
  VALUES(p_user_id, p_book_id, p_rating, p_comment)
  ON DUPLICATE KEY UPDATE 
    rating = p_rating, 
    comment = p_comment,
    review_date = CURRENT_TIMESTAMP;

  COMMIT;
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE PROCEDURE (TRANSACTION, IF-ELSE, SIGNAL)'] = $sql;

	// Procedure 2: Get book statistics
	$sql = <<<SQL
CREATE PROCEDURE sp_get_book_stats(IN p_book_id INT)
BEGIN
  SELECT 
    b.book_id,
    b.title,
    b.author,
    c.cat_name,
    COUNT(r.review_id) AS total_reviews,
    AVG(r.rating) AS avg_rating,
    MAX(r.rating) AS max_rating,
    MIN(r.rating) AS min_rating,
    SUM(r.helpful_count) AS total_helpful,
    COUNT(f.fav_id) AS favorite_count
  FROM books b
  LEFT JOIN categories c ON c.cat_id = b.category_id
  LEFT JOIN reviews r ON r.book_id = b.book_id
  LEFT JOIN favorites f ON f.book_id = b.book_id
  WHERE b.book_id = p_book_id
  GROUP BY b.book_id, b.title, b.author, c.cat_name;
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE PROCEDURE sp_get_book_stats'] = $sql;

	// Procedure 3: Bulk update with loop and conditional updates
	$sql = <<<SQL
CREATE PROCEDURE sp_bulk_update_ratings()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_book_id INT;
  DECLARE v_avg_rating DECIMAL(3,2);
  DECLARE v_review_count INT;
  
  DECLARE cur CURSOR FOR 
    SELECT book_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
    FROM reviews
    GROUP BY book_id;
  
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
  
  -- Create temp table for results
  DROP TEMPORARY TABLE IF EXISTS temp_book_ratings;
  CREATE TEMPORARY TABLE temp_book_ratings (
    book_id INT,
    avg_rating DECIMAL(3,2),
    review_count INT,
    category VARCHAR(50)
  );
  
  OPEN cur;
  
  read_loop: LOOP
    FETCH cur INTO v_book_id, v_avg_rating, v_review_count;
    
    IF done THEN
      LEAVE read_loop;
    END IF;
    
    -- Conditional categorization using CASE
    INSERT INTO temp_book_ratings(book_id, avg_rating, review_count, category)
    VALUES(
      v_book_id, 
      v_avg_rating, 
      v_review_count,
      CASE 
        WHEN v_avg_rating >= 4.5 THEN 'Excellent'
        WHEN v_avg_rating >= 3.5 THEN 'Good'
        WHEN v_avg_rating >= 2.5 THEN 'Average'
        ELSE 'Poor'
      END
    );
    
  END LOOP;
  
  CLOSE cur;
  
  -- Return results
  SELECT * FROM temp_book_ratings ORDER BY avg_rating DESC;
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE PROCEDURE (LOOP, CURSOR, CASE)'] = $sql;

	// ============ TRIGGERS ============
	
	// Trigger 1: Audit recommendations on INSERT
	$sql = <<<SQL
CREATE TRIGGER trg_recommendations_after_insert
AFTER INSERT ON recommendations
FOR EACH ROW
BEGIN
  INSERT INTO rec_audit(rec_id, action, note) 
  VALUES(NEW.rec_id, 'INSERT', CONCAT('Recommendation created for book ', NEW.book_id, ' by user ', COALESCE(NEW.user_id, 0)));
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TRIGGER (AFTER INSERT)'] = $sql;

	// Trigger 2: Log review activity
	$sql = <<<SQL
CREATE TRIGGER trg_reviews_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
  INSERT INTO activity_log(table_name, record_id, action, user_id, details)
  VALUES('reviews', NEW.review_id, 'INSERT', NEW.user_id, 
         CONCAT('Added review for book ', NEW.book_id, ' with rating ', NEW.rating));
END
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TRIGGER trg_reviews_after_insert'] = $sql;

	// ============ DML: INSERT Sample Data ============
	
	$hasUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
	if ($hasUsers === 0) {
		$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
		$sql = "INSERT INTO users(name, email, password, role, age, status) VALUES 
			('Admin User', 'admin@bookverse.local', '$adminPass', 'admin', 30, 'active'),
			('Alice Johnson', 'alice@example.com', '" . password_hash('alice123', PASSWORD_DEFAULT) . "', 'user', 25, 'active'),
			('Bob Smith', 'bob@example.com', '" . password_hash('bob123', PASSWORD_DEFAULT) . "', 'user', 32, 'active'),
			('Charlie Brown', 'charlie@example.com', '" . password_hash('charlie123', PASSWORD_DEFAULT) . "', 'user', 28, 'active'),
			('Diana Prince', 'diana@example.com', '" . password_hash('diana123', PASSWORD_DEFAULT) . "', 'user', 27, 'active')";
		$pdo->exec($sql);
		$sqlLog['DML: INSERT users'] = "INSERT INTO users(name, email, password, role, age, status) VALUES (...)";
	}

	$hasCats = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
	if ($hasCats === 0) {
		$sql = "INSERT INTO categories(cat_name, description, icon) VALUES 
			('Fiction', 'Imaginative stories and novels', '📖'),
			('Non-fiction', 'Factual and informative books', '📚'),
			('Fantasy', 'Magical and mythical stories', '🧙'),
			('Science', 'Scientific knowledge and discoveries', '🔬'),
			('Technology', 'Tech guides and programming', '💻'),
			('Biography', 'Life stories of notable people', '👤'),
			('History', 'Historical events and periods', '🏛️'),
			('Mystery', 'Thriller and detective stories', '🔍')";
		$pdo->exec($sql);
		$sqlLog['DML: INSERT categories'] = $sql;
	}

	$hasBooks = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
	if ($hasBooks === 0) {
		$books = [
			['The Pragmatic Programmer','https://images.unsplash.com/photo-1510936111840-65e151ad71bb?q=80&w=800','Andrew Hunt',5,'978-0135957059',352,'English','A comprehensive guide to pragmatic programming',1999,49.99,15,1],
			['Clean Code','https://images.unsplash.com/photo-1513475382585-d06e58bcb0ea?q=80&w=800','Robert C. Martin',5,'978-0132350884',464,'English','A handbook of agile software craftsmanship',2008,42.99,20,1],
			['Dune','https://images.unsplash.com/photo-1541963463532-d68292c34b19?q=80&w=800','Frank Herbert',3,'978-0441172719',688,'English','Epic science fiction novel',1965,15.99,30,1],
			['Sapiens','https://images.unsplash.com/photo-1495446815901-a7297e633e8d?q=80&w=800','Yuval Noah Harari',2,'978-0062316097',464,'English','A brief history of humankind',2011,18.99,25,1],
			['A Brief History of Time','https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=800','Stephen Hawking',4,'978-0553380163',256,'English','From the Big Bang to black holes',1988,16.99,18,1],
			['The Hobbit','https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=800','J.R.R. Tolkien',3,'978-0547928227',300,'English','A fantasy adventure',1937,14.99,22,1],
			['To Kill a Mockingbird','https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=800','Harper Lee',1,'978-0061120084',324,'English','A classic American novel',1960,12.99,28,0],
			['The Martian','https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=800','Andy Weir',4,'978-0553418026',369,'English','Survival story on Mars',2011,13.99,32,1],
			['Steve Jobs','https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=800','Walter Isaacson',6,'978-1451648539',656,'English','Biography of Apple founder',2011,19.99,12,0],
			['The Da Vinci Code','https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=800','Dan Brown',8,'978-0307474278',489,'English','Mystery thriller',2003,14.99,24,0],
		];
		$stmt = $pdo->prepare('INSERT INTO books(title, picture, author, category_id, isbn, pages, language, description, published_year, price, stock, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
		foreach ($books as $b) { $stmt->execute($b); }
		$sqlLog['DML: INSERT books'] = "INSERT INTO books(...) VALUES (...) -- 10 books inserted";
	}

	// DML: INSERT reviews using stored procedure
	$hasReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
	if ($hasReviews === 0) {
		$reviews = [
			[2, 1, 5, 'Excellent book for developers! Must read.'],
			[2, 2, 4, 'Very useful and well-written.'],
			[3, 3, 5, 'Epic fantasy masterpiece!'],
			[3, 6, 4, 'Classic adventure, highly recommend.'],
			[4, 1, 5, 'Changed my programming perspective.'],
			[4, 4, 5, 'Fascinating insights into human history.'],
			[5, 5, 4, 'Mind-bending physics made accessible.'],
			[5, 8, 5, 'Gripping survival story!'],
			[2, 7, 5, 'Timeless American classic.'],
			[3, 10, 4, 'Page-turner mystery novel.'],
		];
		foreach ($reviews as $r) {
			$stmt = $pdo->prepare('CALL sp_add_review(?, ?, ?, ?)');
			$stmt->execute($r);
		}
		$sqlLog['DML: INSERT reviews via PROCEDURE'] = "CALL sp_add_review(user_id, book_id, rating, comment)";
	}

	// DML: INSERT recommendations (triggers will log)
	$hasRecs = (int)$pdo->query('SELECT COUNT(*) FROM recommendations')->fetchColumn();
	if ($hasRecs === 0) {
		$sql = "INSERT INTO recommendations(book_id, user_id, reason, priority) VALUES 
			(1, 1, 'Must read for developers', 'high'),
			(3, 2, 'Sci-fi masterpiece', 'high'),
			(4, NULL, 'Editor\\'s pick', 'medium'),
			(8, 1, 'Exciting Mars adventure', 'medium'),
			(6, 3, 'Perfect for fantasy lovers', 'high')";
		$pdo->exec($sql);
		$sqlLog['DML: INSERT recommendations (fires TRIGGER)'] = $sql;
	}

	// DML: INSERT favorites
	$hasFavs = (int)$pdo->query('SELECT COUNT(*) FROM favorites')->fetchColumn();
	if ($hasFavs === 0) {
		$sql = "INSERT INTO favorites(user_id, book_id, notes) VALUES 
			(2, 1, 'Want to read again'),
			(2, 3, 'Favorite sci-fi'),
			(3, 6, 'Re-reading this year'),
			(4, 1, NULL),
			(5, 5, 'Mind = blown')";
		$pdo->exec($sql);
		$sqlLog['DML: INSERT favorites'] = $sql;
	}

	// DML: UPDATE example with CASE statement
	$sql = <<<SQL
UPDATE books 
SET stock = CASE 
  WHEN book_id <= 3 THEN stock + 10
  WHEN book_id <= 6 THEN stock + 5
  ELSE stock + 2
END
WHERE book_id <= 8
SQL;
	$pdo->exec($sql);
	$sqlLog['DML: UPDATE with CASE'] = $sql;

	// DML: DELETE example (cleanup old recommendations)
	$sql = "DELETE FROM recommendations WHERE priority = 'low' AND created_date < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
	$pdo->exec($sql);
	$sqlLog['DML: DELETE with condition'] = $sql;

	echo '<div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 text-green-900 rounded-lg p-6 mb-6 shadow-lg">';
	echo '<div class="flex items-center mb-3">';
	echo '<svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
	echo '<h2 class="text-2xl font-bold">✓ Database Initialized Successfully!</h2>';
	echo '</div>';
	echo '<p class="text-lg">All SQL features have been implemented: DDL, DML, Constraints, Views, Procedures, Functions, and Triggers.</p>';
	echo '<p class="mt-2 text-sm">Created: ' . count($sqlLog) . ' SQL operations</p>';
	echo '</div>';

} catch (Throwable $e) {
	echo '<div class="bg-red-50 border-2 border-red-300 text-red-900 rounded-lg p-6 shadow-lg">';
	echo '<h3 class="text-xl font-bold mb-2">❌ Error</h3>';
	echo '<p>' . e($e->getMessage()) . '</p>';
	echo '<pre class="mt-2 text-xs bg-red-100 p-2 rounded">' . e($e->getTraceAsString()) . '</pre>';
	echo '</div>';
}

// Display SQL log in organized sections
echo '<div class="mt-8 space-y-4">';

$sections = [
	'DDL' => [],
	'DML' => [],
	'Constraints' => [],
	'Views' => [],
	'Procedures' => [],
	'Functions' => [],
	'Triggers' => [],
];

foreach ($sqlLog as $label => $sql) {
	if (str_contains($label, 'DDL:')) {
		if (str_contains($label, 'VIEW')) $sections['Views'][$label] = $sql;
		elseif (str_contains($label, 'PROCEDURE')) $sections['Procedures'][$label] = $sql;
		elseif (str_contains($label, 'FUNCTION')) $sections['Functions'][$label] = $sql;
		elseif (str_contains($label, 'TRIGGER')) $sections['Triggers'][$label] = $sql;
		else $sections['DDL'][$label] = $sql;
	} elseif (str_contains($label, 'DML:')) {
		$sections['DML'][$label] = $sql;
	}
}

foreach ($sections as $sectionName => $items) {
	if (empty($items)) continue;
	
	$colors = [
		'DDL' => 'from-blue-500 to-indigo-600',
		'DML' => 'from-green-500 to-emerald-600',
		'Views' => 'from-purple-500 to-violet-600',
		'Procedures' => 'from-orange-500 to-red-600',
		'Functions' => 'from-pink-500 to-rose-600',
		'Triggers' => 'from-teal-500 to-cyan-600',
	];
	
	$color = $colors[$sectionName] ?? 'from-gray-500 to-gray-600';
	
	echo '<div class="bg-white border-2 border-gray-200 rounded-lg shadow-lg overflow-hidden">';
	echo '<div class="bg-gradient-to-r ' . $color . ' text-white px-6 py-4">';
	echo '<h3 class="text-xl font-bold">📊 ' . $sectionName . ' Operations (' . count($items) . ')</h3>';
	echo '</div>';
	echo '<div class="p-6 space-y-3">';
	
	foreach ($items as $label => $sql) {
		echo '<div class="border-l-4 border-indigo-500 pl-4 py-2 hover:bg-gray-50 transition-colors">';
		echo '<div class="font-semibold text-gray-800 mb-1">' . e($label) . '</div>';
		echo '<pre class="text-xs font-mono bg-gray-100 p-3 rounded border overflow-x-auto">' . e($sql) . '</pre>';
		echo '</div>';
	}
	
	echo '</div></div>';
}

echo '</div>';

// Auto-redirect to homepage after 5 seconds
echo '<script>
setTimeout(function() {
    window.location.href = "' . base_url('/') . '";
}, 5000);
</script>';

// Quick links
echo '<div class="mt-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg p-6 shadow-lg">';
echo '<h3 class="text-xl font-bold mb-4">🚀 Next Steps</h3>';
echo '<p class="mb-4 text-indigo-100">✅ Database initialized successfully! Redirecting to homepage in 5 seconds...</p>';
echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
echo '<a href="' . e(base_url('/')) . '" class="bg-white/20 hover:bg-white/30 rounded-lg p-4 text-center transition-all hover:scale-105 font-semibold">🏠 Home</a>';
echo '<a href="' . e(base_url('/books.php')) . '" class="bg-white/20 hover:bg-white/30 rounded-lg p-4 text-center transition-all hover:scale-105 font-semibold">📚 Browse Books</a>';
echo '<a href="' . e(base_url('/sql-demo.php')) . '" class="bg-white/20 hover:bg-white/30 rounded-lg p-4 text-center transition-all hover:scale-105 font-semibold">💻 SQL Demos</a>';
echo '<a href="' . e(base_url('/admin/dashboard.php')) . '" class="bg-white/20 hover:bg-white/30 rounded-lg p-4 text-center transition-all hover:scale-105 font-semibold">⚙️ Admin Panel</a>';
echo '</div></div>';

render_footer();
?>