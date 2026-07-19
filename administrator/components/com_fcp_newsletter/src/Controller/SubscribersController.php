<?php
namespace FelizComPouco\Component\Fcpnewsletter\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class SubscribersController extends AdminController
{
	protected $text_prefix = 'COM_FCP_NEWSLETTER';

	public function getModel($name = 'Subscriber', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function export(): void
	{
		Session::checkToken('request') or Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		/** @var \FelizComPouco\Component\Fcpnewsletter\Administrator\Model\SubscribersModel $model */
		$model = $this->getModel('Subscribers', 'Administrator', ['ignore_request' => false]);
		$items = $model->getItemsForExport();

		$filename = 'newsletter-' . date('Y-m-d') . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');

		$out = fopen('php://output', 'w');
		fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
		fputcsv($out, ['id', 'email', 'name', 'created', 'status', 'source', 'ip'], ';');

		foreach ($items as $item) {
			fputcsv($out, [
				$item->id,
				$item->email,
				$item->name,
				$item->created,
				(int) $item->status === 1 ? 'ativo' : 'inativo',
				$item->source,
				$item->ip,
			], ';');
		}

		fclose($out);
		jexit();
	}

	public function delete(): void
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$cid = (array) $this->input->get('cid', [], 'int');
		$cid = array_filter(array_map('intval', $cid));

		if (!$cid) {
			$this->setMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_fcp_newsletter&view=subscribers', false));
			return;
		}

		/** @var \FelizComPouco\Component\Fcpnewsletter\Administrator\Model\SubscriberModel $model */
		$model = $this->getModel('Subscriber');
		$model->delete($cid);

		$this->setMessage(Text::plural('COM_FCP_NEWSLETTER_N_ITEMS_DELETED', \count($cid)));
		$this->setRedirect(Route::_('index.php?option=com_fcp_newsletter&view=subscribers', false));
	}
}
