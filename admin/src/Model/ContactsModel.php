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

namespace Joomla\Component\ALFContact\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class ContactsModel extends ListModel
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'c.id',
				'name', 'c.name',
				'email', 'c.email',
				'bcc', 'c.bcc',
				'prefix', 'c.prefix',
				'extra', 'c.extra',
				'defsubject', 'c.defsubject',
				'language', 'c.language'
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				array(
					$db->quoteName('c.id'),
					$db->quoteName('c.name'),
					$db->quoteName('c.email'),
					$db->quoteName('c.bcc'),
					$db->quoteName('c.prefix'),
					$db->quoteName('c.extra'),
					$db->quoteName('c.defsubject'),
					$db->quoteName('c.ordering'),
					$db->quoteName('c.access'),
					$db->quoteName('c.language'),
					$db->quoteName('c.published')
				)
			)
		)
			->select($db->quoteName('ag.title', 'access_level'))
			->from($db->quoteName('#__alfcontact') . ' AS c')
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('c.access'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->leftJoin(
				$db->quoteName('#__languages', 'l'),
				$db->quoteName('l.lang_code') . ' = ' . $db->quoteName('c.language')
			);

		// Filter by access level.
		$access = $this->getState('filter.access');

		if (is_numeric($access)) {
			$access = (int) $access;
			$query->where($db->quoteName('c.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		} elseif (\is_array($access)) {
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('c.access'), $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('c.published = ' . (int) $published);
		}

		// Filter by language
		$language = $this->getState('filter.language');

		if ($language != '')
		{
			$query->where('c.language = ' . $db->Quote($db->escape($language)));
		}

		// Filter by search in name or email
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search = (int) StringHelper::substr($search, 3);
				$query->where($db->quoteName('c.id') . ' = :search')
					->bind(':search', $search, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'email:') === 0)
			{
				$escaped = $db->Quote('%' . $db->escape(StringHelper::substr($search, 6), true) . '%');
				$query->where('c.email LIKE ' . $escaped);
			}
			else
			{
				$escaped = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('c.name LIKE ' . $escaped);
			}
		}

		$ordering  = $this->state->get('list.ordering', 'c.ordering');
		$direction = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($ordering . ' ' . $direction));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   1.6
	 */
	protected function populateState($ordering = 'c.ordering', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$language = $app->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}
}
