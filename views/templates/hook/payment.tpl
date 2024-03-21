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

<div id="module-prepayment-payment" class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module">
			<a class="wallet" href="{$link->getModuleLink('prepayment', 'payment', [], true)|escape:'html':'UTF-8'}" title="{l s='Pay with my wallet' mod='prepayment'}">
				{l s='Pay with my wallet' mod='prepayment'} <span>{l s='(order processing will be shorter)' mod='prepayment'}</span>
			</a>
		</p>
   </div>
</div>
