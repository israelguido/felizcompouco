<?php
/**
 * @package SJ Contact Ajax
 * @version 1.0.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once dirname(__FILE__) . '/core/helper.php';
$file = 'https://www.google.com/recaptcha/api.js?onload=JoomlaInitReCaptcha2&render=explicit&hl=' . JFactory::getLanguage()->getTag();
JHtml::_('script', $file);
JHtml::_('script', 'plg_captcha_recaptcha/recaptcha.min.js', false, true);
			
if (!class_exists('plgSystemPlg_Sj_Contact_Ajax')) {
	echo JText::_('WARNING_NOT_INSTALL_PLUGIN');
	return;
}

$layout = $params->get('layout', 'default');
$cacheid = md5(serialize(array($layout, $module->id)));
$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = 'ContactAjax';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = $cacheid;
$list = ContactAjax::getList($params) ;

$captcha_type = $params->get('captcha_type');
$captcha_dis = $params->get('captcha_dis');
$captcha_disable = $params->get('captcha_disable');

if ($captcha_dis == 1) {
	if ($captcha_type == 0) {
		$captcha_plg = JPluginHelper::importPlugin('captcha');
		if ($captcha_plg == null) {
			echo JText::_('WARNING_NOT_INSTALL_PLUGIN_RECAPTCHA');
			return;
		}
	}
}

$user = JFactory::getUser();
$currentSession = JFactory::getSession();
if ($list != false) {
	$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	if ($is_ajax) {
		// Bloqueado: endpoint legado sem CSRF/rate limit (risco de spam/flood).
		header('Content-Type: application/json; charset=utf-8', true, 403);
		echo json_encode(['error' => 'Forbidden', 'message' => 'Contact AJAX desativado por segurança.']);
		jexit();
	} else {
		require JModuleHelper::getLayoutPath($module->module, $layout);
		require JModuleHelper::getLayoutPath($module->module, $layout . '_js');
	}
} else {
	echo JText::_('WARNING_MASSAGE');
}

