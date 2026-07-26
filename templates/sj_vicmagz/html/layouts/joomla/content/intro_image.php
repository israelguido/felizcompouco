<?php
/**
 * Intro image — usa Imagem da Introdução + alt + classe CSS do artigo.
 *
 * Não usa YTTemplateUtils::resize: o resizer do template gera JPEGs pretos
 * (background #000 + falha no processamento) e os reutiliza do cache.
 *
 * @package     Joomla.Site
 * @subpackage  Layout
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

// includes placehold
$yt_temp = Factory::getApplication()->getTemplate();
include JPATH_BASE . '/templates/' . $yt_temp . '/includes/placehold.php';

$params = $displayData->params;
$images = json_decode($displayData->images ?: '{}');

if (!is_object($images) || empty($images->image_intro)) {
	return;
}

// Classe CSS do artigo (campo "Classe da imagem" = float_intro no J6)
$imgClass = trim((string) ($images->float_intro ?? $images->float_into ?? ''));
if ($imgClass === '' || in_array($imgClass, ['left', 'right', 'none'], true)) {
	$float = $imgClass !== '' ? $imgClass : (string) $params->get('float_intro', '');
	$figureClass = trim(($float !== '' ? 'pull-' . $float . ' ' : '') . 'item-image');
} else {
	$figureClass = trim($imgClass . ' item-image');
}

$alt = trim((string) ($images->image_intro_alt ?? ''));
if ($alt === '' && empty($images->image_intro_alt_empty)) {
	$alt = (string) $displayData->title;
}

$srcRaw  = (string) $images->image_intro;
$cleaned = HTMLHelper::_('cleanImageURL', $srcRaw);
$srcPath = is_object($cleaned) && !empty($cleaned->url) ? (string) $cleaned->url : strtok($srcRaw, '#');
$srcPath = trim((string) $srcPath);

if ($srcPath === '') {
	return;
}

// URL pública (relativa à raiz do site)
if (strpos($srcPath, 'http') !== 0 && strpos($srcPath, '//') !== 0) {
	$rel = ltrim(preg_replace('#^' . preg_quote(Uri::root(true), '#') . '/#', '', $srcPath), '/');
	$localOk = $rel !== '' && is_file(JPATH_BASE . '/' . $rel);
	$imgsrc  = Uri::root(true) . '/' . $rel;
} else {
	$localOk = true;
	$imgsrc  = $srcPath;
}

$thumb_img = '';

if ($localOk || (is_object($cleaned) && !empty($cleaned->url))) {
	$layoutAttr = [
		'src' => $imgsrc,
		'alt' => $alt,
	];
	if ($imgClass !== '' && !in_array($imgClass, ['left', 'right', 'none'], true)) {
		$layoutAttr['class'] = $imgClass;
	}
	$thumb_img = LayoutHelper::render('joomla.html.image', $layoutAttr);
} elseif (!empty($is_placehold)) {
	$thumb_img = yt_placehold($placehold_size['small'] ?? '200x150', $alt, $alt);
}

if ($thumb_img === '') {
	return;
}
?>
<figure class="<?php echo htmlspecialchars($figureClass, ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
		<a href="<?php echo Route::_(RouteHelper::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language ?? 0)); ?>" title="<?php echo htmlspecialchars($displayData->title, ENT_QUOTES, 'UTF-8'); ?>">
			<?php echo $thumb_img; ?>
		</a>
	<?php else : ?>
		<?php echo $thumb_img; ?>
	<?php endif; ?>
	<?php if (!empty($images->image_intro_caption)) : ?>
		<figcaption class="caption"><?php echo htmlspecialchars($images->image_intro_caption, ENT_QUOTES, 'UTF-8'); ?></figcaption>
	<?php endif; ?>
</figure>
