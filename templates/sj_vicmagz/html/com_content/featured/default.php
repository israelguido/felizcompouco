<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
global $leadingFlag;
?>
	
   
<?php 
	
	
// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<div class="blog blog-featured<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading') != 0) : ?>
	<h2 class="heading-category"> 				
		<span class="subheading-category"><?php echo $this->escape($this->params->get('page_heading')); ?></span>
	</h2>
<?php endif; ?>


<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination-warpper pagination-top">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<div class="counter pull-left"> <?php echo $this->pagination->getPagesCounter(); ?> </div>
		<?php endif; ?>
		<div class="pull-right"><?php echo $this->pagination->getPagesLinks(); ?> </div></div>
	<?php  endif; ?>
<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
<?php
	if (empty($this->columns) || (int) $this->columns < 1) {
		$this->columns = max(1, (int) $this->params->get('num_columns', 1));
	}
	$columns = max(1, (int) $this->columns);
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
</div>
	<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
<?php
	$introcount = count($this->intro_items);
	$counter = 0;
	$columns = max(1, (int) $this->columns);
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
			<div class="item <?php echo $item->state == 0 ? ' system-unpublished' : null; ?> col-sm-<?php echo $introColSpan; ?>">
			<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
			?>
			</div>
			<?php $counter++; ?>
			<?php if ($rowcount === $columns || $counter === $introcount) : ?>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<div class="items-more">
	<?php echo $this->loadTemplate('links'); ?>
	</div>
<?php endif; ?>

<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination-warpper pagination-bottom">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<div class="counter pull-left"> <?php echo $this->pagination->getPagesCounter(); ?> </div>
		<?php endif; ?>
		<div class="pull-right"><?php echo $this->pagination->getPagesLinks(); ?> </div></div>
	<?php  endif; ?>

</div>
