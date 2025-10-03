<?php
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$pass = $_POST['password'] ?? '';
	if (login_user($email, $pass)) {
		header('Location: index.php');
		exit;
	}
	$error = 'Invalid credentials';
}

render_header('Login');

if ($error) echo '<div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 mb-3">' . e($error) . '</div>';

echo '<form method="post" class="bg-white border rounded p-4 max-w-md">';
echo '<label class="block text-sm">Email</label><input name="email" type="email" class="border rounded w-full px-3 py-2 mb-3" required />';
echo '<label class="block text-sm">Password</label><input name="password" type="password" class="border rounded w-full px-3 py-2 mb-4" required />';
echo '<button class="px-3 py-2 bg-blue-600 text-white rounded">Login</button>';
echo '</form>';

sql_info_panel('Login query', [
	'SELECT user_id, name, email, password, role, profile_pic FROM users WHERE email = :email',
]);

render_footer();
?>


