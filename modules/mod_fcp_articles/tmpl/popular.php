<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$yt_temp = \Joomla\CMS\Factory::getApplication()->getTemplate();
@include JPATH_BASE . '/templates/' . $yt_temp . '/includes/placehold.php';

$showTitle = (int) $params->get('show_title', 1);
$showCat   = (int) $params->get('show_category', 1);
$showIntro = (int) $params->get('show_intro', 1);
$titleLim  = (int) $params->get('title_limit', 40);
$count     = count($items);
?>
<div id="k2ModuleBox<?php echo (int) $module->id; ?>" class="itemsPopular k2ItemsBlock<?php echo $params->get('moduleclass_sfx') ? ' ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8') : ''; ?>">
	<?php if ($params->get('pretext')) : ?>
		<div class="modulePretext before-top"><span><?php echo $params->get('pretext'); ?></span></div>
	<?php endif; ?>

	<?php if ($count) : ?>
	<div class="row">
		<?php foreach ($items as $key => $item) : ?>
			<?php if ($count == 5 && $key == 0) : ?>
				<div class="col-sm-3">
			<?php elseif ($count != 5) : ?>
				<div class="col-sm-<?php echo $count < 5 ? (int) (12 / max(1, $count)) : 4; ?>">
			<?php endif; ?>
			<div class="item">
				<div class="moduleItemIntrotext">
					<div class="moduleItemImageBlock">
						<a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
							<?php if ($item->image) : ?>
								<img src="<?php echo htmlspecialchars($item->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" />
							<?php elseif (!empty($is_placehold)) : ?>
								<?php echo yt_placehold($placehold_size['medium'] ?? '570x400', $item->title, $item->title); ?>
							<?php endif; ?>
						</a>
					</div>
					<div class="main">
						<div class="main-inner">
							<?php if ($showCat) : ?>
								<div class="moduleItemCategory">
									<a class="bnt btn-color" href="<?php echo $item->categoryLink; ?>"><?php echo htmlspecialchars($item->categoryname, ENT_QUOTES, 'UTF-8'); ?></a>
								</div>
							<?php endif; ?>
							<?php if ($showTitle) : ?>
								<h3 class="moduleItemTitle"><a href="<?php echo $item->link; ?>"><?php echo htmlspecialchars(ModFcpArticlesHelper::truncate($item->title, $titleLim), ENT_QUOTES, 'UTF-8'); ?></a></h3>
							<?php endif; ?>
							<?php if ($showIntro) : ?>
								<div class="introtext"><?php echo $item->displayIntrotext; ?></div>
							<?php endif; ?>
							<?php if ($key === 2 || $count != 5) : ?>
								<a class="moduleItemReadMore btn btn-color" href="<?php echo $item->link; ?>"><?php echo Text::_('COM_CONTENT_READ_MORE'); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php if ($count == 5 && $key == 1) : ?>
				</div><div class="col-sm-6 center">
			<?php elseif ($count == 5 && $key == 2) : ?>
				</div><div class="col-sm-3">
			<?php elseif (($count == 5 && $key == 4) || $count != 5) : ?>
				</div>
			<?php endif; ?>
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
