<?php
/**
 * Desativa extensões SJ inseguras (newsletter popup / contact ajax).
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

$elements = ['mod_sj_newsletter_popup', 'mod_sj_contact_ajax'];
foreach ($elements as $el) {
	$n = $pdo->exec("UPDATE {$p}extensions SET enabled=0 WHERE element=" . $pdo->quote($el));
	echo "extension $el disabled (rows=$n)\n";
	$n = $pdo->exec("UPDATE {$p}modules SET published=0 WHERE module=" . $pdo->quote($el));
	echo "modules $el unpublished (rows=$n)\n";
}

echo "Done.\n";
