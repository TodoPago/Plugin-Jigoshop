<?php
if (isset($_GET['Answer']) || isset($_GET['Error'])) {
    require_once('../../../wp-load.php');
}

/** Requiere Sdk de TodoPago **/
require_once('lib/vendor/autoload.php');
require_once('lib/todopagopayment.php');
require_once('lib/todopagopaymentbilletera.php');
require_once(dirname(__FILE__) . '/lib/logger.php');
include_once(dirname(__FILE__) . '/../jigoshop/admin/jigoshop-install.php');
register_activation_hook(__FILE__, 'install');


use TodoPago\Sdk as Sdk;
use TodoPago\Data\User as User;

/** Carga de actions **/
add_action('add_meta_boxes', 'todopago_meta_boxes');
add_action('wp_ajax_todopago_credentials', 'getCredentials');
add_action('wp_ajax_nopriv_todopago_credentials', 'getCredentials');
add_action('wp_ajax_todopago_getStatus', 'getStatus');
add_action('wp_ajax_nopriv_todopago_getStatus', 'getStatus');
add_action('wp_ajax_todopago_voidRequest', 'voidRequest');
add_action('wp_ajax_nopriv_todopago_voidRequest', 'voidRequest');
add_action('wp_ajax_todopago_returnRequest', 'returnRequest');
add_action('wp_ajax_nopriv_todopago_returnRequest', 'returnRequest');
add_action('plugins_loaded', 'jigoshop_todopagopayment', 0);

function install()
{
	
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "todopago_gmaps";
    $sql = "CREATE TABLE IF NOT EXISTS $nombreTabla (
      ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      billing_street varchar(60) NOT NULL,
      billing_state varchar(60) NOT NULL,
      billing_city varchar(64) NOT NULL,
      billing_country varchar(100) NOT NULL,
      billing_postalcode varchar(100) NOT NULL,
      shipping_street varchar(60) NOT NULL,
      shipping_state varchar(60) NOT NULL,
      shipping_city varchar(64) NOT NULL,
      shipping_country varchar(100) NOT NULL,
      shipping_postalcode varchar(100) NOT NULL,
      identify_key varchar(100) NOT NULL,
      PRIMARY KEY (ID)
    ) DEFAULT CHARSET=utf8";
    dbDelta($sql);

	
	/* **** 
    $todopagopayment = new todopagopayment();
    $todopagopayment->installCore();
	*/



    //Googleaddress
    $table_name = $wpdb->prefix . "todopago_google_address";
    $charset_collate = $wpdb->get_charset_collate();    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name ( 
            `id` INT NOT NULL AUTO_INCREMENT,
            `md5_hash` VARCHAR(33),
            `street` VARCHAR(100),
            `state` VARCHAR(3),
            `city` VARCHAR(100),
            `country` VARCHAR(3),
            `postal` VARCHAR(50),
            PRIMARY KEY (id)
           ) $charset_collate;";
    dbDelta($sql);


    $table_name = $wpdb->prefix . "todopago_transaccion2";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id INT NOT NULL AUTO_INCREMENT,
          id_orden INT NOT NULL,
          tipo TEXT NOT NULL,
          step DATETIME NOT NULL,
          params TEXT NOT NULL,
          response TEXT NOT NULL,
          returned_key TEXT NULL,
          public_request_key TEXT NULL,
          PRIMARY KEY (id)
          ) $charset_collate;";
    dbDelta($sql);


}

/**funciones de actualización de estado**/
function orden_iniciada($order, $status)
{
    $order->update_status($status, __($status, 'jigoshop'));
}
function orden_aprobada($order, $status)
{
    $order->update_status($status, __($status, 'jigoshop'));
}
function orden_rechazada($order, $status)
{
    $order->update_status($status, __($status, 'jigoshop'));
}
function orden_offline($order, $status)
{
    $order->update_status($status, __($status, 'jigoshop'));
}

/**
Plugin Name: Jigoshop - Todopago Payment Gateway
**/
/** Generador de panel de TodoPago
en la orden del pedido **/
function todopago_meta_boxes()
{
    add_meta_box('todopago-order', "TodoPago pedidos", 'todopago_panel', 'shop_order', 'normal', 'default');
}

