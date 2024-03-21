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

{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
	<div id="content" class="bootstrap">
{/if}
<div class="col-lg-12">
	<div class="panel">
		<h3><i class="icon-tag"></i> {l s='Gift rule' mod='prepayment'}</h3>
		<div class="ruleTabs">
			<ul class="tab nav nav-tabs">
				<li class="tab-row">
					<a class="tab-page" id="cart_rule_link_informations" href="javascript:displayCartRuleTab('informations');"><i class="icon-info"></i> {l s='Information' mod='prepayment'}</a>
				</li>
				<li class="tab-row">
					<a class="tab-page" id="cart_rule_link_conditions" href="javascript:displayCartRuleTab('conditions');"><i class="icon-random"></i> {l s='Conditions' mod='prepayment'}</a>
				</li>
				<li class="tab-row">
					<a class="tab-page" id="cart_rule_link_actions" href="javascript:displayCartRuleTab('actions');"><i class="icon-wrench"></i> {l s='Actions' mod='prepayment'}</a>
				</li>
			</ul>
		</div>
		<form action="{$currentIndex|escape:'html':'UTF-8'}&amp;token={$currentToken|escape:'quotes':'UTF-8'}&amp;addprepayment_gifts" id="prepayment_gifts_form" class="form-horizontal" method="post">
			{if $currentObject->id}<input type="hidden" name="id_prepayment_gifts" value="{$currentObject->id|intval}" />{/if}
			<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
			<div id="prepayment_gifts_informations" class="panel prepayment_gifts_tab">
				{include file="$tpl_informations"}
			</div>
			<div id="prepayment_gifts_conditions" class="panel prepayment_gifts_tab">
				{include file="$tpl_conditions"}
			</div>
			<div id="prepayment_gifts_actions" class="panel prepayment_gifts_tab">
				{include file="$tpl_actions"}
			</div>
			<button type="submit" class="btn btn-default pull-right" name="submitAddprepayment_gifts" id="{$table|escape:'quotes':'UTF-8'}_form_submit_btn">{l s='Save' mod='prepayment'}
			</button>
			<!--<input type="submit" value="{l s='Save and stay' mod='prepayment'}" class="button" name="submitAddprepayment_giftsAndStay" id="" />-->
		</form>
	</div>
</div>
{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
	</div>
{/if}

<script type="text/javascript">
	var product_rule_groups_counter = {if isset($product_rule_groups_counter)}{$product_rule_groups_counter|intval}{else}0{/if};
	var product_rule_counters = new Array();
	var currentToken = "{$currentToken|escape:'quotes':'UTF-8'}";
	var currentFormTab = "{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'quotes':'UTF-8'}{else}informations{/if}";

	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k|intval}] = {
			id_lang: {$language.id_lang|intval},
			iso_code: "{$language.iso_code|escape:'quotes':'UTF-8'}",
			name: "{$language.name|escape:'quotes':'UTF-8'}"
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default|intval});
</script>
<script type="text/javascript" src="../modules/prepayment/views/js/admin/form.js"></script>
