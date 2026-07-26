<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \FelizComPouco\Component\Fcpguia\Administrator\View\Item\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_fcp_guia&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" aria-label="<?php echo Text::_('COM_FCP_GUIA_ITEM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
	class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_FCP_GUIA_FIELDSET_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $this->form->renderField('image'); ?>
				<?php echo $this->form->renderField('image_alt'); ?>
				<?php echo $this->form->renderField('url'); ?>
				<?php echo $this->form->renderField('introtext'); ?>
			</div>
			<div class="col-lg-3">
				<?php echo $this->form->renderField('state'); ?>
				<?php echo $this->form->renderField('ordering'); ?>
				<?php echo $this->form->renderField('created'); ?>
				<?php echo $this->form->renderField('created_by'); ?>
				<?php echo $this->form->renderField('id'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
