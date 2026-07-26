<?php
namespace FelizComPouco\Component\Fcpguia\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class ItemsModel extends ListModel
{
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'title', 'a.title',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created', 'a.created',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
	{
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('a.*')
			->from($db->quoteName('#__fcp_guia', 'a'));

		$search = $this->getState('filter.search');

		if ($search !== '' && $search !== null) {
			if (stripos($search, 'id:') === 0) {
				$id = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);
			} else {
				$search = '%' . trim($search) . '%';
				$query->where('(' . $db->quoteName('a.title') . ' LIKE :search OR ' . $db->quoteName('a.alias') . ' LIKE :search2)')
					->bind(':search', $search)
					->bind(':search2', $search);
			}
		}

		$state = $this->getState('filter.state');

		if ($state !== '' && $state !== null) {
			$state = (int) $state;
			$query->where($db->quoteName('a.state') . ' = :state')
				->bind(':state', $state, ParameterType::INTEGER);
		} else {
			$query->where($db->quoteName('a.state') . ' IN (0, 1)');
		}

		$orderCol  = $this->getState('list.ordering', 'a.ordering');
		$orderDirn = $this->getState('list.direction', 'ASC');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
