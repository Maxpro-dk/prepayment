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

<div id="module-prepayment-column">
	{if $packs && count($packs)}
	<div id="deposits" class="block products_block">
		<h4 class="title_block">
	    	{l s='Deposit into wallet' mod='prepayment'}</a>
	    </h4>
		<div class="block_content">
			<div class="table-packs">
				<table>
					{foreach from=$packs item=pack}
						<tr id="{$pack.id_product|intval}">
							<td class="first">
								<span>{$pack.credits|escape:'html':'UTF-8'}</span>
								<span class="extra_credits"> {if isset($pack.extra_credits)} + {$pack.extra_credits|escape:'html':'UTF-8'} {l s='offered' mod='prepayment'} !{/if}</span>
							</td>
						</tr>
					{/foreach}
				</table>
				<div class="plnk">
				<a href="{$link->getModuleLink('prepayment','deposits')|escape:'html':'UTF-8'}" title="{l s='Recharge my wallet' mod='prepayment'}">
					<span>{l s='Recharge my wallet' mod='prepayment'}</span>
				</a>
				</div>
			</div>
		</div>
	</div>
	{/if}
	{if $gifts && count($gifts)}
	<div id="gifts" class="block products_block">
		<h4 class="title_block">
	    	{l s='Available gifts !' mod='prepayment'}</a>
	    </h4>
		<div class="block_content">
			{foreach from=$gifts item=gift}
				<div id="gift_{$gift.id_prepayment_gifts|intval}" class="gift_box">
					<i class="icon-caret-right"></i>
					<span class="content">
						{l s='Get' mod='prepayment'}<span class="content_amount">{if $gift.gift_percent > 0} {$gift.gift_percent|floatval} %{elseif isset($gift.gift_amount)} {$gift.gift_amount|escape:'html':'UTF-8'}{/if} {l s=' credits bonus ' mod='prepayment'}</span>{l s='on your next order' mod='prepayment'}*
					</span>
					<span class="content_conditions"><a href="javascript:void('')" onclick="fancybox({$gift.id_prepayment_gifts|intval});">({l s='Display conditions' mod='prepayment'})</a></span>
				</div>
				<div id="conditions_{$gift.id_prepayment_gifts|intval}" style="display:none">
					<div class="panel block-gift-conditions">
						<div class="panel-heading">
							<h3>{l s='Gift terms & conditions' mod='prepayment'}</h3>
						</div>
						<div class="panel-content">
							<h4>{l s='Get' mod='prepayment'}{if $gift.gift_percent > 0} {$gift.gift_percent|floatval} %{elseif isset($gift.gift_amount)} {$gift.gift_amount|escape:'html':'UTF-8'}{/if} {l s='credits bonus on your next order' mod='prepayment'}</h4>
							<div class="condition">
								{if isset($gift.minimum_amount)}
								<span class="item">{l s='Minimum order amount' mod='prepayment'}</span>
								<span class="value">{$gift.minimum_amount|escape:'html':'UTF-8'}{if $gift.minimum_amount_shipping|floatval == 1}{l s='shipping include' mod='prepayment'}{else} {l s='shipping exlude' mod='prepayment'}{/if}</span>
								{else}
								<span class="value">{l s='No minimum order amount required' mod='prepayment'}</span>
								{/if}
							</div>
							{if $gift.product_rule_groups|@count}
							<div class="condition">
								{foreach from=$gift.product_rule_groups item='product_rule_group'}
									<span class="item">{l s='The cart must contain at least' mod='prepayment'} {$product_rule_group.quantity|intval} {l s='product(s) matching the following rules' mod='prepayment'}</span><br/>
									{if isset($product_rule_group.product_rules) && $product_rule_group.product_rules|@count}
										{foreach from=$product_rule_group.product_rules item='product_rule'}
											<span class="item">{if $product_rule.type == 'products'}{l s='Products:' mod='prepayment'}{elseif $product_rule.type == 'categories'}{l s='Categories:' mod='prepayment'}{elseif $product_rule.type == 'manufacturers'}{l s='Manufacturers:' mod='prepayment'}{elseif $product_rule.type == 'suppliers'}{l s='Suppliers:' mod='prepayment'}{elseif $product_rule.type == 'attributes'}{l s='Attributes:' mod='prepayment'}{/if}</span>
											{foreach from=$product_rule.values item='item'}
												<span class="value">{$item|escape:'html':'UTF-8'}</span>
											{/foreach}<br/>
										{/foreach}
									{/if}
								{/foreach}
							</div>
							{/if}
							{if $gift.countries.unselected|@count}
							<div class="condition">
								<span class="item">{l s='Delivery adress' mod='prepayment'}</span>
								<span class="value">
									{foreach from=$gift.countries.selected item='country'}
										{$country.name|escape:'html':'UTF-8'},
									{/foreach}
								</span>
							</div>
							{/if}
							{if $gift.carriers.unselected|@count}
							<div class="condition">
								<span class="item">{l s='Carrier' mod='prepayment'}</span>
								<span class="value">
									{foreach from=$gift.carriers.selected item='carrier'}
										{$carrier.name|escape:'html':'UTF-8'},
									{/foreach}
								</span>
							</div>
							{/if}
							{if $gift.payments.unselected|@count}
							<div class="condition">
								<span class="item">{l s='Payment Method' mod='prepayment'}</span>
								<span class="value">
									{foreach from=$gift.payments.selected item='payment'}
										{$payment.name|escape:'html':'UTF-8'},
									{/foreach}
								</span>
							</div>
							{/if}
							<div class="condition">
								<span class="item">{l s='Available until' mod='prepayment'}</span>
								<span class="value">{dateFormat date=$gift.date_to full=0}</span>
							</div>
							{if $gift.quantity|intval > 0}
							<div class="condition">
								<span class="item">{l s='Available for the' mod='prepayment'}</span>
								<span class="value">{$gift.quantity|intval} {l s='first' mod='prepayment'}{if $gift.quantity|intval > 1} {l s='orders' mod='prepayment'}{else} {l s='order' mod='prepayment'}{/if}</span>
							</div>
							{/if}
							{if $gift.quantity_per_user|intval > 0}
							<div class="condition">
								<span class="item">{l s='Limited to' mod='prepayment'}</span>
								<span class="value">{$gift.quantity_per_user|intval} {if $gift.quantity_per_user|intval > 1}{l s='quantities' mod='prepayment'}{else}{l s='quantity' mod='prepayment'}{/if} {l s='per customer' mod=prepayment}</span>
							</div>
							{/if}
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<script type="text/javascript">
		function fancybox(idGift) {
			$.fancybox({
				'content' : $('#conditions_'+idGift).html()
			});
		}
	</script>
	{/if}
</div>
