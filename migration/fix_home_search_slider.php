<?php
/**
 * Fix: slider só na home, busca sem duplicar, limpa assignment.
 * Run: warden env exec php-fpm php /var/www/html/migration/fix_home_search_slider.php
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
echo "Home menu id: $homeId\n";

// 1) Slider (#112 / position slideshow) → apenas Home
$sliderIds = $pdo->query("SELECT id FROM {$p}modules WHERE client_id=0 AND position='slideshow' AND published=1")->fetchAll(PDO::FETCH_COLUMN);
foreach ($sliderIds as $mid) {
	$mid = (int) $mid;
	$pdo->exec("DELETE FROM {$p}modules_menu WHERE moduleid=$mid");
	$pdo->exec("INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($mid, $homeId)");
	echo "Slider #$mid → only menu $homeId\n";
}

// 2) Busca duplicada: manter mod_custom estilizado (#111), despublicar mod_finder em top2
$pdo->exec("UPDATE {$p}modules SET published=0
	WHERE client_id=0 AND position='top2' AND module='mod_finder'");
echo "Unpublished mod_finder on top2\n";

$pdo->exec("UPDATE {$p}modules SET published=1, showtitle=0
	WHERE client_id=0 AND position='top2' AND module='mod_custom' AND title LIKE '%Busca%'");
echo "Kept mod_custom Busca on top2 published\n";

// Garantir assignment all-pages para a busca custom
$buscaIds = $pdo->query("SELECT id FROM {$p}modules
	WHERE client_id=0 AND position='top2' AND module='mod_custom' AND published=1")->fetchAll(PDO::FETCH_COLUMN);
foreach ($buscaIds as $mid) {
	$mid = (int) $mid;
	$pdo->exec("DELETE FROM {$p}modules_menu WHERE moduleid=$mid");
	$pdo->exec("INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($mid, 0)");
	echo "Busca #$mid → all pages\n";
}

// 3) Newsletter → apenas Home
$nlIds = $pdo->query("SELECT id FROM {$p}modules WHERE client_id=0 AND module='mod_fcp_newsletter'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($nlIds as $mid) {
	$mid = (int) $mid;
	$pdo->exec("DELETE FROM {$p}modules_menu WHERE moduleid=$mid");
	$pdo->exec("INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($mid, $homeId)");
	echo "Newsletter #$mid → only menu $homeId\n";
}

// 4) Popular (position1) → apenas Home
$popularIds = $pdo->query("SELECT id FROM {$p}modules WHERE client_id=0 AND published=1 AND (title='Popular' OR (position='position1' AND module='mod_fcp_articles'))")->fetchAll(PDO::FETCH_COLUMN);
foreach ($popularIds as $mid) {
	$mid = (int) $mid;
	$pdo->exec("DELETE FROM {$p}modules_menu WHERE moduleid=$mid");
	$pdo->exec("INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($mid, $homeId)");
	echo "Popular #$mid → only menu $homeId\n";
}

echo "Done.\n";
