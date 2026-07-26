<?php
/**
 * Registra com_fcp_newsletter no admin e cria item de menu.
 *
 * Produção:
 *   php migration/setup_newsletter_admin.php
 * Local (Warden):
 *   warden env exec php-fpm php /var/www/html/migration/setup_newsletter_admin.php
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

echo "== Setup Newsletter Admin ==\n";
echo "DB host={$c->host} db={$c->db} prefix={$p}\n";

q($db, "CREATE TABLE IF NOT EXISTS {$p}fcp_newsletter (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	email VARCHAR(190) NOT NULL,
	name VARCHAR(190) NOT NULL DEFAULT '',
	created DATETIME NOT NULL,
	ip VARCHAR(45) DEFAULT NULL,
	user_agent VARCHAR(255) DEFAULT NULL,
	status TINYINT NOT NULL DEFAULT 1,
	source VARCHAR(80) NOT NULL DEFAULT 'module',
	PRIMARY KEY (id),
	UNIQUE KEY email (email),
	KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// --- Front: trocar placeholder "Em breve!" pelo módulo real ---
$modExt = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='mod_fcp_newsletter' AND type='module'")->fetch_row();
if (!$modExt) {
	q($db, "INSERT INTO {$p}extensions (name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state)
		VALUES ('mod_fcp_newsletter','module','mod_fcp_newsletter','',0,1,1,0,'{\"name\":\"mod_fcp_newsletter\",\"type\":\"module\",\"version\":\"1.0.0\"}','{}','',0,NULL,0,0)");
	echo "Registered mod_fcp_newsletter\n";
} else {
	q($db, "UPDATE {$p}extensions SET enabled=1 WHERE extension_id=" . (int) $modExt[0]);
}

$modParams = json_encode([
	'heading'         => 'Newsletter',
	'intro'           => 'Receba novidades, inspirações e ofertas no seu e-mail.',
	'placeholder'     => 'Seu e-mail',
	'button_text'     => 'Assinar',
	'background'      => '',
	'overlay'         => '45',
	'enable_captcha'  => '1',
	'captcha'         => 'powcaptcha',
	'layout'          => '_:default',
	'moduleclass_sfx' => 'newsletters',
	'cache'           => '0',
	'module_tag'      => 'div',
	'bootstrap_size'  => '0',
	'header_tag'      => 'h3',
	'header_class'    => '',
	'style'           => '0',
], JSON_UNESCAPED_UNICODE);

// Qualquer módulo com o alert antigo ou o custom da position2
$fixed = 0;
$r = q($db, "SELECT id, title, module, position, content FROM {$p}modules
	WHERE published=1 AND (
		content LIKE '%Em breve%'
		OR (title='Newsletter' AND position='position2')
		OR (position='position2' AND module='mod_custom' AND content LIKE '%fcp-newsletter%')
		OR module='mod_fcp_newsletter'
	)");
while ($row = $r->fetch_assoc()) {
	$id = (int) $row['id'];
	q($db, "UPDATE {$p}modules SET module='mod_fcp_newsletter', content='', showtitle=0, params=" . esc($db, $modParams) . " WHERE id=$id");
	$mm = q($db, "SELECT COUNT(*) FROM {$p}modules_menu WHERE moduleid=$id")->fetch_row();
	if (!(int) $mm[0]) {
		q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($id, 0)");
	}
	echo "Fixed front module #{$id} ({$row['title']} / {$row['module']} → mod_fcp_newsletter)\n";
	$fixed++;
}
if ($fixed === 0) {
	// cria se não existir
	$exists = q($db, "SELECT id FROM {$p}modules WHERE module='mod_fcp_newsletter' AND position='position2' LIMIT 1")->fetch_row();
	if (!$exists) {
		q($db, "INSERT INTO {$p}modules (title,note,content,ordering,position,checked_out,checked_out_time,publish_up,publish_down,published,module,access,showtitle,params,client_id,language)
			VALUES ('Newsletter','','',1,'position2',0,NULL,NULL,NULL,1,'mod_fcp_newsletter',1,0," . esc($db, $modParams) . ",0,'*')");
		$newId = (int) $db->insert_id;
		q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($newId, 0)");
		echo "Created front Newsletter module #$newId\n";
	} else {
		echo "Front Newsletter já está com mod_fcp_newsletter (#{$exists[0]})\n";
	}
}

// Garante captcha Proof-of-Work ativo
q($db, "UPDATE {$p}extensions SET enabled=1 WHERE element='powcaptcha' AND folder='captcha'");

$manifest = json_encode([
	'name'         => 'COM_FCP_NEWSLETTER',
	'type'         => 'component',
	'creationDate' => date('Y-m'),
	'author'       => 'Feliz com Pouco',
	'version'      => '1.0.0',
	'description'  => 'COM_FCP_NEWSLETTER_XML_DESCRIPTION',
	'namespace'    => 'FelizComPouco\\Component\\Fcpnewsletter',
	'filename'     => 'fcp_newsletter',
], JSON_UNESCAPED_SLASHES);


$ext = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='com_fcp_newsletter' AND type='component'")->fetch_row();
if ($ext) {
	$extId = (int) $ext[0];
	q($db, "UPDATE {$p}extensions SET name='COM_FCP_NEWSLETTER', enabled=1, protected=0, client_id=1,
		manifest_cache=" . esc($db, $manifest) . "
		WHERE extension_id=$extId");
	echo "Updated component #$extId\n";
} else {
	q($db, "INSERT INTO {$p}extensions
		(package_id,name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state,note)
		VALUES (0,'COM_FCP_NEWSLETTER','component','com_fcp_newsletter','',1,1,1,0," . esc($db, $manifest) . ",'{}','',0,NULL,0,0,'')");
	$extId = (int) $db->insert_id;
	echo "Registered component #$extId\n";
}

$asset = q($db, "SELECT id FROM {$p}assets WHERE name='com_fcp_newsletter'")->fetch_row();
if (!$asset) {
	$rules = '{"core.admin":{"7":1},"core.manage":{"6":1,"7":1},"core.delete":{"6":1,"7":1}}';
	q($db, "INSERT INTO {$p}assets (parent_id,lft,rgt,level,name,title,rules)
		VALUES (1,0,0,1,'com_fcp_newsletter','com_fcp_newsletter'," . esc($db, $rules) . ")");
	echo "Created asset #" . $db->insert_id . "\n";
}

$menu = q($db, "SELECT id FROM {$p}menu WHERE client_id=1 AND menutype='main' AND link='index.php?option=com_fcp_newsletter' LIMIT 1")->fetch_row();
if ($menu) {
	$menuId = (int) $menu[0];
	q($db, "UPDATE {$p}menu SET title='COM_FCP_NEWSLETTER', alias='com-fcp-newsletter', published=1, component_id=$extId, img='class:mail' WHERE id=$menuId");
	echo "Updated admin menu #$menuId\n";
} else {
	q($db, "INSERT INTO {$p}menu
		(menutype,title,alias,note,path,link,type,published,parent_id,level,component_id,checked_out,checked_out_time,browserNav,access,img,template_style_id,params,lft,rgt,home,language,client_id)
		VALUES (
			'main',
			'COM_FCP_NEWSLETTER',
			'com-fcp-newsletter',
			'',
			'com-fcp-newsletter',
			'index.php?option=com_fcp_newsletter',
			'component',
			1,
			1,
			1,
			$extId,
			0,NULL,0,1,'class:mail',0,'{}',0,0,0,'*',1
		)");
	echo "Created admin menu #" . $db->insert_id . "\n";
}

foreach (['pt-BR', 'en-GB'] as $tag) {
	$srcDir = $root . "/administrator/components/com_fcp_newsletter/language/$tag";
	$dstDir = $root . "/administrator/language/$tag";
	if (!is_dir($dstDir)) {
		@mkdir($dstDir, 0755, true);
	}
	foreach (['com_fcp_newsletter.ini', 'com_fcp_newsletter.sys.ini'] as $file) {
		$src = "$srcDir/$file";
		$dst = "$dstDir/$file";
		if (is_file($src)) {
			copy($src, $dst);
			echo "Lang $tag/$file\n";
		}
	}
}

// Rebuild PSR-4 namespace map (usa o root real do site)
$rootEsc = var_export($root, true);
passthru('php -r ' . escapeshellarg(
	'define("_JEXEC",1);'
	. 'define("JPATH_BASE",' . $rootEsc . ');'
	. 'require JPATH_BASE."/includes/defines.php";'
	. 'require JPATH_LIBRARIES."/vendor/autoload.php";'
	. 'require JPATH_LIBRARIES."/namespacemap.php";'
	. 'echo ((new JNamespacePsr4Map())->create() ? "Namespace map OK\n" : "Namespace map FAIL\n");'
));

echo "Done.\n";
echo "No admin: Componentes → Newsletter\n";
echo "URL: /administrator/index.php?option=com_fcp_newsletter\n";

// Limpa cache de arquivos
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
