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

namespace Joomla\Component\ALFContact\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

class ContactModel extends AdminModel
{
	/**
	 * Form control name.
	 *
	 * @var    string
	 * @since  5.0
	 */
	protected $form_control = 'fc';

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_alfcontact.contact', 'contact', ['control' => $this->form_control, 'load_data' => $loadData]);

		if (empty($form)) {
			return false;
		}

		$params = $this->getState('params');

		if (!empty($params->get('maxchars'))) {
			$form->setFieldAttribute('message', 'data-max', (int) $params->get('maxchars'));
		}

		if ($params->get('copytome') == 0) {
			$form->removeField('copy');
		} else {
			//$form->setFieldAttribute('copy', 'checked', true);
		}

		if ($params->get('gpdrcheck') == 0) {
			$form->removeField('gpdr_consent');
		} else {
			$form->setFieldAttribute('gpdr_consent', 'label', $params->get('gpdr_label'));
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity();
		$data = !$user->guest ? $app->getUserState('com_alfcontact.edit.contact.' . $user->id . '.data', []) : null;

		if (empty($data)) {
			$data = (object) array();
		}

		$this->preprocessData('com_alfcontact.contact', $data);

		return $data;
	}

	/**
	 * Allows preprocessing of the Form object.
	 *
	 * @param   Form    $form   The form object
	 * @param   object  $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   4.1
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{
		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$params = $this->getState('params');

		if (!$params->get('autouser') || ($params->get('autouser') && !$user->name)) {
			$form->setValue('name', null, $app->getUserState('com_alfcontact.name', ''));
		} else {
			$form->setFieldAttribute('name', 'readonly', true);
			$form->setValue('name', null, htmlspecialchars($user->name));
		}

		if (!$params->get('autouser') || ($params->get('autouser') && !$user->email)) {
			$form->setValue('email', null, $app->getUserState('com_alfcontact.email', ''));
		} else {
			$form->setFieldAttribute('email', 'readonly', true);
			$form->setValue('email', null, htmlspecialchars($user->email));
		}

		if ($app->getUserState('com_alfcontact.copy', 0)) {
			$form->setFieldAttribute('copy', 'checked', true);
		}

		if (!$user->authorise('core.captcha', 'com_alfcontact') || $user->get('isRoot'))
		{
			$form->removeField('captcha');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string  The return URL.
	 *
	 * @since   1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page', ''));
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		/** @var SiteApplication $app */
		$app = Factory::getContainer()->get(SiteApplication::class);

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams = $menu->getParams();
		} else {
			$menuParams = new Registry();
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($app->getParams());

		$this->setState('params', $mergedParams);
		$this->setState('return_page', Uri::getInstance());
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  boolean|\Joomla\Component\ALFContact\Administrator\Table\ContactTable  A Table object
	 *
	 * @throws  \Exception
	 * @since   4.1
	 */
	public function getTable($name = 'Contact', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|bool  Array of filtered data if valid, false otherwise.
	 *
	 * @see     FormRule
	 * @see     InputFilter
	 * @since   1.6
	 */
	public function validate($form, $data, $group = null)
	{
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_alfcontact');

		if (($params->get('maxchars') != 0) && (StringHelper::strlen($data['message']) > $params->get('maxchars')))
		{
			$this->setError(Text::_('COM_ALFCONTACT_ERROR_YOUR_MESSAGE_IS_TOO_LONG'));

			return false;
		}

		return parent::validate($form, $data, $group);
	}
}
