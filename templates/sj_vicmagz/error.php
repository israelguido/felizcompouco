<?php
/**
 * Página de erro 404 — Feliz com Pouco / VicMagz
 *
 * @package  templates.sj_vicmagz
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (defined('JPATH_PLUGINS') && is_file(JPATH_PLUGINS . '/system/j3legacy/j3legacy.php')) {
	require_once JPATH_PLUGINS . '/system/j3legacy/j3legacy.php';
	\PlgSystemJ3legacyBootstrap::register();
}

$app      = Factory::getApplication();
$doc      = Factory::getDocument();
$template = $app->getTemplate(true);
$params   = $template->params;
$base     = rtrim(Uri::root(true), '/');
$tplUri   = $base . '/templates/' . $this->template;
$homeUrl  = $base . '/';
$logoSrc  = $base . '/images/Logo/Logo2-2.png';
$code     = (int) $this->error->getCode();
$message  = $this->error->getMessage() ?: Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND');
$cssFile  = JPATH_THEMES . '/' . $this->template . '/css/error.css';
$cssVer   = is_file($cssFile) ? filemtime($cssFile) : time();

$year = Factory::getDate()->format('Y');
$copy = (string) $params->get('ytcopyright', '© {year} Feliz com Pouco');
$copy = str_replace('{year}', $year, $copy);

$this->language  = $doc->language ?: 'pt-BR';
$this->direction = $doc->direction ?: 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($this->language, ENT_QUOTES, 'UTF-8'); ?>" dir="<?php echo htmlspecialchars($this->direction, ENT_QUOTES, 'UTF-8'); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,follow">
	<title><?php echo (int) $code; ?> — <?php echo htmlspecialchars($this->title ?: 'Página não encontrada', ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo htmlspecialchars($tplUri . '/css/error.css?v=' . $cssVer, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="fcp-error fcp-error--<?php echo (int) $code; ?>">
	<div class="fcp-error__bg" aria-hidden="true"></div>

	<main class="fcp-error__main">
		<a class="fcp-error__brand" href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>">
			<img src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Feliz com Pouco" width="220" height="auto">
		</a>

		<p class="fcp-error__code" aria-hidden="true"><?php echo (int) $code; ?></p>

		<h1 class="fcp-error__title">Ops! Esta página se perdeu no caminho</h1>
		<p class="fcp-error__lead">
			<?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>.
			Que tal voltar para a home e descobrir inspirações do blog?
		</p>

		<div class="fcp-error__actions">
			<a class="fcp-error__btn fcp-error__btn--primary" href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>">
				Voltar para o início
			</a>
			<a class="fcp-error__btn fcp-error__btn--ghost" href="<?php echo htmlspecialchars($homeUrl . 'mundo-geek', ENT_QUOTES, 'UTF-8'); ?>">
				Ver Mundo Geek
			</a>
		</div>

		<p class="fcp-error__hint">♥ Feliz com pouco, com carinho e boas histórias ♥</p>
	</main>

	<footer class="fcp-error__footer">
		<?php echo htmlspecialchars(strip_tags($copy), ENT_QUOTES, 'UTF-8'); ?>
	</footer>
</body>
</html>
