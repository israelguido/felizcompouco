<?php
/**
 * Remove o bloco "Segue no Instagram" (position5) do banco.
 * Rode em PRODUÇÃO (rsync não altera o DB):
 *   php migration/remove_instagram_home.php
 * ou via warden:
 *   warden env exec php-fpm php /var/www/html/migration/remove_instagram_home.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("Forbidden: CLI only\n");
}

// Bootstrap mínimo via configuration.php
$root = dirname(__DIR__);
if (!is_file($root . '/configuration.php')) {
	fwrite(STDERR, "configuration.php não encontrado\n");
	exit(1);
}

require $root . '/configuration.php';
$c = new JConfig();

$db = @new mysqli($c->host, $c->user, $c->password, $c->db);
if ($db->connect_error) {
	fwrite(STDERR, "DB: {$db->connect_error}\n");
	exit(1);
}
$db->set_charset('utf8mb4');
$p = $c->dbprefix;

$sql = "SELECT id, title, position, published FROM {$p}modules
	WHERE module='mod_fcp_instagram'
	  AND (position='position5' OR title LIKE '%Segue no Instagram%' OR params LIKE '%\"layout\":\"_:home\"%' OR params LIKE '%\"layout\":\"home\"%')";
$r = $db->query($sql);
if (!$r) {
	fwrite(STDERR, $db->error . "\n");
	exit(1);
}

$n = 0;
while ($row = $r->fetch_assoc()) {
	$id = (int) $row['id'];
	$db->query("DELETE FROM {$p}modules_menu WHERE moduleid=$id");
	$db->query("DELETE FROM {$p}modules WHERE id=$id");
	echo "Removed #{$id} {$row['title']} pos={$row['position']} pub={$row['published']}\n";
	$n++;
}

if ($n === 0) {
	echo "Nenhum módulo de Instagram na home encontrado.\n";
} else {
	echo "Done. Removidos: $n\n";
}

// Limpa cache de arquivos se existir
foreach ([$root . '/cache', $root . '/administrator/cache'] as $dir) {
	if (!is_dir($dir)) {
		continue;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $f) {
		if ($f->isFile() && $f->getFilename() !== 'index.html') {
			@unlink($f->getPathname());
		}
	}
}
echo "Cache limpo.\n";
