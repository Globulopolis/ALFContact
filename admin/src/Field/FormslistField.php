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

namespace Joomla\Component\ALFContact\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class FormslistField extends ListField
{
	protected $type = 'FormsList';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		$app             = Factory::getApplication();
		$data            = $this->collectLayoutData();
		$model           = $app->bootComponent('com_alfcontact')->getMVCFactory()->createModel('Contact', 'Site');
		$data['options'] = (array) $this->getOptions();
		$totalOpts       = count($data['options']);

		if ($totalOpts > 1) {
			/** @var MenuItem $menu */
			$menu = $app->getMenu()->getActive();
			$contactId = $app->input->getInt('contactid');

			if (!empty($contactId)) {
				$selected = $contactId;
			} else {
				$selected = (int) $menu->getParams()->get('defcontact');
			}

			$html = HTMLHelper::_(
				'select.genericlist',
				$data['options'],
				$this->name,
				array('class' => 'form-select'),
				'value', 'text',
				array_key_exists($selected, $data['options']) ? $data['options'][$selected]->value : 0,
				$this->id
			);
		} elseif ($totalOpts == 0) {
			$html = '<input readonly type="text" value="' . htmlspecialchars($app->get('fromname')) . '" class="form-control pe-none"/>
				<input type="hidden" id="' . $model->get('form_control') . '_emailid" value="'. base64_encode('99,,') . '"/>';
		} else {
			$html = '<input readonly type="text" value="' . htmlspecialchars($data['options'][0]->text) . '" class="form-control pe-none"/>
				<input type="hidden" id="' . $model->get('form_control') . '_emailid" value="' . $data['options'][0]->value . '"/>';
		}

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  object[]  The field option objects.
	 *
	 * @since   3.7.0
	 */
	public function getOptions()
	{
		$options = array();
		$app     = Factory::getApplication();
		$user    = $app->getIdentity();
		$groups  = $user->getAuthorisedViewLevels();

		/** @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		try {
			$query->select(
				array(
					$db->quoteName('id'),
					$db->quoteName('name', 'text'),
					$query->concatenate($db->quoteName(array('id', 'extra', 'defsubject')), ',') . ' AS ' . $db->quoteName('value'),
				)
			)
				->from($db->quoteName('#__alfcontact'))
				->where($db->quoteName('published') . ' = 1')
				->whereIn($db->quoteName('access'), $groups)
				->whereIn($db->quoteName('language'), array($db->quote($app->getLanguage()->getTag()), $db->quote('*')))
				->order($db->quoteName('ordering') . ' ASC');

			$db->setQuery($query);
			$options = $db->loadObjectList();
		} catch (\RuntimeException $e) {
			Log::add('ERROR', $e->getMessage(), 'com_alfcontact');
		}

		foreach ($options as $option) {
			if (!empty($option->value)) {
				$option->value = base64_encode(htmlspecialchars($option->value));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
