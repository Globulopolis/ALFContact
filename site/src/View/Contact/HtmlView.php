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

namespace Joomla\Component\ALFContact\Site\View\Contact;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * HTML Contact View class for the ALFContact component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The item being created
     *
     * @var  \stdClass
     */
    protected $item;

    /**
     * The page to return to after the form is submitted
     *
     * @var  string
     */
    protected $return_page = '';

	/**
	 * Form control
	 *
	 * @var  string
	 */
	protected $control = 'jform';

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * The user object
     *
     * @var \Joomla\CMS\User\User
     *
     * @since  4.0.0
     */
    protected $user = null;

	/**
	 * Should we show a captcha form for the submission?
	 *
	 * @var    bool
	 *
	 * @since  3.7.0
	 */
	protected $captchaEnabled = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   3.0
	 */
    public function display($tpl = null)
    {
	    $app           = Factory::getApplication();
	    $user          = $app->getIdentity();
	    $model         = $this->getModel();
	    $this->control = $model->get('form_control');

        // Get model data.
        $this->state       = $this->get('State');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

        $this->params = $params;
        $this->user = $user;

	    $captchaSet = $this->params->get('captcha', $app->get('captcha', '0'));

        foreach (PluginHelper::getPlugin('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                $this->captchaEnabled = true;
                break;
            }
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();

        // Because the application sets a default page title, we need to get it from the menu item itself
        $menu = $app->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_ALFCONTACT_FORM'));
        }

        $title = $this->params->def('page_title', Text::_('COM_ALFCONTACT_FORM'));

        $this->setDocumentTitle($title);

        $app->getPathway()->addItem($title);

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }

	    $style = $this->params->get('css_style', '');

	    if (!empty($style))
	    {
		    $this->getDocument()->getWebAssetManager()->addInlineStyle($style);
	    }
    }
}
