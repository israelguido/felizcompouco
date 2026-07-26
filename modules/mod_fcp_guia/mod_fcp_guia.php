<?php
/**
 * @package  mod_fcp_guia
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$list = ModFcpGuiaHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_fcp_guia', $params->get('layout', 'default'));