function todopago_panel()
{

    include_once('lib/view/todopago_panel.php');
}


/**
 * Add the gateway to JigoShop
 **/
function add_todopagopayment_gateway($methods)
{
    $methods[] = 'todopagopayment';
    return $methods;
}
function add_todopagopaymentbilletera_gateway($methods)
{
    $methods[] = 'todopagopaymentbilletera';
    return $methods;
}
/*
 *  TodoPago Obtener Credenciales
 */
function getCredentials($datos)
{

    /** Envio de datos al script getStatus **/
    //  $instance    = new Admin_Data();
    //$merchantId = $todopagopayment->merchantid;
    //$security   = $todopagopayment->security;

    $nonce = wp_create_nonce('getCredentials');
    $_REQUEST['_wpnonce']=$nonce;
	//echo 'sarasa';

    $todopagopayment = new todopagopayment();
	//echo 'HHH';
	//exit;
    $result = $todopagopayment->getCredentials();
	//echo 'DDD';
    try {
        if ($_POST['mode'] == 'test') {
            update_option('ambiente', 'test');
        } else if ($_POST['mode'] == 'prod') {
            update_option('ambiente', 'prod');
        }
    }
    catch (TodoPago\Exception\ResponseException $e) {
        $result = array(
            "mensajeResultado" => $e->getMessage()
        );

    }
    catch (TodoPago\Exception\ConnectionException $e) {
        $result = array(
            "mensajeResultado" => $e->getMessage()
        );
    }
    catch (TodoPago\Exception\Data\EmptyFieldException $e) {
        $result = array(
            "mensajeResultado" => $e->getMessage()
        );
    }
    echo json_encode($result);
    exit;
    
}

/** Status de la orden **/
function getStatus($datos)
{
    $todopagopayment = new todopagopayment();
    $rta="";

    try {
        //ejecutas el get status
        /** Envio de datos al script getStatus **/
        //  $instance    = new Admin_Data();
        $apiKey      = $todopagopayment->authorization;
        $merchantId  = $todopagopayment->merchantid;
        $operationId = get_option('post');
        $http_header = array(
            'Authorization' => $apiKey
        );
        $connector = new Sdk($http_header, get_option('todopago_environment'));
        $resp      = $connector->getStatus(array(
            'MERCHANT' => $merchantId,
            'OPERATIONID' => $operationId
        ));
        if ($resp) {
            if (isset($resp['Operations']) && is_array($resp['Operations'])) {

                $rta = printGetStatus($resp['Operations'], 0);
            } else {
                $rta = 'No hay operaciones para esta orden.';
            }
        } else {
            $rta = 'No se ecuentra la operación. Esto puede deberse a que la operación no se haya finalizado o a una configuración erronea.';
        }
    } catch (Exception $e) {
        //$this->logger->fatal("Ha surgido un error al consultar el estado de la orden: ", $e);
        $rta = 'ERROR AL CONSULTAR LA ORDEN';
    }
    echo $rta;
    exit;
}
function printGetStatus($array, $indent) {
    $rta = '';
    foreach ($array as $key => $value) {
        if ($key !== 'nil' && $key !== "@attributes") {
            if (is_array($value) ){
                $rta .= str_repeat("-", $indent) . "$key: <br/>";
                $rta .= printGetStatus($value, $indent + 2);
            } else {
                $rta .= str_repeat("-", $indent) . "$key: $value <br/>";
            }
        }
    }

    return $rta;
}







/*
 *  TodoPago Devoluciones Total
 */
