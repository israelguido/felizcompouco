<?php
namespace FelizComPouco\Component\Fcpnewsletter\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;

class SubscriberModel extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		return false;
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return false;
	}

	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$pks = array_filter(array_map('intval', $pks));

		if (!$pks) {
			return false;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__fcp_newsletter'))
			->whereIn($db->quoteName('id'), $pks, ParameterType::INTEGER);
		$db->setQuery($query)->execute();

		return true;
	}
}
