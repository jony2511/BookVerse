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
	
	// DDL: CREATE DATABASE
	$sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE DATABASE'] = $sql;
	
	$pdo->exec("USE `$dbName`");

	// DDL: CREATE TABLES with all constraint types
	
	// Table: users (PRIMARY KEY, UNIQUE, CHECK constraints)
	$sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  profile_pic VARCHAR(255) NULL,
  age INT NULL CHECK (age >= 13 AND age <= 120),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL;
	$pdo->exec($sql);
	$sqlLog['DDL: CREATE TABLE users (PK, UNIQUE, CHECK)'] = $sql;

	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS categories (
  cat_id INT AUTO_INCREMENT PRIMARY KEY,
  cat_name VARCHAR(100) NOT NULL UNIQUE
);
SQL);

	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS books (
  book_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  picture VARCHAR(255) NULL,
  author VARCHAR(150) NOT NULL,
  category_id INT NOT NULL,
  published_year INT NULL,
  CONSTRAINT fk_books_category FOREIGN KEY (category_id)
    REFERENCES categories(cat_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
SQL);

	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT NULL,
  review_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
SQL);

	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS recommendations (
  rec_id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  user_id INT NULL,
  reason VARCHAR(255) NOT NULL,
  created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_recs_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_recs_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE SET NULL ON UPDATE CASCADE
);
SQL);

	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS favorites (
  fav_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fav_user_book (user_id, book_id),
  CONSTRAINT fk_fav_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fav_book FOREIGN KEY (book_id)
    REFERENCES books(book_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
SQL);

	// View: average ratings per book
	$pdo->exec("DROP VIEW IF EXISTS v_book_ratings");
	$pdo->exec(<<<SQL
CREATE VIEW v_book_ratings AS
SELECT b.book_id, b.title, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.review_id) AS review_count
FROM books b
LEFT JOIN reviews r ON r.book_id = b.book_id
GROUP BY b.book_id, b.title;
SQL);

	// Stored procedure: add a review with validation and transaction
	$pdo->exec("DROP PROCEDURE IF EXISTS sp_add_review");
	$pdo->exec(<<<SQL
CREATE PROCEDURE sp_add_review(IN p_user_id INT, IN p_book_id INT, IN p_rating TINYINT, IN p_comment TEXT)
BEGIN
  DECLARE v_exists INT DEFAULT 0;
  DECLARE v_err VARCHAR(100);

  IF p_rating < 1 OR p_rating > 5 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Rating must be 1..5';
  END IF;

  START TRANSACTION;
  SELECT COUNT(*) INTO v_exists FROM users WHERE user_id = p_user_id;
  IF v_exists = 0 THEN
    SET v_err = 'User not found';
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err;
  END IF;

  SET v_exists = 0;
  SELECT COUNT(*) INTO v_exists FROM books WHERE book_id = p_book_id;
  IF v_exists = 0 THEN
    SET v_err = 'Book not found';
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err;
  END IF;

  INSERT INTO reviews(user_id, book_id, rating, comment)
  VALUES(p_user_id, p_book_id, p_rating, p_comment);

  COMMIT;
END;
SQL);

	// Trigger example: audit recommendations insert into a log table
	$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS rec_audit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rec_id INT NOT NULL,
  note VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);
	$pdo->exec("DROP TRIGGER IF EXISTS trg_recommendations_after_insert");
	$pdo->exec(<<<SQL
CREATE TRIGGER trg_recommendations_after_insert
AFTER INSERT ON recommendations
FOR EACH ROW
BEGIN
  INSERT INTO rec_audit(rec_id, note) VALUES(NEW.rec_id, CONCAT('Recommendation created for book ', NEW.book_id));
END;
SQL);

	// Seed data if empty
	$hasUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
	if ($hasUsers = 0) { }
	if ($hasUsers === 0) {
		$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
		$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES (?,?,?,?)')->execute(['Admin','admin@bookverse.local',$adminPass,'admin']);
		$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES (?,?,?,?)')->execute(['Alice','alice@example.com',password_hash('alice123', PASSWORD_DEFAULT),'user']);
		$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES (?,?,?,?)')->execute(['Bob','bob@example.com',password_hash('bob123', PASSWORD_DEFAULT),'user']);
	}

	$hasCats = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
	if ($hasCats === 0) {
		$pdo->exec("INSERT INTO categories(cat_name) VALUES ('Fiction'),('Non-fiction'),('Fantasy'),('Science'),('Technology')");
	}

	$hasBooks = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
	if ($hasBooks === 0) {
		$books = [
			['The Pragmatic Programmer','https://images.unsplash.com/photo-1510936111840-65e151ad71bb?q=80&w=800','Andrew Hunt',5,1999],
			['Clean Code','https://images.unsplash.com/photo-1513475382585-d06e58bcb0ea?q=80&w=800','Robert C. Martin',5,2008],
			['Dune','https://images.unsplash.com/photo-1541963463532-d68292c34b19?q=80&w=800','Frank Herbert',3,1965],
			['Sapiens','https://images.unsplash.com/photo-1495446815901-a7297e633e8d?q=80&w=800','Yuval Noah Harari',2,2011],
			['A Brief History of Time','https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=800','Stephen Hawking',4,1988],
			['The Hobbit','https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=800','J.R.R. Tolkien',3,1937],
			['To Kill a Mockingbird','https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=800','Harper Lee',1,1960],
			['The Martian','https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=800','Andy Weir',4,2011],
		];
		$stmt = $pdo->prepare('INSERT INTO books(title,picture,author,category_id,published_year) VALUES (?,?,?,?,?)');
		foreach ($books as $b) { $stmt->execute($b); }
	}

	$hasReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
	if ($hasReviews === 0) {
		$pdo->exec("INSERT INTO reviews(user_id, book_id, rating, comment) VALUES (2,1,5,'Great insights'),(2,2,4,'Very useful'),(3,3,5,'Epic'),(3,6,4,'Classic')");
	}

	$hasRecs = (int)$pdo->query('SELECT COUNT(*) FROM recommendations')->fetchColumn();
	if ($hasRecs === 0) {
		$pdo->exec("INSERT INTO recommendations(book_id,user_id,reason) VALUES (1,1,'Must read for developers'),(3,2,'Sci-fi masterpiece'),(4,NULL,'Editor\'s pick')");
	}

	echo '<div class="bg-green-50 border border-green-200 text-green-800 rounded p-4">Database initialized and seeded successfully.</div>';
} catch (Throwable $e) {
	echo '<div class="bg-red-50 border border-red-200 text-red-800 rounded p-4">Error: ' . e($e->getMessage()) . '</div>';
}

sql_info_panel('Initialization SQL (highlights)', [
	'CREATE DATABASE IF NOT EXISTS bookverse_db ...',
	'CREATE TABLE users (...), categories (...), books (... FK ON DELETE CASCADE ...), reviews (...), recommendations (... ON DELETE SET NULL ...), favorites (... UNIQUE ...)',
	'CREATE VIEW v_book_ratings AS SELECT b.book_id, ... LEFT JOIN reviews GROUP BY ...',
	'CREATE PROCEDURE sp_add_review(...) BEGIN ... IF/ELSE, TRANSACTION, SIGNAL ... END',
	'CREATE TRIGGER trg_recommendations_after_insert AFTER INSERT ON recommendations ...',
]);

render_footer();
?>


