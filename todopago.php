<?php
if(isset($_GET['Answer']) || isset($_GET['Error'])) {
    require_once('../../../wp-load.php');
}

/** Requiere Sdk de TodoPago **/
require_once('lib/vendor/autoload.php');
require_once('lib/todopagopayment.php');
require_once(dirname(__FILE__) .'/lib/logger.php');
include_once(dirname(__FILE__).'/../jigoshop/admin/jigoshop-install.php');

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

/** Verifica si hay una transacción en curso, en caso afirmativo ejecuta el GAA**/


/**funciones de actualización de estado**/
function orden_iniciada($order,$status)
{
	$order->update_status($status, __($status, 'jigoshop'));
}
function orden_aprobada($order,$status)
{
	$order->update_status($status, __($status, 'jigoshop'));
}
function orden_rechazada($order,$status)
{
	$order->update_status($status, __($status, 'jigoshop'));
}
function orden_offline($order,$status)
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

/*
*  TodoPago Obtener Credenciales
*/
function getCredentials($datos)
{
    $todopagopayment = new todopagopayment();

    /** Envio de datos al script getStatus **/
  //  $instance    = new Admin_Data();
    $merchantId  = $todopagopayment->merchantid;
    $security = $todopagopayment->security;

    try {
  $http_header = array();
        $connector = new Sdk($http_header, $_POST['mode']);
        $userInstance = new User($_POST['user'], $_POST['password']);
        $rta          = $connector->getCredentials($userInstance);

        $apiKey= $rta->getApiKey();
        $security = explode(" ", $rta->getApikey()); //substr($apiKey, 8);
        $result = array('merchantId' => $rta->getMerchant(), 'ApiKey' => $rta->getApiKey(), 'security' => $security[1]);

        if ($_POST['mode'] == 'test'){
            update_option( 'ambiente', 'test');
        }else if ($_POST['mode'] == 'prod'){
            update_option( 'ambiente', 'prod');
        }
    }catch(TodoPago\Exception\ResponseException $e){
        $result = array(
            "mensajeResultado" => $e->getMessage()
        );

    }catch(TodoPago\Exception\ConnectionException $e){
        $result = array(
            "mensajeResultado" => $e->getMessage()
        );
    }catch(TodoPago\Exception\Data\EmptyFieldException $e){
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

    /** Envio de datos al script getStatus **/
    //  $instance    = new Admin_Data();
    $apiKey  = $todopagopayment->authorization;
    $merchantId  = $todopagopayment->merchantid;
    $operationId = get_option('post');

    $http_header = array(
        'Authorization' => $apiKey
    );

    $connector = new Sdk($http_header,  get_option('todopago_environment'));
    $resp      = $connector->getStatus(array(
        'MERCHANT' => $merchantId,
        'OPERATIONID' => $operationId
    ));

    echo json_encode($resp);
    exit;
}

/*
*  TodoPago Devoluciones Total
*/
 function voidRequest()
{

        $todopagopayment = new todopagopayment();

        $logger = $todopagopayment->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $todopagopayment->todopago_environment , get_current_user_id(), get_option('post'), true);

        $apiKey  = $todopagopayment->authorization;
        $security  = $todopagopayment->security;
        $merchantId  = $todopagopayment->merchantid;
        $operationId = get_option('post');
        $requestKey= get_option($operationId);

        $http_header = array('Authorization' => $apiKey);

        $connector    = new Sdk($http_header,  get_option('todopago_environment'));
        $options = array(
            "Security" => $security, // API Key del comercio asignada por TodoPago
            "Merchant" => $merchantId, // Merchant o Nro de comercio asignado por TodoPago
            "RequestKey" => $requestKey// RequestKey devuelto como respuesta del servicio SendAutorizeRequest
        );


        $logger->info('Devolucion Total Request:'. json_encode($options));

        $resp = $connector->voidRequest($options);

        $logger->info('Devolucion Total Response:'. json_encode($resp));

        echo json_encode($resp);
		exit;
    }

    /*
    *  TodoPago Devolucion Parcial
    */
    function returnRequest()
    {    $todopagopayment = new todopagopayment();

        $logger = $todopagopayment->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $todopagopayment->todopago_environment , get_current_user_id(),  get_option('post'), true);

        $apiKey  = $todopagopayment->authorization;
        $security  = $todopagopayment->security;
        $merchantId  = $todopagopayment->merchantid;
        $operationId = get_option('post');
        $requestKey= get_option($operationId);

        $http_header = array('Authorization' => $apiKey);

        $connector    = new Sdk($http_header,  get_option('todopago_environment'));

        $options = array(
            "Security" => $security, // API Key del comercio asignada por TodoPago
            "Merchant" => $merchantId, // Merchant o Nro de comercio asignado por TodoPago
            "RequestKey" => $requestKey, // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
            "AMOUNT" => $_POST['amount'] // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
        );

        $logger->info('Devolucion Parcial Request:'. json_encode($options));

        $resp = $connector->returnRequest($options);

        $logger->info('Devolucion Parcial Response:'. json_encode($resp));

        echo json_encode($resp);
	  	exit;
    }

