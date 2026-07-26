<?php
/**
 * Registra com_fcp_guia + mod_fcp_guia, migra artigos da categoria 138
 * e troca o módulo #119 (Guia de Fornecedores) para o novo módulo.
 *
 * Produção:
 *   php migration/setup_guia_admin.php
 * Local (Warden):
 *   warden env exec php-fpm php /var/www/html/migration/setup_guia_admin.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("Forbidden: CLI only\n");
}

$root = dirname(__DIR__);
if (!is_file($root . '/configuration.php')) {
	fwrite(STDERR, "configuration.php não encontrado em $root\n");
	exit(1);
}

require $root . '/configuration.php';
$c = new JConfig();

$db = @new mysqli($c->host, $c->user, $c->password, $c->db);
if ($db->connect_error) {
	fwrite(STDERR, "DB ({$c->host}/{$c->db}): {$db->connect_error}\n");
	exit(1);
}
$db->set_charset('utf8mb4');
$p = $c->dbprefix;

function q(mysqli $db, string $sql): mysqli_result|bool
{
	$r = $db->query($sql);
	if ($r === false) {
		fwrite(STDERR, "SQL: {$db->error}\n$sql\n");
		exit(1);
	}

	return $r;
}

function esc(mysqli $db, $v): string
{
	return "'" . $db->real_escape_string((string) $v) . "'";
}

function cleanImagePath(string $raw): string
{
	$raw = trim($raw);
	if ($raw === '') {
		return '';
	}
	if (strpos($raw, '#') !== false) {
		$raw = strtok($raw, '#');
	}

	return ltrim((string) $raw, '/');
}

echo "== Setup Guia de Fornecedores ==\n";
echo "DB host={$c->host} db={$c->db} prefix={$p}\n";

// --- Table ---
q($db, "CREATE TABLE IF NOT EXISTS {$p}fcp_guia (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL DEFAULT '',
	alias VARCHAR(255) NOT NULL DEFAULT '',
	image VARCHAR(512) NOT NULL DEFAULT '',
	image_alt VARCHAR(255) NOT NULL DEFAULT '',
	url VARCHAR(512) NOT NULL DEFAULT '',
	introtext TEXT,
	ordering INT NOT NULL DEFAULT 0,
	state TINYINT NOT NULL DEFAULT 1,
	created DATETIME DEFAULT NULL,
	created_by INT UNSIGNED NOT NULL DEFAULT 0,
	modified DATETIME DEFAULT NULL,
	modified_by INT UNSIGNED NOT NULL DEFAULT 0,
	checked_out INT UNSIGNED DEFAULT NULL,
	checked_out_time DATETIME DEFAULT NULL,
	PRIMARY KEY (id),
	KEY idx_state (state),
	KEY idx_ordering (ordering),
	KEY idx_alias (alias(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Table {$p}fcp_guia OK\n";

// --- Migrate articles from category 138 (only if table empty) ---
$count = (int) q($db, "SELECT COUNT(*) FROM {$p}fcp_guia")->fetch_row()[0];
if ($count === 0) {
	$catId = 138;
	$r = q($db, "SELECT id, title, alias, introtext, images, state, created, created_by, modified, modified_by, ordering
		FROM {$p}content
		WHERE catid={$catId}
		ORDER BY ordering ASC, id DESC");
	$order = 1;
	$migrated = 0;
	while ($row = $r->fetch_assoc()) {
		$images = json_decode($row['images'] ?: '{}', true) ?: [];
		$image  = cleanImagePath((string) ($images['image_intro'] ?? ''));
		$alt    = trim((string) ($images['image_intro_alt'] ?? ''));
		if ($alt === '') {
			$alt = (string) $row['title'];
		}

		$url = 'index.php?option=com_content&view=article&id=' . (int) $row['id'] . ':' . $row['alias'] . '&catid=' . $catId;
		$intro = trim(strip_tags((string) $row['introtext']));
		$created = $row['created'] && $row['created'] !== '0000-00-00 00:00:00' ? $row['created'] : date('Y-m-d H:i:s');
		$modified = $row['modified'] && $row['modified'] !== '0000-00-00 00:00:00' ? $row['modified'] : $created;

		q($db, "INSERT INTO {$p}fcp_guia
			(title, alias, image, image_alt, url, introtext, ordering, state, created, created_by, modified, modified_by)
			VALUES (
				" . esc($db, $row['title']) . ",
				" . esc($db, $row['alias']) . ",
				" . esc($db, $image) . ",
				" . esc($db, $alt) . ",
				" . esc($db, $url) . ",
				" . esc($db, $intro) . ",
				" . (int) $order . ",
				" . ((int) $row['state'] === 1 ? 1 : 0) . ",
				" . esc($db, $created) . ",
				" . (int) $row['created_by'] . ",
				" . esc($db, $modified) . ",
				" . (int) ($row['modified_by'] ?: 0) . "
			)");
		$order++;
		$migrated++;
	}
	echo "Migrated {$migrated} items from category {$catId}\n";
} else {
	echo "Table already has {$count} items — skip migration\n";
}

// --- Register component ---
$manifest = json_encode([
	'name'         => 'COM_FCP_GUIA',
	'type'         => 'component',
	'creationDate' => date('Y-m'),
	'author'       => 'Feliz com Pouco',
	'version'      => '1.0.0',
	'description'  => 'COM_FCP_GUIA_XML_DESCRIPTION',
	'namespace'    => 'FelizComPouco\\Component\\Fcp_guia',
	'filename'     => 'fcp_guia',
], JSON_UNESCAPED_SLASHES);

$ext = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='com_fcp_guia' AND type='component'")->fetch_row();
if ($ext) {
	$extId = (int) $ext[0];
	q($db, "UPDATE {$p}extensions SET name='COM_FCP_GUIA', enabled=1, protected=0, client_id=1,
		manifest_cache=" . esc($db, $manifest) . "
		WHERE extension_id=$extId");
	echo "Updated component #$extId\n";
} else {
	q($db, "INSERT INTO {$p}extensions
		(package_id,name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state,note)
		VALUES (0,'COM_FCP_GUIA','component','com_fcp_guia','',1,1,1,0," . esc($db, $manifest) . ",'{}','',0,NULL,0,0,'')");
	$extId = (int) $db->insert_id;
	echo "Registered component #$extId\n";
}

$asset = q($db, "SELECT id FROM {$p}assets WHERE name='com_fcp_guia'")->fetch_row();
if (!$asset) {
	$rules = '{"core.admin":{"7":1},"core.manage":{"6":1,"7":1},"core.create":{"6":1,"7":1},"core.edit":{"6":1,"7":1},"core.edit.state":{"6":1,"7":1},"core.delete":{"6":1,"7":1}}';
	q($db, "INSERT INTO {$p}assets (parent_id,lft,rgt,level,name,title,rules)
		VALUES (1,0,0,1,'com_fcp_guia','com_fcp_guia'," . esc($db, $rules) . ")");
	echo "Created asset #" . $db->insert_id . "\n";
}

$menu = q($db, "SELECT id FROM {$p}menu WHERE client_id=1 AND menutype='main' AND link='index.php?option=com_fcp_guia' LIMIT 1")->fetch_row();
if ($menu) {
	$menuId = (int) $menu[0];
	q($db, "UPDATE {$p}menu SET title='COM_FCP_GUIA', alias='com-fcp-guia', published=1, component_id=$extId, img='class:images' WHERE id=$menuId");
	echo "Updated admin menu #$menuId\n";
} else {
	q($db, "INSERT INTO {$p}menu
		(menutype,title,alias,note,path,link,type,published,parent_id,level,component_id,checked_out,checked_out_time,browserNav,access,img,template_style_id,params,lft,rgt,home,language,client_id)
		VALUES (
			'main',
			'COM_FCP_GUIA',
			'com-fcp-guia',
			'',
			'com-fcp-guia',
			'index.php?option=com_fcp_guia',
			'component',
			1,
			1,
			1,
			$extId,
			0,NULL,0,1,'class:images',0,'{}',0,0,0,'*',1
		)");
	echo "Created admin menu #" . $db->insert_id . "\n";
}

// --- Register front module ---
$modManifest = json_encode([
	'name'        => 'mod_fcp_guia',
	'type'        => 'module',
	'version'     => '1.0.0',
	'description' => 'MOD_FCP_GUIA_XML_DESCRIPTION',
], JSON_UNESCAPED_SLASHES);

$modExt = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='mod_fcp_guia' AND type='module'")->fetch_row();
if (!$modExt) {
	q($db, "INSERT INTO {$p}extensions (name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state)
		VALUES ('mod_fcp_guia','module','mod_fcp_guia','',0,1,1,0," . esc($db, $modManifest) . ",'{}','',0,NULL,0,0)");
	echo "Registered mod_fcp_guia #" . $db->insert_id . "\n";
} else {
	q($db, "UPDATE {$p}extensions SET enabled=1, manifest_cache=" . esc($db, $modManifest) . " WHERE extension_id=" . (int) $modExt[0]);
	echo "Updated mod_fcp_guia #" . (int) $modExt[0] . "\n";
}

$modParams = json_encode([
	'count'           => '8',
	'show_title'      => '1',
	'title_limit'     => '60',
	'theme'           => 'style1',
	'nb_column0'      => '3',
	'nb_column1'      => '3',
	'nb_column2'      => '2',
	'layout'          => '_:default',
	'moduleclass_sfx' => '',
	'cache'           => '0',
	'module_tag'      => 'div',
	'bootstrap_size'  => '0',
	'header_tag'      => 'h3',
	'header_class'    => '',
	'style'           => '0',
], JSON_UNESCAPED_UNICODE);

// Swap module #119 (or any Guia module) to mod_fcp_guia
$guiaMods = q($db, "SELECT id, title, module, position FROM {$p}modules
	WHERE client_id=0 AND (
		id=119
		OR title LIKE '%Guia de Fornecedores%'
		OR (module='mod_fcp_articles' AND position='position4' AND params LIKE '%trending%')
	)");
$swapped = 0;
while ($row = $guiaMods->fetch_assoc()) {
	$id = (int) $row['id'];
	q($db, "UPDATE {$p}modules SET
		module='mod_fcp_guia',
		title=" . esc($db, $row['title'] ?: '♥ Guia de Fornecedores Queridos ♥') . ",
		published=1,
		showtitle=1,
		params=" . esc($db, $modParams) . "
		WHERE id=$id");
	$mm = q($db, "SELECT COUNT(*) FROM {$p}modules_menu WHERE moduleid=$id")->fetch_row();
	if (!(int) $mm[0]) {
		q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($id, 0)");
	}
	echo "Swapped front module #{$id} ({$row['title']}) → mod_fcp_guia\n";
	$swapped++;
}

if ($swapped === 0) {
	$exists = q($db, "SELECT id FROM {$p}modules WHERE module='mod_fcp_guia' AND position='position4' LIMIT 1")->fetch_row();
	if (!$exists) {
		q($db, "INSERT INTO {$p}modules (title,note,content,ordering,position,checked_out,checked_out_time,publish_up,publish_down,published,module,access,showtitle,params,client_id,language)
			VALUES (" . esc($db, '♥ Guia de Fornecedores Queridos ♥') . ",'','',1,'position4',0,NULL,NULL,NULL,1,'mod_fcp_guia',1,1," . esc($db, $modParams) . ",0,'*')");
		$newId = (int) $db->insert_id;
		q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($newId, 0)");
		echo "Created front Guia module #$newId\n";
	}
}

// --- Languages ---
foreach (['pt-BR', 'en-GB'] as $tag) {
	$srcDir = $root . "/administrator/components/com_fcp_guia/language/$tag";
	$dstDir = $root . "/administrator/language/$tag";
	if (!is_dir($dstDir)) {
		@mkdir($dstDir, 0755, true);
	}
	foreach (['com_fcp_guia.ini', 'com_fcp_guia.sys.ini'] as $file) {
		$src = "$srcDir/$file";
		$dst = "$dstDir/$file";
		if (is_file($src)) {
			copy($src, $dst);
			echo "Lang admin $tag/$file\n";
		}
	}

	$modSrcDir = $root . "/modules/mod_fcp_guia/language/$tag";
	$modDstDir = $root . "/language/$tag";
	if (!is_dir($modDstDir)) {
		@mkdir($modDstDir, 0755, true);
	}
	foreach (['mod_fcp_guia.ini', 'mod_fcp_guia.sys.ini'] as $file) {
		$src = "$modSrcDir/$file";
		$dst = "$modDstDir/$file";
		if (is_file($src)) {
			copy($src, $dst);
			echo "Lang site $tag/$file\n";
		}
	}
}

// Rebuild PSR-4 namespace map (cache não vai no Git — obrigatório após deploy)
$rebuild = $root . '/migration/rebuild_namespaces.php';
if (is_file($rebuild)) {
	passthru('php ' . escapeshellarg($rebuild), $rebuildCode);
	if (!empty($rebuildCode)) {
		fwrite(STDERR, "Falha ao regenerar namespace map\n");
	}
} else {
	$rootEsc = var_export($root, true);
	passthru('php -r ' . escapeshellarg(
		'define("_JEXEC",1);'
		. 'define("JPATH_BASE",' . $rootEsc . ');'
		. 'require JPATH_BASE."/includes/defines.php";'
		. 'require JPATH_LIBRARIES."/vendor/autoload.php";'
		. 'require JPATH_LIBRARIES."/namespacemap.php";'
		. 'echo ((new JNamespacePsr4Map())->create() ? "Namespace map OK\n" : "Namespace map FAIL\n");'
	));
}

// Clear caches
foreach ([$root . '/cache', $root . '/administrator/cache'] as $dir) {
	if (!is_dir($dir)) {
		continue;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $f) {
		if ($f->isFile() && $f->getFilename() !== 'index.html' && $f->getFilename() !== 'autoload_psr4.php') {
			@unlink($f->getPathname());
		}
	}
}
echo "Cache limpo.\n";

echo "Done.\n";
echo "Admin: Componentes → Guia de Fornecedores\n";
echo "URL: /administrator/index.php?option=com_fcp_guia\n";
