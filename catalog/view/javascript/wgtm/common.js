function waddtocart(product_id, quantity = 1){
	$.ajax({
		url: 'index.php?route=extension/wgoogletagmanger&product_id='+product_id,
		dataType: 'json',
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json){
			dataLayer.push({
				'event': 'enhanceEcom addToCart',
				'ecommerce': {
					'currencyCode': json['currencycode'],
					'add': {
						'products': [
							{
							'name': json['name'],
							'id': product_id,
							'price': json['price'],
							'brand': json['manufacturer'],
							'quantity': quantity,
							'category': json['categories']
							}
						]
					}
				}
			});
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}
function wremovetocart(product_id, quantity){
	$.ajax({
		url: 'index.php?route=extension/wgoogletagmanger&product_id='+product_id,
		dataType: 'json',
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json){
			dataLayer.push({
				'event': 'enhanceEcom productRemoveFromCart',
				'ecommerce': {
					'currencyCode': json['currencycode'],
					'remove': {
						'products': [
							{
							'name': json['name'],
							'id': product_id,
							'price': json['price'],
							'brand': json['manufacturer'],
							'quantity': quantity,
							'category': json['categories']
							}
						]
					}
				}
			});
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}
function waddtowishlist(product_id){
	$.ajax({
		url: 'index.php?route=extension/wgoogletagmanger&product_id='+product_id,
		dataType: 'json',
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json){
			dataLayer.push({
				'event': 'enhanceEcom addToWishlist',
				'ecommerce': {
					'currencyCode': json['currencycode'],
					'add': {
						'products': [
							{
							'name': json['name'],
							'id': product_id,
							'price': json['price'],
							'brand': json['manufacturer'],
							'category': json['categories']
							}
						]
					}
				}
			});
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}
function waddtocompare(product_id){
	$.ajax({
		url: 'index.php?route=extension/wgoogletagmanger&product_id='+product_id,
		dataType: 'json',
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json){
			dataLayer.push({
				'event': 'enhanceEcom addToCompare',
				'ecommerce': {
					'currencyCode': json['currencycode'],
					'add': {
						'products': [
							{
							'name': json['name'],
							'id': product_id,
							'price': json['price'],
							'brand': json['manufacturer'],
							'category': json['categories']
							}
						]
					}
				}
			});
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}
function productclick(product_id, href = '#',position='1'){
	$.ajax({
		url: 'index.php?route=extension/wgoogletagmanger&product_id='+product_id,
		dataType: 'json',
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json){
			dataLayer.push({
				'event': 'enhanceEcom productClick',
				'ecommerce': {
					'currencyCode': json['currencycode'],
					'click': {
						'actionField': {'list': 'Popular Products'},
						'products': [
							{
							'name': json['name'],
							'id': product_id,
							'price': json['price'],
							'brand': json['manufacturer'],
							'category': json['categories'],	
							'position': position
							}
						]
					}
				}
			});
			window.location.href = href;
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}