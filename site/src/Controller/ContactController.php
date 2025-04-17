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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Utilities\IpHelper;
use PHPMailer\PHPMailer\Exception as phpmailerException;

/**
 * ALFContact Form Controller
 *
 * @since  1.5
 */
class ContactController extends FormController
{
	/**
	 * Method to send email.
	 *
	 * @return  void
	 *
	 * @since   5.0
	 */
	public function send()
	{
		$this->checkToken();

		$app        = $this->app;
		$menu       = $app->getMenu()->getItem($app->input->getInt('menu'));
		$model      = $this->getModel();
		$params     = ComponentHelper::getParams('com_alfcontact');
		$context    = "$this->option.edit.$this->context";
		$ip         = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? IpHelper::getIp();
		$data       = $this->input->post->get($model->get('form_control'), array(), 'array');
		$form       = $model->getForm($data, false);
		$return     = $this->getReturnPage();
		$menuParams = $menu->getParams();
		$user       = $app->getIdentity();

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect(Route::_($return, false));

			return;
		}

		$objData = (object) $data;
		$this->getDispatcher()->dispatch(
				'onContentNormaliseRequestData',
				AbstractEvent::create(
						'onContentNormaliseRequestData',
						array($this->option . '.' . $this->context, $objData, $form, 'subject' => new \stdClass())
				)
		);
		$data = (array) $objData;

		$validData = $model->validate($form, $data);

		if ($validData === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof \Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.' . $user->id . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_($return, false));

			return;
		}

		$subject = $validData['subject'];

		if (isset($data['extra'])) {
			$filter = InputFilter::getInstance();

			foreach ($data['extra'] as $key => $value) {
				$validData['extra'][$key] = $filter->clean($value);
			}
		}

		if ($params->get('mailformat', 1)) {
			$sep  = '<br>';
			$line = '<hr>';
		} else {
			$sep  = PHP_EOL;
			$line = PHP_EOL . '-------------------------------------------------------------------------------' . PHP_EOL;
		}

		if ($validData['emailto_id'] == '99') {
			$emailto = $app->get('mailfrom');
			$origSubject = '';
		} else {
			$row       = $model->getItem($validData['emailto_id']);
			$emailto   = !empty($row->email) ? $row->email : $app->get('mailfrom');
			$bcc       = $row->bcc;
			$prefix    = $row->prefix;
			$optfields = $row->extra;

			//Adding prefix to subject
			$origSubject = $validData['subject'];
			$subject = $prefix.' '.$validData['subject'];
		}

		// Split multiple email addresses into an array
		$recipients = explode("\n", $emailto);
		$message = $validData['message'];

		// Add information from the extra fields if applicable
		if (!empty($optfields)) {
			$fieldsArray = explode("\n", $optfields);
			$valuesArray = explode('#', $validData['extravalues']);

			unset($valuesArray[0]);
			$extra_array = array_combine($fieldsArray, $valuesArray);

			$extramsg = '';

			foreach ($extra_array as $key => $value) {
				$extramsg = $extramsg . $key . ' ' . $value . $sep . $sep;
			}

			$message = $extramsg . $sep . Text::_('COM_ALFCONTACT_FORM_MESSAGE') . $sep . $validData['message'];
		}

		// Send copy if requested
		if (isset($validData['copy'])) {
			$copySubject = Text::_('COM_ALFCONTACT_COPYOFMESSAGE') . ' ' . $app->get('fromname');
			$copyHeader = Text::_('COM_ALFCONTACT_FORM_SUBJECT') . ' ' . $origSubject . $sep;

			if (isset($validData['gpdr_consent'])) {
				$copyHeader = $copyHeader . $sep . Text::_('COM_ALFCONTACT_OPTIONS_GPDR_CONSENT_COPY') . ' ' . $sep;
				$copyHeader = $copyHeader . ' ' . $menuParams->get('gpdr_info', '') . $sep;
				$copyHeader = $copyHeader . ' ' . $menuParams->get('gpdr_label', '') . $sep . $sep;
			}

			$copyHeader = $copyHeader . $line;
			$copyMessage = $copyHeader . $validData['message'];

			/** @var Mail $mail */
			$mail = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
			$mail->addRecipient($validData['email']);
			$mail->setSender(array($app->get('mailfrom'), $app->get('fromname')));
			$mail->setSubject($copySubject);
			$mail->setBody($copyMessage);

			if ($params->get('mailformat', 1)) {
				$mail->isHtml();
				$mail->setBody(nl2br($message));
			}

			try {
				// If required check GPDR value before sending mail
				if ($params->get('gpdrcheck', 0)) {
					if (isset($validData['gpdr_consent'])) {
						$sent = $mail->Send();
					}
				} else {
					$sent = $mail->Send();
				}
			} catch (MailDisabledException | phpmailerException $e) {
				try {
					Log::add($e->getMessage() . ' in ' . __METHOD__ . '#' . __LINE__, Log::WARNING, 'com_alfcontact');
				} catch (\RuntimeException $exception) {
					$app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
				}

				$this->setRedirect(Route::_($this->getReturnPage()));

				return;
			}
		}

