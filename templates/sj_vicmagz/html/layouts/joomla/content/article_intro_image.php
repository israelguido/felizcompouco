<?php
/**
 * Imagem da Introdução em tamanho de artigo (primeira imagem do post).
 *
 * @package     Joomla.Site
 * @subpackage  Layout
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$yt_temp = Factory::getApplication()->getTemplate();
include JPATH_BASE . '/templates/' . $yt_temp . '/includes/placehold.php';

$params = $displayData->params ?? null;
$images = json_decode($displayData->images ?: '{}');

if (!is_object($images) || empty($images->image_intro)) {
	return;
}

$imgClass = trim((string) ($images->float_intro ?? $images->float_into ?? ''));
if ($imgClass === '' || in_array($imgClass, ['left', 'right', 'none'], true)) {
	$float = $imgClass !== '' ? $imgClass : (string) ($params ? $params->get('float_intro', '') : '');
	$figureClass = trim('img-intro img-fulltext' . ($float !== '' ? ' pull-' . $float : ''));
} else {
	$figureClass = trim($imgClass . ' img-intro img-fulltext');
}

$alt = trim((string) ($images->image_intro_alt ?? ''));
if ($alt === '' && empty($images->image_intro_alt_empty)) {
	$alt = (string) ($displayData->title ?? '');
}

$srcRaw  = (string) $images->image_intro;
$cleaned = HTMLHelper::_('cleanImageURL', $srcRaw);
$srcPath = is_object($cleaned) && !empty($cleaned->url) ? (string) $cleaned->url : strtok($srcRaw, '#');
$srcPath = ltrim((string) $srcPath, '/');

$localOk = is_file(JPATH_BASE . '/' . $srcPath) || strpos($srcPath, 'http') === 0 || strpos($srcPath, '//') === 0;
$thumb_img = '';

if ($localOk || (is_object($cleaned) && !empty($cleaned->url))) {
	$cleanedUrl = is_object($cleaned) && !empty($cleaned->url) ? (string) $cleaned->url : $srcPath;

	$layoutAttr = [
		'src'     => $cleanedUrl,
		'alt'     => $alt,
		'loading' => 'eager',
	];
	if ($imgClass !== '' && !in_array($imgClass, ['left', 'right', 'none'], true)) {
		$layoutAttr['class'] = $imgClass;
	}
	$thumb_img = LayoutHelper::render('joomla.html.image', $layoutAttr);
} elseif (!empty($is_placehold)) {
	$thumb_img = yt_placehold($placehold_size['article'] ?? '870x450', $alt, $alt);
}

if ($thumb_img === '') {
	return;
}
?>
<figure class="<?php echo htmlspecialchars($figureClass, ENT_QUOTES, 'UTF-8'); ?>">
	<?php echo $thumb_img; ?>
	<?php if (!empty($images->image_intro_caption)) : ?>
		<figcaption class="caption"><?php echo htmlspecialchars($images->image_intro_caption, ENT_QUOTES, 'UTF-8'); ?></figcaption>
	<?php endif; ?>
</figure>
