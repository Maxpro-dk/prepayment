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

{extends file="helpers/form/form.tpl"}

{block name="other_fieldsets"}

<script type="text/javascript">

		if ($('#ids_customer').val() == '')
			ids_customer = [];
		else
			ids_customer = $('#ids_customer').val().split('|');

		$('#customer').autocomplete("ajax-tab.php", {
				delay: 100,
				minChars: 3,
				autoFill: true,
				max:100,
				matchContains: true,
				mustMatch:false,
				scroll:false,
				cacheLength:0,
	            dataType: 'json',
	            extraParams: {
					ajax : '1',
					controller : 'AdminPrepaymentWallets',
					token : '{$token|escape:'html':'UTF-8'}',
					action : 'searchCustomer'
	            },
	            parse: function(data) {
		            if (data == null || data == 'undefined')
			        	return [];
	            	var res = $.map(data, function(row) {

	            		if (jQuery.inArray(row.id, ids_customer) == -1)
		    				return {
		    					data: row,
		    					result: row.name+' - '+row.email,
		    					value: row.id
		    				}
	    			});
	    			return res;
	            },
	    		formatItem: function(item) {
	    			return item.name+' - '+item.email;
	    		}
	        }).result(function(event, item){
				$('#ids_customer').val(item.id);
	            if (typeof(ajax_running_timeout) !== 'undefined')
	            	clearTimeout(ajax_running_timeout);
			});

</script>
{/block}
