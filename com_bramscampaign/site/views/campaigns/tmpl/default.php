<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="container custom_container container_margin">
	<?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
	<div class='row'>
		<div class='col custom_col'>
			<p id='message' class='<?php echo $this->message['css_class']; ?>'>
				<?php echo $this->message['message']; ?>
			</p>
			<h1>Campaigns</h1>
			<p>
				Click on one of the buttons on the right side column to edit or delete the campaign.
			</p>
		</div>
	</div>
	<div class='row'>
		<div class='col custom_col'>
			<button
				type='button'
				class='customBtn new'
				onclick="window.location.href='/index.php?option=com_bramscampaign&view=campaignEdit&id=';"
			>
				<i class="fa fa-plus-square-o" aria-hidden="true"></i>
				New Campaign
			</button>
		</div>
	</div>
	<div class='row'>
		<div class='col'>
			<table class='table'>
				<thead>
				<tr>
					<th class='headerCol' onclick="sortTable(this, 'name')">
						Name <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
					</th>
					<th class='headerCol' onclick="sortTable(this, 'type')">
						Type
					</th>
                    <th class='headerCol' onclick="sortTable(this, 'station')">
                        Station
                    </th>
					<th class='headerCol' onclick="sortTable(this, 'start')">
						Start
					</th>
					<th class='headerCol' onclick="sortTable(this, 'end')">
						End
					</th>
					<th class='headerCol'>
						Actions
					</th>
				</tr>
				</thead>
				<tbody id='campaigns'>

				</tbody>
			</table>
		</div>
	</div>
</div>