function cargaCostoFinanciero($operation_id)
{
  global $wpdb;
   $total_amount = get_option('AMOUNTBUYER'.$operation_id);

  $results = $wpdb->get_results( 'SELECT * FROM wp_postmeta WHERE post_id = '.$operation_id.'', OBJECT );
  $results = unserialize($results[0]->meta_value);
  $results['order_total']=$total_amount;
  $serialized_array=serialize($results);
  $sql = "UPDATE `wp_postmeta` SET `meta_value` = '".$serialized_array."' WHERE `meta_key` = 'order_data' AND `post_id` = '".$operation_id."'";
  $wpdb->query($sql);
}

function jigoshop_todopagopayment()
{
    if (!class_exists('jigoshop_payment_gateway'))
        return; // if the Jigoshop payment gateway class is not available, do nothing

    add_filter('jigoshop_payment_gateways', 'add_todopagopayment_gateway', 50);

    $todopagopayment = new todopagopayment();


    $merchantId  = $todopagopayment->merchantid;
    $security = $todopagopayment->security;
    $operationId = $_GET['post'];

}
if(isset($_GET['Error'])) {
      require_once('../../../wp-load.php');
  add_filter('show_admin_bar', '__return_false');

  get_header();
             ?>
  <article id="error-message" class="type-page status-publish hentry" style="width:666px;">
      <header class="entry-header">
      </header>
      <?php if (isset($_GET['Error'])){ echo '<p>' . $_GET['Error'] .'</p>';}?>
      <div class="button-alt"> <a href=" <?php  echo get_home_url();  ?>" style="color:white">Volver al inicio</a></div>
    <div class="button-alt"> <a href=" <?php  echo get_home_url(). '/index.php/cart';  ?>" style="color:white">Ir al carrito de compras</a></div>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
  </article>
  <?php
  get_footer();

}

if (isset($_GET['Answer'])) {

    include('../../../wp-config.php');
    require_once('../../../wp-load.php');

    $todopagopayment = new todopagopayment();
    $response_GAA = $todopagopayment->call_GAA();

    cargaCostoFinanciero($response_GAA['Payload']['Request']['OPERATIONID']);

    if ($response_GAA['StatusCode'] == '-1') {
             header('Location: ../../../index.php/checkout/thanks');
             exit;
    } else {
      require_once('../../../wp-load.php');
  add_filter('show_admin_bar', '__return_false');

  get_header();
             ?>
  <article id="error-message" class="type-page status-publish hentry" style="width:666px;">
      <header class="entry-header">
      </header>
      <?php if (isset($response_GAA['StatusMessage'])){ echo '<p>' . $response_GAA['StatusMessage'] .'</p>';}?>
      <div class="button-alt"> <a href=" <?php  echo get_home_url();  ?>" style="color:white">Volver al inicio</a></div>
    <div class="button-alt"> <a href=" <?php  echo get_home_url(). '/index.php/cart';  ?>" style="color:white">Ir al carrito de compras</a></div>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
  </article>
  <?php
  get_footer();

    }
}

/** Registro de scripts **/
$basename = plugin_basename(dirname(__FILE__));
$baseurl = plugins_url();
wp_deregister_script('jquery');
wp_enqueue_script('jquery');
wp_register_script('getStatus', "{$baseurl}/{$basename}/js/tpajax.js", array('jquery'), '1.1', true);
wp_enqueue_script('getStatus');
