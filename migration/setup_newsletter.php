<?php
/**
 * Instala mod_fcp_newsletter, cria tabela e substitui o placeholder da home.
 * Produção: php migration/setup_newsletter.php
 * Local:    warden env exec php-fpm php /var/www/html/migration/setup_newsletter.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
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
		fwrite(STDERR, "SQL error: {$db->error}\nSQL: $sql\n");
		exit(1);
	}
	return $r;
}

function esc(mysqli $db, $v): string
{
	return "'" . $db->real_escape_string((string) $v) . "'";
}

echo "== Setup Newsletter ==\n";

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
echo "Table {$p}fcp_newsletter OK\n";

$ext = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='mod_fcp_newsletter' AND type='module'")->fetch_row();
if (!$ext) {
	q($db, "INSERT INTO {$p}extensions (name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state)
		VALUES ('mod_fcp_newsletter','module','mod_fcp_newsletter','',0,1,1,0,'{\"name\":\"mod_fcp_newsletter\",\"type\":\"module\",\"version\":\"1.0.0\"}','{}','',0,NULL,0,0)");
	echo "Registered mod_fcp_newsletter\n";
} else {
	q($db, "UPDATE {$p}extensions SET enabled=1 WHERE extension_id=" . (int) $ext[0]);
	echo "mod_fcp_newsletter already registered (#{$ext[0]})\n";
}

$params = json_encode([
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


$mod = q($db, "SELECT id, module FROM {$p}modules WHERE title='Newsletter' AND position='position2' LIMIT 1")->fetch_assoc();
if ($mod) {
	q($db, "UPDATE {$p}modules SET module='mod_fcp_newsletter', content='', showtitle=0, published=1, params=" . esc($db, $params) . " WHERE id=" . (int) $mod['id']);
	$modId = (int) $mod['id'];
	echo "Converted Newsletter module #{$modId}\n";
} else {
	q($db, "INSERT INTO {$p}modules (title,note,content,ordering,position,checked_out,checked_out_time,publish_up,publish_down,published,module,access,showtitle,params,client_id,language)
		VALUES ('Newsletter','','',1,'position2',0,NULL,NULL,NULL,1,'mod_fcp_newsletter',1,1," . esc($db, $params) . ",0,'*')");
	$modId = (int) $db->insert_id;
	echo "Created Newsletter module #$modId\n";
}

$mm = q($db, "SELECT COUNT(*) FROM {$p}modules_menu WHERE moduleid=$modId")->fetch_row();
if (!(int) $mm[0]) {
	q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($modId, 0)");
	echo "Assigned to all pages\n";
}

// Importar assinantes do dump AcyMailing (arquivo opcional)
$emailsFile = $root . '/migration/acy_emails.txt';
$imported = 0;
if (is_file($emailsFile)) {
	$lines = file($emailsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
	foreach ($lines as $line) {
		$email = strtolower(trim($line));
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			continue;
		}
		$emailEsc = esc($db, $email);
		$ok = $db->query("INSERT IGNORE INTO {$p}fcp_newsletter (email,name,created,status,source)
			VALUES ($emailEsc,'',NOW(),1,'acymailing')");
		if ($ok && $db->affected_rows > 0) {
			$imported++;
		}
	}
	echo "Imported $imported emails from acy_emails.txt\n";
}

$count = q($db, "SELECT COUNT(*) FROM {$p}fcp_newsletter WHERE status=1")->fetch_row();
echo "Active subscribers: {$count[0]}\n";
echo "Done.\n";
