<?php
namespace FelizComPouco\Component\Fcpnewsletter\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class SubscribersModel extends ListModel
{
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'email', 'a.email',
				'created', 'a.created',
				'status', 'a.status',
				'source', 'a.source',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'a.created', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.status');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('a.*')
			->from($db->quoteName('#__fcp_newsletter', 'a'));

		$search = $this->getState('filter.search');

		if ($search !== '' && $search !== null) {
			$search = '%' . trim($search) . '%';
			$query->where($db->quoteName('a.email') . ' LIKE :search')
				->bind(':search', $search);
		}

		$status = $this->getState('filter.status', '');

		if ($status !== '' && $status !== null) {
			$status = (int) $status;
			$query->where($db->quoteName('a.status') . ' = :status')
				->bind(':status', $status, ParameterType::INTEGER);
		}

		$orderCol  = $this->getState('list.ordering', 'a.created');
		$orderDirn = $this->getState('list.direction', 'DESC');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	public function getItemsForExport(): array
	{
		$db    = $this->getDatabase();
		$query = $this->getListQuery();
		$db->setQuery($query);

		return $db->loadObjectList() ?: [];
	}
}
