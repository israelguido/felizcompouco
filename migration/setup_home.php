<?php
/**
 * Setup Feliz com Pouco homepage (mysqli, no full Joomla boot).
 * Run: warden env exec php-fpm php /var/www/html/migration/setup_home.php
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

echo "== FCP Home Setup ==\n";

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

// Register module
$ext = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='mod_fcp_articles' AND type='module'")->fetch_row();
if (!$ext) {
	q($db, "INSERT INTO {$p}extensions (name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state)
		VALUES ('mod_fcp_articles','module','mod_fcp_articles','',0,1,1,0,'{\"version\":\"1.0.0\"}','{}','',0,NULL,0,0)");
	echo "Registered mod_fcp_articles\n";
} else {
	echo "mod_fcp_articles already registered\n";
}

// Template style
$style = q($db, "SELECT id, params FROM {$p}template_styles WHERE template='sj_vicmagz' AND client_id=0 ORDER BY home DESC, id ASC LIMIT 1")->fetch_assoc();
if ($style) {
	$params = json_decode($style['params'] ?: '{}', true) ?: [];
	$params['themecolor'] = 'serenity';
	$params['logoType'] = 'image';
	$params['overrideLogoImage'] = 'images/Logo/Logo2-2.png';
	$params['logoWidth'] = '400';
	$params['logoHeight'] = '167';
	$params['favicon'] = 'images/Logo/favicon.png';
	$params['hideComponentHomePage'] = '1';
	$params['ytcopyright'] = 'Copyright © {year} Feliz com Pouco ♥';
	$params['copyright'] = '1';
	$json = esc($db, json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	q($db, "UPDATE {$p}template_styles SET params=$json, home=1 WHERE id=" . (int) $style['id']);
	q($db, "UPDATE {$p}template_styles SET home=0 WHERE client_id=0 AND id<>" . (int) $style['id']);
	echo "Template #{$style['id']} → serenity + logo\n";
}

$cats = [
	'festas' => 133, 'eventos' => 143, 'gastronomia' => 146, 'tecnologia' => 165,
	'fornecedores' => 138, 'mundo' => 125, 'beleza' => 139, 'novidades' => 149,
	'marketing' => 151, 'decor' => 161, 'filmes' => 163,
];
$homeCats = [$cats['festas'], $cats['eventos'], $cats['decor'], $cats['gastronomia'], $cats['tecnologia'], $cats['novidades'], $cats['mundo'], $cats['beleza'], $cats['filmes']];

function upsertModule(mysqli $db, string $p, array $data): int
{
	$title = $data['title'];
	$module = $data['module'];
	$row = $db->query("SELECT id FROM {$p}modules WHERE module=" . esc($db, $module) . " AND title=" . esc($db, $title) . " AND client_id=0")->fetch_assoc();
	$params = is_array($data['params']) ? json_encode($data['params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data['params'];
	$content = $data['content'] ?? '';
	$note = $data['note'] ?? '';
	$position = $data['position'];
	$ordering = (int) ($data['ordering'] ?? 1);
	$published = (int) ($data['published'] ?? 1);
	$showtitle = (int) ($data['showtitle'] ?? 1);

	if ($row) {
		$id = (int) $row['id'];
		q($db, "UPDATE {$p}modules SET
			content=" . esc($db, $content) . ",
			ordering=$ordering,
			position=" . esc($db, $position) . ",
			published=$published,
			module=" . esc($db, $module) . ",
			showtitle=$showtitle,
			params=" . esc($db, $params) . ",
			note=" . esc($db, $note) . ",
			language='*'
			WHERE id=$id");
		echo "Updated #$id $title @ $position\n";
	} else {
		q($db, "INSERT INTO {$p}modules (title,note,content,ordering,position,published,module,access,showtitle,params,client_id,language)
			VALUES (" . esc($db, $title) . "," . esc($db, $note) . "," . esc($db, $content) . ",$ordering," . esc($db, $position) . ",$published," . esc($db, $module) . ",1,$showtitle," . esc($db, $params) . ",0,'*')");
		$id = (int) $db->insert_id;
		echo "Created #$id $title @ $position\n";
	}

	q($db, "DELETE FROM {$p}modules_menu WHERE moduleid=$id");
	q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($id, 0)");
	return $id;
}

q($db, "UPDATE {$p}modules SET published=0 WHERE client_id=0 AND position IN ('sidebar-right','breadcrumbs','sidebar-left') AND module IN ('mod_menu','mod_login','mod_breadcrumbs')");

$base = [
	'show_title' => '1', 'show_category' => '1', 'show_date' => '0', 'show_intro' => '1',
	'show_child' => '1', 'featured' => '0', 'moduleclass_sfx' => '',
];

upsertModule($db, $p, [
	'title' => 'Social Top', 'module' => 'mod_custom', 'position' => 'top1', 'ordering' => 1, 'showtitle' => 0,
	'content' => '<div class="social-top"><a href="https://twitter.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a> <a href="https://www.facebook.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a> <a href="https://instagram.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a></div>',
	'params' => ['prepare_content' => '0'],
]);

upsertModule($db, $p, [
	'title' => 'Busca', 'module' => 'mod_custom', 'position' => 'top2', 'ordering' => 1, 'showtitle' => 0,
	'content' => '<div class="k2SearchBlock search-top"><form class="k2SearchBlockForm fcp-search-form" action="index.php" method="get" role="search"><input type="hidden" name="option" value="com_finder" /><input type="hidden" name="view" value="search" /><input type="search" name="q" class="inputbox" placeholder="Buscar..." aria-label="Buscar" /><button type="submit" class="button" aria-label="Buscar"><i class="fa fa-search"></i></button></form></div>',
	'params' => ['prepare_content' => '0', 'moduleclass_sfx' => ' search-top'],
]);
// Evita busca duplicada (setup antigo criava mod_finder + mod_custom)
q($db, "UPDATE {$p}modules SET published=0 WHERE client_id=0 AND position='top2' AND module='mod_finder'");

$sliderId = upsertModule($db, $p, [
	'title' => 'Slider', 'module' => 'mod_fcp_articles', 'position' => 'slideshow', 'ordering' => 1, 'showtitle' => 0,
	'params' => array_merge($base, [
		'layout' => 'slideshow', 'count' => '5', 'ordering' => 'featured', 'catid' => $homeCats,
		'show_intro' => '0', 'title_limit' => '40', 'theme' => 'style1',
		// Como VicMagz antigo (nb-column0=2 + center): centro + laterais = 3 slides visíveis
		'nb_column0' => '2', 'nb_column1' => '2', 'nb_column2' => '1',
	]),
]);
// Slider só na Home
$homeId = (int) (q($db, "SELECT id FROM {$p}menu WHERE client_id=0 AND home=1 LIMIT 1")->fetch_row()[0] ?? 101);
q($db, "DELETE FROM {$p}modules_menu WHERE moduleid=$sliderId");
q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($sliderId, $homeId)");
echo "Slider #$sliderId assigned only to Home (#$homeId)\n";

upsertModule($db, $p, [
	'title' => 'Popular', 'module' => 'mod_fcp_articles', 'position' => 'position1', 'ordering' => 1, 'showtitle' => 1,
	'params' => array_merge($base, [
		'layout' => 'popular', 'count' => '5', 'ordering' => 'hits', 'catid' => $homeCats,
		'pretext' => 'Os mais queridinhos você encontra aqui! :)',
		'custom_link' => '1', 'custom_link_title' => 'Veja mais', 'custom_link_url' => '/',
		'moduleclass_sfx' => ' text-center', 'title_limit' => '40', 'intro_limit' => '100',
	]),
]);
$popularId = (int) $db->query("SELECT id FROM {$p}modules WHERE module='mod_fcp_articles' AND title='Popular' AND client_id=0")->fetch_row()[0];
q($db, "DELETE FROM {$p}modules_menu WHERE moduleid=$popularId");
q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($popularId, $homeId)");
echo "Popular #$popularId assigned only to Home (#$homeId)\n";

$nlId = upsertModule($db, $p, [
	'title' => 'Newsletter', 'module' => 'mod_fcp_newsletter', 'position' => 'position2', 'ordering' => 1, 'showtitle' => 1,
	'content' => '',
	'params' => [
		'placeholder' => 'Seu e-mail',
		'button_text' => 'ASSINAR',
		'moduleclass_sfx' => 'newsletters',
		'layout' => '_:default',
		'cache' => '0',
	],
]);
q($db, "DELETE FROM {$p}modules_menu WHERE moduleid=$nlId");
q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($nlId, $homeId)");
echo "Newsletter #$nlId assigned only to Home (#$homeId)\n";

upsertModule($db, $p, [
	'title' => 'PARA VOCÊ', 'module' => 'mod_fcp_articles', 'position' => 'position3', 'ordering' => 1, 'showtitle' => 1,
	'params' => array_merge($base, [
		'layout' => 'latest', 'count' => '6', 'ordering' => 'latest',
		'catid' => [$cats['festas'], $cats['eventos'], $cats['decor'], $cats['beleza']],
		'show_date' => '1', 'custom_link' => '1', 'custom_link_title' => 'Veja mais', 'custom_link_url' => '/',
		'title_limit' => '60', 'intro_limit' => '150',
	]),
]);

upsertModule($db, $p, [
	'title' => 'Mais Lidas', 'module' => 'mod_fcp_articles', 'position' => 'right', 'ordering' => 1, 'showtitle' => 1,
	'params' => array_merge($base, [
		'layout' => 'mostviewed', 'count' => '5', 'ordering' => 'hits', 'catid' => $homeCats,
		'show_intro' => '0', 'moduleclass_sfx' => ' most-viewed', 'title_limit' => '50',
	]),
]);

upsertModule($db, $p, [
	'title' => 'Instagram', 'module' => 'mod_fcp_instagram', 'position' => 'right', 'ordering' => 2, 'showtitle' => 1,
	'params' => [
		'username' => 'felizcompouco',
		'shortcodes' => "Czcd8YUuryo\nCz_-_o0OTmA\nCzZ8qzVOo7m\nCywY7DvucGH\nCyobDMXOqJZ\nCyEFNpKAuGI",
		'count' => '6', 'columns' => '3', 'image_size' => 'm',
		'show_follow' => '1', 'follow_text' => 'Siga no Instagram', 'layout' => '_:default',
	],
]);

upsertModule($db, $p, [
	'title' => 'Pinterest ♥', 'module' => 'mod_custom', 'position' => 'right', 'ordering' => 3, 'showtitle' => 1,
	'content' => '<a href="https://www.pinterest.com/felizcompouco" data-pin-do="embedUser" data-pin-lang="pt" data-pin-board-width="350" data-pin-scale-height="240" data-pin-scale-width="80"></a><script async defer src="//assets.pinterest.com/js/pinit.js"></script>',
	'params' => ['prepare_content' => '0'],
]);

upsertModule($db, $p, [
	'title' => '♥ Guia de Fornecedores Queridos ♥', 'module' => 'mod_fcp_articles', 'position' => 'position4', 'ordering' => 1, 'showtitle' => 1,
	'params' => array_merge($base, [
		'layout' => 'trending', 'count' => '8', 'ordering' => 'latest', 'catid' => [$cats['fornecedores']],
		'show_intro' => '0', 'theme' => 'style5', 'nb_column0' => '3', 'nb_column1' => '3', 'nb_column2' => '2',
		'moduleclass_sfx' => 'text-center', 'title_limit' => '60',
	]),
]);

upsertModule($db, $p, [
	'title' => 'Sobre', 'module' => 'mod_custom', 'position' => 'bottom1', 'ordering' => 1, 'showtitle' => 1,
	'content' => '<p>O blog vai muito além do nome, pretende ser um lugar onde as pessoas gostam de saber das novidades, tendências e inspirações, mas tudo com muita qualidade e bom gosto.</p><p>Venha você, também, ser <strong>Feliz</strong> com Pouco! ♥</p><p><a class="btn btn-color" href="/sobre">Saiba mais</a></p>',
	'params' => ['prepare_content' => '0'],
]);

upsertModule($db, $p, [
	'title' => 'Categorias', 'module' => 'mod_menu', 'position' => 'bottom2', 'ordering' => 1, 'showtitle' => 1,
	'params' => ['menutype' => 'mainmenu', 'startLevel' => '1', 'endLevel' => '1', 'showAllChildren' => '0'],
]);

upsertModule($db, $p, [
	'title' => 'Redes Sociais', 'module' => 'mod_custom', 'position' => 'bottom3', 'ordering' => 1, 'showtitle' => 1,
	'content' => '<div class="social-footer"><a href="https://twitter.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a> <a href="https://www.facebook.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a> <a href="https://www.pinterest.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-pinterest"></i></a> <a href="https://www.youtube.com/@felizcompouco" target="_blank" rel="noopener"><i class="fa fa-youtube"></i></a> <a href="https://instagram.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a></div>',
	'params' => ['prepare_content' => '0'],
]);

upsertModule($db, $p, [
	'title' => 'Recentes', 'module' => 'mod_fcp_articles', 'position' => 'bottom4', 'ordering' => 1, 'showtitle' => 1,
	'params' => array_merge($base, [
		'layout' => 'recent', 'count' => '3', 'ordering' => 'latest', 'catid' => $homeCats,
		'show_intro' => '0', 'title_limit' => '25',
	]),
]);

upsertModule($db, $p, [
	'title' => 'Footer Logo', 'module' => 'mod_custom', 'position' => 'footer', 'ordering' => 1, 'showtitle' => 0,
	'content' => '<div class="logo-footer"><a title="Feliz com Pouco" href="/"><img src="images/Logo/coroa-roxa.jpg" width="120" height="80" alt="Feliz com Pouco" /></a></div>',
	'params' => ['prepare_content' => '0', 'moduleclass_sfx' => ' custom-logofooter'],
]);

upsertModule($db, $p, [
	'title' => 'Social Footer', 'module' => 'mod_custom', 'position' => 'footer', 'ordering' => 2, 'showtitle' => 0,
	'content' => '<div class="social-footer"><a href="https://twitter.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a> <a href="https://www.facebook.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a> <a href="https://www.pinterest.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-pinterest"></i></a> <a href="https://www.youtube.com/@felizcompouco" target="_blank" rel="noopener"><i class="fa fa-youtube"></i></a> <a href="https://instagram.com/felizcompouco" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a></div>',
	'params' => ['prepare_content' => '0'],
]);

$renames = [
	'home-decor' => 'Decoração', 'eventos' => 'Eventos', 'marketing-e-latino' => 'Negócios',
	'gastronomia' => 'Gastronomia', 'beleza-e-feminices' => 'Bem-estar', 'novidades' => 'Tendências',
	'tecnologia' => 'Tecnologia', 'festas' => 'Festas', 'filmes' => 'Filmes', 'mundo-geek' => 'Dicas',
];
foreach ($renames as $alias => $title) {
	q($db, "UPDATE {$p}menu SET title=" . esc($db, $title) . " WHERE client_id=0 AND menutype='mainmenu' AND alias=" . esc($db, $alias));
}
echo "Menu titles updated\n";

// Enable Font Awesome on ytshortcodes if plugin exists
$plg = q($db, "SELECT extension_id, params FROM {$p}extensions WHERE element='ytshortcodes' AND type='plugin'")->fetch_assoc();
if ($plg) {
	$pp = json_decode($plg['params'] ?: '{}', true) ?: [];
	$pp['show_sjfont-awesome'] = '1';
	q($db, "UPDATE {$p}extensions SET params=" . esc($db, json_encode($pp)) . " WHERE extension_id=" . (int) $plg['extension_id']);
	echo "Font Awesome enabled on ytshortcodes\n";
}

echo "DONE\n";
