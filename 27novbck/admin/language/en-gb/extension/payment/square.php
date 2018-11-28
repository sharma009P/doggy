<?php
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

$version = 'v302.2';

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						= 'Square Payment Gateway';
$_['text_square']						= '<a target="blank" href="https://squareup.com/dashboard"><img src="view/image/square.jpg" alt="Square" title="Square" /></a>';

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			= 'Extension Settings';
$_['heading_extension_settings']		= 'Extension Settings';

$_['entry_status']						= 'Status: <div class="help-text">Set the status for the extension as a whole.</div>';
$_['entry_sort_order']					= 'Sort Order: <div class="help-text">Enter the sort order for the extension, relative to other payment methods.</div>';
$_['entry_title']						= 'Title: <div class="help-text">Enter the title for the payment method displayed to the customer. HTML is supported.</div>';
$_['entry_button_text']					= 'Button Text: <div class="help-text">Enter the text for the order confirmation button.</div>';
$_['entry_button_class']				= 'Button Class: <div class="help-text">Enter the CSS class for buttons in your theme.</div>';
$_['entry_button_styling']				= 'Button Styling: <div class="help-text">Optionally enter extra CSS styling for the button.</div>';

// Payment Page Text
$_['heading_payment_page_text']			= 'Payment Page Text';
$_['help_payment_page_text']			= 'HTML is supported in most fields.';

$_['entry_text_card_details']			= 'Card Details:';
$_['entry_text_use_your_stored_card']	= 'Use Your Stored Card:';
$_['entry_text_ending_in']				= 'ending in: <div class="help-text">Used for stored cards, such as "Visa ending in 4242"</div>';
$_['entry_text_delete_this_card']		= 'Delete This Card:';
$_['entry_text_confirm']				= 'Confirm Message:';
$_['entry_text_success']				= 'Success:';
$_['entry_text_use_a_new_card']			= 'Use a New Card:';
$_['entry_text_card_name']				= 'Name on Card:';
$_['entry_text_card_number']			= 'Card Number:';
$_['entry_text_card_type']				= 'Card Type:';
$_['entry_text_card_expiry']			= 'Card Expiry (MM/YY):';
$_['entry_text_card_security']			= 'Card Security Code (CVV):';
$_['entry_text_card_postcode']			= 'Card Postcode:';
$_['entry_text_store_card']				= 'Store Card for Future Use:';
$_['entry_text_please_wait']			= 'Please Wait:';

// Square Error Codes
$_['heading_square_error_codes']		= 'Square Error Codes';
$_['help_square_error_codes']			= 'Leave any of these fields blank to display Square\'s default error message for that error code. HTML is supported.';

$_['entry_error_cardNumber']			= 'cardNumber_validation_error:';
$_['entry_error_expirationDate']		= 'expirationDate_validation_error:';
$_['entry_error_postalCode']			= 'postalCode_validation_error:';
$_['entry_error_cvv']					= 'cvv_validation_error:';
$_['entry_error_missing_card_data']		= 'missing_card_data:';
$_['entry_error_unknown']				= 'unknown:';
$_['entry_error_verify_cvv_failure']	= 'verify_cvv_failure:';
$_['entry_error_verify_avs_failure']	= 'verify_avs_failure:';
$_['entry_error_invalid_expiration_year'] = 'invalid_expiration_year:';
$_['entry_error_invalid_expiration_date'] = 'invalid_expiration_date:';
$_['entry_error_invalid_card']			= 'invalid_card:';

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				= 'Order Statuses';
$_['heading_order_statuses']			= 'Order Statuses';
$_['help_order_statuses']				= 'Choose the order statuses set when a payment meets each condition. You can refund a payment by using the link provided in the History tab for the order.';

$_['entry_success_status_id']			= 'Successful Payment (Captured):';
$_['entry_authorize_status_id']			= 'Successful Payment (Authorized):';
$_['entry_error_status_id']				= 'Order Completion Error: <div class="help-text">This status will apply when the payment is completed successfully, but the order cannot be completed using the normal OpenCart order confirmation functions. This usually happens when you have entered incorrect SMTP settings in System > Settings > Mail, or you have installed modifications that affect customer orders.</div>';
$_['entry_refund_status_id']			= 'Fully Refunded Payment:';
$_['entry_partial_status_id']			= 'Partially Refunded Payment:';

$_['text_ignore']						= '--- Ignore ---';

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					= 'Restrictions';
$_['heading_restrictions']				= 'Restrictions';
$_['help_restrictions']					= 'Set the required cart total and select the eligible stores, geo zones, and customer groups for this payment method.';

$_['entry_min_total']					= 'Minimum Total: <div class="help-text">Enter the minimum order total that must be reached before this payment method becomes active. Leave blank to have no restriction.</div>';
$_['entry_max_total']					= 'Maximum Total: <div class="help-text">Enter the maximum order total that can be reached before this payment method becomes inactive. Leave blank to have no restriction.</div>';

