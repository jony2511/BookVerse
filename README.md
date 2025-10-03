## BookVerse – A Book Review & Recommendation Platform

BookVerse is a PHP + MySQL web app showcasing comprehensive SQL usage in a practical platform: users can browse books, review them, save favorites, and view recommendations. Admins manage books, categories, and highlighted recommendations.

### Stack
- PHP 8+ (works with XAMPP)
- MySQL 8+
- Tailwind CSS (CDN)

### Features and SQL Coverage
- Users: register, login, profile
- Books: browse by category, details, search
- Reviews: ratings, comments, average ratings (aggregate)
- Favorites: save/unsave, list
- Recommendations: by users/admins with reasons
- Admin: CRUD on categories and books
- SQL visibility: Each page shows the SQL used; buttons include hover tooltips with the query to be executed
- Keyword search: Find where a given SQL concept is demonstrated

#### SQL Concepts Demonstrated
- DDL: CREATE DATABASE, CREATE TABLE, foreign keys with ON DELETE/UPDATE CASCADE
- DML: INSERT, UPDATE, DELETE
- SELECT: WHERE, ORDER BY, LIMIT, LIKE
- Joins: INNER, LEFT (used across listing/detail pages)
- Aggregation: COUNT, AVG, GROUP BY, HAVING (ratings/reviews)
- Subqueries: recommendations and filtering
- CTE (WITH): search and analytics queries (MySQL 8)
- Views: `v_book_ratings` for book averages
- Stored Procedures/Functions: review insertion with validations and control flow (IF/ELSE, LOOP)
- Triggers: maintain derived data and audit
- Transactions: for multi-step admin operations

### Setup
1. Clone/copy the folder to your XAMPP `htdocs` as `BookVerse`.
2. Open `http://localhost/BookVerse/init_db.php` once to create the database, schema, and sample data.
3. Visit `http://localhost/BookVerse/` to use the app.

Default admin: `admin@bookverse.local` / password: `admin123`

### Project Structure (key files)
- `config.php` – environment config
- `includes/db.php` – DB connection, query helper, SQL logging
- `includes/auth.php` – session, auth helpers, guards
- `includes/ui.php` – UI helpers (SQL info panel, tooltips)
- `partials/header.php`, `partials/footer.php` – layout
- `init_db.php` – creates DB, tables, FKs, views, procedures, triggers, seeds
- `index.php` – home with featured books and examples
- `books.php`, `book.php` – browsing and details (joins, aggregates)
- `favorites.php` – saved books
- `admin/categories.php`, `admin/books.php` – CRUD + transactions
- `recommendations.php` – user/admin recs (subqueries/CTE)
- `sql-search.php` – find where SQL concepts are used

### Notes
- Images are demo URLs. Replace with your own as needed.
- Tailwind is loaded via CDN in the layout.
- All SQL statements are visible in-page and via hover tooltips on related actions.


