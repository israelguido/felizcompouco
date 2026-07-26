<?php
require dirname(__DIR__) . '/configuration.php';
$c = new JConfig();
$pdo = new PDO(
	'mysql:host=' . $c->host . ';dbname=' . $c->db . ';charset=utf8mb4',
	$c->user,
	$c->password,
	[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$p = $c->dbprefix;

$aliases = ['tecnologia', 'festas', 'gastronomia', 'filmes', 'home-decor', 'novidades', 'mundo-geek', 'beleza-e-feminices', 'eventos'];
$in = implode(',', array_map([$pdo, 'quote'], $aliases));

echo "=== MENU ITEMS ===\n";
$q = $pdo->query("SELECT id,title,alias,link,params FROM {$p}menu WHERE client_id=0 AND published=1 AND alias IN ($in) ORDER BY title");
foreach ($q as $r) {
	$params = json_decode($r['params'], true) ?: [];
	$keys = [
		'num_columns', 'num_leading_articles', 'num_intro_articles', 'num_links',
		'multi_column_order', 'orderby_pri', 'orderby_sec', 'layout_type',
		'show_category_title', 'blog_class', 'blog_class_items', 'pageclass_sfx',
		'show_description', 'show_description_image', 'show_subcategory_content',
	];
	echo "\n#{$r['id']} {$r['title']} ({$r['alias']})\n  link={$r['link']}\n";
	foreach ($keys as $k) {
		if (array_key_exists($k, $params)) {
			echo "  $k=" . json_encode($params[$k], JSON_UNESCAPED_UNICODE) . "\n";
		}
	}
}

echo "\n=== FULL PARAMS DIFF (tecnologia / festas / gastronomia) ===\n";
$q = $pdo->query("SELECT alias, params FROM {$p}menu WHERE client_id=0 AND published=1 AND alias IN ('tecnologia','festas','gastronomia')");
$all = [];
foreach ($q as $r) {
	$all[$r['alias']] = json_decode($r['params'], true) ?: [];
}
$keys = array_unique(array_merge(...array_map('array_keys', $all)));
sort($keys);
foreach ($keys as $k) {
	$vals = [];
	foreach (['tecnologia', 'festas', 'gastronomia'] as $a) {
		$vals[$a] = $all[$a][$k] ?? '∅';
	}
	if (json_encode($vals['tecnologia']) !== json_encode($vals['gastronomia'])
		|| json_encode($vals['festas']) !== json_encode($vals['gastronomia'])) {
		echo "$k:\n";
		foreach ($vals as $a => $v) {
			echo "  $a=" . json_encode($v, JSON_UNESCAPED_UNICODE) . "\n";
		}
	}
}

echo "\n=== ARTICLE COUNTS ===\n";
foreach ([165 => 'tecnologia', 133 => 'festas', 146 => 'gastronomia', 163 => 'filmes'] as $id => $name) {
	$n = (int) $pdo->query("SELECT COUNT(*) FROM {$p}content WHERE catid=$id AND state=1")->fetchColumn();
	echo "$name (cat $id): $n published articles\n";
}
