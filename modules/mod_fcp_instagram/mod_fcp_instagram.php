<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$username   = preg_replace('/[^a-zA-Z0-9._]/', '', (string) $params->get('username', 'felizcompouco'));
$profileUrl = 'https://www.instagram.com/' . $username . '/';
$columns    = max(2, min(6, (int) $params->get('columns', 3)));
$showFollow = (int) $params->get('show_follow', 1) === 1;
$followText = (string) $params->get('follow_text', 'Siga no Instagram');
$items      = ModFcpInstagramHelper::getItems($params);

$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
// Fallback if WebAsset not registered — use stylesheet path
$doc = \Joomla\CMS\Factory::getDocument();
$doc->addStyleSheet(\Joomla\CMS\Uri\Uri::root(true) . '/modules/mod_fcp_instagram/assets/css/fcp-instagram.css');

require ModuleHelper::getLayoutPath('mod_fcp_instagram', $params->get('layout', 'default'));
