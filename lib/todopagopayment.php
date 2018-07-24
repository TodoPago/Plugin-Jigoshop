<?php
require_once('vendor/autoload.php');
require_once(dirname(__FILE__) . '/../../jigoshop/gateways/gateway.class.php');
require_once(dirname(__FILE__) . '/ControlFraude/ControlFraudeFactory.php');
require_once(dirname(__FILE__) . '/logger.php');
require_once(dirname(__FILE__) . '/StatusCodeCS.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


define('TODOPAGO_PLUGIN_VERSION', '1.7.0');
define('TP_FORM_EXTERNO', 'ext');
define('TP_FORM_HIBRIDO', 'hib');
define('TODOPAGO_DEVOLUCION_OK', 2011);
define('TODOPAGO_FORMS_PROD', 'https://forms.todopago.com.ar');
define('TODOPAGO_FORMS_TEST', 'https://developers.todopago.com.ar');

use TodoPago\Sdk as Sdk;
use TodoPago\Data\User as User;




require_once(dirname(__FILE__) . '/../Core/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../Core/ControlFraude/ControlFraudeFactory.php');


use TodoPago\Core;
use TodoPago\Core\Address\AddressDTO;
use TodoPago\Core\Config\ConfigDTO;
use TodoPago\Core\Customer\CustomerDTO;
use TodoPago\Core\Exception\ExceptionBase;
use TodoPago\Utils\Constantes;





class todopagopayment extends jigoshop_payment_gateway
{

    const TP_PLUGIN_MADRE = 'JigoShop';
    const TP_PLUGIN_GITHUB_API = 'https://api.github.com/repos/TodoPago/Plugin-Jigoshop/releases/latest';
    const TP_PLUGIN_GITHUB_REPO = 'https://github.com/TodoPago/Plugin-Jigoshop';


    public function __construct()
    {
		
		//echo "instance ";
        parent::__construct();
		
		
		
        $this->jigoshop_options = Jigoshop_Base::get_options();
        $this->id               = 'todopagopayment';
        $this->has_fields       = false;
        $this->enabled          = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_enabled');
        $this->title            = "Todo Pago";
        $this->description      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_description');
        $this->icon             = "https://todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg";
        $this->instant          = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_instant');
        $this->language         = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_language');

        $this->mode     = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_mode');
        $this->user     = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_user');
        $this->password = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_password');
        $this->form     = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_form');

        $this->maxcuotas         = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxcuotas');
        $this->maxcuotas_enabled = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxinstallments_enabled');
        $this->timeout      = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_timeout');
        $this->timeout_enabled = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_timeout_enabled');
        $this->emptycart_enabled = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_emptycart_enabled');
        $this->gmaps_enabled     = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_gmaps_enabled');

        add_option('order_id', 'None', '', 'yes');
        $this->todopago_environment = $this->mode;
        $this->authorization        = ($this->todopago_environment == 'test') ? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_dev') : Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_prod');
        $this->merchantid           = ($this->todopago_environment == 'test') ? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_dev') : Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_prod');
        $this->security             = ($this->todopago_environment == 'test') ? Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_dev') : Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_prod');

        $this->orden_iniciada  = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_iniciada');
        $this->orden_aprobada  = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_aprobada');
        $this->orden_rechazada = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_rechazada');
        $this->orden_offline   = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_offline');

        add_option('apiKey', 'None', '', 'yes');
        add_option('merchantId', 'None', '', 'yes');
        add_option('security', 'None', '', 'yes');
        add_option('order_id', 'None', '', 'yes');
        add_option('todopago_environment', 'None', '', 'yes');
        add_option('post', 'None', '', 'yes');
