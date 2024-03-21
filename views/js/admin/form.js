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

function addProductRuleGroup()
{
	$('#product_rule_group_table').show();
	product_rule_groups_counter += 1;
	product_rule_counters[product_rule_groups_counter] = 0;

	$.get(
		'ajax-tab.php',
		{controller:'AdminPrepaymentGifts',token:currentToken,newProductRuleGroup:1,product_rule_group_id:product_rule_groups_counter},
		function(content) {
			if (content != "")
				$('#product_rule_group_table').append(content);
		}
	);
}

function removeProductRuleGroup(id)
{
	$('#product_rule_group_' + id + '_tr').remove();
}

function addProductRule(product_rule_group_id)
{
	product_rule_counters[product_rule_group_id] += 1;
	if ($('#product_rule_type_' + product_rule_group_id).val() != 0)
		$.get(
			'ajax-tab.php',
			{controller:'AdminPrepaymentGifts',token:currentToken,newProductRule:1,product_rule_type:$('#product_rule_type_' + product_rule_group_id).val(),product_rule_group_id:product_rule_group_id,product_rule_id:product_rule_counters[product_rule_group_id]},
			function(content) {
				if (content != "")
					$('#product_rule_table_' + product_rule_group_id).append(content);
			}
		);
}

function removeProductRule(product_rule_group_id, product_rule_id)
{
	$('#product_rule_' + product_rule_group_id + '_' + product_rule_id + '_tr').remove();
}

function toggleCartRuleFilter(id)
{
	if ($(id).prop('checked'))
		$('#' + $(id).attr('id') + '_div').show(400);
	else
		$('#' + $(id).attr('id') + '_div').hide(200);
}

function removeCartRuleOption(item)
{
	var id = $(item).attr('id').replace('_remove', '');
	$('#' + id + '_2 option:selected').remove().appendTo('#' + id + '_1');
}

function addCartRuleOption(item)
{
	var id = $(item).attr('id').replace('_add', '');
	$('#' + id + '_1 option:selected').remove().appendTo('#' + id + '_2');
}

function updateProductRuleShortDescription(item)
{
	/******* For IE: put a product in condition on cart rules *******/
	if(typeof String.prototype.trim !== 'function') {
	  String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	  }
	}

	var id1 = $(item).attr('id').replace('_add', '').replace('_remove', '');
	var id2 = id1.replace('_select', '');
	var length = $('#' + id1 + '_2 option').length;
	if (length == 1)
		$('#' + id2 + '_match').val($('#' + id1 + '_2 option').first().text().trim());
	else
		$('#' + id2 + '_match').val(length);
}

var restrictions = new Array('country', 'carrier', 'group', 'shop', 'payment');
for (i in restrictions)
{
	toggleCartRuleFilter($('#' + restrictions[i] + '_restriction'));
	$('#' + restrictions[i] + '_restriction').click(function() {toggleCartRuleFilter(this);});
	$('#' + restrictions[i] + '_select_remove').click(function() {removeCartRuleOption(this);});
	$('#' + restrictions[i] + '_select_add').click(function() {addCartRuleOption(this);});
}

toggleCartRuleFilter($('#product_restriction'));

$('#product_restriction').click(function() {
	toggleCartRuleFilter(this);
});

function toggleApplyDiscount(percent, amount)
{
	if (percent)
	{
		$('#apply_discount_percent_div').show(400);
	}
	else
	{
		$('#apply_discount_percent_div').hide(200);
		$('#gift_percent').val('0');
	}

	if (amount)
	{
		$('#apply_discount_amount_div').show(400);
	}
	else
	{
		$('#apply_discount_amount_div').hide(200);
		$('#gift_amount').val('0');
	}
}

$('#apply_discount_percent').click(function() {toggleApplyDiscount(true, false);});
if ($('#apply_discount_percent').prop('checked'))
	toggleApplyDiscount(true, false);

$('#apply_discount_amount').click(function() {toggleApplyDiscount(false, true);});
if ($('#apply_discount_amount').prop('checked'))
	toggleApplyDiscount(false, true);

// Main form submit
$('#prepayment_gifts_form').submit(function() {
	if ($('#customerFilter').val() == '')
		$('#id_customer').val('0');

	for (i in restrictions)
	{
		if ($('#' + restrictions[i] + '_select_1 option').length == 0)
			$('#' + restrictions[i] + '_restriction').removeAttr('checked');
		else
		{
			$('#' + restrictions[i] + '_select_2 option').each(function(i) {
				$(this).attr('selected', true);
			});
		}
	}

	$('.product_rule_toselect option').each(function(i) {
		$(this).attr('selected', true);
	});
});

$('#customerFilter')
	.autocomplete(
			'ajax-tab.php', {
			minChars: 2,
			max: 50,
			width: 500,
			selectFirst: false,
			scroll: false,
			dataType: 'json',
			formatItem: function(data, i, max, value, term) {
				return value;
			},
			parse: function(data) {
				var mytab = new Array();
				for (var i = 0; i < data.length; i++)
					mytab[mytab.length] = { data: data[i], value: data[i].cname + ' (' + data[i].email + ')' };
				return mytab;
			},
			extraParams: {
				controller: 'AdminPrepaymentGifts',
				token: currentToken,
				customerFilter: 1
			}
		}
	)
	.result(function(event, data, formatted) {
		$('#id_customer').val(data.id_customer);
		$('#customerFilter').val(data.cname + ' (' + data.email + ')');
	});

function displayCartRuleTab(tab)
{
	$('.prepayment_gifts_tab').hide();
	$('.tab-row.active').removeClass('active');
	$('#prepayment_gifts_' + tab).show();
	$('#prepayment_gifts_link_' + tab).parent().addClass('active');
	$('#currentFormTab').val(tab);
}

$('.prepayment_gifts_tab').hide();
$('.tab-row.active').removeClass('active');
$('#prepayment_gifts_' + currentFormTab).show();
$('#prepayment_gifts_link_' + currentFormTab).parent().addClass('active');

var date = new Date();
var hours = date.getHours();
if (hours < 10)
	hours = "0" + hours;
var mins = date.getMinutes();
if (mins < 10)
	mins = "0" + mins;
var secs = date.getSeconds();
if (secs < 10)
	secs = "0" + secs;
$('.datepicker').datepicker({
	prevText: '',
	nextText: '',
	dateFormat: 'yy-mm-dd ' + hours + ':' + mins + ':' + secs
});
