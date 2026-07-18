<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (is_file(JPATH_SITE . '/components/com_users/helpers/route.php')) { require_once JPATH_SITE . '/components/com_users/helpers/route.php'; }

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
?>
<div class="login-form-wrapper">
<a href="#" class="dropdown-toggle" data-toggle="modal" data-target="#myModal<?php echo $module->id; ?>"><i class="fa fa-user"></i></a>

<!-- Modal -->

<div class="modal fade" id="myModal<?php echo $module->id; ?>" role="dialog">
    <div class="modal-dialog modal-md">
    
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><i class="fa fa-times-circle"></i></button>
				<h3 class="title"><span class="fa fa-lock"> </span><?php echo JText::_('MOD_LOGIN_TITLE'); ?>  </h3>
			</div>
			<form action="<?php echo JRoute::_(htmlspecialchars(JUri::getInstance()->toString()), true, $params->get('usesecure')); ?>" method="post" id="login-form" class="">
				 
				<?php if ($params->get('pretext')) : ?>
					<div class="pretext">
						<p><?php echo $params->get('pretext'); ?></p>
					</div>
				<?php endif; ?>
				<div class="userdata modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div id="form-login-username" class="form-group">
								<div class="input-group">
									<?php if (!$params->get('usetext')) : ?>
										<div class="input-group-addon"><i class="fa fa-user"></i></div>
										<input id="modlgn-username" type="text" name="username" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>" />
									<?php else: ?>
										<label for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
										<input id="modlgn-username" type="text" name="username" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>" />
									<?php endif; ?>
								</div>
							</div>
							<div id="form-login-password" class="form-group">
								<div class="input-group">
									<?php if (!$params->get('usetext')) : ?>
										<div class="input-group-addon"><i class="fa fa-key"></i></div>
										<input id="modlgn-passwd" type="password" name="password" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
									<?php else: ?>
										<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
										<input id="modlgn-passwd" type="password" name="password" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
									<?php endif; ?>
								</div>
							</div>
							<?php if (count($twofactormethods) > 1): ?>
							<div id="form-login-secretkey" class="control-group">
								<div class="input-group">
									<?php if (!$params->get('usetext')) : ?>
										<label for="modlgn-secretkey" class="element-invisible">
											<span class="fa fa-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></span>
											<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
										</label>

										<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
										<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
											<span class="fa fa-help"></span>
										</span>
									<?php else: ?>
										<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
										<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
										<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
											<span class="fa fa-help"></span>
										</span>
									<?php endif; ?>

								</div>
							</div>
							<?php endif; ?>
							
							<!--<div id="form-login-remember" class="control-group checkbox">
								<label for="modlgn-remember" class="control-label">
								<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
								<?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?>
								</label> 
							</div>-->
							<ul class="unstyled">
									<li>
										<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset&Itemid=' . UsersHelperRoute::getResetRoute()); ?>">
										<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
									</li>
								</ul>
							
							<input type="hidden" name="option" value="com_users" />
							<input type="hidden" name="task" value="user.login" />
							<input type="hidden" name="return" value="<?php echo $return; ?>" />
							<?php echo JHtml::_('form.token'); ?>
							
							<div id="form-login-submit" class="control-group">
								<div class="input-group">
									<button type="submit" tabindex="0" name="Submit" class="btn-block">
										<span class="fa fa-power-off"></span> 
										<?php echo JText::_('JLOGIN') ?>
									</button>
								</div>
							</div>
							
						</div>
						<div class="col-sm-6">
							<div class="content-creat">
								<h3 class="title-new"><?php echo JText::_('JLOGIN_NEW_HERE') ?></h3>
								<span class="des"><?php echo JText::_('JLOGIN_DES') ?></span>
								<ul class="list">
									<li><?php echo JText::_('JLOGIN_LIST1') ?></li>
									<li><?php echo JText::_('JLOGIN_LIST2') ?></li>
									<li><?php echo JText::_('JLOGIN_LIST3') ?></li>
								</ul>
								
								<?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
									
								<?php if ($usersConfig->get('allowUserRegistration')) : ?>
								<div class="allowUserRegistration">
									<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration&Itemid=' . UsersHelperRoute::getRegistrationRoute()); ?>">
										<?php echo JText::_('MOD_LOGIN_REGISTER'); ?> <!--<span class="fa fa-arrow-right"></span>-->
									</a>
								</div>
								<?php endif; ?>
								
							</div>
						</div>
					</div>
				</div>
				
				<!--<div class="modal-footer">
					<button class="" data-dismiss="modal" type="submit">
						<span class="fa fa-remove"></span>Cancel
					</button>
					
				</div>-->
				<?php if ($params->get('posttext')) : ?>
					<div class="posttext">
						<p><?php echo $params->get('posttext'); ?></p>
					</div>
				<?php endif; ?>
			</form>
		</div>
    </div>
</div>
</div>

