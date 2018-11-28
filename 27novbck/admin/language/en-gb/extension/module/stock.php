<?php
// Heading
$_['heading_title']													= 'Stock';
$_['tab_settings']                          = 'Settings';
$_['tab_theme']															= 'Theme customization';

// Text
$_['text_edit']															= 'Edit Stock Module';
$_['text_module']														= 'Extensions';
$_['text_success']													= 'Success: You have modified module stock!';
$_['entry_status']													= 'Status:';
$_['entry_limit']														= 'Default stock report limit:';
$_['entry_show_cart_quantities']						= 'Show quantities in shopping cart page:';
$_['entry_check_cart_quantities']						= 'Include cart quantities when checking product\'s availability:';
$_['entry_colorize_product']								= 'Use colors to indicate product availability:';
$_['text_colorize_tip']											= '(Click in the first field to choose a color or enter a value directly in the second field)';
$_['text_colorize_instock']									= 'Color when product is in stock';
$_['text_colorize_outofstock']							= 'Color when product is out of stock';
$_['text_combination_instock']							= 'Color when combination is in stock';
$_['text_combination_outofstock']						= 'Color when combination is out of stock';
$_['entry_update_stock_display']						= 'Check selected combination availability:';
$_['entry_update_stock_display_behaviour'] 	= 'Update display behaviour';
$_['entry_behavior_default']								= 'Replace product availability with combination availability';
$_['entry_behavior_quantities']							= 'Show both product and combination availabilities';
$_['text_limit_help']												= 'Sets the default stock limit for the stock report in the List stock page';
$_['text_check_cart_quantities_help']				= 'When adding a product to cart, the quantity check takes into account items that have already been added to cart (if any)';
$_['text_colorize_product_help']						= 'Uses a color to display product\'s availability';
$_['text_update_stock_display_help']				= 'In the product page dynamically checks the available stock when the user selects a combination of options. The product\'s availability is updated according to the selected behaviour';

$_['text_theme_customization_warning']			= ' The values below are meant to be used by the extension\'s developer for customization/integration with your theme (if needed). Please do not change these values unless you are instructed to do so.';
$_['text_replace_xpath']										= 'Find selector (XPath)';
$_['text_replace_xpath_help']								= 'XPath selector to use to find the html that contains the \'In Stock\' text. Ideally this expression should not include the \'Availability\' text. (used only when a color related feature is enabled)';
$_['text_decorate_html']										= 'Replace template (HTML)';
$_['text_decorate_html_help']								= 'HTML template to use to replace the element found by the Find XPath selector. This replacement takes place only the first time the page is loaded. The contents of the element with id=\'stock\' are also used to display the status when a product/combination is out of stock. (used only when a color related feature is enabled)';
$_['text_remove_jquery']										= 'Remove jQuery selector';
$_['text_remove_jquery_help']								= 'A jQuery selector to remove html elements that are not needed after the replacement above (if any)';
$_['text_stock_html']												= 'Stock template (HTML)';
$_['text_stock_html_help']									= 'The HTML to use when replacing the available stock (the element with id=\'stock\') in case both product and combination availabilities are displayed and the selected combination has positive stock i.e. the text \'In Stock (1)\'.';

// Error
$_['error_permission']											= 'Warning: You do not have permission to modify module stock!';
$_['error_limit_required']									= 'Stock report limit is required!';
$_['error_limit_positive']									= 'Stock report limit must be a positive number!';
$_['error_vqmod_install']										= 'It seems that VQMod is not installed properly or it is missing. Please make sure you have installed VQMod properly.';
$_['error_vqmod_version']										= 'Invalid VQMod version. Detected Opencart version: %s, VQMod version: %s. Required VQMod version: %s';
$_['error_permissions_missing']							= 'Some permissions seem to be missing. Please make sure you have assigned all permissions mentioned in the installation guide.';