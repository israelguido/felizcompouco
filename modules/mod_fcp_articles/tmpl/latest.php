<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$yt_temp = \Joomla\CMS\Factory::getApplication()->getTemplate();
@include JPATH_BASE . '/templates/' . $yt_temp . '/includes/placehold.php';

$showTitle = (int) $params->get('show_title', 1);
$showCat   = (int) $params->get('show_category', 1);
$showDate  = (int) $params->get('show_date', 1);
$showIntro = (int) $params->get('show_intro', 1);
$titleLim  = (int) $params->get('title_limit', 60);
?>
<div id="k2ModuleBox<?php echo (int) $module->id; ?>" class="grid latestNews k2ItemsBlock<?php echo $params->get('moduleclass_sfx') ? ' ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8') : ''; ?>">
	<?php if ($params->get('pretext')) : ?>
		<div class="modulePretext before-top"><span><?php echo $params->get('pretext'); ?></span></div>
	<?php endif; ?>

	<?php if (count($items)) : ?>
	<div class="items row">
		<?php foreach ($items as $key => $item) : ?>
			<div class="col-sm-<?php echo (($key + 1) % 3 == 0) ? '12 item-full' : '6'; ?> item<?php echo (count($items) == $key + 1) ? ' lastItem' : ''; ?>">
				<?php if ($item->image || !empty($is_placehold)) : ?>
				<div class="moduleItemImageBlock">
					<a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
						<?php if ($item->image) : ?>
							<?php echo ModFcpArticlesHelper::renderIntroImg($item); ?>
						<?php else : ?>
							<?php echo yt_placehold($placehold_size['large'] ?? '770x540', $item->title, $item->image_alt ?: $item->title); ?>
						<?php endif; ?>
					</a>
				</div>
				<?php endif; ?>
				<div class="main">
					<div class="main-inner">
						<div class="moduleItemHeader">
							<?php if ($showDate) : ?>
								<span class="itemDateCreated">
									<span><?php echo str_replace(' ', '</span><span>', ModFcpArticlesHelper::formatDate($item->created, 'd M')); ?></span>
								</span>
							<?php endif; ?>
							<?php if ($showCat) : ?>
								<div class="itemCategory">
									<a href="<?php echo $item->categoryLink; ?>"><?php echo htmlspecialchars($item->categoryname, ENT_QUOTES, 'UTF-8'); ?></a>
									<div class="arow-before"></div>
									<div class="arow-after"></div>
								</div>
							<?php endif; ?>
							<?php if ($showTitle) : ?>
								<h3 class="moduleItemTitle"><a href="<?php echo $item->link; ?>"><?php echo htmlspecialchars(ModFcpArticlesHelper::truncate($item->title, $titleLim), ENT_QUOTES, 'UTF-8'); ?></a></h3>
							<?php endif; ?>
						</div>
						<?php if ($showIntro) : ?>
							<div class="introtext"><?php echo $item->displayIntrotext; ?></div>
						<?php endif; ?>
						<a class="moduleItemReadMore" href="<?php echo $item->link; ?>"><?php echo Text::_('COM_CONTENT_READ_MORE'); ?></a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ((int) $params->get('custom_link', 0)) : ?>
		<div class="before-bottom">
			<div class="moduleCustomLink">
				<a class="btn btn-border" href="<?php echo htmlspecialchars($params->get('custom_link_url', '/'), ENT_QUOTES, 'UTF-8'); ?>">
					<?php echo htmlspecialchars($params->get('custom_link_title', 'Veja mais'), ENT_QUOTES, 'UTF-8'); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
</div>