		// Add an infomation banner to the top of the contacts message.
		switch ($params->get('verbose', 1)) {
			case 1:
				$header = Text::_('COM_ALFCONTACT_DETAILS_HEADER') . $sep;
				$header = $header . $line;
				$header = $header . Text::_('COM_ALFCONTACT_DETAILS_NAME') . ' ' . $validData['name'] . $sep;
				$header = $header . Text::_('COM_ALFCONTACT_DETAILS_EMAIL') . ' ' . $validData['email'] . $sep;
				$header = $header . Text::_('COM_ALFCONTACT_DETAILS_IP') . ' ' . $ip . $sep;
				$header = $header . Text::_('COM_ALFCONTACT_DETAILS_BROWSER') . ' ' .$_SERVER['HTTP_USER_AGENT'] . $sep;

				if (isset($validData['gpdr_consent'])) {
					$header = $header . $sep . Text::_('COM_ALFCONTACT_OPTIONS_GPDR_CONSENT') . ' ' . $sep;
					$header = $header . ' ' . $menuParams->get('gpdr_info', '') . $sep;
					$header = $header . ' ' . $menuParams->get('gpdr_label', '') . $sep . $sep;
				}

				$header = $header . $line;
				$message = $header . $message;
				break;
			case 2:
				$header  = Text::_('COM_ALFCONTACT_DETAILS_HEADER') . $sep;
				$header  = $header . $line;
				$header  = $header . Text::_('COM_ALFCONTACT_DETAILS_NAME') . ' ' . $validData['name'] . $sep;
				$header  = $header . Text::_('COM_ALFCONTACT_DETAILS_EMAIL') . ' ' . $validData['email'] . $sep;
				$header  = $header . $line;
				$message = $header . $message;
				break;
			case 3:
				break;
		}

		// Send mail
		/** @var Mail $mail */
		$mail = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

		foreach ($recipients as $value) {
			$mail->addRecipient($value);
		}

		if (!empty($bcc)) {
			// Split multiple bcc addresses into an array
			$bccs = explode("\n", $bcc);

			foreach($bccs as $value) {
				$mail->addBCC($value);
			}
		}

		if ($params->get('fromsite', 0))
		{
			$mail->setSender(array($app->get('mailfrom'), $validData['name']));
		}
		else
		{
			$mail->setSender(array($validData['email'], $validData['name']));
		}

		$mail->setSubject($subject);
		$mail->setBody($message);
		$mail->addReplyTo($validData['email'], $validData['name']);

		if ($params->get('mailformat', 1))
		{
			$mail->isHtml();
			$mail->setBody(nl2br($message));
		}

		try {
			// If required check GPDR value before sending mail
			if ($params->get('gpdrcheck', 0)) {
				if (isset($validData['gpdr_consent'])) {
					$sent = $mail->Send();
				}
			} else {
				$sent = $mail->Send();
			}
		} catch (MailDisabledException | phpmailerException $e) {
			try {
				Log::add($e->getMessage() . ' in ' . __METHOD__ . '#' . __LINE__, Log::WARNING, 'com_alfcontact');
			} catch (\RuntimeException $exception) {
				$app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
			}

			$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			$this->setRedirect(Route::_($this->getReturnPage()));

			return;
		}

		$app->setUserState('com_alfcontact.edit.contact.' . $user->id . '.data', []);

		switch ($params->get('redirect_option', 1)) {
			case 2:
				$this->setRedirect(Route::_($this->getReturnPage(), false), Text::_('COM_ALFCONTACT_SENDED'), 'success');
				break;
			case 3:
				$this->setRedirect(Route::_('index.php?option=com_alfcontact&view=response&' . Session::getFormToken() . '=1', false));
				break;
			case 4:
				$this->setRedirect($params->get('url', ''));
				break;
			case 5:
				$articleId = $params->get('redirect_article');

				if (empty($articleId)) {
					$this->setRedirect(Route::_('index.php', false));
				} else {
					$articleModel = $app->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site');
					$item = $articleModel->getItem($articleId);
					$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
					$this->setRedirect(
						Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language), false),
						Text::_('COM_ALFCONTACT_SENDED'),
						'success'
					);
				}
				break;
			default:
				$this->setRedirect(Route::_(Uri::root(), false), Text::_('COM_ALFCONTACT_SENDED'), 'success');
				break;
		}
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string  The return URL.
	 *
	 * @since   1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return))) {
			return Uri::base();
		}

		return base64_decode($return);
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. ignore_request = true - do not call populateState()
	 *
	 * @return  object  The model.
	 *
	 * @since   1.5
	 */
	public function getModel($name = 'Contact', $prefix = 'Site', $config = ['ignore_request' => false]) {
		return parent::getModel($name, $prefix, $config);
	}
}
