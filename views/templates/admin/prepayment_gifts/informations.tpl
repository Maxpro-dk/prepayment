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
	<label class="control-label col-lg-3 required">
		<span class="label-tooltip" data-toggle="tooltip"
		title="{l s='This will be displayed in the cart summary, as well as on the invoice.' mod='prepayment'}">
			{l s='Name' mod='prepayment'}
		</span>
	</label>
	<div class="col-lg-8">
		{foreach from=$languages item=language}
		{if $languages|count > 1}
		<div class="row">
			<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $id_lang_default}style="display:none"{/if}>
				<div class="col-lg-9">
		{/if}
					<input id="name_{$language.id_lang|intval}" type="text"  name="name_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, 'name', $language.id_lang|intval)|escape:'html':'UTF-8'}">
		{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$language.iso_code|escape:'quotes':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'quotes'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		</div>
		{/if}
		{/foreach}
	</div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
		title="{l s='For your eyes only. This will never be displayed to the customer.' mod='prepayment'}">
			{l s='Description' mod='prepayment'}
		</span>
	</label>
	<div class="col-lg-8">
		{foreach from=$languages item=language}
		{if $languages|count > 1}
		<div class="row">
			<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $id_lang_default}style="display:none"{/if}>
				<div class="col-lg-9">
		{/if}
					<textarea name="description_{$language.id_lang|intval}" rows="4" class="textarea-autosize">{$currentTab->getFieldValue($currentObject, 'description', $language.id_lang|intval)|escape}</textarea>
		{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$language.iso_code|escape:'quotes':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'quotes'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		</div>
		{/if}
		{/foreach}
	</div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
		title="{l s='Gift rules are applied by priority. A gift rule with a priority of "1" will be processed before a gift rule with a priority of "2".' mod='prepayment'}">
			{l s='Priority' mod='prepayment'}
		</span>
	</label>
	<div class="col-lg-1">
		<input type="text" class="input-mini" name="priority" value="{$currentTab->getFieldValue($currentObject, 'priority')|intval}" />
	</div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">{l s='Status' mod='prepayment'}</label>
	<div class="col-lg-9">
		<span class="switch prestashop-switch fixed-width-lg">
			<input type="radio" name="active" id="active_on" value="1" {if $currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
			<label class="t" for="active_on">{l s='Yes' mod='prepayment'}</label>
			<input type="radio" name="active" id="active_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
			<label class="t" for="active_off">{l s='No' mod='prepayment'}</label>
			<a class="slide-button btn"></a>
		</span>
	</div>
</div>
