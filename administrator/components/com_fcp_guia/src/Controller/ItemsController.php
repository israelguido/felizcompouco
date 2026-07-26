<?php
namespace FelizComPouco\Component\Fcp_guia\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class ItemsController extends AdminController
{
	protected $text_prefix = 'COM_FCP_GUIA';

	public function getModel($name = 'Item', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
