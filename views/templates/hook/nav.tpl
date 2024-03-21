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

{if $is_logged}
	<div id="module-prepayment-nav" {if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}class="display_top"{/if}>
		<div class="header_user_info">
			<a href="{$link->getModuleLink('prepayment','dashboard')|escape:'html':'UTF-8'}" title="{l s='My wallet' mod='prepayment'}">
				<i class="icon-suitcase"></i> {$balance|escape:'quotes':'UTF-8'}
			</a>
		</div>
	</div>
{/if}
