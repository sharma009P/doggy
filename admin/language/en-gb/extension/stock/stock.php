<?php
// Heading
$_['sm_heading_title']          									= 'Stock'; 
$_['sm_heading_report_title']          						= 'Stock Report'; 
$_['sm_heading_combinations_title']          			= 'Stock Combinations'; 

// Text  
$_['sm_text_list']              									= 'Stock List';
$_['sm_text_combinations_list']              			= 'Stock Combination List';
$_['sm_text_edit']              									= 'Edit Stock';
$_['sm_text_success']           									= 'Success: You have modified product stock!';
$_['sm_text_notfound']														= 'The specified product was not found!';
$_['sm_text_stock_available']											= 'Stock availability (<%s items)';
$_['sm_text_stock_report_limit']									= 'Combinations having stock less than: ';
$_['sm_text_filter']															= 'Filter';
$_['sm_text_no_results']													= 'No results!';

// Column
$_['sm_column_name']            									= 'Product Name';
$_['sm_column_model']           									= 'Model';
$_['sm_column_image']           									= 'Image';
$_['sm_column_quantity']        									= 'Quantity';
$_['sm_column_sku']																= 'SKU';
$_['sm_column_status']        										= 'Status';
$_['sm_column_action']          									= 'Action';
$_['sm_column_product_model']           					= 'Product Model';
$_['sm_column_product_quantity']									= 'Product Quantity';
$_['sm_column_comb_quantity']           					= 'Combination Quantity';
$_['sm_column_option_value']      								= 'Option value';

// Button
$_['sm_button_stock_report']											= 'Generate report';
$_['sm_button_stock_combinations']								= 'Combinations';
$_['sm_button_stock_report_xl']										= 'Download in Excel format';
$_['sm_button_collapse']													= 'Collapse';
$_['sm_button_expand']														= 'Expand';
$_['sm_button_add_combination']										= 'Add Combination';
$_['sm_button_add_all_combinations']							= 'Add All';
$_['sm_button_filter']														= 'Filter';
$_['sm_button_edit']															= 'Edit';
$_['sm_button_save']															= 'Save';
$_['sm_button_cancel']														= 'Cancel';
$_['sm_button_remove']														= 'Remove';

// Error
$_['sm_error_permission']													= 'Warning: You do not have permission to modify product stock!';
$_['sm_error_combination']												= 'Combination \'%s\' is entered more than one times!';
$_['sm_error_quantity']														= 'Items marked with *** contain invalid quantities. Quantity must be greater or equal to zero!';
$_['sm_error_stale_data']													= 'The product and/or its combinations displayed, have been changed and are not up-to-date anymore. Please refresh the page.';
$_['sm_error_stock_limit_not_numeric']						= 'The stock report limit must be zero or a positive number!';
$_['sm_information_product_disabled']							= 'The product has been modified successfully, but has been disabled because there is no up-to-date stock available';

// used by edit option page
$_['sm_error_product_option_value']								= 'Warning: An option value is removed, that is currently assigned to %s products!';

// used by menu
$_['sm_text_menu_stock']													= 'Stock';

// used by product
$_['sm_entry_stock_enabled']											= 'Stock Enabled';
$_['sm_entry_allow_preorder']											= 'Allow Pre-order';
$_['sm_entry_no_all_options']											= 'No, but show all options';
$_['sm_help_allow_preorder']											= 'This field is taken into account only when the product contains stock-enabled options';
$_['sm_attention_invalid_stock']									= 'The product has been modified successfully, but has been disabled because its <a href="%s">stock</a> is not up-to-date. (Reason: %s)';
$_['sm_invalid_stock_reason_unknown']							= 'Unknown';
$_['sm_invalid_stock_reason_no_entries']					= 'No stock entries';
$_['sm_invalid_stock_reason_duplicate_entries']		= 'Duplicate stock entries';
$_['sm_invalid_stock_reason_incomplete_entries'] 	= 'Invalid stock entries';
$_['sm_error_not_required']												= 'Stock-enabled options must be set as \'Required\'';
$_['sm_error_option_not_subtract']								= 'Stock-enabled option values must be set to \'Subtract Stock\' = \'Yes\'';