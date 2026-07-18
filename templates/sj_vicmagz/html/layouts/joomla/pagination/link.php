<?php

/**
 * VicMagz pagination link — ícones Font Awesome + classes do tema
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$item    = $displayData['data'];
$display = $item->text;
$app     = Factory::getApplication();
$rtl     = $app->getLanguage()->isRtl();

switch ((string) $item->text) {
	case Text::_('JLIB_HTML_START'):
		$icon = $rtl ? 'fa-angle-double-right' : 'fa-angle-double-left';
		$aria = Text::_('JLIB_HTML_GOTO_POSITION_START');
		break;
	case Text::_('JPREV'):
		$icon = $rtl ? 'fa-angle-right' : 'fa-angle-left';
		$aria = Text::_('JLIB_HTML_GOTO_POSITION_PREVIOUS');
		break;
	case Text::_('JNEXT'):
		$icon = $rtl ? 'fa-angle-left' : 'fa-angle-right';
		$aria = Text::_('JLIB_HTML_GOTO_POSITION_NEXT');
		break;
	case Text::_('JLIB_HTML_END'):
		$icon = $rtl ? 'fa-angle-double-left' : 'fa-angle-double-right';
		$aria = Text::_('JLIB_HTML_GOTO_POSITION_END');
		break;
	default:
		$icon = null;
		$aria = Text::sprintf('JLIB_HTML_GOTO_PAGE', strtolower($item->text));
		break;
}

if ($icon !== null) {
	$display = '<i class="fa ' . $icon . '" aria-hidden="true"></i>';
}

$link  = '';
$class = 'disabled';

if ($displayData['active']) {
	$class = '';
	if ($item->base > 0) {
		$limit = 'limitstart.value=' . $item->base;
	} else {
		$limit = 'limitstart.value=0';
	}

	if ($app->isClient('administrator')) {
		$link = 'href="#" onclick="document.adminForm.' . $item->prefix . $limit . '; Joomla.submitform();return false;"';
	} else {
		$link = 'href="' . $item->link . '"';
	}
} elseif (property_exists($item, 'active') && $item->active) {
	$class = 'active';
}
?>
<?php if ($displayData['active']) : ?>
	<li class="<?php echo $class; ?>">
		<a aria-label="<?php echo $aria; ?>" <?php echo $link; ?>><?php echo $display; ?></a>
	</li>
<?php elseif (isset($item->active) && $item->active) : ?>
	<?php $aria = Text::sprintf('JLIB_HTML_PAGE_CURRENT', strtolower($item->text)); ?>
	<li class="active">
		<span aria-current="true" aria-label="<?php echo $aria; ?>"><?php echo $display; ?></span>
	</li>
<?php else : ?>
	<li class="disabled">
		<span aria-hidden="true"><?php echo $display; ?></span>
	</li>
<?php endif; ?>
