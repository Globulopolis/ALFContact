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

namespace Joomla\Component\ALFContact\Site\Controller;

defined('_JEXEC') or die;

/**
 * ALFContact Component Controller
 *
 * @since  1.5
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * Method to display a view.
     *
     * @param   bool  $cachable   If true, the view output will be cached.
     * @param   bool  $urlparams  An array of safe URL parameters and their variable types.
     *
     * @see     \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  DisplayController  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $vName = $this->input->getCmd('view', 'contact');
        $this->input->set('view', $vName);

	    $safeurlparams = [
		    'return' => 'BASE64',
		    'lang'   => 'CMD',
		    'Itemid' => 'INT',];

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