//        add_option($orderId, 'None', '', 'yes');

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

        $this->settings['deadLine']='10';
        $this->settings['timeoutValor']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_timeout');
        $this->settings['timeout_enabled']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_timeout_enabled');

        $this->settings['maxCuotas']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxcuotas');
        $this->settings['maxCuotas_enabled']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_maxinstallments_enabled');

        $this->settings['version']=TODOPAGO_PLUGIN_VERSION;

        $this->settings['security_test']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_dev');
        $this->settings['merchant_id_test']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_dev');
        $this->settings['security_prod']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_security_prod');
        $this->settings['merchant_id_prod']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_merchantid_prod');

        $this->settings['http_header_prod']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_prod');
        $this->settings['http_header_test']=Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_authorization_dev');

        if($this->mode=="test"){
            $this->ambiente=Constantes::TODOPAGO_TEST;    
        }else{
            $this->ambiente=Constantes::TODOPAGO_PROD;
        }

        if(Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_form')=='integrado'){
            $this->tipoForm=Constantes::TODOPAGO_HIBRIDO;
        }else{
            $this->tipoForm=Constantes::TODOPAGO_EXT;
        }    

        //Datos generales
        $this->version = $this->todopago_getValueOfArray($this->settings, 'version');
        $this->title = "Todo Pago";
        $this->description = $this->todopago_getValueOfArray($this->settings, 'description');
        //$this->ambiente = $this->todopago_getValueOfArray($this->settings, 'ambiente');
        $this->clean_carrito = $this->todopago_getValueOfArray($this->settings, 'clean_carrito');
        $this->tipo_segmento = $this->todopago_getValueOfArray($this->settings, 'tipo_segmento');

        //$this -> canal_ingreso  = $this -> settings['canal_ingreso'];
        $this->deadline = $this->todopago_getValueOfArray($this->settings, 'deadline');
        $this->tipo_formulario = $this->todopago_getValueOfArray($this->settings, 'tipo_formulario');
        $this->max_cuotas = $this->todopago_getValueOfArray($this->settings, 'max_cuotas');
        $this->enabledCuotas = $this->todopago_getValueOfArray($this->settings, 'enabledCuotas');

        //Datos credentials;
        $this->credentials = $this->todopago_getValueOfArray($this->settings, 'credentials');
        $this->user = $this->todopago_getValueOfArray($this->settings, 'user');
        $this->password = $this->todopago_getValueOfArray($this->settings, 'password');
        $this->btnCredentials = $this->todopago_getValueOfArray($this->settings, 'btnCredentials');

        //Datos estado de pedidos
        $this->estado_inicio = $this->todopago_getValueOfArray($this->settings, 'estado_inicio');
        $this->estado_aprobacion = $this->todopago_getValueOfArray($this->settings, 'estado_aprobacion');
        $this->estado_rechazo = $this->todopago_getValueOfArray($this->settings, 'estado_rechazo');
        $this->estado_offline = $this->todopago_getValueOfArray($this->settings, 'estado_offline');

        //Timeout 
        $this->expiracion_formulario_personalizado = $this->todopago_getValueOfArray($this->settings, 'expiracion_formulario_personalizado');
        $this->timeout_limite = $this->todopago_getValueOfArray($this->settings, 'timeout_limite');

        $this->gmaps_validacion = $this->todopago_getValueOfArray($this->settings, 'gmaps_validacion');


        $this->wpnonce_credentials = $this->todopago_getValueOfArray($this->settings, 'wpnonce');

        $this->msg['message'] = "";
        $this->msg['class'] = "";

        $urlPath = "wp-content/plugins/".plugin_basename(dirname(__DIR__))."/";

        
        if($this->gmaps_enabled=='no'){
            $this->gmaps_enabled=false;
        }else{
            $this->gmaps_enabled=true;
        }

        //Con Gmaps
        //$this->setCoreConfig(new ConfigDTO($this->ambiente, $this->tipoForm, false, false, $this->gmaps_enabled, $urlPath, self::TP_PLUGIN_MADRE, WC_VERSION, $wp_version, TODOPAGO_PLUGIN_VERSION));

        global $wp_version;
        $this->setCoreConfig(new ConfigDTO($this->ambiente, $this->tipoForm, false, false, false, $urlPath, self::TP_PLUGIN_MADRE, jigoshop::jigoshop_version(), $wp_version, TODOPAGO_PLUGIN_VERSION));

        $merchant = $this->buildMerchantDTO();

        $this->core = new Core($this->getCoreConfig(), $merchant);


        $config = $this->core->getConfigDTO();

        $basename = plugin_basename(dirname(__FILE__));
        $baseurl  = plugins_url();
        $return_URL_ERROR = "{$baseurl}/{$basename}/../todopago.php?second_step=true";
