<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var object[] $items */
/** @var string $username */
/** @var string $profileUrl */
/** @var int $columns */
/** @var bool $showFollow */
/** @var string $followText */
/** @var object $module */
/** @var \Joomla\Registry\Registry $params */

$modClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
?>
<div class="fcp-ig fcp-ig--cols-<?php echo (int) $columns; ?><?php echo $modClass ? ' ' . $modClass : ''; ?>">
	<?php if ($items) : ?>
		<ul class="fcp-ig__grid">
			<?php foreach ($items as $item) : ?>
				<li class="fcp-ig__item">
					<a class="fcp-ig__link" href="<?php echo htmlspecialchars($item->permalink, ENT_QUOTES, 'UTF-8'); ?>"
						target="_blank" rel="noopener noreferrer"
						title="@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
						<img class="fcp-ig__img" src="<?php echo htmlspecialchars($item->thumb, ENT_QUOTES, 'UTF-8'); ?>"
							alt="@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
							loading="lazy" width="320" height="320" />
						<span class="fcp-ig__hover"><i class="fa fa-instagram" aria-hidden="true"></i></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ($showFollow) : ?>
		<a class="fcp-ig__follow" href="<?php echo htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>"
			target="_blank" rel="noopener noreferrer">
			<i class="fa fa-instagram" aria-hidden="true"></i>
			<span>@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
			<em><?php echo htmlspecialchars($followText, ENT_QUOTES, 'UTF-8'); ?></em>
		</a>
	<?php endif; ?>
</div>
