<?php
/**
 * Install mod_fcp_instagram and configure sidebar + homepage gallery.
 * Uso: warden env exec php-fpm php /var/www/html/migration/setup_instagram.php
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

echo "== Setup Instagram ==\n";

$shortcodes = implode("\n", [
	'Da5tLVwOPcr', 'Da0-bA6mDOS', 'DaoGD2rN5Yf',
	'DajV9JMOgtK', 'DagoYhmGEve', 'DadNRBVulDE',
	'DaT_71duAmA', 'DaS-xmODpH5', 'DaQTvRmuSJV',
	'DaP3NLjjiZc', 'DaK1wGRuQ0u', 'DaJcUn_mJF9',
]);

$ext = q($db, "SELECT extension_id FROM {$p}extensions WHERE element='mod_fcp_instagram' AND type='module'")->fetch_row();
if (!$ext) {
	q($db, "INSERT INTO {$p}extensions (name,type,element,folder,client_id,enabled,access,protected,manifest_cache,params,custom_data,checked_out,checked_out_time,ordering,state)
		VALUES ('mod_fcp_instagram','module','mod_fcp_instagram','',0,1,1,0,'{\"name\":\"mod_fcp_instagram\",\"type\":\"module\",\"version\":\"1.0.0\"}','{}','',0,NULL,0,0)");
	echo "Registered mod_fcp_instagram\n";
} else {
	echo "mod_fcp_instagram already registered (#{$ext[0]})\n";
}

$sideParams = json_encode([
	'username'        => 'felizcompouco',
	'auto_latest'     => '1',
	'shortcodes'      => $shortcodes,
	'count'           => '6',
	'columns'         => '3',
	'image_size'      => 'm',
	'show_follow'     => '1',
	'follow_text'     => 'Siga no Instagram',
	'layout'          => '_:default',
	'moduleclass_sfx' => '',
	'cache'           => '0',
	'cache_time'      => '900',
	'module_tag'      => 'div',
	'bootstrap_size'  => '0',
	'header_tag'      => 'h3',
	'header_class'    => '',
	'style'           => '0',
], JSON_UNESCAPED_UNICODE);

// Sidebar: convert existing Instagram custom module
$side = q($db, "SELECT id FROM {$p}modules WHERE title='Instagram' AND position='right' LIMIT 1")->fetch_row();
if ($side) {
	q($db, "UPDATE {$p}modules SET module='mod_fcp_instagram', content='', showtitle=1, published=1, params=" . esc($db, $sideParams) . " WHERE id=" . (int) $side[0]);
	echo "Converted sidebar Instagram #{$side[0]}\n";
	$sideId = (int) $side[0];
} else {
	q($db, "INSERT INTO {$p}modules (title,note,content,ordering,position,checked_out,checked_out_time,publish_up,publish_down,published,module,access,showtitle,params,client_id,language)
		VALUES ('Instagram','','',2,'right',0,NULL,NULL,NULL,1,'mod_fcp_instagram',1,1," . esc($db, $sideParams) . ",0,'*')");
	$sideId = (int) $db->insert_id;
	echo "Created sidebar Instagram #$sideId\n";
}

$mm = q($db, "SELECT COUNT(*) FROM {$p}modules_menu WHERE moduleid=$sideId AND menuid=0")->fetch_row();
if (!(int) $mm[0]) {
	q($db, "INSERT INTO {$p}modules_menu (moduleid, menuid) VALUES ($sideId, 0)");
}

// Homepage "Segue no Instagram" (position5) — removido a pedido
$homes = q($db, "SELECT id FROM {$p}modules WHERE title='Segue no Instagram' OR (module='mod_fcp_instagram' AND position='position5')");
while ($home = $homes->fetch_row()) {
	$id = (int) $home[0];
	q($db, "DELETE FROM {$p}modules_menu WHERE moduleid=$id");
	q($db, "DELETE FROM {$p}modules WHERE id=$id");
	echo "Removed homepage Instagram #$id\n";
}

echo "Done.\n";
