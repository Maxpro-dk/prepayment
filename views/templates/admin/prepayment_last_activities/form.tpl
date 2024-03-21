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

<script type="text/javascript">
	var id_customer = "{$currentObj->id_customer|intval}";

	$(document).ready(function() {
		$('#customer').typeWatch({
			captureLength: 3,
			highlight: true,
			wait: 100,
			callback: function(){ searchCustomers(); }
		});

		$('#last_activities').hide();

		$('#customer_part').on('click', 'button.setup-customer', function(e){
			e.preventDefault();
			setupCustomer($(this).data('customer'));
			$(this).removeClass('setup-customer').addClass('change-customer').html('<i class="icon-refresh"></i>&nbsp;{l s='Change' mod='prepayment'}').blur();
			$(this).closest('.customerCard').addClass('selected-customer');
			$('.selected-customer .panel-heading').prepend('<i class="icon-ok text-success"></i>');
			$('.customerCard').not('.selected-customer').remove();
			$('#search-customer-form-group').hide();
		});

		$('#customer_part').on('click', 'button.change-customer', function(e){
			e.preventDefault();
			id_customer = 0;
			$('#search-customer-form-group').show();
			$(this).blur();
		});

		resetBind();

		$('#customer').focus();

		if (id_customer > 0) {
			$('#customer').val("{$customerEmail|escape:'quotes':'UTF-8'}");
			searchCustomers();
		}
	});

	function searchCustomers()
	{
		$.ajax({
			type:"POST",
			url : "{$link->getAdminLink('AdminCustomers')|escape:'quotes':'UTF-8'}",
			async: true,
			dataType: "json",
			data : {
				ajax: "1",
				tab: "AdminCustomers",
				action: "searchCustomers",
				customer_search: $('#customer').val()},
			success : function(res)
			{
				if(res.found)
				{
					var html = '';
					$.each(res.customers, function() {
						html += '<div class="customerCard col-lg-4">';
						html += '<div class="panel">';
						html += '<div class="panel-heading">'+this.firstname+' '+this.lastname;
						html += '<span class="pull-right">#'+this.id_customer+'</span></div>';
						html += '<span>'+this.email+'</span><br/>';
						html += '<span class="text-muted">'+((this.birthday != '0000-00-00') ? this.birthday : '')+'</span><br/>';
						html += '<div class="panel-footer">';
						html += '<a href="{$link->getAdminLink('AdminCustomers')|escape:'quotes':'UTF-8'}&id_customer='+this.id_customer+'&viewcustomer&liteDisplaying=1" class="btn btn-default fancybox"><i class="icon-search"></i> {l s='Details' mod='prepayment'}</a>';
						html += '<button type="button" data-customer="'+this.id_customer+'" class="setup-customer btn btn-default pull-right"><i class="icon-arrow-right"></i> {l s='Choose' mod='prepayment'}</button>';
						html += '</div>';
						html += '</div>';
						html += '</div>';
					});
				}
				else
					html = '<div class="alert alert-warning">{l s='No customers found' mod='prepayment'}</div>';
				$('#customers').html(html);
				if (id_customer > 0) {
					$('button.setup-customer').trigger('click');
				}
			}
		});
	}

	function setupCustomer(idCustomer)
	{
		$('#last_activities').show();
		$('#movement_part').show();
		id_customer = idCustomer;

		$.ajax({
			type:"POST",
			url : "{$link->getAdminLink('AdminPrepaymentLastActivities')|escape:'quotes':'UTF-8'}",
			async: false,
			dataType: "json",
			data : {
				ajax: "1",
				token: "{getAdminToken tab='AdminPrepaymentLastActivities'}",
				tab: "AdminPrepaymentLastActivities",
				action: "searchLastActivities",
				id_customer: id_customer,
			},
			success : function(res)
			{
				if(res.found)
				{
					var html_last_activities = '';
					$.each(res.last_activities, function() {
						html_last_activities += '<tr>';
						html_last_activities += '<td>'+this.id_prepayment_last_activities+'</td>';
						html_last_activities += '<td>'+this.operation+'</td>';
						html_last_activities += '<td>'+this.date_add+'</td>';
						html_last_activities += '<td>'+this.price+'</td>';
						html_last_activities += '<td>'+this.credits+'</td>';
						html_last_activities += '<td>'+this.status+'</td>';
						html_last_activities += '</tr>';
					});

					$('#old_last_activities table tbody').html(html_last_activities);
					$("#id_customer").val(id_customer);
				}
				resetBind();
			}
		});
	}

	function resetBind()
	{
		$('.fancybox').fancybox({
			'type': 'iframe',
			'width': '90%',
			'height': '90%',
		});

		$('.fancybox_customer').fancybox({
			'type': 'iframe',
			'width': '90%',
			'height': '90%',
			'afterClose' : function () {
				searchCustomers();
			}
		});
	}
