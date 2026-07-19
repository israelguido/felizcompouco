<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \FelizComPouco\Component\Fcpnewsletter\Administrator\View\Subscribers\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_fcp_newsletter&view=subscribers'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="subscriberList">
						<caption class="visually-hidden"><?php echo Text::_('COM_FCP_NEWSLETTER_SUBSCRIBERS_TITLE'); ?></caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FCP_NEWSLETTER_EMAIL', 'a.email', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-15 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FCP_NEWSLETTER_CREATED', 'a.created', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.status', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FCP_NEWSLETTER_SOURCE', 'a.source', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->email); ?>
									</td>
									<th scope="row">
										<?php echo $this->escape($item->email); ?>
										<?php if (!empty($item->name)) : ?>
											<br><small class="text-muted"><?php echo $this->escape($item->name); ?></small>
										<?php endif; ?>
									</th>
									<td class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4') . ' H:i'); ?>
									</td>
									<td class="text-center">
										<?php if ((int) $item->status === 1) : ?>
											<span class="badge bg-success"><?php echo Text::_('COM_FCP_NEWSLETTER_STATUS_ACTIVE'); ?></span>
										<?php else : ?>
											<span class="badge bg-secondary"><?php echo Text::_('COM_FCP_NEWSLETTER_STATUS_INACTIVE'); ?></span>
										<?php endif; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $this->escape($item->source); ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo (int) $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
