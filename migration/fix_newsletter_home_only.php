<?php
/**
 * Newsletter só na Home.
 * Run: php migration/fix_newsletter_home_only.php
 */
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("CLI only\n");
}

require dirname(__DIR__) . '/configuration.php';
$c = new JConfig();
$pdo = new PDO(
	'mysql:host=' . $c->host . ';dbname=' . $c->db . ';charset=utf8mb4',
	$c->user,
	$c->password,
	[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$p = $c->dbprefix;

$homeId = (int) $pdo->query("SELECT id FROM {$p}menu WHERE client_id=0 AND home=1 LIMIT 1")->fetchColumn();
if ($homeId < 1) {
	$homeId = 101;
}

$ids = $pdo->query(
	"SELECT id FROM {$p}modules WHERE client_id=0 AND module='mod_fcp_newsletter'"
)->fetchAll(PDO::FETCH_COLUMN);

if (!$ids) {
	echo "Nenhum mod_fcp_newsletter encontrado.\n";
	exit(0);
}

foreach ($ids as $mid) {
	$mid = (int) $mid;
	$pdo->exec("DELETE FROM {$p}modules_menu WHERE moduleid=$mid");
	$pdo->exec("INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($mid, $homeId)");
	echo "Newsletter #$mid → only Home #$homeId\n";
}

echo "Done.\n";
