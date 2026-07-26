<?php
namespace FelizComPouco\Component\Fcpguia\Administrator\View\Item;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$isNew = empty($this->item->id);
		$canDo = ContentHelper::getActions('com_fcp_guia');

		ToolbarHelper::title(
			Text::_($isNew ? 'COM_FCP_GUIA_ITEM_NEW' : 'COM_FCP_GUIA_ITEM_EDIT'),
			'pencil-alt'
		);

		if ($canDo->get('core.create') || $canDo->get('core.edit')) {
			ToolbarHelper::apply('item.apply');
			ToolbarHelper::save('item.save');
			ToolbarHelper::save2new('item.save2new');
		}

		if (!$isNew && $canDo->get('core.create')) {
			ToolbarHelper::save2copy('item.save2copy');
		}

		ToolbarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
