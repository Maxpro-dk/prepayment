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
{if $status == 'ok'}
	<div class="box">
		<p class="cheque-indent">
			<strong class="dark">{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='prepayment'}</strong>
		<br /><br />{l s='The sum of' mod='prepayment'}<span class="price"> <strong>{$total_to_pay|escape:'quotes':'UTF-8'}</strong></span> {l s='has been debited from you wallet' mod='prepayment'}
		<br /><br /> <strong>{l s='Your order will be sent in the shortest time.' mod='prepayment'}</strong>
		<br />{l s='If you have questions, comments or concerns, please contact our' mod='prepayment'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='prepayment'}</a>.
		</p>
	</div>
{else}
	<p class="alert alert-warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='prepayment'}
		<a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='prepayment'}</a>.
	</p>
{/if}
