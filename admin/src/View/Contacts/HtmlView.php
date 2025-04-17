<?php
/**
 * ALFContact - Small 'Contact Us' form for Joomla!
 *
 * @component         ALFContact
 * @author            Alfred Vink
 * @copyright     (C) 2016 Alfred Vink (https://www.alfsoft.com)
 *                (C) 2025 Vladimir Globulopolis (https://github.com/Globulopolis)
 * @license           GNU General Public License version 2 or later; GNU/GPL: https://www.gnu.org/copyleft/gpl.html
 *
 **/

namespace Joomla\Component\ALFContact\Administrator\View\Contacts;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   4.0
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state         = $this->get('State');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 *
	 * @throws  \Exception
	 */
	protected function addToolbar()
	{
		$toolbar = Toolbar::getInstance();
		$canDo   = ContentHelper::getActions('com_alfcontact', 'component');
		$user    = Factory::getApplication()->getIdentity();

		ToolbarHelper::title(Text::_('COM_ALFCONTACT_MANAGER_ALFCONTACTS'), 'fa fa-address-book');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('contact.add');
		}

		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action')
			->listCheck(true);

		$childBar = $dropdown->getChildToolbar();

		if ($canDo->get('core.edit.state'))
		{
			$childBar->publish('contacts.publish')->listCheck(true);
			$childBar->unpublish('contacts.unpublish')->listCheck(true);
		}

		if ($canDo->get('core.delete'))
		{
			$childBar->delete('contacts.delete')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_alfcontact') || $user->authorise('core.options', 'com_alfcontact'))
		{
			$toolbar->preferences('com_alfcontact');
		}
	}
}
