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

<section>
  <p>{l s='Your wallet will be debited following these rules:' mod='prepayment'}
    <dl>
      <dt>{l s='Current wallet balance' mod='prepayment'}</dt>
      <dd>{$current_balance|escape:'html':'UTF-8'}</dd>
      <dt>{l s='Amount debited' mod='prepayment'}{if $is_partial}<br/>{l s='Partial payment (shipping cost excluded)' mod='prepayment'}{/if}</dt>
      <dd>{$total_order|escape:'html':'UTF-8'}</dd>
      <dt>{l s='Remaining wallet balance' mod='prepayment'}</dt>
      <dd>{$total_balance|escape:'html':'UTF-8'}</dd>
	  {if $is_partial && $total_to_be_paid|intval > 0}
	  <dt>{l s='Remaining to paid with another payment method' mod='prepayment'}</dt>
      <dd>{$total_to_be_paid|escape:'html':'UTF-8'}</dd>
	  {/if}
    </dl>
  </p>
</section>
