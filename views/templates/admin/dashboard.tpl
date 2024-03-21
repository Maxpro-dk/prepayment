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

{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
    {include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
    <div id="content" class="bootstrap">
{/if}
<div id="wallet_dashboard">
  <div class="row">
    <div class="col-md-4 col-lg-3">
      <div class="panel col-lg-12">
        <div class="panel-heading">
          <i class="icon-bar-chart"></i> {l s='Views' mod='prepayment'}
      	</div>
        {include file="$tpl_overview"}
      </div>
    </div>
    <div class="col-md-8 col-lg-9">
      <div class="panel col-lg-12">
        <div class="panel-heading">
          <i class="icon-bars"></i> {l s='Dashboard' mod='prepayment'}
      	</div>
        <div class="form-group">
        	<ul class="nav nav-pills" role="tablist">
        	  <li role="presentation" class="active"><a href="#wallets" role="tab" data-toggle="tab">{l s='Wallets' mod='prepayment'}</a></li>
        	  <li role="presentation"><a href="#last_activities" role="tab" data-toggle="tab">{l s='Last Activities' mod='prepayment'}</a></li>
        	  <li role="presentation"><a href="#packs" role="tab" data-toggle="tab">{l s='Credit Packs' mod='prepayment'}</a></li>
        	  <li role="presentation"><a href="#gifts" role="tab" data-toggle="tab">{l s='Gift Rules' mod='prepayment'}</a></li>
        	</ul>
        </div>
        <div class="tab-content">
          {include file="$tpl_wallets"}
          {include file="$tpl_last_activities"}
          {include file="$tpl_packs"}
          {include file="$tpl_gifts"}
        </div>
      </div>
    </div>
  </div>
</div>
{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
    </div>
{/if}
