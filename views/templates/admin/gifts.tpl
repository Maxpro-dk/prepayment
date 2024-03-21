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

<div role="tabpanel" class="tab-pane" id="gifts">
  <div class="row">
    <div class="col-sm-6">
      <dl class="well list-detail">
				<dt>{l s='Most used gift rule' mod='prepayment'}</dt>
					<dd><span class="badge"><i class="icon-bars"></i> {if !empty($gift_stats.most_used.name)}{$gift_stats.most_used.name|escape:'quotes':'UTF-8'}{else}{l s='No rules used' mod='prepayment'}{/if}</span></dd>
        <dt>{l s='Average credits per gift rule' mod='prepayment'}</dt>
  				<dd><span class="badge badge-success">{$gift_stats.credits_per_gift|floatval}</span></dd>
				<dt>{l s='Number of gift rules used' mod='prepayment'}</dt>
					<dd><span class="badge badge-warning"><i class="icon-check-square-o"></i> {$gift_stats.gifts_used|intval}</span></dd>
				<dt>{l s='Unactive gift rule' mod='prepayment'}</dt>
					<dd><span class="badge badge-danger"><i class="icon-minus-square"></i> {$gift_stats.expired_gifts|intval}</span></dd>
			</dl>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_viewgifts|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-success btn-lg">
              <i class="icon-cogs"></i> {l s='Display details..' mod='prepayment'}
            </button>
          </a>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_addgift|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-warning btn-lg">
              <i class="icon-cogs"></i> {l s='Add a new gift rule' mod='prepayment'}
            </button>
          </a>
        </div>
    </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 panel">
      <div class="panel-heading">
        {l s='Active gift rules' mod='prepayment'}
      </div>
      <table class="table">
        <thead>
          <tr>
            <th class="center"><span class="title_box">{l s='ID' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Name' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='priority' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Date to' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Reduction' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Action' mod='prepayment'}</span></th>
          </tr>
        </thead>
        <tbody>
          {if isset($gifts) && count($gifts)}
            {foreach $gifts as $gift}
            <tr>
              <td class="center">{$gift.id_prepayment_gifts|intval}</td>
              <td>{$gift.name|escape:'quotes':'UTF-8'}</td>
              <td>{$gift.priority|intval}</td>
              <td>{$gift.date_to|escape:'html':'UTF-8'}</td>
              <td>{$gift.reduction|escape:'html':'UTF-8'}</td>
              <td>
                 <a class="btn btn-default" href="{$link->getAdminLink('AdminPrepaymentGifts')|escape:'html':'UTF-8'}&id_prepayment_gifts={$gift.id_prepayment_gifts|intval}&updateprepayment_gifts" title="{l s='View' mod='prepayment'}">
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
