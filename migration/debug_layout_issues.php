<?php
/**
 * Debug: slideshow menu assignment, search modules, category columns
 */
define('_JEXEC', 1);
$root = dirname(__DIR__);
require $root . '/configuration.php';
$c = new JConfig();
$pdo = new PDO(
	'mysql:host=' . $c->host . ';dbname=' . $c->db . ';charset=utf8mb4',
	$c->user,
	$c->password,
	[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$p = $c->dbprefix;

echo "=== MODULES top1/top2/slideshow ===\n";
$q = $pdo->query("SELECT id, title, module, position, published, ordering
	FROM {$p}modules
	WHERE client_id=0 AND position IN ('top1','top2','slideshow','header')
	ORDER BY position, ordering");
foreach ($q as $r) {
	echo "{$r['id']} | {$r['position']} | {$r['module']} | pub={$r['published']} | {$r['title']}\n";
}

echo "\n=== Search/finder modules (any position) ===\n";
$q = $pdo->query("SELECT id, title, module, position, published
	FROM {$p}modules
	WHERE client_id=0 AND (
		module LIKE '%search%' OR module LIKE '%finder%'
		OR title LIKE '%busca%' OR title LIKE '%Busca%'
		OR title LIKE '%search%' OR title LIKE '%Search%'
		OR module = 'mod_fcp_articles'
	)
	ORDER BY position, id");
foreach ($q as $r) {
	echo "{$r['id']} | {$r['position']} | {$r['module']} | pub={$r['published']} | {$r['title']}\n";
}

echo "\n=== Menu assignment slideshow ===\n";
$q = $pdo->query("SELECT m.id, m.title, m.published, mm.menuid
	FROM {$p}modules m
	LEFT JOIN {$p}modules_menu mm ON mm.moduleid = m.id
	WHERE m.client_id=0 AND m.position='slideshow'");
foreach ($q as $r) {
	echo "mod={$r['id']} | {$r['title']} | pub={$r['published']} | menuid={$r['menuid']}\n";
}

echo "\n=== Menu assignment top2 ===\n";
$q = $pdo->query("SELECT m.id, m.title, m.module, m.published, mm.menuid
	FROM {$p}modules m
	LEFT JOIN {$p}modules_menu mm ON mm.moduleid = m.id
	WHERE m.client_id=0 AND m.position='top2'");
foreach ($q as $r) {
	echo "mod={$r['id']} | {$r['module']} | {$r['title']} | pub={$r['published']} | menuid={$r['menuid']}\n";
}

echo "\n=== Category blog menus (num_columns) ===\n";
$q = $pdo->query("SELECT id, title, alias, link, params
	FROM {$p}menu
	WHERE client_id=0 AND published=1
	AND (link LIKE '%view=category%' OR link LIKE '%view=categories%')");
foreach ($q as $r) {
	$params = json_decode($r['params'], true) ?: [];
	$cols = $params['num_columns'] ?? '(unset)';
	$numLead = $params['num_leading_articles'] ?? '?';
	$numIntro = $params['num_intro_articles'] ?? '?';
	echo "{$r['id']} | {$r['title']} | alias={$r['alias']} | cols={$cols} | lead={$numLead} intro={$numIntro}\n";
	echo "  link={$r['link']}\n";
}

echo "\n=== com_content category params (tecnologia/festas) ===\n";
$q = $pdo->query("SELECT id, title, alias, params FROM {$p}categories
	WHERE extension='com_content' AND (alias LIKE '%tecnolog%' OR alias LIKE '%festa%' OR title LIKE '%Tecnolog%' OR title LIKE '%Festa%')");
foreach ($q as $r) {
	$params = json_decode($r['params'], true) ?: [];
	echo "{$r['id']} | {$r['title']} | {$r['alias']}\n";
	foreach (['num_columns','blog_layout','category_layout'] as $k) {
		if (isset($params[$k])) echo "  $k={$params[$k]}\n";
	}
}

echo "\n=== Home menu id ===\n";
$q = $pdo->query("SELECT id, title, home, link FROM {$p}menu WHERE client_id=0 AND home=1");
foreach ($q as $r) {
	echo "{$r['id']} | {$r['title']} | {$r['link']}\n";
}
