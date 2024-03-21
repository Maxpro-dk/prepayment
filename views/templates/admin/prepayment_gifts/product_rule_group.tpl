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

<tr id="product_rule_group_{$product_rule_group_id|intval}_tr">
	<td>
		<a class="btn btn-default" href="javascript:removeProductRuleGroup({$product_rule_group_id|intval});">
			<i class="icon-remove text-danger"></i>
		</a>
	</td>
	<td>


		<div class="form-group">
			<label class="control-label col-lg-4">{l s='The cart must contain at least' mod='prepayment'}</label>
			<div class="col-lg-1">
				<input type="hidden" name="product_rule_group[]" value="{$product_rule_group_id|intval}" />
				<input class="form-control" type="text" name="product_rule_group_{$product_rule_group_id|intval}_quantity" value="{$product_rule_group_quantity|intval}" />
			</div>
			<div class="col-lg-7">
				<p class="form-control-static">{l s='product(s) matching the following rules:' mod='prepayment'}</p>
			</div>
		</div>



		<div class="form-group">
			<label class="control-label col-lg-4">{l s='Add a rule concerning' mod='prepayment'}</label>
			<div class="col-lg-4">
				<select class="form-control" id="product_rule_type_{$product_rule_group_id|intval}">
					<option value="">{l s='-- Choose --' mod='prepayment'}</option>
					<option value="products">{l s='Products' mod='prepayment'}</option>
					<option value="attributes">{l s='Attributes' mod='prepayment'}</option>
					<option value="categories">{l s='Categories' mod='prepayment'}</option>
					<option value="manufacturers">{l s='Manufacturers' mod='prepayment'}</option>
					<option value="suppliers">{l s='Suppliers' mod='prepayment'}</option>
				</select>
			</div>
			<div class="col-lg-4">
				<a class="btn btn-default" href="javascript:addProductRule({$product_rule_group_id|intval});">
					<i class="icon-plus-sign"></i>
					{l s='Add' mod='prepayment'}
				</a>
			</div>

		</div>

		{l s='The product(s) are matching one of these:' mod='prepayment'}
		<table id="product_rule_table_{$product_rule_group_id|intval}" class="table table-bordered">
			{if isset($product_rules) && $product_rules|@count}
				{foreach from=$product_rules item='product_rule'}
					{$product_rule nofilter}{* HTML, cannot escape *}
				{/foreach}
			{/if}
		</table>

	</td>
</tr>
