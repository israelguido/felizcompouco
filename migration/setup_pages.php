<?php
/**
 * Cria menu-hidden + itens para páginas estáticas já migradas (Sobre, Contato, etc.)
 * e módulo de links no footer.
 *
 * Uso: warden env exec php-fpm php /var/www/html/migration/setup_pages.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	exit("Forbidden: CLI only\n");
}

$db = new mysqli('db', 'wordpress', 'wordpress', 'wordpress');
if ($db->connect_error) {
	fwrite(STDERR, "DB error: {$db->connect_error}\n");
	exit(1);
}
$db->set_charset('utf8mb4');
$p = 'a6u17_';

function q(mysqli $db, string $sql): mysqli_result|bool
{
	$r = $db->query($sql);
	if ($r === false) {
		fwrite(STDERR, "SQL error: {$db->error}\nSQL: $sql\n");
		exit(1);
	}
	return $r;
}

function esc(mysqli $db, $v): string
{
	return "'" . $db->real_escape_string((string) $v) . "'";
}

echo "== Setup páginas estáticas ==\n";

$componentId = (int) q($db, "SELECT extension_id FROM {$p}extensions WHERE element='com_content' AND type='component'")->fetch_row()[0];
if (!$componentId) {
	fwrite(STDERR, "com_content não encontrado\n");
	exit(1);
}

// 1) Menu type
$mt = q($db, "SELECT COUNT(*) c FROM {$p}menu_types WHERE menutype='menu-hidden'")->fetch_assoc();
if (!(int) $mt['c']) {
	q($db, "INSERT INTO {$p}menu_types (menutype, title, description, client_id)
		VALUES ('menu-hidden', 'Menu Hidden', 'Páginas estáticas (footer)', 0)");
	echo "Criado menutype menu-hidden\n";
} else {
	echo "menutype menu-hidden já existe\n";
}

$pages = [
	['title' => 'Contato',   'alias' => 'contato',   'article_id' => 37],
	['title' => 'Anuncie',   'alias' => 'anuncie',   'article_id' => 36],
	['title' => 'Sobre',     'alias' => 'sobre',     'article_id' => 39],
	['title' => 'Mídia Kit', 'alias' => 'midia-kit', 'article_id' => 38],
];

foreach ($pages as $page) {
	$alias = $page['alias'];
	$title = $page['title'];
	$link  = 'index.php?option=com_content&view=article&id=' . (int) $page['article_id'];
	$row   = q($db, "SELECT id FROM {$p}menu WHERE menutype='menu-hidden' AND alias=" . esc($db, $alias) . " AND client_id=0")->fetch_assoc();

	if ($row) {
		q($db, "UPDATE {$p}menu SET
			title=" . esc($db, $title) . ",
			link=" . esc($db, $link) . ",
			type='component',
			published=1,
			component_id={$componentId},
			access=1,
			language='*'
			WHERE id=" . (int) $row['id']);
		echo "Atualizado /{$alias} → artigo {$page['article_id']} (#{$row['id']})\n";
		continue;
	}

	// Conflict check across all site menus
	$conflict = q($db, "SELECT id, menutype FROM {$p}menu WHERE alias=" . esc($db, $alias) . " AND client_id=0 AND published > -2")->fetch_assoc();
	if ($conflict) {
		fwrite(STDERR, "Alias /{$alias} já existe no menu #{$conflict['id']} ({$conflict['menutype']})\n");
		continue;
	}

	// Insert as last child of root: open a gap at root.rgt
	$root = q($db, "SELECT id, lft, rgt FROM {$p}menu WHERE id=1")->fetch_assoc();
	$lft = (int) $root['rgt'];
	$rgt = $lft + 1;
	q($db, "UPDATE {$p}menu SET rgt = rgt + 2 WHERE rgt >= {$lft}");
	q($db, "UPDATE {$p}menu SET lft = lft + 2 WHERE lft > {$lft}");

	q($db, "INSERT INTO {$p}menu
		(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id,
		 checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt,
		 home, language, client_id)
		VALUES (
			'menu-hidden',
			" . esc($db, $title) . ",
			" . esc($db, $alias) . ",
			'',
			" . esc($db, $alias) . ",
			" . esc($db, $link) . ",
			'component',
			1,
			1,
			1,
			{$componentId},
			NULL,
			NULL,
			0,
			1,
			'',
			0,
			'{}',
			{$lft},
			{$rgt},
			0,
			'*',
			0
		)");
	$id = (int) $db->insert_id;
	echo "Criado /{$alias} → artigo {$page['article_id']} (#{$id})\n";
}

// 2) Módulo footer Páginas
$modParams = json_encode([
	'menutype'        => 'menu-hidden',
	'base'            => '',
	'startLevel'      => 1,
	'endLevel'        => 0,
	'showAllChildren' => 1,
	'layout'          => '_:default',
	'moduleclass_sfx' => ' menu-horizontal',
	'class_sfx'       => 'menu-horizontal',
	'cache'           => 1,
	'cache_time'      => 900,
	'cachemode'       => 'itemid',
	'module_tag'      => 'div',
	'bootstrap_size'  => '0',
	'header_tag'      => 'h3',
	'header_class'    => '',
	'style'           => '0',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$mod = q($db, "SELECT id FROM {$p}modules WHERE module='mod_menu' AND title='Páginas' AND client_id=0")->fetch_assoc();
if ($mod) {
	$modId = (int) $mod['id'];
	q($db, "UPDATE {$p}modules SET position='footer', published=1, showtitle=0, params=" . esc($db, $modParams) . " WHERE id={$modId}");
	echo "Atualizado módulo Páginas #{$modId}\n";
} else {
	q($db, "INSERT INTO {$p}modules
		(asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down,
		 published, module, access, showtitle, params, client_id, language)
		VALUES (0, 'Páginas', '', '', 10, 'footer', NULL, NULL, NULL, NULL,
		 1, 'mod_menu', 1, 0, " . esc($db, $modParams) . ", 0, '*')");
	$modId = (int) $db->insert_id;
	echo "Criado módulo Páginas #{$modId}\n";
}

$assigned = (int) q($db, "SELECT COUNT(*) c FROM {$p}modules_menu WHERE moduleid={$modId}")->fetch_assoc()['c'];
if (!$assigned) {
	q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ({$modId}, 0)");
	echo "Módulo Páginas atribuído a todas as páginas\n";
}

echo "OK — /contato /anuncie /sobre /midia-kit\n";

// Mídia Kit (artigo 38) está em Uncategorised (131) — precisa estar publicada
q($db, "UPDATE {$p}categories SET published=1 WHERE id=131 AND published=0");
echo "Categoria do Mídia Kit publicada (se estava oculta)\n";
