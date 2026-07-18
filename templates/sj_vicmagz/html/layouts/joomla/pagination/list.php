<?php

/**
 * VicMagz pagination list — markup compatível com CSS BS3 do tema
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$list = $displayData['list'];
?>
<nav class="pagination-wrap" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
	<ul class="pagination">
		<?php echo $list['start']['data']; ?>
		<?php echo $list['previous']['data']; ?>
		<?php foreach ($list['pages'] as $page) : ?>
			<?php echo $page['data']; ?>
		<?php endforeach; ?>
		<?php echo $list['next']['data']; ?>
		<?php echo $list['end']['data']; ?>
	</ul>
</nav>
