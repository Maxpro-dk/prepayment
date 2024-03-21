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

<tr id="product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_tr">
	<td>
		<input type="hidden" name="product_rule_{$product_rule_group_id|intval}[]" value="{$product_rule_id|intval}" />
		<input type="hidden" name="product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_type" value="{$product_rule_type|escape:'html':'UTF-8'}" />
		{* Everything is on a single line in order to avoid a empty space between the [ ] and the word *}
		[{if $product_rule_type == 'products'}{l s='Products:' mod='prepayment'}{elseif $product_rule_type == 'categories'}{l s='Categories:' mod='prepayment'}{elseif $product_rule_type == 'manufacturers'}{l s='Manufacturers:' mod='prepayment'}{elseif $product_rule_type == 'suppliers'}{l s='Suppliers:' mod='prepayment'}{elseif $product_rule_type == 'attributes'}{l s='Attributes:' mod='prepayment'}{/if}]
	</td>
	<td>
		<input type="text" id="product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_match" value="" disabled="disabled" />
	</td>
	<td>
		<a class="btn btn-default" id="product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_choose_link" href="#product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_choose_content">
			<i class="icon-list-ul"></i>
			{l s='Choose' mod='prepayment'}
		</a>
		<div>
			<div id="product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_choose_content">
				{$product_rule_choose_content nofilter}{* HTML, cannot escape *}
			</div>
		</div>
	</td>
	<td class="text-right">
		<a class="btn btn-default" href="javascript:removeProductRule({$product_rule_group_id|intval}, {$product_rule_id|intval});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>

<script type="text/javascript">
	$('#product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_choose_content').parent().hide();
	$("#product_rule_{$product_rule_group_id|intval}_{$product_rule_id|intval}_choose_link").fancybox({
		autoDimensions: false,
		autoSize: false,
		width: 600,
		height: 250});
	$(document).ready(function() { updateProductRuleShortDescription($('#product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_add')); });
</script>
