<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<div class="login-form-wrapper">
	<a href="#" class="dropdown-toggle"><i class="fa fa-user"></i></a>
	<div class="dropdown-toggle-content">
		<form action="<?php echo JRoute::_(htmlspecialchars(JUri::getInstance()->toString()), true, $params->get('usesecure')); ?>" method="post" id="login-form" class="form-vertical">
			<?php if ($params->get('greeting')) : ?>
			<div class="login-greeting">
			<?php if ($params->get('name') == 0) : {
				echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
			} else : {
				echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
			} endif; ?>
			</div>
			<?php endif; ?>
			<div class="logout-button">
				<input type="submit" name="Submit" class="btReverse" value="<?php echo JText::_('JLOGOUT'); ?>" />
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.logout" />
				<input type="hidden" name="return" value="<?php echo $return; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.login-form-wrapper .dropdown-toggle-content').slideToggle("show");
	$(".login-form-wrapper .dropdown-toggle").on('click', function() {
		$('.login-form-wrapper .dropdown-toggle-content').slideToggle("show");
	});
});
</script>
