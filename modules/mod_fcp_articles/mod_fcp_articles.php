<?php
/**
 * @package  mod_fcp_articles
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$layout = $params->get('layout', 'popular');
// Normalize layout path (admin may store _:popular)
$layout = str_replace(['_:', 'sj_vicmagz:'], '', (string) $layout);
$items  = ModFcpArticlesHelper::getList($params);
$list   = $items; // alias for slideshow/trending templates

require ModuleHelper::getLayoutPath('mod_fcp_articles', $layout);
