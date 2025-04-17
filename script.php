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

defined('_JEXEC') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
 
/**
 * Script file of AlfContact component
 *
 * @since 3.7.0
 */
class com_AlfContactInstallerScript
{
	/**
	 * Called only with install
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.7.0
	 */
	public function install ($parent) {
		// Shows after install
		echo '<div class="well">
			<h2 class="text-warning">' . Text::_('COM_ALFCONTACT') . ' v5.0.0</h2>
			<div>
				<p><br>' . Text::_('COM_ALFCONTACT_DESCRIPTION') . '</p>
				<p>' . Text::_('COM_ALFCONTACT_INSTALL_TEXT') . '</p>
				<p><a class="fw-bold text-warning" href="' . Route::_('index.php?option=com_alfcontact') . '">' . Text::_('COM_ALFCONTACT_GOTO_ADMIN') . '</a></p>
			</div>
		</div>';
	}

	/**
	 * Called on uninstall
	 *
	 * @param   InstallerAdapter  $installer  The class calling this method
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.7.0
	 */
	public function uninstall($installer)
	{
		// Shows after uninstall
		echo '<div class="well">
			<h2 class="text-warning">' . Text::_('COM_ALFCONTACT') . ' v5.0.0</h2>
			<div>
				<p>' . Text::_('COM_ALFCONTACT_UNINSTALL_TEXT') . '</p>
			</div>
		</div>';
	}

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string  $action  Which action is happening (install|uninstall|discover_install|update)
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function update($action)
	{
		// Shows after update
		echo '<div class="well">
			<h2 class="text-warning">' . Text::_('COM_ALFCONTACT') . ' v5.0.0</h2>
			<div>
				<p>' . Text::_('COM_ALFCONTACT_UPDATE_TEXT') . '</p>
			</div>
		</div>';
	}

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function preflight($action, $installer)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)

		echo '<p>' . Text::_('COM_ALFCONTACT_PREFLIGHT_' . $action . '_TEXT') . '</p>';
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string            $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   InstallerAdapter  $installer  The class calling this method
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.7.0
	 */
	public function postflight($action, $installer)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)

        echo '<p><b>' . 'NOTICE: GPDR info and label texts have been moved to the menu-item options to allow for localization.' .'</p></b></br>';
        echo '<p>' . Text::_('COM_ALFCONTACT_POSTFLIGHT_' . $action . '_TEXT') . '</p>';
	}
}