function voidRequest()
{
    $amount=$_POST['amount'];
    //echo "traza voidrequest - post: ".get_option('post');
    $todopagopayment = new todopagopayment();
    $resp=$todopagopayment->voidRequest_core(get_option('post'), $amount);
    echo json_encode($resp, true);
    die;

/*

    $orderDTO = new \TodoPago\Core\Order\OrderDTO();
    $orderDTO->setOrderId(get_option('post'));
    $orderDTO->setRefundAmount(0);
    $resp=$this->core->process_refund($orderDTO);
    echo $resp;
  */

/*
    $todopagopayment = new todopagopayment();

    $logger = $todopagopayment->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $todopagopayment->todopago_environment, get_current_user_id(), get_option('post'), true);

    $logger->info('Devolucion Total Request:' . get_option('post'));

    $logger->info('Devolucion Total Response:' . json_encode($resp));

    echo json_encode($resp);
    exit;
*/








/*
    die;




    $apiKey      = $todopagopayment->authorization;
    $security    = $todopagopayment->security;
    $merchantId  = $todopagopayment->merchantid;
    $operationId = get_option('post');
    $requestKey  = get_option($operationId);

    $http_header = array(
        'Authorization' => $apiKey
    );

    $connector = new Sdk($http_header, get_option('todopago_environment'));
    $options   = array(
        "Security" => $security, // API Key del comercio asignada por TodoPago
        "Merchant" => $merchantId, // Merchant o Nro de comercio asignado por TodoPago
        "RequestKey" => $requestKey // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
    );


    $logger->info('Devolucion Total Request:' . json_encode($options));

    $resp = $connector->voidRequest($options);

    $logger->info('Devolucion Total Response:' . json_encode($resp));

    echo json_encode($resp);
    exit;
    */
}

/*
 *  TodoPago Devolucion Parcial
 */
function returnRequest()
{
    $todopagopayment = new todopagopayment();

    $logger = $todopagopayment->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $todopagopayment->todopago_environment, get_current_user_id(), get_option('post'), true);

    $apiKey      = $todopagopayment->authorization;
    $security    = $todopagopayment->security;
    $merchantId  = $todopagopayment->merchantid;
    $operationId = get_option('post');
    $requestKey  = get_option($operationId);

    $http_header = array(
        'Authorization' => $apiKey
    );

    $connector = new Sdk($http_header, get_option('todopago_environment'));

    $options = array(
        "Security" => $security, // API Key del comercio asignada por TodoPago
        "Merchant" => $merchantId, // Merchant o Nro de comercio asignado por TodoPago
        "RequestKey" => $requestKey, // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
        "AMOUNT" => $_POST['amount'] // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
    );

    $logger->info('Devolucion Parcial Request:' . json_encode($options));

    $resp = $connector->returnRequest($options);

    $logger->info('Devolucion Parcial Response:' . json_encode($resp));

    echo json_encode($resp);
    exit;
}

function cargaCostoFinanciero($operation_id)
{
    global $wpdb;
    $total_amount = get_option('AMOUNTBUYER' . $operation_id);

    $results                = $wpdb->get_results('SELECT * FROM wp_postmeta WHERE post_id = ' . $operation_id . '', OBJECT);
    $results                = unserialize($results[0]->meta_value);
    $results['order_total'] = $total_amount;
    $serialized_array       = serialize($results);
    $sql                    = "UPDATE `wp_postmeta` SET `meta_value` = '" . $serialized_array . "' WHERE `meta_key` = 'order_data' AND `post_id` = '" . $operation_id . "'";
    $wpdb->query($sql);
}

