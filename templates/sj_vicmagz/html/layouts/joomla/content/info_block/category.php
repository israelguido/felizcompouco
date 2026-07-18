<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
			<div class="category-name">
				<?php $title = $this->escape($displayData['item']->category_title); ?>
				<?php
				$item = $displayData['item'];
				$catslug = !empty($item->catslug) ? $item->catslug : ($item->catid . (!empty($item->category_alias) ? ':' . $item->category_alias : ''));
				?>
				<?php if ($displayData['params']->get('link_category') && $catslug) : ?>
					<?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($catslug)) . '" itemprop="genre">' . $title . '</a>'; ?>
					<?php //echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
					<?php echo $url; ?>
				<?php else : ?>
					<?php //echo JText::sprintf('COM_CONTENT_CATEGORY', '<span itemprop="genre">' . $title . '</span>'); ?>
					<?php echo '<span itemprop="genre">' . $title . '</span>'; ?>
				<?php endif; ?>
			</div>