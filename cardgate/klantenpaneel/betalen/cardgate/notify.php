<?php
// Load WeFact database connection and settings
chdir( '../' );
require_once "config.php";
// Load payment provider class
require_once "cardgate/payment_provider.php";
$tmp_payment_provider = new cardgate();
 
if ( isset( $_POST['status'] ) ) {
    $tmp_payment_provider->isNotificationScript = true;
}

if ( isset( $_REQUEST['ref'] )) {
    // Validate transaction
    $tmp_payment_provider->validateTransaction( $_REQUEST['ref'] );
} else {
    // If no REQUEST-variable
    $tmp_payment_provider->paymentStatusUnknown( 'transaction id unknown' );
}
?>