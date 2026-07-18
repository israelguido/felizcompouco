<?php
/**
 * VicMagz ytmod chrome for Joomla 4+
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

if ((string) $module->content === '') {
	return;
}

$title = str_replace(['(', ')'], ['<span class="stylecolortitle">', '</span>'], $module->title);
$badge = preg_match('/badge/', (string) $params->get('moduleclass_sfx')) ? "<span class=\"badge\"></span>\n" : '';
$scrollreveal = $params->get('header_class');

$icons = '';
$modclass_sfx = (string) $params->get('moduleclass_sfx');

if (strpos($modclass_sfx, 'fa-') !== false) {
	$modclass_sfx_parts = explode('fa-', $modclass_sfx);
	$arr = explode(' ', trim($modclass_sfx_parts[1] ?? ''));
	$fontName = $arr[0] ?? '';
	$modclass_sfx2 = str_replace('fa-' . $fontName, '', $modclass_sfx);
	$icons = "<i class='fa fa-" . htmlspecialchars($fontName, ENT_QUOTES, 'UTF-8') . "'></i>";
	$modclass_sfx = 'style-icon ' . $modclass_sfx2;
}
?>
<div class="module <?php echo htmlspecialchars($modclass_sfx, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($scrollreveal != '') ? 'data-sr="' . htmlspecialchars($scrollreveal, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
	<?php if ((bool) $module->showtitle) : ?>
		<h3 class="modtitle"><span class="styletitle"><?php echo $icons; ?><?php echo $title; ?></span><?php echo $badge; ?></h3>
	<?php endif; ?>
	<div class="modcontent clearfix">
		<?php echo $module->content; ?>
	</div>
</div>
