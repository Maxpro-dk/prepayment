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

{capture name=path}{l s='Wallet payment' mod='prepayment'}{/capture}

<h1 class="page-heading">{l s='Order summary' mod='prepayment'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='prepayment'}</p>
{else}

<form action="{$link->getModuleLink('prepayment', 'validation', [], true)|escape:'html':'UTF-8'}" method="post">
<div class="box wallet-box">
<h3 class="page-subheading">{l s='Wallet payment' mod='prepayment'}</h3>
{include file="$tpl_dir./errors.tpl"}
{if !$errors}
	<p class="cheque-indent">
        <strong class="dark">
		{l s='You have chosen to pay with your wallet.' mod='prepayment'}
		{l s='Here is a short summary of your order:' mod='prepayment'}
	    </strong>
    </p>

	<p>
		- {l s='The total amount of your order comes to:' mod='prepayment'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
			{l s='(tax incl.)' mod='prepayment'}
		{/if}

	</p>
	<p>
		- {l s='Current balance' mod='prepayment'} : <span class="price bold" style="color:#5C9939;">{displayPrice price=$balance}</span>
	</p>
	<p>
		<br />
		{if $balance|floatval + Configuration::get('WALLET_NEGATIVE_BALANCE_MAX')|floatval <= 0}
		<b>{l s='Please recharge your wallet by clicking "Recharge my wallet"' mod='prepayment'}.</b>
		{else}
		<b>{l s='Please confirm your order by clicking "I confirm my order"' mod='prepayment'}.</b>
		{/if}
	</p>
{/if}
</div>
	<p class="cart_navigation clearfix" id="cart_navigation">
		<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" class="button-exclusive btn btn-default">
			<i class="icon-chevron-left"></i> {l s='Other payment methods' mod='prepayment'}
        </a>

		<button class="button btn btn-default button-medium" type="submit">
			<span>
			{if $balance|floatval + Configuration::get('WALLET_NEGATIVE_BALANCE_MAX')|floatval < $total|floatval}
				{if (Configuration::get('WALLET_PARTIAL_PAYMENT') && ($balance|floatval + Configuration::get('WALLET_NEGATIVE_BALANCE_MAX')|floatval > 0))}
					{l s='Partial payment' mod='prepayment'}
				{else}
					{l s='Recharge my wallet' mod='prepayment'}
				{/if}
			{else}
				{l s='I confirm my order' mod='prepayment'}
			{/if}
				<i class="icon-chevron-right right"></i>
			</span>
		</button>

	</p>
</form>
{/if}