//        $return_URL_OK    = "{$baseurl}/{$basename}/../todopago.php?sessionid={$sessionid}&second_step=true";
        $return_URL_OK    = "{$baseurl}/{$basename}/../todopago.php?second_step=true";


        $config->setUrlSuccess("$return_URL_OK");
        $config->setUrlError("$return_URL_ERROR");

        $opcionales = $this->buildOpcionales();
        $config->setArrayOpcionales($opcionales);
        
        $config->setIsBilletera(get_class($this));
        
        $this->core->setTpLogger($this->tplogger);
        $this->core->setConfigModel($config);   
    }


    function installCore(){
        $core = new Core();
        $core->todopago_core_install();    
    }

    /*
     *  TodoPago Obtener Credenciales
     */
    function getCredentials()
    {

        $core = new Core();
        $result = $core->get_credentials();

        return $result;
    }

    /**
    * @param mixed $coreConfig
    */
    public function setCoreConfig($coreConfig)
    {
        $this->coreConfig = $coreConfig;
    }

        /**
         * @return mixed
         */
        public function getCoreConfig()
        {
            return $this->coreConfig;
        }

        protected function buildOpcionales()
        {
            if($this->settings['maxCuotas_enabled']=="yes"){
                $this->settings['maxCuotas_enabled']="si";
            }

            if($this->settings['timeout_enabled']=="yes"){
                $this->settings['timeout_enabled']="si";
            }

            

            $opcionales = Array();
            $opcionalesBenchmark = array(
                'deadLine' => $this->settings['deadLine'],
                'timeoutValor' => $this->settings['timeoutValor'],
                'enabledTimeoutForm' => "{$this->settings['timeout_enabled']}",

                'maxCuotas' => $this->settings['maxCuotas'],
                'enabledCuotas' => "{$this->settings['maxCuotas_enabled']}"
            );
            foreach ($opcionalesBenchmark as $parametro => $valor) {
                if (!empty($valor))
                    $opcionales[$parametro] = $valor;
            }
            return $opcionales;
        }


        function todopago_getValueOfArray($array, $key)
        {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            } else {
                return FALSE;
            }
        }

        protected function buildMerchantDTO()
        {
            $http_header = $this->getHttpHeader();
            $esProductivo = $this->ambiente == Constantes::TODOPAGO_PROD;
            $apikey = $esProductivo ? $this->settings['security_prod'] : $this->settings['security_test'];


            $merchantId = strval($esProductivo ? $this->settings['merchant_id_prod'] : $this->settings['merchant_id_test']);
            $merchant = new TodoPago\Core\Merchant\MerchantDTO();
            $merchant->setMerchantId($merchantId);
            $merchant->setApiKey($apikey);
            $merchant->setHttpHeader($http_header);
            return $merchant;
        }


        private function getHttpHeader()
        {
            $esProductivo = $this->ambiente == "prod";
            $http_header = $esProductivo ? $this->settings['http_header_prod'] : $this->settings['http_header_test'];

            $header_decoded = json_decode(html_entity_decode($http_header, TRUE));
            return (!empty($header_decoded)) ? $header_decoded : array("authorization" => $http_header);
        }

    public function _obtain_logger($php_version, $jigoshop_version, $todopago_plugin_version, $endpoint, $customer_id, $order_id, $is_payment)
    {
        // global $tplogger
        $this->tplogger->setPhpVersion($php_version);
        $this->tplogger->setCommerceVersion($jigoshop_version);
        $this->tplogger->setPluginVersion($todopago_plugin_version);
        $this->tplogger->setEndPoint($endpoint);
        $this->tplogger->setCustomer($customer_id);
        $this->tplogger->setOrder($order_id);


        return $this->tplogger->getLogger(true);
    }

    /**
     * Default Option settings for WordPress Settings API using the Jigoshop_Options class
     *
     * These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
     *
     */

    protected function get_default_options()
    {
        $class_name = get_class($this);
        if($class_name != "todopagopaymentbilletera") 
        apply_filters('todopago_github_update', self::TP_PLUGIN_GITHUB_API, self::TP_PLUGIN_GITHUB_REPO);

        $defaults[] = array(
            'name' => __('Todo Pago', 'jigoshop'),
            'type' => 'title',
            'desc' => __('', 'jigoshop')
        );




       $defaults[] = array(
           'name' => __('
           <script type="text/javascript">
               console.log("TODOPAGO_PLUGIN_VERSION: '.TODOPAGO_PLUGIN_VERSION.'");

               versionCompare = function(left, right) {
                   if (typeof left + typeof right != "stringstring")
                       return false;
      

                   var a = left.split(".")
                   ,   b = right.split(".")
                   ,   i = 0, len = Math.max(a.length, b.length);
                       
                   for (; i < len; i++) {
                       if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
                           return 1;
                       } else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
                           return -1;
                       }
                   }
                   
                   return 0;
               }

               jQuery.ajax({
                 method: "GET",
                 url: "https://api.github.com/repos/TodoPago/Plugin-Jigoshop/releases/latest",
                 context: document.body,

                headers: {
                    "Authorization":"token 21600a0757d4b32418c54e3833dd9d47f78186b4",
                }

               }).done(function(jsonRes) {
                   var versionActual="'.TODOPAGO_PLUGIN_VERSION.'";
                   versionActual=versionActual.replace(/[^0-9.]/g, "");
                   console.log("actual: "+versionActual);

                   var versionProdHuman=jsonRes.tag_name;
                   var versionProd=jsonRes.tag_name;
                   versionProd=versionProd.replace(/[^0-9.]/g, "");
                   console.log("en api: "+versionProd);

                   if(versionCompare( versionProd, versionActual )>0){
                       console.log("Tiene una versión instalada vieja");
                       jQuery("#tp_configuracion_version_actualiza").show();
                       //jQuery("#tp_configuracion_version_produccion").text(versionProdHuman);
                   }
               });    
           </script>

           <div id="tp_configuracion_version_actualiza" style="display: none">
               <p>Se encuentra disponible una versi&oacute;n m&aacute;s reciente del plugin de Todo Pago, puede consultarla desde <span id="tp_configuracion_version_produccion"></span>
               <a href="https://github.com/TodoPago/Plugin-Jigoshop" target="_blank">aqu&iacute;</a></p>
           </div>', 'jigoshop'),
           'type' => 'title',
           'desc' => __('', 'jigoshop')
       ); 



        // List each option in order of appearance with details
        $defaults[] = array(
            'name' => __('Habilitar Todo Pago', 'jigoshop'),
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
        $defaults[] = array(
            'name' => __('Obtener credenciales ambiente desarrollo', 'jigoshop'),
            'type' => 'title',
            'desc' => __('', 'jigoshop')
        );
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
        $defaults[] = array(
            'name' => __('Obtener credenciales ambiente Produccion', 'jigoshop'),
            'type' => 'title',
            'desc' => __('', 'jigoshop')
        );
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
        $defaults[] = array(
            'name' => __('Activar servicio de geolocalización con Gmaps', 'jigoshop'),
            'desc' => '',
            'tip' => __('Se obtiene la dirección y otros datos correctamente formateados utilizando el servicio de geolocalización de Google.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_gmaps_enabled',
            'std' => 'no',
            'type' => 'checkbox',
            'choices' => array(
                'no' => __('No', 'jigoshop'),
                'yes' => __('Yes', 'jigoshop')
            )
        );
        $defaults[] = array(
            'name' => __('Activar Timeout', 'jigoshop'),
            'desc' => '',
            'tip' => __('Ofrecera el máximo de cuotas al comprador de la cantidad seleccionada en el combo anterior.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_timeout_enabled',
            'std' => 'no',
            'type' => 'checkbox',
            'choices' => array(
                'no' => __('No', 'jigoshop'),
                'yes' => __('Yes', 'jigoshop')
            )
        );
        $defaults[] = array(
            'name' => __('Billetera en Checkout', 'jigoshop'),
            'desc' => '',
            'tip' => __('Seleccione el banner que desea mostrar para billetera.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_billetera',
            'type' => 'radio',
            'extra' => array('vertical'),
            'choices' => array(
                'https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta1.jpg' => __('<img src="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta1.jpg" style="vertical-align: middle;">', 'jigoshop'),
                'https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta2.jpg' => __('<img src="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta2.jpg" style="vertical-align: middle;">', 'jigoshop'),
                'https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta3.jpg' => __('<img src="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta3.jpg" style="vertical-align: middle;">', 'jigoshop'),
            ),
        );

        $defaults[] = array(
            'name' => __('Timeout', 'jigoshop'),
            'desc' => '',
            'tip' => __('Generate life time of From.', 'jigoshop'),
            'id' => 'jigoshop_todopagopayment_timeout',
            'std' => '',
            'type' => 'number'
        );

        $defaults[] = array(
            'name' => __('Estados de pedidos', 'jigoshop'),
            'type' => 'title',
            'desc' => __('', 'jigoshop')
        );


        $defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido iniciada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_iniciada',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'pending' => 'Pending Payment',
                'processing' => 'Processing',
                'on-hold' => 'On Hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed'
            )
        );
        $defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido aprobada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_aprobada',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'pending' => 'Pending Payment',
                'processing' => 'Processing',
                'on-hold' => 'On Hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed'
            )
        );
        $defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido rechazada', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_rechazada',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'pending' => 'Pending Payment',
                'processing' => 'Processing',
                'on-hold' => 'On Hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed'
            )
        );
        $defaults[] = array(
            'name' => __('Estado cuando la transacción ha sido offline', 'jigoshop'),
            'desc' => '',
            'id' => 'jigoshop_todopagopayment_offline',
            'std' => '',
            'type' => 'select',
            'choices' => array(
                'pending' => 'Pending Payment',
                'processing' => 'Processing',
                'on-hold' => 'On Hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed'
            )
        );



        //Validación para campo timeout
        $defaults[] = array(
            'name' => __('
                <script type="text/javascript">
                function isEmpty(value) {
                 return typeof value == "string" && !value.trim() || typeof value == "undefined" || value === null;
                }

                console.log("Validación campo timeout");
                console.log("Debug: " + jQuery("#jigoshop_todopagopayment_timeout").val());

                //Si está vacío lo deja en el mínimo
                if(
                    jQuery("#jigoshop_todopagopayment_timeout").val()=="" || 
                    jQuery("#jigoshop_todopagopayment_timeout").val()=="0" || 
                    jQuery("#jigoshop_todopagopayment_timeout").val()==null
                ){
                    jQuery("#jigoshop_todopagopayment_timeout").val("300000");
                }

                jQuery("#jigoshop_todopagopayment_timeout").attr("min", "300000");
                jQuery("#jigoshop_todopagopayment_timeout").attr("max", "21600000");
                </script>
                ', 'jigoshop'),
            'type' => 'title',
            'desc' => __('', 'jigoshop')
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


    public function todopago_persistRequestKey($order_id, $request_key)
    {
        update_post_meta($order_id, 'request_key', $request_key);
    }

    private function load_products(jigoshop_order $order)
    {
        $products = array();

        foreach ($order->items as $product) {
            $ProductDTO = new \TodoPago\Core\Product\ProductDTO();
            
            $productJigo = new jigoshop_product($product['id']);
            $ProductDTO = new \TodoPago\Core\Product\ProductDTO();

            $totalAmount=$product['cost_inc_tax'] * $product['qty'];
            $sku=$productJigo->get_sku();

            $ProductDTO->setProductName($product['name']);
            $ProductDTO->setProductCode("$sku");
            $ProductDTO->setProductDescription($product['name']);            
            $ProductDTO->setProductSKU("{$product['id']}");
            $ProductDTO->setTotalAmount($totalAmount);
            $ProductDTO->setQuantity($product['qty']);
            $ProductDTO->setPrice($product['cost_inc_tax']);

            $products[] = $ProductDTO;
        }
        //var_dump($products);
        //die("820");
        return $products;
    }

    /**
     * Generate the todopago button link
     **/
    public function generate_form($order_id)
    {
        $logger = $this->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $this->todopago_environment, $order_id, $order_id, true);
        update_option('orderId', $order_id);
        //Se genera orden de compra
        $order = new jigoshop_order($order_id);

        /** Se genera payload para enviar el sendAuthorizeRequest **/
        $arrayorder = (array) $order;

        $controlFraude = ControlFraudeFactory::get_ControlFraude_extractor('Retail', $order, $arrayorder);

        $datosCs   = $controlFraude->getDataCF();
        $sessionid = $order->order_key;
        $home      = home_url();

        $basename = plugin_basename(dirname(__FILE__));
        $baseurl  = plugins_url();

        $return_URL_ERROR = "{$baseurl}/{$basename}/../todopago.php?second_step=true";
        $return_URL_OK    = "{$baseurl}/{$basename}/../todopago.php?sessionid={$sessionid}&second_step=true";

        $esProductivo = $this->testmode == "no";

        $this->core->setTpLogger($logger);

        $logger->error("Test");
        


        //Hace el SAR pero del core
        $customerBillingDTO = $this->buildCustomerBillingDTO($order);
        $customerShippingDTO = $this->buildCustomerShippingDTO($order);


        $addressBilling = $this->buildAddressBillingDTO($order);
        $addressShipping = $this->buildAddressShippingDTO($order);

        $products=$this->load_products($order);

        $orderDTO = $this->buildOrderDTO($addressBilling, $addressShipping, $products, $order, $customerBillingDTO, $customerShippingDTO);

        try {
            $this->core->setOrderModel($orderDTO);
        } catch (ExceptionBase $e) {
            $logger->error("LINEA: " . $e->getLine() . " " . $e->getMessage());
            $this->_printErrorMsg('Error al validar datos.');
        }
        
        try {
            $transactionModel = $this->core->call_sar();
        } catch (ExceptionBase $e) {
            $logger->error("LINEA: " . $e->getLine() . " " . $e->getMessage());
            $this->_printErrorMsg("Error al validar datos.\n" . $e->getMessage());
        }

        $rta=$transactionModel->getResponse();
/*
        print_R($datosCs);
        print_R($rta);
        echo "merchant: ".$this->merchantid;
        die;
*/
        $this->todopago_persistRequestKey($order->id, $rta->RequestKey);

        $action_adr = $rta->URL_Request;
        if (isset($action_adr)) {
            $status = $this->orden_iniciada;
            $var    = orden_iniciada($order, $status);
        }

        session_start();
        $_SESSION['RequestKey'] = $rta->RequestKey;
        $RequestKey             = $rta->RequestKey;

        $_SESSION['Order'] = $order;
        update_option('order_id', $RequestKey);

        if ($rta->StatusCode == -1) {
            /** Selección FH o Retail **/
            $isFormHybrid = $this->form;

            if ($isFormHybrid == 'externo') {

                $submit_button = '<form action="' . $action_adr . '" method="post" id="todopago_payment_form">
                    ' . $fields . '

                    <input type="submit" class="button-alt" id="submit_todopago_payment_form" value="' . __('Pagar con Todo Pago', 'jigoshop') . '" style="padding: 10px 30px 11px;" />
                        <a class="button-alt" href="' . esc_url($order->get_cancel_order_url()) . '"  style="padding: 10px 30px 11px;">' . __('Cancelar Orden', 'jigoshop') . '
                        </a>


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

                //echo "Formulario externo muestra";
                echo $submit_button;
                exit;

                // return $submit_button;
            } else { // FORM HIBRIDO

                //Híbrido V3
                $basename = plugin_basename(dirname(__FILE__));
                $baseurl  = plugins_url();
                ?>
                <link href="<?php echo "{$baseurl}/{$basename}"; ?>/../css/todopago-formulario-2.css" rel="stylesheet" type="text/css">

                <?php
                $this->core->initFormulario($transactionModel);
                exit;
                
/*
                //Híbrido V2
                $form_dir  = "{$baseurl}/{$basename}/lib/view/formulario-hibrido";
                $firstname = $datosCs['CSSTFIRSTNAME'];
                $lastname  = $datosCs['CSSTLASTNAME'];
                $email     = $datosCs['CSSTEMAIL'];
                $merchant  = $this->merchantid;
                $amount    = $datosCs['CSPTGRANDTOTALAMOUNT'];
                $prk       = $rta->PublicRequestKey;

                $home      = home_url();


                $return_URL_ERROR = "{$baseurl}/{$basename}/../todopago.php?second_step=true";
                $return_URL_OK    = "{$baseurl}/{$basename}/../todopago.php?sessionid={$sessionid}&second_step=true";

                $env_url = ($this->todopago_environment == "prod" ? TODOPAGO_FORMS_PROD : TODOPAGO_FORMS_TEST);

                get_header();
                require 'view/formulario-hibrido/formulario.php';
                get_footer();
                exit;
                */
            }

        } else {
            echo '<div style="color: red" align="left">Lo sentimos, ha ocurrido un error.  ' . $rta->StatusMessage . '<a href="' . home_url() . '" class="wc-backward">  Volver a la p&aacute;gina de inicio</a></div>';
            if ($this->emptycart_enabled == 'yes') {
                jigoshop_cart::empty_cart();
            }
            exit;

        }
    }

    public function _printErrorMsg($msg = null)
    {
        if ($msg != null) {
            echo '<div class="woocommerce-error">Lo sentimos, ha ocurrido un error. ' . $msg . ' <a href="' . home_url() . '" class="wc-backward">Volver a la página de inicio</a></div>';
        } else {
            echo '<div class="woocommerce-error">Lo sentimos, ha ocurrido un error. <a href="' . home_url() . '" class="wc-backward">Volver a la página de inicio</a></div>';
        }
    }


    protected function buildOrderDTO(AddressDTO $addressBillingDTO, AddressDTO $addressShippingDTO, $products, jigoshop_order $order, CustomerDTO $customerBillingDTO, CustomerDTO $customerShippingDTO)
    {
        $orderDTO = new \TodoPago\Core\Order\OrderDTO();
        $orderDTO->setOrderId($order->id);
        //$orderDTO->setOrderId(time());
        $orderDTO->setAddressBilling($addressBillingDTO);
        $orderDTO->setAddressShipping($addressShippingDTO);
        $orderDTO->setProducts($products);
        if (method_exists($order, 'get_total'))
            $orderDTO->setTotalAmount($order->get_total());
        else
            $orderDTO->setTotalAmount($order->order_total);
        $orderDTO->setTotalAmount($order->order_total);
        $orderDTO->setCustomerBilling($customerBillingDTO);
        $orderDTO->setCustomerShipping($customerShippingDTO);
        
        return $orderDTO;
    }


    function getOptionsSARComercio($esProductivo, $return_URL_OK, $return_URL_ERROR)
    {
        return array(
            'Security' => $this->security,
            'EncodingMethod' => 'XML',
            'Merchant' => strval($this->merchantid),
            'URL_OK' => $return_URL_OK,
            'URL_ERROR' => $return_URL_ERROR
        );
    }

    function getOptionsSAROperacion($esProductivo, $order)
    {

        $arrayResult = array(
            'MERCHANT' => $this->merchantid,
            'OPERATIONID' => strval($order->id),
            'CURRENCYCODE' => '032' //Por el momento es el único tipo de moneda aceptada
        );

/*
        //var_dump($this->settings['maxCuotas_enabled']);
        //die;
        //var_dump($this->settings['timeoutValor']);
        //var_dump($this->settings['maxCuotas']);
        */


        // setea max de cuotas si es que se esta habilitada la opcion
        if ($this->settings['maxCuotas_enabled'] == 'yes') {
            $arrayResult['MAXINSTALLMENTS'] = strval($this->settings['maxCuotas']);
        }

        if($this->settings['timeout_enabled']  == 'yes'){
            $arrayResult['TIMEOUT']  =  strval( $this->settings['timeoutValor'] );
        }

        return $arrayResult;
    }


    protected function buildCustomerBillingDTO(jigoshop_order $order)
    {
        $customerDTO = new CustomerDTO();
        $customerDTO->setFirstName($order->billing_first_name);
        $customerDTO->setLastName($order->billing_last_name);
        $customerDTO->setUserEmail($order->billing_email);
        $customerDTO->setId(0); // si es guest seteo ID=0

        return $customerDTO;
    }

    protected function buildCustomerShippingDTO(jigoshop_order $order)
    {
        $customerDTO = new CustomerDTO();
        $customerDTO->setFirstName($order->shipping_first_name);
        $customerDTO->setLastName($order->shipping_last_name);
        $customerDTO->setUserEmail($order->billing_email);
        $customerDTO->setId(0); // si es guest seteo ID=0

        return $customerDTO;
    }

    protected function buildAddressBillingDTO(jigoshop_order $order)
    {
        $addressDTO = new AddressDTO();
        $addressDTO->setCity($order->billing_city);
        $addressDTO->setCountry($order->billing_country);
        $addressDTO->setPostalCode($order->billing_postcode);
        $addressDTO->setPhoneNumber($order->billing_phone);
        $addressDTO->setState($order->billing_state);
        $addressDTO->setStreet($order->billing_address_1);
        
        return $addressDTO;
    }


    protected function buildAddressShippingDTO(jigoshop_order $order)
    {
        $addressDTO = new AddressDTO();
        $addressDTO->setCity($order->shipping_city);
        $addressDTO->setCountry($order->shipping_country);
        $addressDTO->setPostalCode($order->shipping_postcode);
        $addressDTO->setPhoneNumber($order->billing_phone);
        $addressDTO->setState($order->shipping_state);
        $addressDTO->setStreet($order->shipping_address_1);
        
        return $addressDTO;
    }

    function call_sar($paramsSAR, $logger)
    {
        global $wpdb;

        $logger->debug('call_sar función function call_sar');

        $http_header = $this->getHttpHeader();

        $logger->debug("http header: " . json_encode($http_header));
        $connector = new Sdk($http_header, $this->todopago_environment);


        $logger->debug("Connector: " . json_encode($connector));

        $user_location = str_replace(' ', '', $paramsSAR['operacion']['CSBTSTREET1']) . $paramsSAR['operacion']['CSBTPOSTALCODE'];
        $base_location = $wpdb->get_var("SELECT identify_key FROM wp_todopago_gmaps WHERE identify_key='$user_location'");

        if ($this->gmaps_enabled == 'yes' && !$base_location) {
            $g = new \TodoPago\Client\Google();
            $connector->setGoogleClient($g);
            $response_sar         = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
            $responseGoogleStatus = $connector->getGoogleClient()->getGoogleResponse()['billing']['status'];
            $responseGoogle       = $connector->getGoogleClient()->getFinalAddress();

            if ($responseGoogleStatus == 'OK') {

                $wpdb->insert('wp_todopago_gmaps', array(
                    'identify_key' => $user_location,
                    'billing_street' => $responseGoogle['billing']['CSBTSTREET1'],
                    'billing_state' => $responseGoogle['billing']['CSBTSTATE'],
                    'billing_city' => $responseGoogle['billing']['CSBTCITY'],
                    'billing_country' => $responseGoogle['billing']['CSBTCOUNTRY'],
                    'billing_postalcode' => $responseGoogle['billing']['CSBTPOSTALCODE'],
                    'shipping_street' => $responseGoogle['shipping']['CSSTSTREET1'],
                    'shipping_state' => $responseGoogle['shipping']['CSSTSTATE'],
                    'shipping_city' => $responseGoogle['shipping']['CSSTCITY'],
                    'shipping_country' => $responseGoogle['shipping']['CSSTCOUNTRY'],
                    'shipping_postalcode' => $responseGoogle['shipping']['CSSTPOSTALCODE']
                ));
            }
        } else if ($this->gmaps_enabled == 'yes' && $base_location) {
            $billing_street                           = $wpdb->get_var("SELECT billing_street FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $billing_state                            = $wpdb->get_var("SELECT billing_state FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $billing_city                             = $wpdb->get_var("SELECT billing_city FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $billing_country                          = $wpdb->get_var("SELECT billing_country FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $billing_postalcode                       = $wpdb->get_var("SELECT billing_postalcode FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $shipping_street                          = $wpdb->get_var("SELECT shipping_street FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $shipping_state                           = $wpdb->get_var("SELECT shipping_state FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $shipping_city                            = $wpdb->get_var("SELECT shipping_city FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $shipping_country                         = $wpdb->get_var("SELECT shipping_country FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $shipping_postalcode                      = $wpdb->get_var("SELECT shipping_postalcode FROM wp_todopago_gmaps WHERE identify_key='$user_location'");
            $paramsSAR['operacion']['CSBTSTREET1']    = $billing_street;
            $paramsSAR['operacion']['CSBTSTATE']      = $billing_state;
            $paramsSAR['operacion']['CSBTCITY']       = $billing_city;
            $paramsSAR['operacion']['CSBTCOUNTRY']    = $billing_country;
            $paramsSAR['operacion']['CSBTPOSTALCODE'] = $billing_postalcode;
            $paramsSAR['operacion']['CSBTSTREET1']    = $shipping_street;
            $paramsSAR['operacion']['CSSTSTATE']      = $shipping_state;
            $paramsSAR['operacion']['CSSTCITY']       = $shipping_city;
            $paramsSAR['operacion']['CSSTCOUNTRY']    = $shipping_country;
            $paramsSAR['operacion']['CSSTPOSTALCODE'] = $shipping_postalcode;

            $response_sar = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);

        } else {
            $response_sar = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);

        }

//        $error                         = new StatusCodeCS();
//        $statusCode                    = $response_sar["StatusCode"];
//        $response_sar['StatusMessage'] = $error->getErrorByStatusCode($statusCode);
        $logger->info('response SAR ' . json_encode($response_sar));
        return $response_sar;
    }

    public function setGmaps($paramSar)
    {

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

        //echo '<p>' . __('Thank you for your order, please click the button below to pay with todopago.', 'jigoshop') . '</p>';

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

/*
        $logger = $this->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $this->todopago_environment, $_SESSION['Order']->id, $RequestKey, true);

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


        $connector = new Sdk($http_header, $this->todopago_environment);
        $logger->info('request GAA ' . json_encode($params_GAA));

        $response_GAA = $connector->getAuthorizeAnswer($params_GAA);
*/

        $logger = $this->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $this->todopago_environment, $_SESSION['Order']->id, $_SESSION['Order']->id, true);
        $this->core->setTpLogger($logger);


        $response_GAA=$this->core->call_gaa($_SESSION['Order']->id);
        //print_r($response_GAA);
        //die;

        update_option('AMOUNT' . $_SESSION['Order']->id, $response_GAA['response_GAA']['Payload']['Request']['AMOUNT']);
        update_option('AMOUNTBUYER' . $_SESSION['Order']->id, $response_GAA['response_GAA']['Payload']['Request']['AMOUNTBUYER']);
        $order = new jigoshop_order($_SESSION['Order']->id);

        if ($response_GAA['response_GAA']['StatusCode'] == '-1') {
            jigoshop_cart::empty_cart();
            $status = $this->orden_aprobada;
            $var    = orden_aprobada($order, $status);
        } else {
            $status = $this->orden_rechazada;
            $var    = orden_rechazada($order, $status);
            if ($this->emptycart_enabled == 'yes') {
                jigoshop_cart::empty_cart();
            }
        }

        //$logger->info('response GAA ' . json_encode($response_GAA));
        $logger->info('Costo Total ' . json_encode('$' . get_option('AMOUNTBUYER' . $_SESSION['Order']->id)));

        return $response_GAA;
    }

    /*
     *  TodoPago Devoluciones Total
     */
    public function voidRequest_core($id, $amount)
    {
        $logger = $this->_obtain_logger(phpversion(), 'jigoshop', TODOPAGO_PLUGIN_VERSION, $this->todopago_environment, $id, $id, true);
        $this->core->setTpLogger($logger);

        $orderDTO = new \TodoPago\Core\Order\OrderDTO();
        $orderDTO->setOrderId($id);
        $orderDTO->setRefundAmount($amount);
        $resp=$this->core->process_refund($orderDTO);
        //var_dump($resp);
        return $resp;


/*
        $http_header = array(
            'Authorization' => get_option('apiKey')
        );

        $connector = new Sdk($http_header, get_option('ambiente'));
        $options   = array(
            "Security" => get_option('security'), // API Key del comercio asignada por TodoPago
            "Merchant" => get_option('merchantId'), // Merchant o Nro de comercio asignado por TodoPago
            "RequestKey" => get_option($_GET['post']) // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
        );
        $resp      = $connector->voidRequest($options);

        return json_encode($resp);
        */
    }

    /*
     *  TodoPago Devolucion Parcial
     */
    public function returnRequest($data)
    {
        $respuesta = $data;

        $http_header = array(
            'Authorization' => get_option('apiKey')
        );

        $connector = new Sdk($http_header, get_option('ambiente'));

        $options = array(
            "Security" => get_option('security'), // API Key del comercio asignada por TodoPago
            "Merchant" => get_option('merchantId'), // Merchant o Nro de comercio asignado por TodoPago
            "RequestKey" => get_option($_GET['post']), // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
            "AMOUNT" => $_POST['amount'] // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
        );
        $resp    = $connector->returnRequest($options);

        return json_encode($resp);
    }
}
