<?php
namespace FelizComPouco\Component\Fcp_guia\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

class ItemTable extends Table
{
	protected $_supportNullValue = true;

	public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
	{
		parent::__construct('#__fcp_guia', 'id', $db, $dispatcher);

		$this->setColumnAlias('published', 'state');
	}

	public function check()
	{
		try {
			parent::check();
		} catch (\Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		$this->title = trim((string) $this->title);

		if ($this->title === '') {
			$this->setError('COM_FCP_GUIA_ERROR_TITLE_REQUIRED');

			return false;
		}

		if (trim((string) $this->alias) === '') {
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias);

		if (trim($this->alias) === '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		$this->image     = trim((string) $this->image);
		$this->image_alt = trim((string) $this->image_alt);
		$this->url       = trim((string) $this->url);
		$this->introtext = (string) ($this->introtext ?? '');
		$this->ordering  = (int) $this->ordering;
		$this->state     = (int) $this->state;

		return true;
	}

	public function store($updateNulls = true)
	{
		$date   = Factory::getDate()->toSql();
		$userId = (int) Factory::getApplication()->getIdentity()->id;

		if (!(int) $this->created) {
			$this->created = $date;
		}

		if ((int) $this->id) {
			$this->modified    = $date;
			$this->modified_by = $userId;
		} else {
			if (empty($this->created_by)) {
				$this->created_by = $userId;
			}

			$this->modified    = $date;
			$this->modified_by = $userId;

			if ((int) $this->ordering === 0) {
				$this->ordering = (int) $this->getNextOrder();
			}
		}

		return parent::store($updateNulls);
	}
}
