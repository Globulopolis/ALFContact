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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var Joomla\Component\ALFContact\Administrator\View\Contacts\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$user          = Factory::getApplication()->getIdentity();
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));
$canOrder      = $user->authorise('core.edit.state', 'com_alfcontact');
$saveOrder     = $listOrder == 'c.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_alfcontact&task=contacts.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_alfcontact&view=contacts'); ?>" method="post" name="adminForm"
	  id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<?php if (empty($this->items)): ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-sm">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_ALFCONTACT_MANAGER_ALFCONTACTS'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th class="w-1 nowrap">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'c.ordering', $listDirection, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
							</th>
							<th scope="col" class="w-5 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'c.published', $listDirection, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_ALFCONTACT_CONTACT_NAME', 'c.name', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-15 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_ALFCONTACT_CONTACT_EMAIL', 'c.email', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_ALFCONTACT_CONTACT_PREFIX', 'c.prefix', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-12 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_ALFCONTACT_CONTACT_EXTRA', 'c.extra', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-12 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_ALFCONTACT_CONTACT_DEFAULT_SUBJECT', 'c.defsubject', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-7 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'c.access', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-7 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $listDirection, $listOrder); ?>
							</th>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirection, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tbody <?php if ($saveOrder): ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirection); ?>" data-nested="false"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item):
							$canEdit = $user->authorise('core.edit', 'com_alfcontact');
							$canChange = $user->authorise('core.edit.state', 'com_alfcontact');
							?>
							<tr class="row<?php echo $i % 2; ?>" data-item-id="<?php echo $item->id ?>"
								data-draggable-group="0" data-parents="" data-level="0">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';

									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder): ?>
										<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>"
											   class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'contacts.', $canChange); ?>
								</td>
								<th scope="row" class="has-context">
									<div class="break-word">
										<?php if ($canEdit): ?>
											<a href="<?php echo Route::_('index.php?option=com_alfcontact&task=contact.edit&id=' . (int) $item->id); ?>"
											   title="<?php echo Text::_('JACTION_EDIT'); ?>">
												<span class="read-more"><?php echo $this->escape($item->name); ?></span>
											</a>
										<?php else: ?>
											<span class="read-more"><?php echo $this->escape($item->name); ?></span>
										<?php endif; ?>
									</div>
								</th>
								<td class="small d-none d-md-table-cell text-break">
									<?php
									$recipients = explode("\n", $item->email);

									foreach ($recipients as $value):
										echo $value . '</br>';
									endforeach;
									?>
								</td>
								<td class="small d-none d-md-table-cell text-break">
									<?php echo $this->escape($item->prefix); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php
									$extras = explode("\n", $item->extra);

									foreach ($extras as $value):
										echo $value . '</br>';
									endforeach;
									?>
								</td>
								<td class="small d-none d-md-table-cell text-break">
									<?php echo $this->escape($item->defsubject); ?>
								</td>
								<td class="small d-none d-md-table-cell text-break">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
			</div>
		</div>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
