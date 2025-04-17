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

namespace Joomla\Component\ALFContact\Site\View\Response;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * HTML Response View class for the ALFContact component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void|bool
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$valid = Session::checkToken('get');

		if (!$valid) {
			$referrer = $app->input->server->getString('HTTP_REFERER');

			if (is_null($referrer) || !Uri::isInternal($referrer)) {
				$referrer = 'index.php';
			}

			$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), CMSWebApplicationInterface::MSG_WARNING);
			$app->redirect($referrer);
		}

		parent::display($tpl);
	}
}
