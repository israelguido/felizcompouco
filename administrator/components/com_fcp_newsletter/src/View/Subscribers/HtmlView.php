<?php
namespace FelizComPouco\Component\Fcpnewsletter\Administrator\View\Subscribers;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;

	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_FCP_NEWSLETTER_SUBSCRIBERS_TITLE'), 'mail');

		$canDo = ContentHelper::getActions('com_fcp_newsletter');

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'subscribers.delete', 'JTOOLBAR_DELETE');
		}

		ToolbarHelper::link(
			'index.php?option=com_fcp_newsletter&task=subscribers.export&' . Session::getFormToken() . '=1',
			Text::_('COM_FCP_NEWSLETTER_EXPORT_CSV'),
			'download'
		);
	}
}
