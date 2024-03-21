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
	<span class="navigation_page">{l s='Deposits funds' mod='prepayment'}</span>
{/capture}
{/if}

{if $errors_nb > 0}
	{$errors_rendered nofilter}{* HTML, cannot escape *}
{/if}

<div class="block_buy">
<h1>{l s='Deposit funds into your wallet' mod='prepayment'}</h1>
<h2>{l s='Choose a credit pack below to credit your wallet' mod='prepayment'}</h2>
{if isset($packs) && count($packs)}
	<ul class="pack-list grid row">
		{foreach from=$packs item=pack name=loop}
			<li id="{$pack.id_product|intval}" class="col-lg-3 col-md-4 col-xs-12 block_pack">
				<div class="pack-container">
					<div class="pack-title">
						<i class="icon-th-large"></i><span>{$pack.name|escape:'quotes':'UTF-8'}</span>
					</div>
					<div class="pack-credits" data-value="{$pack.credits|floatval}">
						<span>{Tools::displayPrice($pack.credits|floatval, $currency)}
						{if $pack.extra_credits>0}<br/><span class="extra_credits">+ {Tools::displayPrice($pack.extra_credits|floatval, $currency)} {l s='offered' mod='prepayment'} !</span>{/if}</span>
					</div>
					<div class="pack-select">
						<input type="radio" id="pack_{$pack.id_product|intval}" name="selectPack" value="{$pack.id_product|intval}" {if $smarty.foreach.loop.first}checked="checked"{/if}>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
	<div class="pack-button">
		<form id="buy_block" action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
			<p class="hidden">
				<input type="hidden" name="token" value="{$static_token|escape:'html':'UTF-8'}" />
				<input type="hidden" name="id_product" value="" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
			</p>
			<div class="box-info-product">
				<p id="add_to_cart">
					<button type="submit" name="Submit" class="exclusive">
						<span>{l s='Make a deposit' mod='prepayment'}</span>
					</button>
				</p>
			</div>
		</form>
	</div>
{else}
<p>{l s='Deposit money is not available yet' mod='prepayment'}</p>
{/if}
</div>

<script type="text/javascript">

</script>
