# BookVerse - Complete SQL Operations Demonstration

A modern web-based book management system built with PHP and MySQL, demonstrating **ALL SQL operations** taught in database courses.

## 🎯 Project Overview

BookVerse is a comprehensive book review and discovery platform that showcases real-world implementation of:
- Database Design & DDL
- CRUD Operations & DML
- Complex Queries & Joins
- Stored Procedures & Functions
- Triggers & Transactions
- Views & CTEs
- And much more!

## ✨ Features

### User Features
- 📚 Browse and search books
- ⭐ Review and rate books
- ❤️ Create favorite lists
- 🔍 Advanced filtering and sorting
- 📊 Personalized recommendations

### Admin Features
- 📖 Manage books (Add, Edit, Delete)
- 📁 Manage categories
- 👥 View user activity
- 📈 Analytics dashboard
- 💾 Database operations

## 🗄️ Database Schema

### Tables
1. **users** - User accounts with role-based access
2. **categories** - Book categories/genres
3. **books** - Book catalog with details
4. **reviews** - User reviews and ratings
5. **recommendations** - Book recommendations
6. **favorites** - User favorite books
7. **rec_audit** - Audit log for recommendations (trigger)
8. **activity_log** - Activity tracking (trigger)

### Constraints Implemented
- ✅ PRIMARY KEY
- ✅ FOREIGN KEY
- ✅ UNIQUE
- ✅ CHECK (age, rating, price, stock, year)
- ✅ ON DELETE CASCADE
- ✅ ON DELETE SET NULL
- ✅ ON UPDATE CASCADE
- ✅ UNIQUE composite keys

## 📊 SQL Operations Implemented

### DDL (Data Definition Language)
```sql
✓ CREATE DATABASE
✓ CREATE TABLE (with all constraint types)
✓ ALTER TABLE (ADD COLUMN, MODIFY COLUMN)
✓ DROP TABLE
✓ CREATE VIEW
✓ CREATE PROCEDURE
✓ CREATE FUNCTION
✓ CREATE TRIGGER
✓ CREATE INDEX
```

### DML (Data Manipulation Language)
```sql
✓ INSERT (single and bulk)
✓ UPDATE (with CASE statements)
✓ DELETE (with conditions)
✓ SELECT with WHERE
✓ SELECT with ORDER BY
✓ SELECT with LIMIT
✓ INSERT ... ON DUPLICATE KEY UPDATE
```

### Joins
```sql
✓ INNER JOIN
✓ LEFT JOIN
✓ RIGHT JOIN (simulated)
✓ Multiple table joins
✓ Self joins
```

### Aggregate Functions
```sql
✓ COUNT()
✓ AVG()
✓ MAX()
✓ MIN()
✓ SUM()
✓ COALESCE()
```

### Grouping & Filtering
```sql
✓ GROUP BY
✓ HAVING
✓ GROUP BY with multiple columns
✓ HAVING with aggregates
```

### Subqueries
```sql
✓ Subquery in SELECT
✓ Subquery in WHERE
✓ Subquery in FROM
✓ IN operator
✓ EXISTS operator
✓ NOT EXISTS
✓ Correlated subqueries
```

### Set Operations
```sql
✓ UNION
✓ UNION ALL
✓ INTERSECT (simulated with IN/AND)
✓ EXCEPT/MINUS (simulated with NOT IN)
```

### Advanced Features
```sql
✓ Common Table Expressions (WITH/CTE)
✓ Multiple CTEs
✓ Views
✓ CASE statements (simple and searched)
✓ Conditional updates
✓ String functions (CONCAT, LENGTH, etc.)
✓ Date functions (NOW, DATE_SUB, YEAR, etc.)
```

### Stored Procedures
```sql
✓ sp_add_review - WITH transaction, IF-ELSE, validation
✓ sp_get_book_stats - Complex aggregation
✓ sp_bulk_update_ratings - WITH LOOP, CURSOR, CASE
```

### Functions
```sql
✓ fn_get_user_review_count - Simple count
✓ fn_calculate_book_score - WITH IF-ELSE logic, calculations
```

### Triggers
```sql
✓ trg_recommendations_after_insert - Auto-logging
✓ trg_reviews_after_insert - Activity tracking
```

### Transactions
```sql
✓ START TRANSACTION
✓ COMMIT
✓ ROLLBACK
✓ Error handling with SIGNAL
```

## 🚀 Quick Start