$_['entry_stores']						= 'Store(s): <div class="help-text">Select the stores that can use this payment method.</div>';

$_['entry_geo_zones']					= 'Geo Zone(s): <div class="help-text">Select the geo zones that can use this payment method. The "Everywhere Else" checkbox applies to any locations not within a geo zone.</div>';
$_['text_everywhere_else']				= '<em>Everywhere Else</em>';

$_['entry_customer_groups']				= 'Customer Group(s): <div class="help-text">Select the customer groups that can use this payment method. The "Guests" checkbox applies to all customers not logged in to an account.</div>';
$_['text_guests']						= '<em>Guests</em>';

// Currency Settings
$_['heading_currency_settings']			= 'Currency Settings';
$_['help_currency_settings']			= 'Select the currencies that Square will charge in, based on the order currency.';
$_['entry_currencies']					= 'When Orders Are In [currency], Charge In:';
$_['text_currency_disabled']			= '--- Disabled ---';

//------------------------------------------------------------------------------
// Square Settings
//------------------------------------------------------------------------------
$_['tab_square_settings']				= 'Square Settings';
$_['heading_square_settings']			= 'Square Settings';
$_['help_square_settings']				= 'Application IDs and Access Tokens can be created at <a target="_blank" href="https://connect.squareup.com/apps">connect.squareup.com</a>. Click "New Application", then enter any name you want. Application IDs and Access Tokens will be shown on the subsequent page. You can ignore the "OAuth" portion of that page.';

$_['entry_sandbox_application_id']		= 'Sandbox Application ID:';
$_['entry_sandbox_access_token']		= 'Sandbox Access Token:';
$_['entry_sandbox_location_id']			= 'Sandbox Location: <div class="help-text">Choose the Square location with which you want to associate transactions. Sandbox locations are example locations given by Square.</div>';
$_['entry_live_application_id']			= 'Live Application ID:';
$_['entry_live_access_token']			= 'Live Access Token:';
$_['entry_live_location_id']			= 'Live Location: <div class="help-text">Choose the Square location with which you want to associate transactions. You can create new Locations in your Square admin panel, in Account & Settings > Locations.</div>';

$_['entry_transaction_mode']			= 'Transaction Mode: <div class="help-text">Use "Sandbox" to <a href="https://docs.connect.squareup.com/articles/using-sandbox/" target="_blank">test payments through Square</a>. Use "Live" when you\'re ready to accept payments.</div>';
$_['text_sandbox']						= 'Sandbox';
$_['text_live']							= 'Live';

$_['entry_charge_mode']					= 'Charge Mode: <div class="help-text">Choose whether to authorize payments and manually capture them later, or to capture (i.e. fully charge) payments when orders are placed. For payments that are only Authorized, you can Capture them by using the link provided in the History tab for the order.<br /><br />If you choose "Authorize if possibly fraudulent, Capture otherwise" then the extension will use your fraud settings to determine whether an order might be fraudulent. If the fraud score is over your threshold, the charge will be Authorized; if under, the charge will be Captured.</div>';
$_['text_authorize']					= 'Authorize';
$_['text_capture']						= 'Capture';
$_['text_fraud_authorize']				= 'Authorize if possibly fraudulent, Capture otherwise';

$_['entry_allow_stored_cards']			= 'Allow Customers to Store Cards: <div class="help-text">If set to "Yes", customers will be able to store cards to use for future purchases.</div>';

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							= '<hr /><div class="text-center" style="margin: 15px">' . $_['heading_title'] . ' (' . $version . ') &copy; <a target="_blank" href="http://www.getclearthinking.com/contact">Clear Thinking, LLC</a></div>';

$_['standard_autosaving_enabled']		= 'Auto-Saving Enabled';
$_['standard_confirm']					= 'This operation cannot be undone. Continue?';
$_['standard_error']					= '<strong>Error:</strong> You do not have permission to modify ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			= '<strong>Warning:</strong> The number of settings is close to your <code>max_input_vars</code> server value. You should enable auto-saving to avoid losing any data.';
$_['standard_please_wait']				= 'Please wait...';
$_['standard_saved']					= 'Saved!';
$_['standard_saving']					= 'Saving...';
$_['standard_select']					= '--- Select ---';
$_['standard_success']					= 'Success!';
$_['standard_testing_mode']				= 'Your log is too large to open! Clear it first, then run your test again.';

$_['standard_module']					= 'Modules';
$_['standard_shipping']					= 'Shipping';
$_['standard_payment']					= 'Payments';
$_['standard_total']					= 'Order Totals';
$_['standard_feed']						= 'Feeds';
?>