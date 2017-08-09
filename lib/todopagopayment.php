<?php
require_once('vendor/autoload.php');
require_once(dirname(__FILE__).'/../../jigoshop/gateways/gateway.class.php');
require_once(dirname(__FILE__).'/ControlFraude/ControlFraudeFactory.php');
require_once(dirname(__FILE__) .'/logger.php');
require_once(dirname(__FILE__).'/StatusCodeCS.php');

define('TODOPAGO_PLUGIN_VERSION','1.1.1');
define('TP_FORM_EXTERNO', 'ext');
define('TP_FORM_HIBRIDO', 'hib');
define('TODOPAGO_DEVOLUCION_OK', 2011);
define('TODOPAGO_FORMS_PROD','https://forms.todopago.com.ar');
define('TODOPAGO_FORMS_TEST','https://developers.todopago.com.ar');

use TodoPago\Sdk as Sdk;
use TodoPago\Data\User as User;

class todopagopayment extends jigoshop_payment_gateway
{

    public function __construct()
    {
        parent::__construct();
 $this->jigoshop_options = Jigoshop_Base::get_options();
        $this->id            = 'todopagopayment';
        $this->has_fields    = false;
        $this->enabled       = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_enabled');
        $this->title         = "Todo Pago";
        $this->description   = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_description');
        $this->icon =  plugins_url() . '/' . plugin_basename(dirname(__FILE__)) .'/../src/logo.png';
        $this->instant       = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_instant');
        $this->language      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_language');

        $this->mode          = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_mode');
        $this->user          = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_user');
        $this->password      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_password');
		$this->form      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_form');

        $this->maxcuotas      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxcuotas');
        $this->maxcuotas_enabled = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxinstallments_enabled');
        $this->emptycart_enabled = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_emptycart_enabled');

        add_option( 'order_id', 'None', '', 'yes' );
		$this->todopago_environment = $this->mode;
        $this->authorization = ($this->todopago_environment == 'test')? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_dev'):Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_prod');
        $this->merchantid    = ($this->todopago_environment == 'test')? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_dev'):Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_prod');
        $this->security      = ($this->todopago_environment == 'test')? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_dev'):Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_prod');

			$this->orden_iniciada = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_iniciada');
            $this->orden_aprobada= Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_aprobada');
            $this->orden_rechazada   = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_rechazada');
            $this->orden_offline   = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_offline');

        add_option( 'apiKey', 'None', '', 'yes' );
     	add_option( 'merchantId', 'None', '', 'yes' );
		add_option( 'security', 'None', '', 'yes' );
        add_option( 'order_id', 'None', '', 'yes' );
        add_option( 'todopago_environment', 'None', '', 'yes' );
		add_option( 'post', 'None', '', 'yes' );
		add_option( $orderId, 'None', '', 'yes' );

		update_option('todopago_environment', $this->todopago_environment);
		update_option('apiKey', $this->authorization);
		update_option('merchantId', $this->merchantid);


        add_action('init', array(
            &$this,
            'check_callback'
        ));
        add_action('valid-todopagopayment-callback', array(
            &$this,
            'successful_request'
        ));
        add_action('receipt_todopagopayment', array(
            &$this,
            'receipt_page'
        ));

        add_filter('jigoshop_thankyou_message', array(
            &$this,
            'thankyou_message'
        ));

        $this->tplogger = new TodoPagoLogger();

    }

    public function _obtain_logger(
        $php_version,
        $jigoshop_version,
        $todopago_plugin_version,
        $endpoint,
        $customer_id,
        $order_id,
        $is_payment
        )
    {
       // global $tplogger
        $this->tplogger->setPhpVersion($php_version);
        $this->tplogger->setCommerceVersion($jigoshop_version);
        $this->tplogger->setPluginVersion($todopago_plugin_version);
        $this->tplogger->setEndPoint($endpoint);
        $this->tplogger->setCustomer($customer_id);
        $this->tplogger->setOrder($order_id);

        return  $this->tplogger->getLogger(true);
    }

    /**
     * Default Option settings for WordPress Settings API using the Jigoshop_Options class
     *
     * These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
     *
     */

    protected function get_default_options()
    {

        $defaults[] = array( 'name' => __('Todo Pago', 'jigoshop'), 'type' => 'title' , 'desc' => __('', 'jigoshop') );
        // List each option in order of appearance with details
        $defaults[] = array(
            'name' => __('Habilitar Todopago', 'jigoshop'),
            'desc' => '',
            'tip' => '',
            'id' => 'jigoshop_todopagopayment_enabled',
            'std' => 'no',
            'type' => 'checkbox',
            'choices' => array(
                'no' => __('No', 'jigoshop'),
                'yes' => __('Yes', 'jigoshop')
            )
        );

        $defaults[] = array(
            'name' => __('Description', 'jigoshop'),
            'desc' => '',
            'tip' => __('This controls the description which the user sees during checkout.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_description',
            'std' => __("Pay via todopago using credit card or bank transfer.", 'jigoshop'),
            'type' => 'longtext'
        );


        ////////////////////////////////////////////////////////////////////
        // credenciales AMBIENTE DESARROLLO
        $defaults[] = array( 'name' => __('Obtener credenciales ambiente desarrollo', 'jigoshop'), 'type' => 'title' , 'desc' => __('', 'jigoshop') );
        $defaults[] = array(
            'name' => __('Usuario', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_user_dev',
            'std' => '',
            'type' => 'text'
        );

        $defaults[] = array(
            'name' => __('Password', 'jigoshop'),
            'desc' => 'password',
            'id' => 'jigoshop_todopagopayment_password_dev',
            'std' => '',
            'type' => 'text'
        );

        $defaults[] = array(
            'name' => __(' ', 'jigoshop'),
            'type' => 'text',
            'id' => 'jigoshop_todopagopayment_btnCredentials_dev',
            'std' => '',
            'desc' => ''

        );

        $defaults[] = array(
            'name' => __('Merchant Id', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Id Site Todo Pago.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_merchantid_dev',
            'std' => '',
            'type' => 'longtext'
        );


        $defaults[] = array(
            'name' => __('Authorization HTTP', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Authorization HTTP.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_authorization_dev',
            'std' => '',
            'type' => 'longtext'
        );

        $defaults[] = array(
            'name' => __('Security code', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Security code.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_security_dev',
            'std' => '',
            'type' => 'longtext'
        );

        /////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////
        // credenciales AMBIENTE PRODUCCION
        $defaults[] = array( 'name' => __('Obtener credenciales ambiente Produccion', 'jigoshop'), 'type' => 'title', 'desc' => __('', 'jigoshop') );
        $defaults[] = array(
            'name' => __('Usuario', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_user_prod',
            'std' => '',
            'type' => 'text'
        );

        $defaults[] = array(
            'name' => __('Password', 'jigoshop'),
            'desc' => 'password',
            'id' => 'jigoshop_todopagopayment_password_prod',
            'std' => '',
            'type' => 'text'
        );

        $defaults[] = array(
            'name' => __(' ', 'jigoshop'),
            'type' => 'text',
            'id' => 'jigoshop_todopagopayment_btnCredentials_prod',
            'std' => '',
            'desc' => ''

        );

        $defaults[] = array(
            'name' => __('Merchant Id', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Id Site Todo Pago.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_merchantid_prod',
            'std' => '',
            'type' => 'longtext'
        );


        $defaults[] = array(
            'name' => __('Authorization HTTP', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Authorization HTTP.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_authorization_prod',
            'std' => '',
            'type' => 'longtext'
        );

        $defaults[] = array(
            'name' => __('Security code', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate Security code.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_security_prod',
            'std' => '',
            'type' => 'longtext'
        );

        /////////////////////////////////////////////////////////////




        $defaults[] = array(
            'name' => __(' ', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_space',
            'std' => '',
            'type' => ''
        );

        $defaults[] = array(
            'name' => __('Mode', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_mode',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'test' => 'test',
                'prod' => 'prod'

            )
        );

		 $defaults[] = array(
            'name' => __('Form', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_form',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'integrado' => 'integrado',
                'externo' => 'externo'

            )
        );





		$defaults[] = array(
            'name' => __('Máximo de cuotas', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_maxcuotas',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                '1' => '1',
                '2' => '2',
				'3' => '3',
                '4' => '4',
				'5' => '5',
                '6' => '6',
				'7' => '7',
                '8' => '8',
				'9' => '9',
                '10' => '10',
				'11' => '11',
                '12' => '12'


            )
        );

        $defaults[] = array(
            'name' => __('Habilitar Máximo de Cuotas', 'jigoshop'),
            'desc' => '',
            'tip' => __('Ofrecera el máximo de cuotas al comprador de la cantidad seleccionada en el combo anterior.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_maxinstallments_enabled',
            'std' => 'no',
            'type' => 'checkbox',
            'choices' => array(
                'no' => __('No', 'jigoshop'),
                'yes' => __('Yes', 'jigoshop')
            )
        );
        $defaults[] = array(
            'name' => __('Vaciar carrito cuando se obtiene error en la orden', 'jigoshop'),
            'desc' => '',
            'tip' => __('Al obtener un error en alguna instancia de la transacción, se vaciará el carrito del usuario.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_emptycart_enabled',
            'std' => 'no',
            'type' => 'checkbox',
            'choices' => array(
                'no' => __('No', 'jigoshop'),
                'yes' => __('Yes', 'jigoshop')
            )
        );
		$defaults[] = array( 'name' => __('Estados de pedidos', 'jigoshop'), 'type' => 'title', 'desc' => __('', 'jigoshop') );


		$defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido iniciada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_iniciada',
            'std' => '',
            'type' => 'select',
            'choices' => array(
        		'pending'    => 'Pending Payment',
        		'processing' =>'Processing',
        		'on-hold'    => 'On Hold',
        		'completed'  =>'Completed',
        		'cancelled'  =>'Cancelled',
        		'refunded'   => 'Refunded',
        		'failed'     =>'Failed'
        	)
        );
			$defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido aprobada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_aprobada',
            'std' => '',
            'type' => 'select',
            'choices' => array(
        		'pending'    => 'Pending Payment',
        		'processing' =>'Processing',
        		'on-hold'    => 'On Hold',
        		'completed'  =>'Completed',
        		'cancelled'  =>'Cancelled',
        		'refunded'   => 'Refunded',
        		'failed'     =>'Failed'
        	)
        );
				$defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido rechazada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_rechazada',
            'std' => '',
            'type' => 'select',
                'choices' => array(
        		'pending'    => 'Pending Payment',
        		'processing' =>'Processing',
        		'on-hold'    => 'On Hold',
        		'completed'  =>'Completed',
        		'cancelled'  =>'Cancelled',
        		'refunded'   => 'Refunded',
        		'failed'     =>'Failed'
        	)
        );
				$defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido offline', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_offline',
            'std' => '',
            'type' => 'select',
               'choices' => array(
        		'pending'    => 'Pending Payment',
        		'processing' =>'Processing',
        		'on-hold'    => 'On Hold',
        		'completed'  =>'Completed',
        		'cancelled'  =>'Cancelled',
        		'refunded'   => 'Refunded',
        		'failed'     =>'Failed'
        	)
        );

        return $defaults;
    }

    /**
     * There are no payment fields for todopago, but we want to show the description if set.
     **/
    public function payment_fields()
    {
        if ($jigoshop_todopagopayment_description = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_description'))
            echo wpautop(wptexturize($jigoshop_todopagopayment_description));
    }


    public function todopago_persistRequestKey($order_id, $request_key){
    //  update_option('request_key', $request_key);
        update_post_meta( $order_id, 'request_key', $request_key);
    }

    /**
     * Generate the todopago button link
     **/
    public function generate_form($order_id)
    {
        $logger = $this->_obtain_logger(
            phpversion(),
            'jigoshop',
            TODOPAGO_PLUGIN_VERSION,
            $this->todopago_environment ,
            $order_id, $order_id, true
            );
 			update_option('orderId', $order_id);
        //Se genera orden de compra
        $order = new jigoshop_order($order_id);

        /** Se genera payload para enviar el sendAuthorizeRequest **/
        $arrayorder = (array) $order;

        $controlFraude = ControlFraudeFactory::get_ControlFraude_extractor('Retail', $order, $arrayorder);

        $datosCs = $controlFraude->getDataCF();
        $sessionid = $order->order_key;
        $home = home_url();

        $arrayHome = split ("/", $home);


        $basename = plugin_basename(dirname(__FILE__));
        $baseurl = plugins_url();

        $return_URL_ERROR = "{$baseurl}/{$basename}/../todopago.php?second_step=true";
        $return_URL_OK    =  "{$baseurl}/{$basename}/../todopago.php?sessionid={$sessionid}&second_step=true";

        $esProductivo = $this->testmode == "no";

        $optionsSAR_comercio = $this->getOptionsSARComercio($esProductivo, $return_URL_OK,$return_URL_ERROR);

        $optionsSAR_operacion = $this->getOptionsSAROperacion($esProductivo, $order);

        $optionsSAR_operacion = array_merge_recursive($optionsSAR_operacion, $datosCs);

        $paramsSAR['comercio'] = $optionsSAR_comercio;
        $paramsSAR['operacion'] = $optionsSAR_operacion;

        $logger->info('params SAR '.json_encode($paramsSAR));
        $rta = $this->call_sar($paramsSAR, $logger);
        $this->todopago_persistRequestKey($order->id, $rta["RequestKey"]);

        $action_adr = $rta['URL_Request'];
        if (isset($action_adr)) {
			$status = $this->orden_iniciada;
            $var = orden_iniciada($order,$status);
        }

        session_start();
        $_SESSION['RequestKey'] = $rta['RequestKey'];
		$RequestKey = $rta['RequestKey'];

		$_SESSION['Order'] = $order;
		update_option('order_id', $RequestKey);


        if($rta["StatusCode"] == -1){
            /** Selección FH o Retail **/
            $isFormHybrid = $this->form;

            if ($isFormHybrid == 'externo') {

                $submit_button = '<form action="' . $action_adr . '" method="post" id="todopago_payment_form">
                    ' . $fields . '
                    <input type="submit" class="button-alt" id="submit_todopago_payment_form" value="' . __('Pay via todopago', 'jigoshop') . '" /> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'jigoshop') . '</a>
                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery("body").block(
                                {
                                    message: "<img src=\"' . jigoshop::assets_url() . '/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />' . __('Thank you for your order. We are now redirecting you to todopago to make payment.', 'jigoshop') . '",
                                    overlayCSS:
                                    {
                                        background: "#fff",
                                        opacity: 0.6
                                    },
                                    css: {
                                                padding:        20,
                                                textAlign:      "center",
                                                color:          "#555",
                                                border:         "3px solid #aaa",
                                                backgroundColor:"#fff",
                                                cursor:         "wait"
                                        }
                                });
                            jQuery("#submit_todopago_payment_form").click();
                        });
                    </script>
                </form>';

                echo $submit_button;
                exit;

               // return $submit_button;
            } else {  // FORM HIBRIDO

                $form_dir = "{$baseurl}/{$basename}/lib/view/formulario-hibrido";
                $firstname = $paramsSAR['operacion']['CSSTFIRSTNAME'];
                $lastname = $paramsSAR['operacion']['CSSTLASTNAME'];
                $email = $paramsSAR['operacion']['CSSTEMAIL'];
                $merchant = $paramsSAR['operacion']['MERCHANT'];
                $amount = $paramsSAR['operacion']['CSPTGRANDTOTALAMOUNT'];
                $prk = $rta['PublicRequestKey'];

                $home = home_url();
                $arrayHome = split ("/", $home);

                $return_URL_ERROR = "{$baseurl}/{$basename}/../todopago.php?second_step=true";
                $return_URL_OK    =  "{$baseurl}/{$basename}/../todopago.php?sessionid={$sessionid}&second_step=true";

                $env_url = ($this->todopago_environment == "prod" ? TODOPAGO_FORMS_PROD : TODOPAGO_FORMS_TEST);

                get_header();
                require 'view/formulario-hibrido/formulario.php';
                get_footer();
                exit;
            }

        } else {
          echo '<div style="color: red" align="left">Lo sentimos, ha ocurrido un error.  '.
            $rta['StatusMessage'] . '<a href="' . home_url() . '" class="wc-backward">  Volver a la p&aacute;gina de inicio</a></div>';
            if($this->emptycart_enabled == 'yes'){
              jigoshop_cart::empty_cart();
                }
						exit;

        }
    }

    function getOptionsSARComercio($esProductivo, $return_URL_OK, $return_URL_ERROR){
			   return array (
            'Security'      => $this->security,
            'EncodingMethod'=> 'XML',
            'Merchant'      => strval( $this->merchantid),
            'URL_OK'        => $return_URL_OK,
            'URL_ERROR'     => $return_URL_ERROR
		);
    }

    function getOptionsSAROperacion($esProductivo, $order){

        $arrayResult = array (
            'MERCHANT'    => $this->merchantid,
            'OPERATIONID' => strval($order->id),
            'CURRENCYCODE'=> '032', //Por el momento es el único tipo de moneda aceptada
        );
        // setea max de cuotas si es que se esta habilitada la opcion
        if($this->maxcuotas_enabled == 'yes'){
            $arrayResult['MAXINSTALLMENTS']  =  strval( $this->maxcuotas );
        }

       return $arrayResult;
    }

    function call_sar($paramsSAR, $logger){

        $logger->debug('call_sar');

        $http_header = $this->getHttpHeader();

        $logger->debug("http header: ".json_encode($http_header));
        $connector = new Sdk($http_header, $this->todopago_environment);

        $logger->debug("Connector: ".json_encode($connector));
        $response_sar = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
        $error=new StatusCodeCS();
				$statusCode=$response_sar["StatusCode"];
      	$response_sar['StatusMessage']=$error->getErrorByStatusCode($statusCode);
        $logger->info('response SAR '.json_encode($response_sar));
        return $response_sar;
    }

    protected function getHttpHeader(){
        $http_header = $this->authorization;
        $header_decoded = json_decode(html_entity_decode($http_header,TRUE));
        return (!empty($header_decoded)) ? $header_decoded : array("authorization" => $http_header);
    }

    /**
     * Process the payment and return the result
     **/
    public function process_payment($order_id)
    {
        $order = new jigoshop_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, apply_filters('jigoshop_get_return_url', get_permalink(jigoshop_get_page_id('pay')))))
        );

    }

    /**
     * receipt_page
     **/
    public function receipt_page($order)
    {

        echo '<p>' . __('Thank you for your order, please click the button below to pay with todopago.', 'jigoshop') . '</p>';

        echo $this->generate_form($order);
    }

    /**
     * Check for todopago Response
     **/
    public function check_callback()
    {

        // Cancel order POST
        if (strpos($_SERVER["REQUEST_URI"], 'jigoshop/todopagocancel') !== false) {
            $this->cancel_order(stripslashes_deep($_POST));
            return;
        }

        if (strpos($_SERVER["REQUEST_URI"], 'jigoshop/todopagocallback') !== false) {
            header("HTTP/1.1 200 Ok");
            do_action("valid-todopagopayment-callback", stripslashes_deep($_POST));

        }
    }

    // This is a modified version of jigoshop_cancel_order.
    // We must have our own since the original checks nonce on GET variables.
    public function cancel_order($posted)
    {
        if (isset($posted['orderId']) && is_numeric($posted['orderId']) && isset($posted['s_jigoshop_order_key'])) {

            // Also verify HMAC
            $MAC = $this->todopago_calculate_mac($posted);

            // Cancel order the same way as jigoshop_cancel_order
            $order_id  = $_POST['orderId'];
            $order_key = $_POST['s_jigoshop_order_key'];

            $order = new jigoshop_order($order_id);

            if ($posted['MAC'] == $MAC && $order->id == $order_id && $order->order_key == $order_key && $order->status == 'pending'):
            // Cancel the order + restore stock
                $order->cancel_order(__('Order cancelled by customer.', 'jigoshop'));

                // Message
                jigoshop::add_message(__('Your order was cancelled.', 'jigoshop'));
            elseif ($order->status != 'pending'):
                jigoshop::add_error(__('Your order is no longer pending and could not be cancelled. Please contact us if you need assistance.', 'jigoshop'));
            else:
                jigoshop::add_error(__('Invalid order.', 'jigoshop'));
            endif;

            wp_safe_redirect(jigoshop_cart::get_cart_url());

        }
    }

    public function thankyou_message($message)
    {

        // Fake a GET request for the Thank you page
        if (isset($_POST['orderId']) && is_numeric($_POST['orderId']) && isset($_POST['s_jigoshop_order_key'])) {
            $_GET['order'] = $_POST['orderId'];
            $_GET['key']   = $_POST['s_jigoshop_order_key'];
        }

        return $message;
    }

    /** TodoPago LLamada al GAA **/
    public function call_GAA()
    {
        session_start();

        $RequestKey = get_option('order_id');

		update_option($_SESSION['Order']->id, $RequestKey);


        $logger = $this->_obtain_logger(
            phpversion(),
            'jigoshop',
            TODOPAGO_PLUGIN_VERSION,
            $this->todopago_environment ,
            $_SESSION['Order']->id, $RequestKey, true
            );

        $params_GAA = array(
            'Security' => $this->security,
            'Merchant' => $this->merchantid,
            'RequestKey' => $RequestKey,
            'AnswerKey' => $_GET['Answer']
        );

        $http_header = array(
            'Authorization' => $this->authorization,
            'user_agent' => 'PHPSoapClient'
        );

        $connector    = new Sdk($http_header, $this->todopago_environment);
        $logger->info('request GAA '.json_encode($params_GAA));

        $response_GAA = $connector->getAuthorizeAnswer($params_GAA);
        update_option('AMOUNT'.$_SESSION['Order']->id, $response_GAA['Payload']['Request']['AMOUNT']);
        update_option('AMOUNTBUYER'.$_SESSION['Order']->id, $response_GAA['Payload']['Request']['AMOUNTBUYER']);
		$order = new jigoshop_order($_SESSION['Order']->id);

		if($response_GAA['StatusCode']=='-1'){
      jigoshop_cart::empty_cart();
		   $status = $this->orden_aprobada;
           $var = orden_aprobada($order,$status);
		}else{
			 $status = $this->orden_rechazada;
           $var = orden_rechazada($order,$status);
           if($this->emptycart_enabled == 'yes'){
             jigoshop_cart::empty_cart();
               }
		}

        $logger->info('response GAA '.json_encode($response_GAA));
        $logger->info('Costo Total '.json_encode('$'.get_option('AMOUNTBUYER'.$_SESSION['Order']->id)));

        return $response_GAA;
    }

    /*
    *  TodoPago Devoluciones Total
    */
    public function voidRequest()
    {

        $http_header = array('Authorization' => get_option('apiKey'));

        $connector    = new Sdk($http_header,  get_option('ambiente'));
        $options = array(
            "Security" => get_option('security'), // API Key del comercio asignada por TodoPago
            "Merchant" => get_option('merchantId'), // Merchant o Nro de comercio asignado por TodoPago
            "RequestKey" => get_option( $_GET['post'])// RequestKey devuelto como respuesta del servicio SendAutorizeRequest
        );
        $resp = $connector->voidRequest($options);

        return json_encode( $resp );
    }

    /*
    *  TodoPago Devolucion Parcial
    */
    public function returnRequest($data)
    {
        $respuesta = $data;

        $http_header = array('Authorization' => get_option('apiKey'));

        $connector    = new Sdk($http_header,  get_option('ambiente'));

        $options = array(
        "Security" => get_option('security'), // API Key del comercio asignada por TodoPago
        "Merchant" => get_option('merchantId'), // Merchant o Nro de comercio asignado por TodoPago
        "RequestKey" => get_option( $_GET['post']), // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
        "AMOUNT" => $_POST['amount'] // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
        );
        $resp = $connector->returnRequest($options);

        return json_encode($resp);
    }
}
