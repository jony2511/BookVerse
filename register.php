<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$db = get_db();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$pass = $_POST['password'] ?? '';
	if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 6) {
		try {
			$db->run('INSERT INTO users(name,email,password,role) VALUES(:n,:e,:p,\'user\')', [
				'n' => $name,
				'e' => $email,
				'p' => password_hash($pass, PASSWORD_DEFAULT),
			]);
			login_user($email, $pass);
			header('Location: index.php');
			exit;
		} catch (Throwable $e) {
			$error = 'Email already used';
		}
	} else {
		$error = 'Please provide valid info.';
	}
}

render_header('Register');

if ($error) echo '<div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 mb-3">' . e($error) . '</div>';

echo '<form method="post" class="bg-white border rounded p-4 max-w-md">';
echo '<label class="block text-sm">Name</label><input name="name" class="border rounded w-full px-3 py-2 mb-3" required />';
echo '<label class="block text-sm">Email</label><input name="email" type="email" class="border rounded w-full px-3 py-2 mb-3" required />';
echo '<label class="block text-sm">Password</label><input name="password" type="password" class="border rounded w-full px-3 py-2 mb-4" required />';
echo '<button class="px-3 py-2 bg-blue-600 text-white rounded" title="INSERT INTO users(name,email,password,role) VALUES(...)">Register</button>';
echo '</form>';

sql_info_panel('Registration queries', [
	'INSERT INTO users(name,email,password,role) VALUES(:n,:e,:p,\'user\')',
]);

render_footer();
?>


