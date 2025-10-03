<?php
class Database {
	private PDO $pdo;
	private bool $debug;

	public function __construct(array $cfg) {
		$this->debug = $cfg['app']['debug'] ?? false;
		$dsn = sprintf('mysql:host=%s;port=%d;charset=%s;dbname=%s',
			$cfg['db']['host'],
			$cfg['db']['port'],
			$cfg['db']['charset'],
			$cfg['db']['name']
		);
		$this->pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
	}

	public function pdo(): PDO { return $this->pdo; }

	public function run(string $sql, array $params = []): PDOStatement {
		$stmt = $this->pdo->prepare($sql);
		foreach ($params as $k => $v) {
			$type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$stmt->bindValue(is_int($k) ? $k + 1 : (str_starts_with($k, ':') ? $k : ':' . $k), $v, $type);
		}
		$stmt->execute();
		return $stmt;
	}

	public static function captureSqlForTooltip(string $sql, array $params = []): string {
		// Very simple interpolation for display purposes only (not for execution)
		foreach ($params as $key => $value) {
			$placeholder = is_int($key) ? '?' : (str_starts_with($key, ':') ? $key : ':' . $key);
			$display = is_numeric($value) ? (string)$value : ("'" . str_replace("'", "''", (string)$value) . "'");
			$pattern = '~' . preg_quote($placeholder, '~') . '~';
			$sql = preg_replace($pattern, $display, $sql, 1);
		}
		return $sql;
	}
}

function get_db(): Database {
	static $db = null;
	if ($db === null) {
		$cfg = require __DIR__ . '/../config.php';
		$db = new Database($cfg);
	}
	return $db;
}
?>


