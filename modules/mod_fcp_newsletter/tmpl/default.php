<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\Registry\Registry $params */
/** @var string $heading */
/** @var string $intro */
/** @var string $placeholder */
/** @var string $buttonText */
/** @var string $bgUrl */
/** @var int $overlay */
/** @var string $ajaxUrl */
/** @var string $captchaHtml */
/** @var object $module */

$modClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$formId   = 'fcp-newsletter-' . (int) $module->id;
$style    = '--fcp-nl-bg: url(\'' . htmlspecialchars($bgUrl, ENT_QUOTES, 'UTF-8') . '\');'
	. '--fcp-nl-overlay: ' . (int) $overlay . ';';
?>
<div class="fcp-newsletter fcp-newsletter--hero<?php echo $modClass ? ' ' . $modClass : ''; ?>"
	 style="<?php echo $style; ?>"
	 data-fcp-newsletter="1"
	 data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>">
	<div class="fcp-newsletter__inner">
		<?php if ($heading !== '') : ?>
			<p class="fcp-newsletter__eyebrow">Feliz com Pouco</p>
			<h3 class="fcp-newsletter__title"><?php echo htmlspecialchars($heading, ENT_QUOTES, 'UTF-8'); ?></h3>
		<?php endif; ?>

		<?php if ($intro !== '') : ?>
			<p class="fcp-newsletter__intro"><?php echo htmlspecialchars($intro, ENT_QUOTES, 'UTF-8'); ?></p>
		<?php endif; ?>

		<form id="<?php echo $formId; ?>" class="fcp-newsletter__form" method="post" action="#" novalidate>
			<div class="fcp-newsletter__row">
				<label class="visually-hidden" for="<?php echo $formId; ?>-email">E-mail</label>
				<input id="<?php echo $formId; ?>-email"
					   class="fcp-newsletter__email"
					   type="email"
					   name="email"
					   placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>"
					   required
					   autocomplete="email"
					   inputmode="email" />
				<button type="submit" class="fcp-newsletter__btn">
					<?php echo htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8'); ?>
				</button>
			</div>

			<label class="fcp-newsletter__hp" aria-hidden="true">
				<span>Website</span>
				<input type="text" name="website" tabindex="-1" autocomplete="off" />
			</label>

			<?php if ($captchaHtml !== '') : ?>
				<div class="fcp-newsletter__captcha-wrap">
					<?php echo $captchaHtml; ?>
				</div>
				<input type="hidden" name="captcha_plugin" value="<?php echo htmlspecialchars((string) $params->get('captcha', 'powcaptcha'), ENT_QUOTES, 'UTF-8'); ?>" />
			<?php endif; ?>

			<input type="hidden" name="source" value="home" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
