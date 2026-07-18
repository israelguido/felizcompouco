<?php
defined('_JEXEC') or die;

/** Homepage spotlight — force wider grid */
if ((int) $params->get('columns', 6) < 3) {
	$params->set('columns', 6);
}
require __DIR__ . '/default.php';
