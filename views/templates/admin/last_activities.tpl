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

<div role="tabpanel" class="tab-pane" id="last_activities">

  <div class="row">
    <div class="col-sm-6">
      <dl class="well list-detail">
				<dt>{l s='Movements' mod='prepayment'}</dt>
					<dd><span class="badge"><i class="icon-bars"></i> {$last_activity_stats.nb_activities|intval}</span></dd>
        <dt>{l s='Average credits per deposit' mod='prepayment'}</dt>
  				<dd><span class="badge badge-success"><i class="icon-check-square-o"></i> {$last_activity_stats.credits_per_deposit|floatval}</span></dd>
				<dt>{l s='Average credits per order' mod='prepayment'}</dt>
					<dd><span class="badge badge-warning"><i class="icon-check-square-o"></i> {$last_activity_stats.credits_per_order|floatval}</span></dd>
				<dt>{l s='Deposit per order rate' mod='prepayment'}</dt>
					<dd><span class="badge badge-danger"><i class="icon-minus-square"></i> 1 :{$last_activity_stats.deposit_per_order|floatval}</span></dd>
			</dl>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_viewlast_activities|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-success btn-lg">
              <i class="icon-cogs"></i> {l s='Display details..' mod='prepayment'}
            </button>
          </a>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <a href="{$href_addmovement|escape:'html':'UTF-8'}">
            <button type="button" class="btn btn-warning btn-lg">
              <i class="icon-cogs"></i> {l s='Add a new movement' mod='prepayment'}
            </button>
          </a>
        </div>
    </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 panel">
      <div class="panel-heading">
        {l s='20 last movements' mod='prepayment'}
      </div>
      <table class="table">
        <thead>
          <tr>
            <th class="center"><span class="title_box">{l s='ID' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Operation' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Customer' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Credits' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Paid' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Date' mod='prepayment'}</span></th>
            <th><span class="title_box">{l s='Action' mod='prepayment'}</span></th>
          </tr>
        </thead>
        <tbody>
          {if isset($last_activities) && count($last_activities)}
            {foreach $last_activities as $last_activity}
            <tr>
              <td class="center">{$last_activity.id_prepayment_last_activities|intval}</td>
              <td>{$last_activity.operation|escape:'html':'UTF-8'}</td>
              <td>{$last_activity.customer|escape:'html':'UTF-8'}</td>
              <td>{$last_activity.credits|escape:'quotes':'UTF-8'}</td>
              <td>{if $last_activity.paid}<span class="list-action-enable action-enabled"><i class="icon-check"></i></span>{else}<span class="list-action-enable action-disabled"><i class="icon-remove"></i></span>{/if}</td>
              <td>{$last_activity.date_add|escape:'quotes':'UTF-8'}</td>
              <td>
                  <a class="btn btn-default" href="{$last_activity.href_view|escape:'html':'UTF-8'}" title="Détails">
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
