<?php

class cardgate extends Payment_Provider_Base {

    protected $version = '1.0.5';
    protected $_PaymentMethods = '';
    private $_TEST = false;
    private $_url = '';

    function __construct() {

        $this->conf['PaymentDirectory'] = 'cardgate';
        $this->conf['PaymentMethod'] = 'other'; // ideal / paypal / other
        $this->conf['notify_url'] = IDEAL_EMAIL . 'cardgate/notify.php';

// Load parent constructor
        parent::__construct();
// Load configuration
        $this->loadConf();

        $this->_url = $this->getGatewayUrl();
    }

    public function payOptions() {
 
        $this->_PaymentMethods = $this->getPaymentMethods();

        $cont = '<table>';

        $i = 0;

        foreach ( $this->_PaymentMethods as $method_name => $method ) {

            if ( $i == 0 ) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }


            if ( strtolower( $method['name'] ) == 'ideal' ) {
                $is_true = 1;
            } else {
                $is_true = 0;
            }

            $cont .= '<tr>'
                    . '<td><label style="width:640px;" for="' . strtolower( $method['name'] ) . '">'
                    . '<input type="radio" ' . $checked . ' value="' . strtolower( $method['name'] ) . '" id="' . strtolower( $method['name'] ) . '" name="cgp_option" onChange="showBanks(' . $is_true . ')">'
                    . '<img src="' . $method['image'] . '" hspace="5">'
                    . '&nbsp;' . $method['description'];
            if ( strtolower( $method['name'] ) == 'ideal' ) {
                if ( $i == 0 ) {
                    $style = 'display: inline;';
                } else {
                    $style = 'display: none;';
                }
                $cont .= '&nbsp;<select name="cgp_suboption" id="ideal-banks" style="' . $style . '">';
                foreach ( $this->getBankOptions() as $bank_id => $bank ) {
                    $cont .= "<option value='" . $bank_id . "'>" . $bank . "</opion>";
                }
                $cont .= "</select>";
            }
            $cont .='</label>';
            $cont.="<script>
                        function showBanks(is_true) {
                                if ( is_true ) {
                                        document.getElementById('ideal-banks').style.display = 'inline'; 
                                } else {
                                        document.getElementById('ideal-banks').style.display = 'none';
                                }
                        }
                        </script>";
            $cont .= '</td>'
                    . '</tr>';

            $i++;
        }
        $cont .='</table>';
        /*
          $cont .= '<script>'
          . 'var el = document.getElementsByName("PaymentMethod");'
          . 'if (el.length==1) {'
          . ' el[0].checked=true;'
          . '}'
          . '</script>';
         * 
         */
        return $cont;
    }

    private function getPaymentMethods() {
        
        if ( strpos( $this->conf['MerchantID'], '/' ) > 0 ) {
            $params = explode( '/', $this->conf['MerchantID'] );
            $sSiteID = $params[0];
        } else {
            $sSiteID = $this->conf['MerchantID'];
        }

        $sHashKey = $this->conf['Password'];
        $version = str_replace( '.', '', $this->version );
        $url = $this->_url . "getpm/cgsp$sSiteID/" . md5( $sSiteID . $sHashKey );

        $methods = unserialize( file_get_contents( $url ) );
        return $methods;
    }

    public function getBankOptions() {

        $url = 'https://gateway.cardgateplus.com/cache/idealDirectoryRabobank.dat';

        if ( !ini_get( 'allow_url_fopen' ) || !function_exists( 'file_get_contents' ) ) {
            $result = false;
        } else {
            $result = file_get_contents( $url );
        }

        $aBanks = array();

        if ( $result ) {
            $aBanks = unserialize( $result );
            $aBanks[0] = '-Maak uw keuze a.u.b.-';
        }
        if ( count( $aBanks ) < 1 ) {
            $aBanks = array( '0031' => 'ABN Amro',
                '0091' => 'Friesland Bank',
                '0721' => 'ING Bank',
                '0021' => 'Rabobank',
                '0751' => 'SNS Bank',
                '0761' => 'ASN Bank',
                '0771' => 'SNS Regio Bank',
                '0511' => 'Triodos Bank',
                '0161' => 'Van Landschot Bank'
            );
        }
        return $aBanks;
    }

    public function choosePaymentMethod() {
// If we don't need to ask for payment method upfront, just return false;
//return true;
// Or get the payment methods and create HTML with options
        $html = $this->payOptions();
        return $html;
    }

    public function validateChosenPaymentMethod() {
// If we dont need to ask for payment method upfront, return true (always valid)
//return true;
// Or check the chosen payment methods and store in session		
        if ( isset( $_POST['cgp_suboption'] ) ) {
            $_SESSION['cgp_suboption'] = $_POST['cgp_suboption'];
        }
        if ( isset( $_POST['cgp_option'] ) && $_POST['cgp_option'] ) {
            $_SESSION['cgp_option'] = htmlspecialchars( $_POST['cgp_option'] );
            if ( $_SESSION['cgp_option'] == 'ideal' && ($_SESSION['cgp_suboption'] == 0 || $_SESSION['cgp_suboption'] == 1) ) {
                $this->Error = 'Kies uw bank a.u.b.';
                return false;
            } else {
                return true;
            }
        } elseif ( !isset( $_POST['cgp_option'] ) && isset( $_SESSION['cgp_option'] ) && $_SESSION['cgp_option'] ) {
            return true;
        } else {
            $this->Error = 'Kies uw CardGate betaalmethode a.u.b.';
            return false;
        }
    }

    public function startTransaction() {
        $option = (isset( $_SESSION['cgp_option'] ) && $_SESSION['cgp_option']) ? $_SESSION['cgp_option'] : '';
        $cgp_suboption = (isset( $_SESSION['cgp_suboption'] ) && $_SESSION['cgp_suboption']) ? $_SESSION['cgp_suboption'] : '';

        $data = array();

        if ( $this->Type == 'invoice' ) {
            $orderID = $this->InvoiceCode;
            $description = __( 'description prefix invoice' ) . ' ' . $this->InvoiceCode;
            $data['extra'] = 'invoice';
        } else {
            $orderID = $this->OrderCode;
            $description = __( 'description prefix order' ) . ' ' . $this->OrderCode;
            $data['extra'] = 'order';
        }

        $customer = $this->getCustomerData();

        $data['ref'] = $orderID;
        $data['first_name'] = $customer->Initials;
        $data['last_name'] = $customer->SurName;
        $data['email'] = $customer->EmailAddress;
        $data['address'] = $customer->Address;
        $data['city'] = $customer->City;
        $data['country_code'] = $customer->Country;
        $data['postal_code'] = $customer->ZipCode;
        $data['phone_number'] = '';
        $data['state'] = '';
        $data['language'] = 'nl';
        $data['return_url'] = IDEAL_EMAIL . 'cardgate/notify.php?ref=' . $orderID;
        $data['return_url_failed'] = IDEAL_EMAIL . 'cardgate/notify.php?ref=' . $orderID;
        $data['shop_name'] = 'WeFact';
        $data['shop_version'] = '1.0';
        $data['plugin_name'] = 'Cardgate_WeFact';
        $data['plugin_version'] = $this->version;

        if ( $this->conf['Password'] == '' ) {
            $error_message = 'De Codeer sleutel van de CardGate betaalmethode bij Instellingen ontbreekt.';
        }

        if ( $this->conf['MerchantID'] == '' ) {
            $error_message = 'De Site ID van de CardGate betaalmethode bij Instellingen ontbreekt.';
        } else {
            if ( strpos( $this->conf['MerchantID'], '/' ) > 0 ) {
                $params = explode( '/', $this->conf['MerchantID'] );
                $siteID = $params[0];
            } else {
                $siteID = $this->conf['MerchantID'];
            }
            $data['siteid'] = $siteID;
        }

        $mode = $this->getMode();

        if ( $mode == 'test' ) {
            $data['test'] = 1;
            $hash_prefix = 'TEST';
        } else {
            $data['test'] = 0;
            $hash_prefix = '';
        }
        $amount = $this->Amount * 100;
        $hashkey = $this->conf['Password'];

        $data['amount'] = $amount;
        $data['currency'] = CURRENCY_CODE;
        $data['description'] = 'Order ' . $orderID;
        $data['option'] = $option;

// with an iDEAL transaction, include the bank parameter
        if ( $option == 'ideal' ) {
            $data['suboption'] = $cgp_suboption;
        } else {
            $data['suboption'] = '';
        }

        $data['hash'] = md5( $hash_prefix .
                $siteID .
                $amount .
                $orderID .
                $hashkey );

// Start transaction
// If transaction can be started
        if ( !isset( $error_message ) ) {
// If a transaction ID is known, update to database
            $this->updateTransactionID( $orderID );
            ?>
            <body>
                <p>U verlaat nu deze website, om de transactie te voltooien</p>
                <form name="form" action="<?php echo $this->_url; ?>" method="post">
                    <input type="hidden" name="test" value="<?php echo $data['test']; ?>" />
                    <input type="hidden" name="option" value="<?php echo $data['option']; ?>" />
                    <input type="hidden" name="suboption" value="<?php echo $data['suboption']; ?>" />
                    <input type="hidden" name="siteid" value="<?php echo $data['siteid']; ?>" />
                    <input type="hidden" name="currency" value="<?php echo $data['currency']; ?>" />
                    <input type="hidden" name="amount" value="<?php echo $data['amount']; ?>" />
                    <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>" />
                    <input type="hidden" name="description" value="<?php echo $data['description']; ?>" />
                    <input type="hidden" name="return_url" value="<?php echo $data['return_url']; ?>" />
                    <input type="hidden" name="return_url_failed" value="<?php echo $data['return_url_failed']; ?>" />
                    <input type="hidden" name="email" value="<?php echo $data['email']; ?>" />
                    <input type="hidden" name="first_name" value="<?php echo $data['first_name']; ?>" />
                    <input type="hidden" name="last_name" value="<?php echo $data['last_name']; ?>" />
                    <input type="hidden" name="address" value="<?php echo $data['address']; ?>" />
                    <input type="hidden" name="postal_code" value="<?php echo $data['postal_code']; ?>" />
                    <input type="hidden" name="city" value="<?php echo $data['city']; ?>" />
                    <input type="hidden" name="country_code" value="<?php echo $data['country_code']; ?>" />
                    <input type="hidden" name="hash" value="<?php echo $data['hash']; ?>" />
                </form>
                <script type="text/javascript">
                    document.form.submit();
                </script>  
            </body>
            <?php
            exit;
        } else {
// Return error message for consumer
            $this->paymentStatusUnknown( $error_message );
            exit;
        }
    }

    public function validateTransaction( $transactionID ) {

        if ( $this->isNotificationScript === true ) {
            // Process callback url
            if ( strpos( $this->conf['MerchantID'], '/' ) > 0 ) {
                $params = explode( '/', $this->conf['MerchantID'] );
                $siteID = $params[0];
                $mode = strtolower( $params[1] );
            } else {
                $siteID = $this->conf['MerchantID'];
                $mode = 'live';
            }

            if ( $this->getType( $transactionID ) ) {

                $hashString = ($mode == 'test' ? 'TEST' : '') .
                        $_POST['transaction_id'] .
                        CURRENCY_CODE .
                        $this->Amount * 100 .
                        $_POST['ref'] .
                        $_POST['status'] .
                        $this->conf['Password'];

                if ( md5( $hashString ) != $_POST['hash'] ) {
                    exit( 'hash did not match' );
                }


                print $_POST['transaction_id'] . '.' . $_POST['status'];

                if ( !$this->Paid ) {
                    if ( $_POST['status'] == 200 ) {
                        $this->paymentProcessed( $transactionID );
                    } elseif ( $_POST['status'] == 300 ) {
                        $this->paymentFailed( $transactionID );
                    }
                }
                exit();
            }
        } else {

            // Process return urls
            // For consumer (in this case the status is already changed by server-to-server notification script)
            if ( $this->getType( $transactionID ) && $this->Paid > 0 ) {
                if ( $this->Type == 'invoice' ) {
                    $_SESSION['payment']['type'] = 'invoice';
                    $_SESSION['payment']['id'] = $this->InvoiceID;
                } elseif ( $this->Type == 'order' ) {
                    $_SESSION['payment']['type'] = 'order';
                    $_SESSION['payment']['id'] = $this->OrderID;
                }

                // Because type is found, we know it is paid
                $_SESSION['payment']['status'] = 'paid';
                $_SESSION['payment']['paymentmethod'] = $this->conf['PaymentMethod'];
                $_SESSION['payment']['transactionid'] = $transactionID;
                $_SESSION['payment']['date'] = date( 'Y-m-d H:i:s' );
            } else {
                $_SESSION['payment']['status'] = 'failed';
                $_SESSION['payment']['paymentmethod'] = $this->conf['PaymentMethod'];
                $_SESSION['payment']['transactionid'] = $transactionID;
                $_SESSION['payment']['date'] = date( 'Y-m-d H:i:s' );
            }

            header( "Location: " . IDEAL_EMAIL );
            exit;
        }
    }

    public static function getBackofficeSettings() {
        $settings = array();
        $settings['InternalName'] = 'Cardgate';

        $settings['MerchantID']['Title'] = "Site ID/Mode";
        $settings['MerchantID']['Value'] = "";

        $settings['Password']['Title'] = "Codeer sleutel";
        $settings['Password']['Value'] = "";

        $settings['Advanced']['Title'] = "CardGate";
        $settings['Advanced']['Image'] = "cardgate.jpg";
        $settings['Advanced']['Extra'] = "Kies uw betaalmethode a.u.b.:";

        $settings['Hint'] = "SiteId/Mode voorbeeld: 0001/test of 0001/live<br>"
                . "Vul de Codeersleutel in, die u in uw Cardgate merchant back-office heeft aangemaakt.<br>"
                . "De betaalmethoden die voor u ingesteld zijn door Cardgate, zullen automatisch worden aangemaakt.<br>";
        return $settings;
    }

    public function getGatewayUrl() {
        if ( !empty( $_SERVER['CGP_GATEWAY_URL'] ) ) {
            return $_SERVER['CGP_GATEWAY_URL'];
        } else {
            return "https://gateway.cardgateplus.com/";
        }
    }

    function getMode() {
        if ( strpos( $this->conf['MerchantID'], '/' ) > 0 ) {
            $params = explode( '/', $this->conf['MerchantID'] );
            $mode = strtolower( $params[1] );
        } else {
            $mode = 'live';
        }
        return $mode;
    }

}
