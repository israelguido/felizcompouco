<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

/**
 * position5 / layout home = bloco "Segue no Instagram" no rodapé da home.
 * Removido a pedido — se ainda existir no banco (ex.: produção), não renderiza.
 */
$layoutRaw = (string) $params->get('layout', 'default');
$layout    = strtolower(basename(str_replace('_:','', $layoutRaw)));
$isHomeBlock = ($module->position === 'position5') || ($layout === 'home');

if ($isHomeBlock) {
	$module->showtitle = 0;
	return;
}

$username   = preg_replace('/[^a-zA-Z0-9._]/', '', (string) $params->get('username', 'felizcompouco'));
$profileUrl = 'https://www.instagram.com/' . $username . '/';
$columns    = max(2, min(6, (int) $params->get('columns', 3)));
$showFollow = (int) $params->get('show_follow', 1) === 1;
$followText = (string) $params->get('follow_text', 'Siga no Instagram');
$items      = ModFcpInstagramHelper::getItems($params);

// Sem fotos: não mostra título nem "Nenhuma foto configurada"
if (!$items) {
	$module->showtitle = 0;
	return;
}

$doc = Factory::getApplication()->getDocument();
$doc->addStyleSheet(Uri::root(true) . '/modules/mod_fcp_instagram/assets/css/fcp-instagram.css');

require ModuleHelper::getLayoutPath('mod_fcp_instagram', $params->get('layout', 'default'));
