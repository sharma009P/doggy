//==============================================================================
// Square Payment Gateway v302.2
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

function getQueryVariable(variable) {
	var vars = window.location.search.substring(1).split('&');
	for (i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');
		if (pair[0] == variable) return pair[1];
	}
	return false;
}

function squareCapture(element, transaction_id) {
	var token = getQueryVariable('token');
	token = (token) ? '&token=' + token : '&user_token=' + getQueryVariable('user_token');
	
	element.after('<span id="please-wait" style="font-size: 11px"> Please wait...</span>');
	
	$.get('index.php?route=extension/payment/square/capture&transaction_id=' + transaction_id + token,
		function(error) {
			$('#please-wait').remove();
			if (error) {
				alert(error);
			} else {
				element.prev().html('Yes');
				element.remove();
			}
		}
	);
}

function squareRefund(element, charge_amount, charge_currency, transaction_id, tender_id) {
	var token = getQueryVariable('token');
	token = (token) ? '&token=' + token : '&user_token=' + getQueryVariable('user_token');
	
	amount = prompt('Enter the amount to refund (in ' + charge_currency + '):', charge_amount);
	
	if (amount != null && amount > 0) {
		element.after('<span id="please-wait" style="font-size: 11px"> Please wait...</span>');
		$.get('index.php?route=extension/payment/square/refund&amount=' + amount + '&currency=' + charge_currency + '&transaction_id=' + transaction_id + '&tender_id=' + tender_id + token,
			function(error) {
				$('#please-wait').remove();
				if (error) {
					alert(error);
				} else {
					alert('Success!');
					$('#history').load('index.php?route=sale/order/history&order_id=' + getQueryVariable('order_id') + token);
				}
			}
		);
	}
}