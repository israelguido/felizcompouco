<?php
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$doc = \Joomla\CMS\Factory::getDocument();
$doc->addStyleSheet(Uri::root(true) . '/modules/mod_fcp_articles/assets/css/mostviewed.css');

$showTitle = (int) $params->get('show_title', 1);
$showCat   = (int) $params->get('show_category', 1);
$titleLim  = (int) $params->get('title_limit', 50);
$tag_id    = 'sj_mostviewed_' . (int) $module->id;
?>
<div class="sj-mostviewed" id="<?php echo $tag_id; ?>">
	<div class="mv-wrap">
		<div class="mv-tabs-content">
			<div class="tab-selected-all selected">
				<div class="mv-tab-content">
					<?php foreach ($items as $i => $item) : ?>
						<?php if ($i === 0) : ?>
							<div class="tab-item-first">
								<?php if ($item->image) : ?>
									<a title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo $item->link; ?>">
										<img src="<?php echo htmlspecialchars($item->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" />
									</a>
								<?php endif; ?>
								<span class="count-item"><?php echo $i + 1; ?></span>
								<div class="content">
									<?php if ($showCat) : ?><span><?php echo htmlspecialchars($item->categoryname, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
									<?php if ($showTitle) : ?>
										<h3><a href="<?php echo $item->link; ?>"><?php echo htmlspecialchars(ModFcpArticlesHelper::truncate($item->title, $titleLim), ENT_QUOTES, 'UTF-8'); ?></a></h3>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="tab-item">
								<div class="tab-content-left">
									<span class="count-item"><?php echo $i + 1; ?></span>
									<div class="tab-content-left-img">
										<?php if ($item->image) : ?>
											<a href="<?php echo $item->link; ?>">
												<img src="<?php echo htmlspecialchars($item->image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" />
											</a>
										<?php endif; ?>
									</div>
								</div>
								<div class="tab-content-right">
									<?php if ($showCat) : ?><span><?php echo htmlspecialchars($item->categoryname, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
									<?php if ($showTitle) : ?>
										<h3><a href="<?php echo $item->link; ?>"><?php echo htmlspecialchars(ModFcpArticlesHelper::truncate($item->title, $titleLim), ENT_QUOTES, 'UTF-8'); ?></a></h3>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>