function jigoshop_todopagopayment()
{
	/*
	var_dump($_POST);
	die;
	*/
	
	if( $_POST["option_page"]=="jigoshop_options" ){
        add_filter('jigoshop_payment_gateways', 'add_todopagopayment_gateway', 50);
        add_filter('jigoshop_payment_gateways', 'add_todopagopaymentbilletera_gateway', 50);
	}else{ //No está modificando el formulario de config
		if(!$_POST['action']){ 
			//echo "entró en if de funcion jigoshop_todopagopayment";
			/*	
			if (!class_exists('jigoshop_payment_gateway')){
				return; // if the Jigoshop payment gateway class is not available, do nothing
			}
			*/
			//echo "Add action";
			add_filter('jigoshop_payment_gateways', 'add_todopagopayment_gateway', 50);
            add_filter('jigoshop_payment_gateways', 'add_todopagopaymentbilletera_gateway', 50);
			
            /*
			echo "después de add filter";
			die;
			*/
			

			//$todopagopayment = new todopagopayment();
		}
   }
    
/*

    $merchantId  = $todopagopayment->merchantid;
    $security    = $todopagopayment->security;
    */

//    $operationId = $_GET['post'];

}
if (isset($_GET['Error'])) {
    include('../../../wp-config.php');    
    require_once('../../../wp-load.php');
    add_filter('show_admin_bar', '__return_false');

    get_header();
    $flagMensajeErrorMostrado=false;
?>
  <article id="error-message" class="type-page status-publish hentry" style="width:666px;">
      <header class="entry-header">
      </header>
      <?php
    if (isset($_GET['Error'])) {
        echo '<p>' . $_GET['Error'] . '</p>';
        echo '<p>Orden #' . $_SESSION['Order']->id . '</p>';

        $flagMensajeErrorMostrado=true;
    }


    $configVaciaCarrito=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_emptycart_enabled');

    $todopagopayment = new todopagopayment();
    $response_GAA    = $todopagopayment->call_GAA();

    //var_dump($response_GAA);

    cargaCostoFinanciero($response_GAA['response_GAA']['Payload']['Request']['OPERATIONID']);


?>
      <div class="button-alt"> <a href=" <?php
    echo get_home_url();
?>" style="color:white">Volver al inicio</a></div>

    <?php if($configVaciaCarrito!='yes'){ ?>
        <div class="button-alt"> <a href="<?php
        echo get_home_url() . '/index.php/cart';
    ?>" style="color:white">Ir al carrito de compras</a></div>
    <?php }else{

        jigoshop_cart::empty_cart();
    } ?>


      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
  </article>
  <?php
    get_footer();

}

if (isset($_GET['Answer']) AND $flagMensajeErrorMostrado==false) {

    include('../../../wp-config.php');
    require_once('../../../wp-load.php');

    $todopagopayment = new todopagopayment();
    $response_GAA    = $todopagopayment->call_GAA();

    //var_dump($response_GAA);

    cargaCostoFinanciero($response_GAA['response_GAA']['Payload']['Request']['OPERATIONID']);

    if ($response_GAA['response_GAA']['StatusCode'] == '-1') {
        //header('Location: ../../../index.php/checkout/thanks');
        get_header(); ?>
        <article id="error-message" class="type-page status-publish hentry" style="width:666px;">
          <header class="entry-header">
          </header>
          
          <p>¡Gracias! Tu orden fue procesada exitosamente.</p>
          <p>Orden #<?php echo $_SESSION['Order']->id; ?></p>

    
          <div class="button-alt"> <a href=" <?php echo get_home_url(); ?>" style="color:white">Continuar comprando</a></div>


          <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>

        </article>
        <?php














        exit;
    } else {
        require_once('../../../wp-load.php');
        add_filter('show_admin_bar', '__return_false');

        get_header();
?>
  <article id="error-message" class="type-page status-publish hentry" style="width:666px;">
      <header class="entry-header">
      </header>

      <?php
        if (isset($response_GAA['response_GAA']['StatusMessage'])) {
            echo '<p>' . $response_GAA['response_GAA']['StatusMessage'] . '</p>';
            echo '<p>Orden #'. $_SESSION['Order']->id . '</p>';
        }
?>
      <div class="button-alt"> <a href=" <?php
        echo get_home_url();
?>" style="color:white">Volver al inicio</a></div>

    <?php
    $configVaciaCarrito=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_emptycart_enabled');

    if($configVaciaCarrito!='yes'){
    ?>
    <div class="button-alt">
        <a href="<?php echo get_home_url() . '/index.php/cart'; ?>" style="color:white">Ir al carrito de compras</a>
    </div>
    <?php
    }else{
        jigoshop_cart::empty_cart();
    }
    ?>

      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
  </article>
  <?php
        get_footer();

    }
}

/** Registro de scripts **/
$basename = plugin_basename(dirname(__FILE__));
$baseurl  = plugins_url();
wp_deregister_script('jquery');
wp_enqueue_script('jquery');
wp_register_script('getStatus', "{$baseurl}/{$basename}/js/tpajax.js", array(
    'jquery'
), '1.1', true);
wp_enqueue_script('getStatus');