### Prerequisites
- XAMPP (or similar: Apache + MySQL + PHP)
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+

### Installation

1. **Clone/Copy to XAMPP**
   ```bash
   # Copy BookVerse folder to
   E:\xampp\htdocs\BookVerse
   ```

2. **Start XAMPP**
   - Start Apache
   - Start MySQL

3. **Initialize Database**
   - Open browser: `http://localhost/BookVerse/init_db.php`
   - This will:
     - Create database
     - Create all tables with constraints
     - Create views, procedures, functions, triggers
     - Insert sample data

4. **Access Application**
   - Home: `http://localhost/BookVerse/`
   - Admin: `http://localhost/BookVerse/admin/dashboard.php`
   - SQL Demos: `http://localhost/BookVerse/sql-demo.php`

### Default Credentials

**Admin Account:**
- Email: `admin@bookverse.local`
- Password: `admin123`

**User Accounts:**
- Email: `alice@example.com` / Password: `alice123`
- Email: `bob@example.com` / Password: `bob123`

## 📁 Project Structure

```
BookVerse/
├── index.php              # Homepage with featured books
├── init_db.php            # Database initialization (ALL DDL)
├── config.php             # Database configuration
├── books.php              # Book listing with filters
├── book.php               # Book details page
├── sql-demo.php           # Interactive SQL demonstrations
├── sql-search.php         # SQL keyword index
├── login.php              # User authentication
├── register.php           # User registration
├── favorites.php          # User favorites
├── recommendations.php    # Book recommendations
├── includes/
│   ├── db.php            # Database class
│   ├── ui.php            # UI helpers
│   └── auth.php          # Authentication
├── admin/
│   ├── dashboard.php     # Admin dashboard
│   ├── books.php         # Book management
│   └── categories.php    # Category management
└── views/                # Additional views
```

## 🎓 SQL Learning Paths

### Path 1: Basic Operations
1. Visit `init_db.php` - See all CREATE statements
2. Visit homepage - See SELECT, JOIN, GROUP BY
3. Visit `books.php` - See WHERE, ORDER BY, LIMIT

### Path 2: Advanced Queries
1. Visit `sql-demo.php` → Click "Subqueries"
2. Click "CTEs (WITH)"
3. Click "Set Operations"
4. Click "CASE Statements"

### Path 3: Stored Programs
1. Visit `sql-demo.php` → Click "Stored Procedures"
2. Click "Functions"
3. Click "Triggers"
4. View audit logs

### Path 4: Admin Operations
1. Login as admin
2. Visit `admin/dashboard.php`
3. Add/Edit/Delete books (INSERT, UPDATE, DELETE)
4. View analytics (Complex aggregations)

## 📊 SQL Query Examples

### Example 1: Complex JOIN with Aggregates
```sql
SELECT b.book_id, b.title, b.author, c.cat_name,
    COALESCE(AVG(r.rating), 0) AS avg_rating,
    COUNT(r.review_id) AS review_count,
    MAX(r.rating) AS max_rating,
    MIN(r.rating) AS min_rating
FROM books b
INNER JOIN categories c ON c.cat_id = b.category_id
LEFT JOIN reviews r ON r.book_id = b.book_id
GROUP BY b.book_id, b.title, b.author, c.cat_name
HAVING COUNT(r.review_id) >= 1
ORDER BY avg_rating DESC
```

### Example 2: CTE (Common Table Expression)
```sql
WITH BookStats AS (
    SELECT b.book_id, b.title,
        COUNT(r.review_id) as review_count,
        AVG(r.rating) as avg_rating
    FROM books b
    LEFT JOIN reviews r ON r.book_id = b.book_id
    GROUP BY b.book_id, b.title
)
SELECT book_id, title, review_count, avg_rating,
    CASE 
        WHEN avg_rating >= 4.5 THEN 'Excellent'
        WHEN avg_rating >= 3.5 THEN 'Good'
        WHEN avg_rating >= 2.5 THEN 'Average'
        ELSE 'Poor'
    END as category
FROM BookStats
ORDER BY avg_rating DESC
```

### Example 3: Subquery with EXISTS
```sql
SELECT b.book_id, b.title, b.author
FROM books b
WHERE EXISTS (
    SELECT 1 FROM reviews r WHERE r.book_id = b.book_id AND r.rating = 5
)
AND NOT EXISTS (
    SELECT 1 FROM favorites f WHERE f.book_id = b.book_id
)
```

