{*
* 2014 Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author Keyrnel
* @copyright  2017 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<div {if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}id="retro_admin_customers"{/if} class="panel col-lg-6">
	<div class="panel-heading">
		<i class="icon-group"></i>
		{l s='Wallet' mod='prepayment'}
			<span class="badge">{($history_list)|@count|intval}</span>
	</div>
	<div class="col-lg-5">
		<div class="panel col-lg-9">
			{l s='Account balance' mod='prepayment'}:&nbsp;<span class="bold" style="color:#5C9939;">{$balance|escape:'quotes':'UTF-8'}</span>
		</div>
	</div>
	<div class="col-lg-4">
		 <div class="col-lg-12">
            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Enabled or disabled customer wallet' mod='prepayment'}">{l s='Status' mod='prepayment'}</span>
        </div>
        <div class="col-lg-12">
            <span class="switch prestashop-switch fixed-width-lg">
                <input id="status_on" type="radio" value="1" name="status" {if $wallet->active == 1}checked="checked"{/if}>
                <label class="radioCheck" for="status_on">{l s='Enabled' mod='prepayment'}</label>
                <input id="status_off" type="radio" value="0" name="status" {if $wallet->active == 0}checked="checked"{/if}>
                <label class="radioCheck" for="status_off">{l s='Disabled' mod='prepayment'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
	<div class="col-lg-3">
		 <div class="col-lg-12">
            <div class="btn-group" style="padding-top: 16px">
				<a class="btn btn-default" onclick="update_balance();">
					<i class="icon-search"></i> {l s='Update Balance' mod='prepayment'}
				</a>
			</div>
        </div>
    </div>
	<table class="table">
		<thead>
			<tr>
				<th class="first_item" data-sort-ignore="true">{l s='Operation' mod='prepayment'}</th>
				<th class="item" data-sort-ignore="true">{l s='Date' mod='prepayment'}</th>
				<th data-sort-ignore="true" class="item">{l s='Price' mod='prepayment'}</th>
				<th data-sort-ignore="true" class="item">{l s='Credits' mod='prepayment'}</th>
				<th data-sort-ignore="true" class="item">{l s='Status' mod='prepayment'}</th>
				<th data-sort-ignore="true" class="last_item">{l s='Actions' mod='prepayment'}</th>
			</tr>
		</thead>
		<tbody>
		{if $history_list AND count($history_list)}
			{foreach from=$history_list item=history name=myLoop}
				<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
				<td>
					<span class="operation">
						{$history.operation|escape:'html':'UTF-8'}
					</span>
				</td>
				<td data-value="{$history.date_add|escape:'quotes':'UTF-8'}" class="history_date bold">
					{dateFormat date=$history.date_add full=0}
				</td>
				<td class="history_price" data-value="{$history.price|floatval}">
					<span class="price">
						{if $history.price > 0}{$history.display_price|escape:'html':'UTF-8'}{else} -- {/if}
					</span>
				</td>
				<td class="history_price" data-value="{$history.credits|floatval}">
					<span class="credits">
						{if $history.credits > 0}{$history.display_credits|escape:'html':'UTF-8'}{else} -- {/if}
					</span>
				</td>
				<td  class="history_state">
					{if $history.paid == 0}{l s='Pending' mod='prepayment'}{else}{l s='Done' mod='prepayment'}{/if}
				</td>
				<td class="text-right">
					<div class="btn-group">
						<a class="btn btn-default" href="?tab=AdminOrders&amp;id_order={$history['id_order']|intval}&amp;vieworder=1&amp;token={getAdminToken tab='AdminOrders'}">
							<i class="icon-search"></i> {l s='View' mod='prepayment'}
						</a>
					</div>
				</td>
				</tr>
			{/foreach}
		{else}
		<tr><td>{l s='No records found' mod='prepayment'}</tr></td>
		{/if}
		</tbody>
	</table>
</div>

<script type="text/javascript">
var update_success_msg = "{l s='Update successful' mod='prepayment' js=1}";

function update_balance()
{
	var params = {
		tab : 'AdminPrepaymentWallets',
		action : 'updateBalanceWallet',
		ajax: 1,
		id_wallet : {$wallet->id|intval},
		token : '{getAdminToken tab='AdminPrepaymentWallets'}'
		};

	$.ajax({
		type: "POST",
		url: "index.php",
		data: params,
		async : true,
		success: function(data) {
			data = $.parseJSON(data);
			if (data.success == 'ok'){
				showSuccessMessage(update_success_msg);
			}
		}
	});
}

$("input[name=status]:radio").change(function(){

	var checked = $('input:radio[name=status]:checked').val();
	var params = {
		tab : 'AdminPrepaymentWallets',
		action : 'updateStatusWallet',
		ajax: 1,
		id_wallet : {$wallet->id|intval},
		status : checked,
		token : '{getAdminToken tab='AdminPrepaymentWallets'}'
		};

	$.ajax({
		type: "POST",
		url: "index.php",
		data: params,
		async : true,
		success: function(data) {
			data = $.parseJSON(data);
			if (data.success == 'ok'){
				showSuccessMessage(update_success_msg);
			}
		}
	});
	return false;
});

</script>
