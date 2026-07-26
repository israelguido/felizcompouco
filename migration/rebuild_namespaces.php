<?php
/**
 * Regenera o mapa PSR-4 do Joomla (obrigatório após deploy de componentes novos).
 *
 *   php migration/rebuild_namespaces.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("Forbidden: CLI only\n");
}

$root = dirname(__DIR__);
if (!is_file($root . '/includes/defines.php')) {
	fwrite(STDERR, "Joomla root inválido: $root\n");
	exit(1);
}

define('_JEXEC', 1);
define('JPATH_BASE', $root);

require JPATH_BASE . '/includes/defines.php';
require JPATH_LIBRARIES . '/vendor/autoload.php';
require JPATH_LIBRARIES . '/namespacemap.php';

$map = new JNamespacePsr4Map();
$ok  = $map->create();

echo $ok ? "Namespace map OK\n" : "Namespace map FAIL\n";

// Sanity: guia extension file
$guia = JPATH_ADMINISTRATOR . '/components/com_fcp_guia/src/Extension/GuiaComponent.php';
echo is_file($guia) ? "GuiaComponent.php presente\n" : "ERRO: GuiaComponent.php ausente\n";

$autoload = JPATH_ADMINISTRATOR . '/cache/autoload_psr4.php';
if (is_file($autoload)) {
	$contents = file_get_contents($autoload);
	echo (strpos($contents, 'Fcp_guia') !== false)
		? "autoload_psr4 contém Fcp_guia\n"
		: "AVISO: autoload_psr4 sem Fcp_guia — delete administrator/cache/* e rode de novo\n";
} else {
	echo "AVISO: $autoload ainda não existe (será criado no próximo create())\n";
}

exit($ok ? 0 : 1);
