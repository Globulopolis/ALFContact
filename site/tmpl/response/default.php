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

use Joomla\CMS\Component\ComponentHelper;

$params = ComponentHelper::getParams('com_alfcontact');
?>
<div class="item-page">
	<div class="page-header">
		<h2><?php echo $params->get('custom_header', ''); ?></h2>
	</div>
	<div class="content">
		<?php echo $params->get('custom_text', ''); ?>
	</div>
</div>
