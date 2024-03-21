{*
* 2017 - Keyrnel SARL
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
* @author    Keyrnel
* @copyright 2017 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<section id="dash_live">
	<ul class="data_list_large">
    <li>
			<span class="data_label size_l">
				{l s='Balance' mod='prepayment'}
				<small class="text-muted"><br>
					{l s='All time' mod='prepayment'}
				</small>
			</span>
			<span class="data_value size_xxl">
				<span id="online_visitor">{$global_data.balance|escape:'html':'UTF-8'}</span>
			</span>
		</li>
		<li>
			<span class="data_label size_l">
				{l s='Tendance' mod='prepayment'}
				<small class="text-muted"><br>
					{l s='in the last 30 minutes' mod='prepayment'}
				</small>
			</span>
			<span class="data_value size_xxl">
				<span id="online_visitor">{$global_data.tendance|intval} %</span>
			</span>
		</li>
		<li>
			<span class="data_label size_l">
				{l s='Movements' mod='prepayment'}
				<small class="text-muted"><br>
					{l s='in the last 30 minutes' mod='prepayment'}
				</small>
			</span>
			<span class="data_value size_xxl">
				<span id="active_shopping_cart">{$global_data.movements|intval}</span>
			</span>
		</li>
	</ul>
</section>

<section id="dash_pending" class="">
	<header><i class="icon-time"></i>{l s='Currently pending' mod='prepayment'}</header>
	<ul class="data_list">
		<li>
			<span class="data_label"><a href="{$link->getAdminLink('AdminOrders')|escape:'html':'UTF-8'}">{l s='Deposits' mod='prepayment'}</a></span>
			<span class="data_value size_l">
				<span id="pending_deposits">{$global_data.pending_deposits|intval}</span>
			</span>
		</li>
	</ul>
</section>

<section id="dash_access" class="">
  <div class="data_list">
    <div class="btn-group">
      <button type="button" class="btn btn-danger btn-lg dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{l s='Quick Access' mod='prepayment'}
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><a href="{$href_viewwallets|escape:'html':'UTF-8'}">{l s='Wallets' mod='prepayment'}</a></li>
        <li><a href="{$href_viewlast_activities|escape:'html':'UTF-8'}">{l s='Last Activities' mod='prepayment'}</a></li>
        <li><a href="{$href_viewpacks|escape:'html':'UTF-8'}">{l s='Packs' mod='prepayment'}</a></li>
        <li><a href="{$href_viewgifts|escape:'html':'UTF-8'}">{l s='Gift rules' mod='prepayment'}</a></li>
        <li><a href="{$href_viewsettings|escape:'html':'UTF-8'}"><i class="icon-cogs"></i>{l s='Settings' mod='prepayment'}</a></li>
      </ul>
    </div>
  </div>
</section>

<section id="dash_data" class="">
	<header><i class="icon-time"></i>{l s='Global data' mod='prepayment'}</header>
	<ul class="data_list">
		<li>
			<span class="data_label">{l s='Amount earned' mod='prepayment'}</span>
			<span class="data_value size_l">
				<span id="amount_earned">{$global_data.amount_earned|escape:'html':'UTF-8'}</span>
			</span>
		</li>
		<li>
			<span class="data_label">{l s='Deposits' mod='prepayment'}</span>
			<span class="data_value size_l">
				<span id="total_deposits">{displayPrice price=$global_data.total_deposits}</span>
			</span>
		</li>
	    <li>
	      <span class="data_label">{l s='Orders' mod='prepayment'}</span>
	      <span class="data_value size_l">
	        <span id="total_orders">{displayPrice price=$global_data.total_orders}</span>
	      </span>
	    </li>
		<li>
	      <span class="data_label">{l s='Refunds' mod='prepayment'}</span>
	      <span class="data_value size_l">
	        <span id="total_deposits">{displayPrice price=$global_data.total_refunds}</span>
	      </span>
	    </li>
		<li>
	      <span class="data_label">{l s='Disbursements' mod='prepayment'}</span>
	      <span class="data_value size_l">
	        <span id="total_disbursements">{displayPrice price=$global_data.total_disbursements}</span>
	      </span>
	    </li>
		<li>
	      <span class="data_label">{l s='Gifts' mod='prepayment'}</span>
	      <span class="data_value size_l">
	        <span id="total_disbursements">{displayPrice price=$global_data.total_gifts}</span>
	      </span>
	    </li>
	</ul>
</section>
