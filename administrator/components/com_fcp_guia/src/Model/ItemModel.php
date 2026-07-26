<?php
namespace FelizComPouco\Component\Fcp_guia\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;

class ItemModel extends AdminModel
{
	public $typeAlias = 'com_fcp_guia.item';

	public function getTable($type = 'Item', $prefix = 'Administrator', $config = [])
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_fcp_guia.item', 'item', [
			'control'   => 'jform',
			'load_data' => $loadData,
		]);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_fcp_guia.edit.item.data', []);

		if (empty($data)) {
			$data = $this->getItem();
		}

		$this->preprocessData('com_fcp_guia.item', $data);

		return $data;
	}

	protected function prepareTable($table)
	{
		$table->title = htmlspecialchars_decode((string) $table->title, ENT_QUOTES);
	}

	protected function canDelete($record)
	{
		if (empty($record->id)) {
			return false;
		}

		return Factory::getApplication()->getIdentity()->authorise('core.delete', $this->option);
	}
}