### Example 4: UNION for Combined Results
```sql
SELECT book_id, title, 'Has Reviews' as status
FROM books
WHERE book_id IN (SELECT book_id FROM reviews)

UNION

SELECT book_id, title, 'No Reviews' as status
FROM books
WHERE book_id NOT IN (SELECT book_id FROM reviews)

ORDER BY status, title
```

## 🎯 Key Features for Learning

### 1. Interactive SQL Tooltips
- Hover over any element with `data-sql` attribute
- See the exact SQL query used
- Learn by exploring!

### 2. SQL Query Display Buttons
- Click "Show SQL" buttons
- View formatted queries
- Understand complex operations

### 3. Comprehensive Demo Page
- Visit `/sql-demo.php`
- Click any SQL operation
- See results and queries
- All operations demonstrated

### 4. Admin Panel Analytics
- Real-world business queries
- Complex aggregations
- Multi-table joins
- Practical examples

## 🔍 SQL Features Checklist

✅ **DDL**: CREATE, ALTER, DROP
✅ **DML**: INSERT, UPDATE, DELETE  
✅ **Constraints**: PK, FK, UNIQUE, CHECK  
✅ **Basic SELECT**: WHERE, ORDER BY, LIMIT  
✅ **Joins**: INNER, LEFT, RIGHT, FULL  
✅ **Aggregates**: COUNT, AVG, MAX, MIN, SUM  
✅ **Grouping**: GROUP BY, HAVING  
✅ **Subqueries**: Nested SELECT, IN, EXISTS  
✅ **Set Operations**: UNION, INTERSECT, MINUS  
✅ **Views**: CREATE VIEW, query views  
✅ **CTEs**: WITH clause  
✅ **Transactions**: START, COMMIT, ROLLBACK  
✅ **Cascade**: ON DELETE CASCADE, SET NULL  
✅ **Triggers**: AFTER INSERT, auto-logging  
✅ **Procedures**: With IF-ELSE, loops, cursors  
✅ **Functions**: Return values, calculations  
✅ **Conditional**: CASE statements  

## 🎨 UI/UX Features

- 🌈 Modern gradient designs
- 🎭 Smooth animations and transitions
- 📱 Fully responsive
- 🌙 Professional color schemes
- 💫 Interactive hover effects
- 🔘 Button-based SQL query display
- 🎯 Tooltip SQL previews
- 📊 Visual analytics dashboards

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+ / MariaDB 10.3+
- **Frontend**: HTML5, TailwindCSS
- **JavaScript**: Vanilla JS (tooltips, modals)

## 📝 Database Configuration

Edit `config.php`:
```php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'name' => 'bookverse_db',
    ],
];
```

## 🎓 Educational Value

This project demonstrates:
1. **Complete SQL curriculum** coverage
2. **Real-world application** of database concepts
3. **Best practices** in database design
4. **Professional code** structure
5. **Modern UI/UX** patterns
6. **Security considerations** (prepared statements)

## 📚 Learning Resources

### Included Documentation
- All SQL queries are visible in the UI
- Hover tooltips show query details
- Demo page with 12+ operation types
- Commented code throughout

### Recommended Study Path
1. Initialize database → Study DDL
2. Browse homepage → Study SELECT, JOIN
3. Use filters → Study WHERE, GROUP BY
4. Visit SQL demos → Study advanced features
5. Admin panel → Study DML operations
6. View triggers/procedures → Study programming

## 🤝 Contributing

This is an educational project. Feel free to:
- Add more SQL examples
- Enhance UI components
- Add new features
- Improve documentation

## 📄 License

Educational project - Free to use and modify

## 👨‍💻 Author

Created as a comprehensive SQL operations demonstration for database courses.

## 🌟 Highlights

- ✅ **100% SQL Coverage**: Every SQL operation demonstrated
- ✅ **Interactive Learning**: Click, hover, explore
- ✅ **Real-world App**: Practical book management system
- ✅ **Modern Design**: Professional UI/UX
- ✅ **Well Documented**: Clear code and comments
- ✅ **Easy Setup**: One-click database initialization

## 📞 Support

For issues or questions:
1. Check `init_db.php` for database setup
2. Review `sql-demo.php` for query examples
3. Inspect browser console for errors
4. Verify MySQL service is running

---

**Happy Learning! 📚✨**

Visit `http://localhost/BookVerse/` to get started!