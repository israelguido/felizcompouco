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
			<div class="create">
					<!--<span class="icon-calendar"></span> -->
					<time datetime="<?php echo JHtml::_('date', $displayData['item']->created, 'c'); ?>" itemprop="dateCreated">
						<?php //echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $displayData['item']->created, JText::_('DATE_FORMAT_LC3'))); ?>
						<?php //echo JText::sprintf(JHtml::_('date', $displayData['item']->created, JText::_('DATE_FORMAT_LC2'))); ?>
						<span><?php echo str_replace(' ', '</span><span>', JHtml::_('date', $displayData['item']->created, JText::_('d M')));?></span>
					</time>
			</div>