<?php
/**
 * @package Sj Newletter Popup
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;
/*-- Process---*/
$layout = $params->get('layout', 'default');
$intro = $params->get('intro_text', '');
$footer = $params->get('footer_text', '');
$subject = $params->get('email_template_subject');
$content_email = $params->get('content_email');
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if ($is_ajax && isset($_POST['is_newletter']) && $_POST['is_newletter']) {
	// Bloqueado: endpoint antigo era open mail relay (sem CSRF/captcha/rate limit).
	// Use mod_fcp_newsletter no lugar deste módulo.
	header('Content-Type: text/plain; charset=utf-8', true, 403);
	echo 'Forbidden';
	jexit();
}
require JModuleHelper::getLayoutPath($module->module, $layout);

?>
