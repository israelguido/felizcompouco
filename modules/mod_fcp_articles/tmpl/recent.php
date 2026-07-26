<?php
defined('_JEXEC') or die;

$yt_temp = \Joomla\CMS\Factory::getApplication()->getTemplate();
@include JPATH_BASE . '/templates/' . $yt_temp . '/includes/placehold.php';

$showTitle = (int) $params->get('show_title', 1);
$showCat   = (int) $params->get('show_category', 1);
$titleLim  = (int) $params->get('title_limit', 25);
?>
<div id="k2ModuleBox<?php echo (int) $module->id; ?>" class="k2RecentPosts k2ItemsBlock<?php echo $params->get('moduleclass_sfx') ? ' ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8') : ''; ?>">
	<?php if (count($items)) : ?>
	<ul>
		<?php foreach ($items as $key => $item) : ?>
		<li class="item <?php echo ($key % 2) ? 'odd' : 'even'; ?><?php echo (count($items) == $key + 1) ? ' lastItem' : ''; ?>">
			<div class="media">
				<div class="moduleItemImageBlock pull-left">
					<a class="moduleItemImage" href="<?php echo $item->link; ?>">
						<?php if ($item->image) : ?>
							<?php echo ModFcpArticlesHelper::renderIntroImg($item); ?>
						<?php elseif (!empty($is_placehold)) : ?>
							<?php echo yt_placehold($placehold_size['xsmall'] ?? '90x62', $item->title, $item->image_alt ?: $item->title); ?>
						<?php endif; ?>
					</a>
				</div>
				<div class="media-body">
					<div class="moduleItemIntrotext">
						<?php if ($showCat) : ?>
							<div><a class="moduleItemCategory" href="<?php echo $item->categoryLink; ?>"><?php echo htmlspecialchars($item->categoryname, ENT_QUOTES, 'UTF-8'); ?></a></div>
						<?php endif; ?>
						<?php if ($showTitle) : ?>
							<h4><a class="moduleItemTitle" href="<?php echo $item->link; ?>"><?php echo htmlspecialchars(ModFcpArticlesHelper::truncate($item->title, $titleLim), ENT_QUOTES, 'UTF-8'); ?></a></h4>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</div>
