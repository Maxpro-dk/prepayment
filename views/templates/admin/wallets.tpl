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

<div role="tabpanel" class="tab-pane active" id="wallets">

  <div class="row">
    <div class="col-sm-6">
      <dl class="well list-detail">
				<dt>{l s='Opened wallets' mod='prepayment'}</dt>
					<dd><span class="badge"><i class="icon-bars"></i> {$wallet_stats.nb_wallets|intval}</span></dd>
        <dt>{l s='Opened wallets per customer' mod='prepayment'}</dt>
  				<dd><span class="badge badge-success">{Tools::ps_round($wallet_stats.wallet_per_customer|intval, 0)} %</span></dd>
				<dt>{l s='Active customers' mod='prepayment'}</dt>
					<dd><span class="badge badge-warning"><i class="icon-check-square-o"></i> {$wallet_stats.active_wallets|intval}</span></dd>
				<dt>{l s='Unactive customers' mod='prepayment'}</dt>
					<dd><span class="badge badge-danger"><i class="icon-minus-square"></i> {$wallet_stats.unactive_wallets|intval}</span></dd>
			</dl>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_viewwallets|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-success btn-lg">
              <i class="icon-cogs"></i> {l s='Display details..' mod='prepayment'}
            </button>
          </a>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_addwallet|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-warning btn-lg">
              <i class="icon-cogs"></i> {l s='Open a new wallet' mod='prepayment'}
            </button>
          </a>
        </div>
      </div>
      {if $wallet_stats.wallet_per_customer|intval != 100}
      <form action="{$link->getAdminLink('AdminPrepaymentDashboard')|escape:'html':'UTF-8'}" method="post">
      <div class="form-group">
        <div class="input-group">
            <button type="submit" name="submitOpenWallets" class="btn btn-danger btn-lg">
              <i class="icon-cogs"></i> {l s='Open wallet for all customers' mod='prepayment'}
            </button>
        </div>
      </div>
      </form>
      {/if}
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 panel">
      <div class="panel-heading">
        {l s='20 biggest wallets' mod='prepayment'}
      </div>
      <table class="table">
        <thead>
          <tr>
            <th class="center"><span class="title_box">{l s='ID' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Customer' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Balance' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Date add' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Action' mod='prepayment'}</span></th>
          </tr>
        </thead>
        <tbody>
          {if isset($wallets) && count($wallets)}
            {foreach $wallets as $wallet}
            <tr>
              <td class="center">{$wallet.id_prepayment_wallets|intval}</td>
              <td>{$wallet.customer|escape:'html':'UTF-8'}</td>
              <td>{$wallet.total_amount|escape:'html':'UTF-8'}</td>
              <td>{$wallet.date_add|escape:'quotes':'UTF-8'}</td>
              <td>
                 <a class="btn btn-default" href="{$link->getAdminLink('AdminCustomers')|escape:'html':'UTF-8'}&id_customer={$wallet.id_customer|intval}&viewcustomer" title="{l s='View' mod='prepayment'}">
                   <i class="icon-search"></i>{l s='View' mod='prepayment'}
                  </a>
              </td>
            </tr>
            {/foreach}
          {else}
            <td class="list-empty" colspan="11">
              <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No records found' mod='prepayment'}
              </div>
            </td>
          {/if}
        </tbody>
      </table>
    </div>
  </div>
</div>
