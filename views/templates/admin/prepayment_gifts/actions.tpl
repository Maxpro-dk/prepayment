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

<div class="form-group">
	<label class="control-label col-lg-3">{l s='Apply a discount' mod='prepayment'}</label>
	<div class="col-lg-9">
		<div class="radio">
			<label for="apply_discount_percent">
				<input type="radio" name="apply_discount" id="apply_discount_percent" value="percent" {if $currentTab->getFieldValue($currentObject, 'gift_percent')|floatval > 0}checked="checked"{/if} />
				{l s='Percent (%)' mod='prepayment'}
			</label>
		</div>
		<div class="radio">
			<label for="apply_discount_amount">
				<input type="radio" name="apply_discount" id="apply_discount_amount" value="amount" {if $currentTab->getFieldValue($currentObject, 'gift_amount')|floatval > 0}checked="checked"{/if} />
				{l s='Amount' mod='prepayment'}
			</label>
		</div>
	</div>
</div>

<div id="apply_discount_percent_div" class="form-group">
	<label class="control-label col-lg-3">{l s='Value' mod='prepayment'}</label>
	<div class="col-lg-9">
		<div class="input-group col-lg-2">
			<span class="input-group-addon">%</span>
			<input type="text" id="gift_percent" class="input-mini" name="gift_percent" value="{$currentTab->getFieldValue($currentObject, 'gift_percent')|floatval}" />
		</div>
		<span class="help-block"><i class="icon-warning-sign"></i> {l s='Does not apply to the shipping costs' mod='prepayment'}</span>
	</div>
</div>

<div id="apply_discount_amount_div" class="form-group">
	<label class="control-label col-lg-3">{l s='Amount' mod='prepayment'}</label>
	<div class="col-lg-7">
		<div class="row">
			<div class="col-lg-4">
				<input type="text" id="gift_amount" name="gift_amount" value="{$currentTab->getFieldValue($currentObject, 'gift_amount')|floatval}" onchange="this.value = this.value.replace(/,/g, '.');" />
			</div>
			<div class="col-lg-4">
				<select name="gift_currency" >
				{foreach from=$currencies item='currency'}
					<option value="{$currency.id_currency|intval}" {if $currentTab->getFieldValue($currentObject, 'gift_currency') == $currency.id_currency || (!$currentTab->getFieldValue($currentObject, 'gift_currency') && $currency.id_currency == $defaultCurrency)}selected="selected"{/if}>{$currency.iso_code|escape:'quotes':'UTF-8'}</option>
				{/foreach}
				</select>
			</div>
			<div class="col-lg-4">
				<select name="gift_tax" >
					<option value="0" {if $currentTab->getFieldValue($currentObject, 'gift_tax') == 0}selected="selected"{/if}>{l s='Tax excluded' mod='prepayment'}</option>
					<option value="1" {if $currentTab->getFieldValue($currentObject, 'gift_tax') == 1}selected="selected"{/if}>{l s='Tax included' mod='prepayment'}</option>
				</select>
			</div>
		</div>
	</div>
</div>
