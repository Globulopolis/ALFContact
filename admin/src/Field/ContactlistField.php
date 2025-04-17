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
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

class ContactlistField extends ListField
{
	protected $type = 'ContactList';

	public function getOptions()
	{
		$options = array();

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query= $db->getQuery(true);

		try
		{
			$query->select($db->quoteName('id', 'value'))
				->select($db->quoteName('name', 'text'))
				->from($db->quoteName('#__alfcontact'))
				->where($db->quoteName('published') . ' = 1')
				->order($db->quoteName('ordering') . ' ASC');

			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Log::add('ERROR', $e->getMessage(), 'com_alfcontact');
		}

		return array_merge(parent::getOptions(), $options);
	}
}
