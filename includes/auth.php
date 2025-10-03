<?php
require_once __DIR__ . '/db.php';

function ensure_session_started(): void {
	if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function current_user(): ?array {
	ensure_session_started();
	return $_SESSION['user'] ?? null;
}

function require_login(): void {
	if (!current_user()) {
		header('Location: login.php');
		exit;
	}
}

function require_admin(): void {
	$user = current_user();
	if (!$user || ($user['role'] ?? 'user') !== 'admin') {
		http_response_code(403);
		echo 'Forbidden';
		exit;
	}
}

function login_user(string $email, string $password): bool {
	$db = get_db();
	$sql = 'SELECT user_id, name, email, password, role, profile_pic FROM users WHERE email = :email';
	$stmt = $db->run($sql, ['email' => $email]);
	$user = $stmt->fetch();
	if ($user && password_verify($password, $user['password'])) {
		ensure_session_started();
		unset($user['password']);
		$_SESSION['user'] = $user;
		return true;
	}
	return false;
}

function logout_user(): void {
	ensure_session_started();
	$_SESSION = [];
	@session_destroy();
}
?>