</script>
{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
	<div id="content" class="bootstrap">
{/if}

<div class="panel form-horizontal" id="customer_part">
	<div class="panel-heading">
		<i class="icon-user"></i>
		{l s='Customer' mod='prepayment'}
	</div>
	<div id="search-customer-form-group" class="form-group">
		<label class="control-label col-lg-3">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Search for an existing customer by typing the first letters of his/her name.' mod='prepayment'}">
				{l s='Search for a customer' mod='prepayment'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="row">
				<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="customer" value="" />
						<span class="input-group-addon">
							<i class="icon-search"></i>
						</span>
					</div>
				</div>
				<div class="col-lg-6">
					<span class="form-control-static">{l s='Or' mod='prepayment'}&nbsp;</span>
					<a class="fancybox_customer btn btn-default" href="{$link->getAdminLink('AdminPrepaymentWallets')|escape:'html':'UTF-8'}&amp;addprepayment_wallets">
						<i class="icon-plus-sign-alt"></i>
						{l s='Add new customer' mod='prepayment'}
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div id="customers"></div>
	</div>
	<div id="last_activities">
		<button type="button" id="show_last_activities" class="btn btn-default pull-right" data-toggle="collapse" data-target="#old_last_activities">
			<i class="icon-caret-down"></i>
		</button>

		<ul id="old_last_activities_navtab" class="nav nav-tabs">
			<li class="active">
				<a data-toggle="tab">
					<i class="icon-history"></i>
					{l s='Last Activities' mod='prepayment'}
				</a>
			</li>
		</ul>
		<div id="old_last_activities" class="tab-content panel collapse in">
			<div class="tab-pane active">
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box">{l s='ID' mod='prepayment'}</span></th>
							<th><span class="title_box">{l s='Operation' mod='prepayment'}</span></th>
							<th><span class="title_box">{l s='Date' mod='prepayment'}</span></th>
							<th><span class="title_box">{l s='Price' mod='prepayment'}</span></th>
							<th><span class="title_box">{l s='Credits' mod='prepayment'}</span></th>
							<th><span class="title_box">{l s='Status' mod='prepayment'}</span></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<form class="form-horizontal" action="{$link->getAdminLink('AdminPrepaymentLastActivities')|escape:'html':'UTF-8'}" method="post" autocomplete="off">
	<div class="panel" id="movement_part" style="display:none;">
		<div class="panel-heading">
			<i class="icon-tag"></i>
			{l s='Movement' mod='prepayment'}
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3" for="deposit">
				{l s='Type' mod='prepayment'}
			</label>
			<div class="col-lg-9">
				<div class="radio">
					<label for="deposit">
						<input type="radio" name="type_movement" id="deposit" value="{PrepaymentLastActivities::CUSTOM_DEPOSIT|intval}" {if $currentObj->id_operation == PrepaymentLastActivities::CUSTOM_DEPOSIT}checked="checked"{/if}>
						{l s='Deposit' mod='prepayment'}
					</label>
				</div>
				<div class="radio">
					<label for="disbursement">
						<input type="radio" name="type_movement" id="disbursement" value="{PrepaymentLastActivities::CUSTOM_DISBURSEMENT|intval}" {if $currentObj->id_operation == PrepaymentLastActivities::CUSTOM_DISBURSEMENT}checked="checked"{/if}>
						{l s='Disbursement' mod='prepayment'}
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3" for="label">{l s='Label' mod='prepayment'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<input type="text" id="label" name="label" value="{$currentObj->getLabel()|escape:'html':'UTF-8'}" />
					<span class="input-group-addon">Fr</span>
				</div>

			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3" for="amount">{l s='Amount' mod='prepayment'}</label>
			<div class="col-lg-9">
				<div class="input-group fixed-width-md">
					<input type="text" id="amount" name="amount" value="{$currentObj->credits|floatval}" autocomplete="off">
					<span class="input-group-addon">{$currencyObj->sign|escape:'quotes':'UTF-8'}</span>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-9 col-lg-offset-3">
				<input type="hidden" value="{$currentObj->id_customer|intval}" id="id_customer" name="id_customer" />
				<input type="hidden" value="{$currentObj->id|intval}" id="id_prepayment_last_activities" name="id_prepayment_last_activities" />
				{if $currentObj->id}
				<button type="submit" name="submitUpdateprepayment_last_activities" class="btn btn-default" />
					<i class="icon-check"></i>
					{l s='Update the movement' mod='prepayment'}
				</button>
				{else}
				<button type="submit" name="submitAddprepayment_last_activities" class="btn btn-default" />
					<i class="icon-check"></i>
					{l s='Validate the movement' mod='prepayment'}
				</button>
				{/if}
			</div>
		</div>
	</div>
</form>

{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
	</div>
{/if}
