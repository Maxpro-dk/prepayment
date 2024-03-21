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

{if version_compare($smarty.const._PS_VERSION_, '1.7', '<')}
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
		{l s='My account' mod='prepayment'}
	</a>
	<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>
	<span class="navigation_page">{l s='My wallet' mod='prepayment'}</span>
{/capture}
{/if}

<h1 class="page-heading bottom-indent">{l s='My wallet' mod='prepayment'}</h1>

{if $errors_nb > 0}
	{$errors_rendered nofilter}{* HTML, cannot escape *}
{/if}

<p class="info-title">{l s='All about your wallet' mod='prepayment'}<br/>
{l s='The simplest payement method for easy orders' mod='prepayment'}
</p>

{if $balance && !$is_active}
	<p>{l s='Your wallet is currently disabled' mod='prepayment'}</p>
{/if}

<div class="block-center" id="block-wallet">
	{if !$balance}
		<div class="col-md-4">
			<form id="submitWallet" action="{$link->getModuleLink('prepayment', 'dashboard')|escape:'html':'UTF-8'}" method="post">
				<button type="submit" class="button lnk_view btn btn-default" name="submitWallet">
					<span><i class="icon-ticket"></i> {l s='Open my wallet' mod='prepayment'}</span>
				</button>
			</form>
		</div>
	{else}
		<div class="block-balance">
				<span class="balance">{l s='I have' mod='prepayment'}<span class="credits-left"> {$balance|escape:'quotes':'UTF-8'} </span>{l s='left' mod='prepayment'}</span>
		</div>
		{if $is_active}
		<div class="block-buy">
			<a href="{$link->getModuleLink('prepayment','deposits')|escape:'html':'UTF-8'}" title="{l s='Recharge my wallet' mod='prepayment'}" class="btn btn-default button button-small">
				<span>{l s='Recharge my wallet' mod='prepayment'}<i class="icon-chevron-right right"></i></span>
			</a>
		</div>
		{/if}
		<div class="block-history">
			<span class="title">{l s='Wallet history list' mod='prepayment'}</span>
			<table id="order-list" class="table table-bordered footab">
				<thead>
					<tr>
						<th class="first_item" data-sort-ignore="true">{l s='Operation' mod='prepayment'}</th>
						<th class="item" data-sort-ignore="true">{l s='Reference' mod='prepayment'}</th>
						<th class="item" data-sort-ignore="true">{l s='Date' mod='prepayment'}</th>
						<th data-sort-ignore="true" class="item">{l s='Order price' mod='prepayment'}</th>
						<th data-sort-ignore="true" class="item">{l s='Credits' mod='prepayment'}</th>
						<th data-sort-ignore="true" class="last_item">{l s='Status' mod='prepayment'}</th>
					</tr>
				</thead>
				<tbody>
				{if $history_list && count($history_list)}
					{foreach from=$history_list item=history name=myLoop}
						<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
						<td>
							<span class="operation">
								{$history.operation|escape:'html':'UTF-8'}
							</span>
							{if isset($history.label) && !empty($history.label)}
							<span class="label-operation">
								{$history.label|escape:'html':'UTF-8'}
							</span>
							{/if}
							{if ($history.is_partial)}
							<span class="label-operation">
								{l s='Partial payment' mod='prepayment'}
							</span>
							{/if}
						</td>
						<td class="history_link bold">
							<span>
								{if isset($history.reference) && !empty($history.reference)}{$history.reference|escape:'html':'UTF-8'}{else} -- {/if}
							</span>
						</td>
						<td data-value="{$history.date_add|escape:'quotes':'UTF-8'}" class="history_date bold">
							{dateFormat date=$history.date_add full=1}
						</td>
						<td class="history_price" data-value="{$history.price|floatval}">
							<span class="price">
								{if $history.price > 0}{$history.display_price|escape:'html':'UTF-8'}{else} -- {/if}
							</span>
							{if ($history.is_partial)}
							<span class="label-operation">
								{if $history.partial > 0}{$history.display_partial|escape:'html':'UTF-8'} {l s='remaining to be paid' mod='prepayment'}{else} -- {/if}
							</span>
							{/if}
						</td>
						<td class="history_price" data-value="{$history.credits|floatval}">
							<span class="credits">
								{if $history.credits > 0}{$history.display_credits|escape:'html':'UTF-8'}{else} -- {/if}
							</span>
						</td>
						<td  class="history_state">
							{if $history.paid == 0}{l s='Pending' mod='prepayment'}{else}{l s='Done' mod='prepayment'}{/if}
							{if ($history.is_partial)}
							<span class="label-operation">
								{if isset($history.link_order)}<a href="{$history.link_order|escape:'html':'UTF-8'}">{l s='Pay' mod='prepayment'}</a>{/if}
								<a href="{$link->getModuleLink('prepayment','dashboard', ['deletePartial' => $history.id_prepayment_last_activities|intval])|escape:'html':'UTF-8'}">{l s='Cancel' mod='prepayment'}</a>
							</span>
							{/if}
						</td>
						</tr>
					{/foreach}
				{else}
					<tr>
						<td colspan="6">{l s='No records found' mod='prepayment'}</td>
					</tr>
				{/if}
				</tbody>
			</table>
		</div>
	{/if}
</div>
