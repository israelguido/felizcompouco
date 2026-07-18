<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
global $leadingFlag;
$doc = JFactory::getDocument();
$app = JFactory::getApplication();

// Joomla 4+/6: ensure columns is valid for Bootstrap grid math
if (empty($this->columns) || (int) $this->columns < 1) {
	$this->columns = max(1, (int) $this->params->get('num_columns', 1));
}
?>
   
<div class="blog<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
	<h2 class="heading-category"> <?php echo $this->escape($this->params->get('page_subheading')); ?>
		<?php if ($this->params->get('show_category_title')) : ?>
		<span class="subheading-category"><?php echo $this->category->title;?></span>
		<?php endif; ?>
	</h2>
	<?php endif; ?>
	
	<?php if ($this->params->get('show_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
		<?php $this->category->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
	<?php  endif; ?>
	
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
		<?php endif; ?>
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
		<?php endif; ?>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination-warpper pagination-top">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<div class="counter pull-left"> <?php echo $this->pagination->getPagesCounter(); ?> </div>
		<?php endif; ?>
		<div class="pull-right"><?php echo $this->pagination->getPagesLinks(); ?> </div></div>
	<?php  endif; ?>
	<?php
	$columns = max(1, (int) $this->columns);
	$leadingcount = 0;
	?>
	<?php if (!empty($this->lead_items)) : ?>
	<?php
		$leadTotal = count($this->lead_items);
		$leadColSpan = 12;
	?>
	<?php foreach ($this->lead_items as &$item) : ?>
	<?php
		$rowcount = ($leadingcount % $columns) + 1;
		$row = (int) floor($leadingcount / $columns);

		if ($rowcount === 1) :
			$itemsInRow = min($columns, $leadTotal - ($row * $columns));
			$leadColSpan = (int) round(12 / max(1, $itemsInRow));
	?>
	<div class="items-leading items-row cols-<?php echo (int) $itemsInRow; ?> <?php echo 'row-' . $row; ?> row">
	<?php endif; ?>
		<div class="item col-sm-<?php echo $leadColSpan; ?> leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
				$leadingFlag = 1;
				echo $this->loadTemplate('item');
				$leadingFlag = 0;
			?>
		</div>
	<?php
		$leadingcount++;
		if ($rowcount === $columns || $leadingcount === $leadTotal) :
	?>
	</div><!-- end items-leading row -->
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
	<?php
	$introcount = count($this->intro_items);
	$counter = 0;
	$introColSpan = (int) round(12 / $columns);
?>
	<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
	<?php
		$rowcount = (((int) $key) % $columns) + 1;
		$row = (int) floor((int) $key / $columns);

		if ($rowcount === 1) :
			$itemsInRow = min($columns, $introcount - ($row * $columns));
			$introColSpan = (int) round(12 / max(1, $itemsInRow));
	?>
		<div class="items-row cols-<?php echo (int) $itemsInRow; ?> <?php echo 'row-' . $row; ?> row">
		<?php endif; ?>
				<div class="item col-sm-<?php echo $introColSpan; ?> column-<?php echo $rowcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
					<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
				?>
				</div>
				<?php $counter++; ?>
			<?php if ($rowcount === $columns || $counter === $introcount) : ?>
		</div><!-- end row -->
			<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
	
	<?php if (!empty($this->link_items)) : ?>
	<div class="items-more">
	<?php echo $this->loadTemplate('links'); ?>
	</div>
	<?php endif; ?>
	<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
	<div class="cat-children">
		<h3> <?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
		<?php echo $this->loadTemplate('children'); ?> </div>
	<?php endif; ?>
	<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination-warpper pagination-bottom">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<div class="counter pull-left"> <?php echo $this->pagination->getPagesCounter(); ?> </div>
		<?php endif; ?>
		<div class="pull-right"><?php echo $this->pagination->getPagesLinks(); ?> </div></div>
	<?php  endif; ?>
</div>
