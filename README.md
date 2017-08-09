<a name="inicio"></a>
Jigoshop- módulo Todo Pago (v1.1.0)
============

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración](#configuracion)
 - [Activación](#activacion)
 - [Configuración plug in](#confplugin)
 - [Formulario Hibrido](#formHibrido)
 - [Obtener datos de configuracion](#getcredentials)
 - [Configuración de Maximo de Cuotas](#maxcuotas)
- [Prevencion de Fraude](#cybersource)
 - [Consideraciones generales](#cons_generales)
 - [Consideraciones para vertical retail](#cons_retail)
- [Características](#features)
 - [Consulta de transacciones](#constrans)
 - [Devoluciones](#devoluciones)
- [Tablas de referencia](#tablas)
- [Tabla de errores](#codigoerrores)
- [Versiones disponibles](#availableversions)

<a name="consideracionesgenerales"></a>
## Consideraciones Generales
El plug in de pagos de <strong>Todo Pago</strong>, provee a las tiendas Jigoshop de un nuevo m&eacute;todo de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.5 en adelante y WordPress 4.6 con Jigoshop 1.18

<a name="instalacion"></a>
## Instalación
1. Copiar y pegar la carpeta dev-jigoshop-todopago-[branch] en la carpeta **\wp-content\plugins**.
2. Ir a la administración de plugins, activar **"Jigoshop - Todopago Payment Gateway"**.

Observaciones:
1. Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
Descomentar: <em>extension=php_openssl.dll</em> del php.ini
<br />
2. En caso de tener conflictos con Jquery por los diferentes temas, descomentar la siguiente linea que se encuentra al final del index.php
```php
  // add_action('init', 'my_init');
```
[<sub>Volver a inicio</sub>](#inicio)

<a name="configuracion"></a>
## Configuración
<a name="activacion"></a>
#### Activación
1. Ir a ir a **Jigoshop > Ajustes > Pasarelas de pago**, activar el checkbox "Enable todopago Payment Window" y guardar. 
2. Configurar credenciales para poder operar.

<a name="confplugin"></a>
#### Configuración plug in
Para llegar al menu de configuración del plugin ir a: <em>Jigoshop -> Ajustes -> Pasarelas de pago</em>
![imagen de configuracion](https://postimg.org/image/x9ago6y6n/)</br>

- Estado de transacción iniciada: Se setea luego de completar los datos de facturación y presionar el botón "Purchase" o "Realizar el pedido".
- Estado de transacción aprobada: Se setea luego de volver del formulario de pago de Todo Pago y se obtiene una confirmación del pago.
- Estado de transacción rechazada: Se setea luego de volver del formulario de pago de Todo Pago y se obtiene un rechazo del pago.

[<sub>Volver a inicio</sub>](#inicio)

<a name="formHibrido"></a>
#### Formulario Híbrido
En la configuracion del plugin tambien estara la posibilidad de mostrarle al cliente el formulario de pago de TodoPago integrada en el sitio. 
Para esto , en la configuracion se debe seleccionar la opcion Integrado en la pagina en el campo de seleccion de Tipo de formulario de pago

La opción se encuentra en el mismo menú de configuración explicado en el apartado anterior.

[<sub>Volver a inicio</sub>](#inicio)

<a name="getcredentials"></a>
#### Obtener datos de configuración
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. </br>
a. Ir a la opcion obtener credenciales</br>
b. Loguearse con el mail y password de Todopago.</br>
c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente y solo hay que hacer click en el boton update y listo.</br>

[<sub>Volver a inicio</sub>](#inicio)

<a name="maxcuotas"></a>
#### Configuración de Máximo de Cuotas
Se puede configurar la cantidad máxima de cuotas que ofrecerá el formulario de TodoPago con el campo cantidad máxima de cuotas. Para que se tenga en cuenta este valor se debe habilitar el campo Habilitar máximo de cuotas y tomará el valor fijado para máximo de cuotas. En caso que esté habilitado el campo y no haya un valor puesto para las cuotas se tomará el valor 12 por defecto.
[<sub>Volver a inicio</sub>](#inicio)


<a name="cybersource"></a>
## Prevención de Fraude
- [Consideraciones Generales](#cons_generales)
- [Consideraciones para vertical RETAIL](#cons_retail)

<a name="cons_generales"></a>
#### Consideraciones Generales (para todas las verticales, por defecto RETAIL)
El plugin, toma valores est&aacute;ndar del framework para validar los datos del comprador. Principalmente se sacan los datos de la tabla de base de datos llamada <em>WPSC_PURCHASE_LOGS</em>.

```php
   $order = new get_purchase_logs($order_id);
-- Ciudad de Facturación: $order -> billing_city;
-- País de facturación: $order -> billing_country;
-- Identificador de Usuario: $order -> customer_user;
-- Email del usuario al que se le emite la factura: $order -> billing_email;
-- Nombre de usuario el que se le emite la factura: $order -> billing_first_name;
-- Apellido del usuario al que se le emite la factura: $order -> billing_last_name;
-- Teléfono del usuario al que se le emite la factura: $order -> billing_phone;
-- Provincia de la dirección de facturación: $this -> getStateCode($order -> billing_state);
-- Domicilio de facturación: $order -> billing_address_1;
-- Complemento del domicilio. (piso, departamento): $order -> billing_address_2;
-- Moneda: 'ARS'; //Moneda Fija
-- Total:  $order -> order_total;
-- IP de la pc del comprador: $order -> customer_ip_address;
```
<a name="cons_retail"></a> 
#### Consideraciones para vertical RETAIL
Las consideración para el caso de empresas del rubro <strong>RETAIL</strong> son similares a las <em>consideraciones generales</em> ya que se obtienen de la misma tabla WPSC_PURCHASE_LOGS
```php
-- Ciudad de envío de la orden: $order -> shipping_city;
-- País de envío de la orden: $order -> shipping_country;
-- Mail del destinatario: $order -> shipping_email;
-- Nombre del destinatario: $order -> shipping_first_name;
-- Apellido del destinatario: $order -> shipping_last_name;
-- Número de teléfono del destinatario: $order -> shipping_phone;
-- Código postal del domicio de envío: $order -> shipping_postcode;
-- Provincia de envío: getStateCode($order -> shipping_state);
-- Domicilio de envío: $order -> billing_address_1;
```

<a name="features"></a>
## Características
 - [Consulta de transacciones](#constrans)
 - [Devoluciones](#devoluciones)

<br />
<a name="constrans" ></a>
#### Consulta de Transacciones
Se puede consultar <strong>on line</strong> las características de la transacción en el sistema de Todo Pago .
Para esto se debe ir dentro del detalle de cada orden en la sección -> Pedidos
Hacer click en 'Estado transacción'.
[<sub>Volver a inicio</sub>](#inicio)

<a name="devoluciones"></a>
#### Devoluciones
Es posible realizar devoluciones de TodoPago desde el detalle de la orden. Para ello dirigirse al detalle de la orden, hay un campo para hacer devoluciones parciales y al lado un botón *Devolución parcial*, al hacer click ahí devolvera el monto ingresado. Si se quiere hacer una devolucion total del monto simplemente hacer click en el boton *Devolución*. <br />

  * El monto de devolución se calcula en base al costo original del producto sin los impuestos agregados. *

[<sub>Volver a inicio</sub>](#inicio)

<a name="tablas"></a>
## Tablas de Referencia
###### [Provincias](#p)
###### [Tabla de errores](#codigoerrores)

<a name="p"></a>
<p>Provincias</p>
<table>
<tr><th>Provincia</th><th>Código</th></tr>
<tr><td>CABA</td><td>C</td></tr>
<tr><td>Buenos Aires</td><td>B</td></tr>
<tr><td>Catamarca</td><td>K</td></tr>
<tr><td>Chaco</td><td>H</td></tr>
<tr><td>Chubut</td><td>U</td></tr>
<tr><td>Córdoba</td><td>X</td></tr>
<tr><td>Corrientes</td><td>W</td></tr>
<tr><td>Entre Ríos</td><td>E</td></tr>
<tr><td>Formosa</td><td>P</td></tr>
<tr><td>Jujuy</td><td>Y</td></tr>
<tr><td>La Pampa</td><td>L</td></tr>
<tr><td>La Rioja</td><td>F</td></tr>
<tr><td>Mendoza</td><td>M</td></tr>
<tr><td>Misiones</td><td>N</td></tr>
<tr><td>Neuquén</td><td>Q</td></tr>
<tr><td>Río Negro</td><td>R</td></tr>
<tr><td>Salta</td><td>A</td></tr>
<tr><td>San Juan</td><td>J</td></tr>
<tr><td>San Luis</td><td>D</td></tr>
<tr><td>Santa Cruz</td><td>Z</td></tr>
<tr><td>Santa Fe</td><td>S</td></tr>
<tr><td>Santiago del Estero</td><td>G</td></tr>
<tr><td>Tierra del Fuego</td><td>V</td></tr>
<tr><td>Tucumán</td><td>T</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)
<a name="codigoerrores"></a>
## Tabla de errores operativos

<table>
<tr><th>Id mensaje</th><th>Mensaje</th></tr>
<tr><td>-1</td><td>Aprobada.</td></tr>
<tr><td>1100</td><td>El monto ingresado es menor al mínimo permitido</td></tr>
<tr><td>1101</td><td>El monto ingresado supera el máximo permitido.</td></tr>
<tr><td>1102</td><td>Tu tarjeta no corresponde con el banco seleccionado. Iniciá nuevamente la compra.</td></tr>
<tr><td>1104</td><td>El precio ingresado supera al máximo permitido.</td></tr>
<tr><td>1105</td><td>El precio ingresado es menor al mínimo permitido.</td></tr>
<tr><td>1070</td><td>El plazo para realizar esta devolución caducó.</td></tr>
<tr><td>1081</td><td>El saldo de tu cuenta es insuficiente para realizar esta devolución.</td></tr>
<tr><td>2010</td><td>En este momento la operación no pudo ser realizada. Por favor intentá más tarde. Volver a Resumen.</td></tr>
<tr><td>2031</td><td>En este momento la validación no pudo ser realizada, por favor intentá más tarde.</td></tr>
<tr><td>2050</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2051</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor.</td></tr>
<tr><td>2052</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor. </td></tr>
<tr><td>2053</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor.</td></tr>
<tr><td>2054</td><td>El producto que querés comprar se encuentra agotado. Por favor contactate con tu vendedor.</td></tr>
<tr><td>2056</td><td>Tu compra no pudo ser procesada.</td></tr>
<tr><td>2057</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2058</td><td>La operación fué rechazada. Comunicate con el 0800 333 0010.</td></tr>
<tr><td>2059</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2062</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2064</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2074</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>2075</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>2076</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>90000</td><td>La cuenta destino de los fondos es inválida. Verificá la información ingresada en Mi Perfil.</td></tr>
<tr><td>90001</td><td>La cuenta ingresada no pertenece al CUIT/ CUIL registrado.</td></tr>
<tr><td>90002</td><td>No pudimos validar tu CUIT/CUIL.  Comunicate con nosotros <a href="#contacto" target="_blank">acá</a> para más información.</td></tr>
<tr><td>99900</td><td>Tu compra fue exitosa.</td></tr>
<tr><td>99901</td><td>Tu Billetera Virtual no tiene medios de pago adheridos. Ingresá a tu cuenta de Todo Pago y cargá tus tarjetas.</td></tr>
<tr><td>99902</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99903</td><td>Lo sentimos, hubo un error al procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99904</td><td>El saldo de tu tarjeta no te permite realizar esta compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99905</td><td>En este momento la operación no pudo ser procesada. Intentá nuevamente.</td></tr>
<tr><td>99907</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor. </td></tr>
<tr><td>99910</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99911</td><td>Lo sentimos, se terminó el tiempo para confirmar esta compra. Por favor iniciá nuevamente el proceso de pago.</td></tr>
<tr><td>99950</td><td>Tu compra no pudo ser procesada.</td></tr>
<tr><td>99960</td><td>Esta compra requiere autorización de VISA. Comunicate al número que se encuentra al dorso de tu tarjeta.</td></tr>
<tr><td>99961</td><td>Esta compra requiere autorización de AMEX. Comunicate al número que se encuentra al dorso de tu tarjeta.</td></tr>
<tr><td>99970</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99971</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99972</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99974</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99975</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99977</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99979</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99978</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99979</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99980</td><td>Ya realizaste una compra por el mismo importe. Iniciala nuevamente en unos minutos.</td></tr>
<tr><td>99982</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando.</td></tr>
<tr><td>99983</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99984</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99985</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99986</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99987</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99988</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99989</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99990</td><td>Tu tarjeta está vencida. Iniciá nuevamente la compra utilizando otro medio de pago.</td></tr>
<tr><td>99991</td><td>Los datos informados son incorrectos. Por favor ingresalos nuevamente.</td></tr>
<tr><td>99992</td><td>El saldo de tu tarjeta no te permite realizar esta compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99993</td><td>Tu tarjeta no autorizó el pago. Iniciá nuevamente la compra utilizando otro medio de pago.</td></tr>
<tr><td>99994</td><td>El saldo de tu tarjeta no te permite realizar esta operacion.</td></tr>
<tr><td>99995</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99996</td><td>La operación fué rechazada por el medio de pago porque el monto ingresado es inválido.</td></tr>
<tr><td>99997</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99998</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.
<tr><td>99999</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="interrores"></a>
## Tabla de errores de integración

<table>
<tr><td>**Id mensaje**</td><td>**Descripción**</td></tr>
<tr><td>98001 </td><td>ERROR: El campo CSBTCITY es requerido</td></tr>
<tr><td>98002 </td><td>ERROR: El campo CSBTCOUNTRY es requerido</td></tr>
<tr><td>98003 </td><td>ERROR: El campo CSBTCUSTOMERID es requerido</td></tr>
<tr><td>98004 </td><td>ERROR: El campo CSBTIPADDRESS es requerido</td></tr>
<tr><td>98005 </td><td>ERROR: El campo CSBTEMAIL es requerido</td></tr>
<tr><td>98006 </td><td>ERROR: El campo CSBTFIRSTNAME es requerido</td></tr>
<tr><td>98007 </td><td>ERROR: El campo CSBTLASTNAME es requerido</td></tr>
<tr><td>98008 </td><td>ERROR: El campo CSBTPHONENUMBER es requerido</td></tr>
<tr><td>98009 </td><td>ERROR: El campo CSBTPOSTALCODE es requerido</td></tr>
<tr><td>98010 </td><td>ERROR: El campo CSBTSTATE es requerido</td></tr>
<tr><td>98011 </td><td>ERROR: El campo CSBTSTREET1 es requerido</td></tr>
<tr><td>98012 </td><td>ERROR: El campo CSBTSTREET2 es requerido</td></tr>
<tr><td>98013 </td><td>ERROR: El campo CSPTCURRENCY es requerido</td></tr>
<tr><td>98014 </td><td>ERROR: El campo CSPTGRANDTOTALAMOUNT es requerido</td></tr>
<tr><td>98015 </td><td>ERROR: El campo CSMDD7 es requerido</td></tr>
<tr><td>98016 </td><td>ERROR: El campo CSMDD8 es requerido</td></tr>
<tr><td>98017 </td><td>ERROR: El campo CSMDD9 es requerido</td></tr>
<tr><td>98018 </td><td>ERROR: El campo CSMDD10 es requerido</td></tr>
<tr><td>98019 </td><td>ERROR: El campo CSMDD11 es requerido</td></tr>
<tr><td>98020 </td><td>ERROR: El campo CSSTCITY es requerido</td></tr>
<tr><td>98021 </td><td>ERROR: El campo CSSTCOUNTRY es requerido</td></tr>
<tr><td>98022 </td><td>ERROR: El campo CSSTEMAIL es requerido</td></tr>
<tr><td>98023 </td><td>ERROR: El campo CSSTFIRSTNAME es requerido</td></tr>
<tr><td>98024 </td><td>ERROR: El campo CSSTLASTNAME es requerido</td></tr>
<tr><td>98025 </td><td>ERROR: El campo CSSTPHONENUMBER es requerido</td></tr>
<tr><td>98026 </td><td>ERROR: El campo CSSTPOSTALCODE es requerido</td></tr>
<tr><td>98027 </td><td>ERROR: El campo CSSTSTATE es requerido</td></tr>
<tr><td>98028 </td><td>ERROR: El campo CSSTSTREET1 es requerido</td></tr>
<tr><td>98029 </td><td>ERROR: El campo CSMDD12 es requerido</td></tr>
<tr><td>98030 </td><td>ERROR: El campo CSMDD13 es requerido</td></tr>
<tr><td>98031 </td><td>ERROR: El campo CSMDD14 es requerido</td></tr>
<tr><td>98032 </td><td>ERROR: El campo CSMDD15 es requerido</td></tr>
<tr><td>98033 </td><td>ERROR: El campo CSMDD16 es requerido</td></tr>
<tr><td>98034 </td><td>ERROR: El campo CSITPRODUCTCODE es requerido</td></tr>
<tr><td>98035 </td><td>ERROR: El campo CSITPRODUCTDESCRIPTION es requerido</td></tr>
<tr><td>98036 </td><td>ERROR: El campo CSITPRODUCTNAME es requerido</td></tr>
<tr><td>98037 </td><td>ERROR: El campo CSITPRODUCTSKU es requerido</td></tr>
<tr><td>98038 </td><td>ERROR: El campo CSITTOTALAMOUNT es requerido</td></tr>
<tr><td>98039 </td><td>ERROR: El campo CSITQUANTITY es requerido</td></tr>
<tr><td>98040 </td><td>ERROR: El campo CSITUNITPRICE es requerido</td></tr>
<tr><td>98101 </td><td>ERROR: El formato del campo CSBTCITY es incorrecto</td></tr>
<tr><td>98102 </td><td>ERROR: El formato del campo CSBTCOUNTRY es incorrecto</td></tr>
<tr><td>98103 </td><td>ERROR: El formato del campo CSBTCUSTOMERID es incorrecto</td></tr>
<tr><td>98104 </td><td>ERROR: El formato del campo CSBTIPADDRESS es incorrecto</td></tr>
<tr><td>98105 </td><td>ERROR: El formato del campo CSBTEMAIL es incorrecto</td></tr>
<tr><td>98106 </td><td>ERROR: El formato del campo CSBTFIRSTNAME es incorrecto</td></tr>
<tr><td>98107 </td><td>ERROR: El formato del campo CSBTLASTNAME es incorrecto</td></tr>
<tr><td>98108 </td><td>ERROR: El formato del campo CSBTPHONENUMBER es incorrecto</td></tr>
<tr><td>98109 </td><td>ERROR: El formato del campo CSBTPOSTALCODE es incorrecto</td></tr>
<tr><td>98110 </td><td>ERROR: El formato del campo CSBTSTATE es incorrecto</td></tr>
<tr><td>98111 </td><td>ERROR: El formato del campo CSBTSTREET1 es incorrecto</td></tr>
<tr><td>98112 </td><td>ERROR: El formato del campo CSBTSTREET2 es incorrecto</td></tr>
<tr><td>98113 </td><td>ERROR: El formato del campo CSPTCURRENCY es incorrecto</td></tr>
<tr><td>98114 </td><td>ERROR: El formato del campo CSPTGRANDTOTALAMOUNT es incorrecto</td></tr>
<tr><td>98115 </td><td>ERROR: El formato del campo CSMDD7 es incorrecto</td></tr>
<tr><td>98116 </td><td>ERROR: El formato del campo CSMDD8 es incorrecto</td></tr>
<tr><td>98117 </td><td>ERROR: El formato del campo CSMDD9 es incorrecto</td></tr>
<tr><td>98118 </td><td>ERROR: El formato del campo CSMDD10 es incorrecto</td></tr>
<tr><td>98119 </td><td>ERROR: El formato del campo CSMDD11 es incorrecto</td></tr>
<tr><td>98120 </td><td>ERROR: El formato del campo CSSTCITY es incorrecto</td></tr>
<tr><td>98121 </td><td>ERROR: El formato del campo CSSTCOUNTRY es incorrecto</td></tr>
<tr><td>98122 </td><td>ERROR: El formato del campo CSSTEMAIL es incorrecto</td></tr>
<tr><td>98123 </td><td>ERROR: El formato del campo CSSTFIRSTNAME es incorrecto</td></tr>
<tr><td>98124 </td><td>ERROR: El formato del campo CSSTLASTNAME es incorrecto</td></tr>
<tr><td>98125 </td><td>ERROR: El formato del campo CSSTPHONENUMBER es incorrecto</td></tr>
<tr><td>98126 </td><td>ERROR: El formato del campo CSSTPOSTALCODE es incorrecto</td></tr>
<tr><td>98127 </td><td>ERROR: El formato del campo CSSTSTATE es incorrecto</td></tr>
<tr><td>98128 </td><td>ERROR: El formato del campo CSSTSTREET1 es incorrecto</td></tr>
<tr><td>98129 </td><td>ERROR: El formato del campo CSMDD12 es incorrecto</td></tr>
<tr><td>98130 </td><td>ERROR: El formato del campo CSMDD13 es incorrecto</td></tr>
<tr><td>98131 </td><td>ERROR: El formato del campo CSMDD14 es incorrecto</td></tr>
<tr><td>98132 </td><td>ERROR: El formato del campo CSMDD15 es incorrecto</td></tr>
<tr><td>98133 </td><td>ERROR: El formato del campo CSMDD16 es incorrecto</td></tr>
<tr><td>98134 </td><td>ERROR: El formato del campo CSITPRODUCTCODE es incorrecto</td></tr>
<tr><td>98135 </td><td>ERROR: El formato del campo CSITPRODUCTDESCRIPTION es incorrecto</td></tr>
<tr><td>98136 </td><td>ERROR: El formato del campo CSITPRODUCTNAME es incorrecto</td></tr>
<tr><td>98137 </td><td>ERROR: El formato del campo CSITPRODUCTSKU es incorrecto</td></tr>
<tr><td>98138 </td><td>ERROR: El formato del campo CSITTOTALAMOUNT es incorrecto</td></tr>
<tr><td>98139 </td><td>ERROR: El formato del campo CSITQUANTITY es incorrecto</td></tr>
<tr><td>98140 </td><td>ERROR: El formato del campo CSITUNITPRICE es incorrecto</td></tr>
<tr><td>98201 </td><td>ERROR: Existen errores en la información de los productos</td></tr>
<tr><td>98202 </td><td>ERROR: Existen errores en la información de CSITPRODUCTDESCRIPTION los productos</td></tr>
<tr><td>98203 </td><td>ERROR: Existen errores en la información de CSITPRODUCTNAME los productos</td></tr>
<tr><td>98204 </td><td>ERROR: Existen errores en la información de CSITPRODUCTSKU los productos</td></tr>
<tr><td>98205 </td><td>ERROR: Existen errores en la información de CSITTOTALAMOUNT los productos</td></tr>
<tr><td>98206 </td><td>ERROR: Existen errores en la información de CSITQUANTITY los productos</td></tr>
<tr><td>98207 </td><td>ERROR: Existen errores en la información de CSITUNITPRICE de los productos</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="availableversions"></a>
## Versiones Disponibles
<table>
  <thead>
    <tr>
      <th>Version del Plugin</th>
      <th>Estado</th>
      <th>Versiones Compatibles</th>
    </tr>
  <thead>
  <tbody>
    <tr>
      <td><a href="https://github.com/TodoPago/dev-jigoshop-plugin/archive/developer.zip">v1.1.0</a></td>
      <td>Stable (Current version)</td>
      <td>WordPress 4.6 <br />
          Jigoshop 1.18
      </td>
    </tr>
  </tbody>
</table>

*Click on the links above for instructions on installing and configuring the module.*


[<sub>Volver a inicio</sub>](#inicio)
