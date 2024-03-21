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

<div id="wallet_pack" class="panel product-tab">
	<h3>{l s='Credit pack' mod='prepayment'}</h3>
	<div class="form-group" id="amount">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define credits that will be given for each recharge.' mod='prepayment'}">{l s='Credits' mod='prepayment'}</span>
		</label>
		<div class="input-group col-lg-2">
			<input type="text" id="credits" name="credits" value="{$pack->credits|floatval}">
		</div>
	</div>
	<div class="form-group" id="extra_amount">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define an extra amount that will be offered for each recharge.' mod='prepayment'}">{l s='Extra credits' mod='prepayment'}</span>
		</label>
		<div class="input-group col-lg-2">
			<input type="text" id="extra_credits" name="extra_credits" value="{$pack->extra_credits|floatval}">
		</div>
	</div>
</div>
<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='prepayment'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='prepayment'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='prepayment'}</button>
	</div>
</div>
