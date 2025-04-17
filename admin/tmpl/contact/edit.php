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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$input = Factory::getApplication()->getInput();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
?>
<form action="<?php echo JRoute::_('index.php?option=com_alfcontact&layout=edit&id='. (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset class="adminform">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="form-grid">
						<?php echo $this->form->renderFieldset('edit'); ?>
					</div>
				</div>
			</div>
		</div>
    </fieldset>

    <input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
 </form>
