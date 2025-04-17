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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\ALFContact\Site\View\Contact\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('form.validate')
	->registerAndUseScript('alfcontact.main', Uri::base() . 'media/com_alfcontact/js/main.min.js');

$user = $this->getCurrentUser();
$htag = $this->params->get('show_page_heading') ? 'h2' : 'h1';
?>
<div class="com-alfcontact-contact item-page<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')): ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
    <?php endif; ?>

	<?php if (!empty($this->params->get('header'))): ?>
		<div><?php echo $this->params->get('header'); ?><br></div>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_alfcontact'); ?>" method="post" name="adminForm"
		  id="contact-form" class="form-validate form-horizontal" data-control="<?php echo $this->control; ?>">
		<div class="row">
			<?php echo $this->form->renderField('name'); ?>
			<?php echo $this->form->renderField('email'); ?>
			<?php echo $this->form->renderField('hr1'); ?>
			<?php echo $this->form->renderField('emailid'); ?>
			<?php echo $this->form->renderField('subject'); ?>
			<?php echo $this->form->renderField('message', null, null, array('class' => 'startfields')); ?>

			<?php if ($this->params->get('copytome') == 1): ?>
				<div class="row">
					<?php echo $this->form->getLabel('copy'); ?>
					<div class="col-auto order-0 pe-0">
						<?php echo $this->form->getInput('copy'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('gpdrcheck') == 1): ?>
				<div>
					<?php echo $this->form->renderField('hr1'); ?>
					<div>
						<p><?php echo $this->params->get('gpdr_info'); ?></p>
						<div class="row mb-4">
							<?php echo $this->form->getLabel('gpdr_consent'); ?>
							<div class="col-auto order-0 pe-0">
								<?php echo $this->form->getInput('gpdr_consent'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->captchaEnabled) : ?>
				<?php echo $this->form->renderField('captcha'); ?>
			<?php endif; ?>

			<?php echo $this->form->renderField('extravalues'); ?>
			<?php echo $this->form->renderField('emailto_id'); ?>
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
			<input type="hidden" name="menu" value="<?php echo \Joomla\CMS\Factory::getApplication()->input->getInt('Itemid'); ?>" />
			<input type="hidden" name="option" value="com_alfcontact" />
			<input type="hidden" name="task" value="contact.send" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>

		<div class="d-grid gap-2 d-sm-block mb-2">
			<button type="button" class="btn btn-primary" data-submit-task="send">
				<span class="icon-check" aria-hidden="true"></span>
				<?php echo Text::_('COM_ALFCONTACT_FORM_SEND'); ?>
			</button>
			<?php if ($this->params->get('resetbtn')): ?>
			<button type="button" class="btn btn-danger" data-submit-task="reset">
				<span class="icon-times" aria-hidden="true"></span>
				<?php echo Text::_('COM_ALFCONTACT_FORM_RESET'); ?>
			</button>
			<?php endif; ?>
		</div>
	</form>

	<?php if (!empty($this->params->get('footer'))): ?>
		<div><?php echo $this->params->get('footer'); ?></div>
	<?php endif; ?>
</div>
