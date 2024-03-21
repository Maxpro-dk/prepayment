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

<div role="tabpanel" class="tab-pane" id="packs">

  <div class="row">
    <div class="col-sm-6">
      <dl class="well list-detail">
				<dt>{l s='Bought packs' mod='prepayment'}</dt>
					<dd><span class="badge"><i class="icon-bars"></i> {$packs_stats.bought_packs|intval}</span></dd>
        <dt>{l s='Most used deposit' mod='prepayment'}</dt>
  				<dd><span class="badge badge-success">{if !empty($packs_stats.best_sale)}{$packs_stats.best_sale.name|escape:'quotes':'UTF-8'}{else}{l s='No sales' mod='prepayment'}{/if}</span></dd>
				<dt>{l s='Average credits per pack' mod='prepayment'}</dt>
					<dd><span class="badge badge-warning"><i class="icon-check-square-o"></i> {convertPrice price=$packs_stats.average_credits}</span></dd>
				<dt>{l s='Unactive packs' mod='prepayment'}</dt>
					<dd><span class="badge badge-danger"><i class="icon-minus-square"></i> {$packs_stats.unactive_packs|intval}</span></dd>
			</dl>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_viewpacks|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-success btn-lg">
              <i class="icon-cogs"></i> {l s='Display details..' mod='prepayment'}
            </button>
          </a>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_addpack|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-warning btn-lg">
              <i class="icon-cogs"></i> {l s='Add a new credit pack' mod='prepayment'}
            </button>
          </a>
        </div>
    </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 panel">
      <div class="panel-heading">
        {l s='Active packs' mod='prepayment'}
      </div>
      <table class="table">
        <thead>
          <tr>
            <th class="center"><span class="title_box">{l s='ID' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Name' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Credits' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Extra credits' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Action' mod='prepayment'}</span></th>
          </tr>
        </thead>
        <tbody>
          {if count($packs)}
            {foreach $packs as $pack}
            <tr>
              <td class="center">{$pack.id_prepayment_packs|intval}</td>
              <td>{$pack.name|escape:'html':'UTF-8'}</td>
              <td>{$pack.credits|escape:'html':'UTF-8'}</td>
              <td>{$pack.extra_credits|escape:'html':'UTF-8'}</td>
              <td>
                 <a class="btn btn-default" href="{$link->getAdminLink('AdminPrepaymentPacks')|escape:'html':'UTF-8'}&id_prepayment_packs={$pack.id_prepayment_packs|intval}&updateprepayment_packs" title="{l s='View' mod='prepayment'}">
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
