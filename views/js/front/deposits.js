/*
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
*/

$(function() {
	var idProduct = $('input[name="selectPack"]:checked').val();
	$("#product_page_product_id").val(idProduct);

	$('li.block_pack').on('click', function(e) {
		e.preventDefault();
		$('li').each(function() {
			$(this).find('.radio span').removeClass('checked');
			$(this).find('input[type="radio"]').prop('checked',false);
			$(this).children().removeClass('hover');
		});
		$(this).find('.radio span').addClass('checked');
		$(this).find('input[type="radio"]').prop('checked',true);
		$(this).children().addClass('hover');
		var idProduct = $(this).attr('id');
		$("#product_page_product_id").val(idProduct);
		return false;
	});
});
