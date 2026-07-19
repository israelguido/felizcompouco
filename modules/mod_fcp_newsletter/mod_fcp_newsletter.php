<?php
defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

ModFcpNewsletterHelper::ensureTable();

$doc = Factory::getApplication()->getDocument();
$cssPath = JPATH_ROOT . '/modules/mod_fcp_newsletter/assets/css/fcp-newsletter.css';
$jsPath  = JPATH_ROOT . '/modules/mod_fcp_newsletter/assets/js/fcp-newsletter.js';
$cssVer  = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
$jsVer   = is_file($jsPath) ? (string) filemtime($jsPath) : '1';
$doc->addStyleSheet(Uri::root(true) . '/modules/mod_fcp_newsletter/assets/css/fcp-newsletter.css', ['version' => $cssVer]);
$doc->addScript(Uri::root(true) . '/modules/mod_fcp_newsletter/assets/js/fcp-newsletter.js', ['version' => $jsVer], ['defer' => true]);

$heading     = (string) $params->get('heading', $module->title ?: 'Newsletter');
$intro       = (string) $params->get('intro', 'Receba novidades, inspirações e ofertas no seu e-mail.');
$placeholder = (string) $params->get('placeholder', 'Seu e-mail');
$buttonText  = (string) $params->get('button_text', 'Assinar');
$overlay     = max(0, min(90, (int) $params->get('overlay', 45)));
$useCaptcha  = (int) $params->get('enable_captcha', 1) === 1;
$captchaPlugin = (string) $params->get('captcha', 'powcaptcha');
if ($captchaPlugin === '' || $captchaPlugin === '0') {
	$captchaPlugin = 'powcaptcha';
}

$bg = trim((string) $params->get('background', ''));
if ($bg === '') {
	$bg = 'templates/sj_vicmagz/images/bg/bg-newsletter.jpg';
}
$bg = ltrim(str_replace('\\', '/', $bg), '/');
$bgUrl = Uri::root() . $bg;

$ajaxUrl = Uri::root(true) . '/index.php?option=com_ajax&module=fcp_newsletter&method=subscribe&format=json';

$captchaHtml = '';
if ($useCaptcha) {
	try {
		PluginHelper::importPlugin('captcha');
		$captcha = Captcha::getInstance($captchaPlugin);
		$captchaHtml = $captcha->display('captcha', 'fcp-nl-captcha-' . (int) $module->id, 'fcp-newsletter__captcha');
	} catch (\Throwable $e) {
		$captchaHtml = '';
	}
}

// Título do chrome fica escondido pelo CSS (:has) — o heading vai dentro do hero
require ModuleHelper::getLayoutPath('mod_fcp_newsletter', $params->get('layout', 'default'));
