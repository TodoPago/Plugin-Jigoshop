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
- [Versiones disponibles](#availableversions)

<a name="consideracionesgenerales"></a>
## Consideraciones Generales
El plug in de pagos de <strong>Todo Pago</strong>, provee a las tiendas Jigoshop de un nuevo m&eacute;todo de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.5 en adelante y WordPress 4.6 con Jigoshop 1.18

<a name="instalacion"></a>
## Instalación
1. Copiar y pegar la carpeta dev-jigoshop-todopago-[branch] en la carpeta **\wp-content\plugins.
2. Ir a la administración de plugins, activar "Jigoshop - Todopago Payment Gateway".

Observaci&oacute;nes:
<br />
1. Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
Descomentar: <em>extension=php_openssl.dll</em> del php.ini 
<br />
2. En caso de tener conflictos con Jquery por los diferentes temas, descomentar la siguiente linea que se encuentra al final del index.php
```php
  // add_action('init', 'my_init');
```
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="configuracion"></a>
##Configuración
<a name="activacion"></a>
####Activación
1. Ir a ir a **Jigoshop > Ajustes > Pasarelas de pago, activar el checkbox "Enable todopago Payment Window" y guardar. <br/>
2. Configurar credenciales para poder operar.<br/>

<a name="confplugin"></a>
####Configuración plug in
Para llegar al menu de configuración del plugin ir a: <em>Jigoshop -> Ajustes -> Pasarelas de pago</em>
![imagen de configuracion](https://postimg.org/image/x9ago6y6n/)</br>

- Estado de transacción iniciada: Se setea luego de completar los datos de facturación y presionar el botón "Purchase" o "Realizar el pedido".
- Estado de transacción aprobada: Se setea luego de volver del formulario de pago de Todo Pago y se obtiene una confirmación del pago.
- Estado de transacción rechazada: Se setea luego de volver del formulario de pago de Todo Pago y se obtiene un rechazo del pago.
</br>


[<sub>Volver a inicio</sub>](#inicio)

<a name="formHibrido"></a>
####Formulario Hibrido
En la configuracion del plugin tambien estara la posibilidad de mostrarle al cliente el formulario de pago de TodoPago integrada en el sitio. 
Para esto , en la configuracion se debe seleccionar la opcion Integrado en la pagina en el campo de seleccion de Tipo de formulario de pago

La opción se encuentra en el mismo menú de configuración explicado en el apartado anterior.

[<sub>Volver a inicio</sub>](#inicio)

<a name="getcredentials"></a>
####Obtener datos de configuracion
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. </br>
a. Ir a la opcion obtener credenciales</br>
b. Loguearse con el mail y password de Todopago.</br>
c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente y solo hay que hacer click en el boton update y listo.</br>

[<sub>Volver a inicio</sub>](#inicio)

<a name="maxcuotas"></a>
####Configuración de Maximo de Cuotas
Se puede configurar la cantidad máxima de cuotas que ofrecerá el formulario de TodoPago con el campo cantidad máxima de cuotas. Para que se tenga en cuenta este valor se debe habilitar el campo Habilitar máximo de cuotas y tomará el valor fijado para máximo de cuotas. En caso que esté habilitado el campo y no haya un valor puesto para las cuotas se tomará el valor 12 por defecto.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<br />
<a name="cybersource"></a>
## Prevención de Fraude
- [Consideraciones Generales](#cons_generales)
- [Consideraciones para vertical RETAIL](#cons_retail)

<a name="cons_generales"></a>
####Consideraciones Generales (para todas las verticales, por defecto RETAIL)
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
####Consideraciones para vertical RETAIL
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
Se puede consultar <strong>on line</strong> las características de la transacci&oacute;n en el sistema de Todo Pago .
Para esto se debe ir dentro del detalle de cada orden en la sección -> Pedidos </br>  
hacer click en 'Estado transacción' .<br />

[<sub>Volver a inicio</sub>](#inicio)
</br>

<a name="devoluciones"></a>
#### Devoluciones
Es posible realizar devoluciones de TodoPago desde el detalle de la orden. Para ello dirigirse al detalle de la orden, hay un campo para hacer devoluciones parciales y al lado un botón *Devolución parcial*, al hacer click ahí devolvera el monto ingresado. Si se quiere hacer una devolucion total del monto simplemente hacer click en el boton *Devolución*. <br />

[<sub>Volver a inicio</sub>](#inicio)


<a name="tablas"></a>
## Tablas de Referencia
######[Provincias](#p)
######[Tabla de errores](#codigoerrores)

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
<p>Tabla de errores</p>  


<table>		
<tr><th>Id mensaje</th><th>Mensaje</th></tr>				
<tr><td>1081</td><td>Tu saldo es insuficiente para realizar la transacción.</td></tr>
<tr><td>1100</td><td>El monto ingresado es menor al mínimo permitido</td></tr>
<tr><td>1101</td><td>El monto ingresado supera el máximo permitido.</td></tr>
<tr><td>1102</td><td>La tarjeta ingresada no corresponde al Banco indicado. Revisalo.</td></tr>
<tr><td>1104</td><td>El precio ingresado supera al máximo permitido.</td></tr>
<tr><td>1105</td><td>El precio ingresado es menor al mínimo permitido.</td></tr>
<tr><td>2010</td><td>En este momento la operación no pudo ser realizada. Por favor intentá más tarde. Volver a Resumen.</td></tr>
<tr><td>2031</td><td>En este momento la validación no pudo ser realizada, por favor intentá más tarde.</td></tr>
<tr><td>2050</td><td>Lo sentimos, el botón de pago ya no está disponible. Comunicate con tu vendedor.</td></tr>
<tr><td>2051</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2052</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2053</td><td>La operación no pudo ser procesada. Por favor, intentá más tarde. Si el problema persiste comunicate con tu vendedor</td></tr>
<tr><td>2054</td><td>Lo sentimos, el producto que querés comprar se encuentra agotado por el momento. Por favor contactate con tu vendedor.</td></tr>
<tr><td>2056</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2057</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2059</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>90000</td><td>La cuenta destino de los fondos es inválida. Verificá la información ingresada en Mi Perfil.</td></tr>
<tr><td>90001</td><td>La cuenta ingresada no pertenece al CUIT/ CUIL registrado.</td></tr>
<tr><td>90002</td><td>No pudimos validar tu CUIT/CUIL.  Comunicate con nosotros <a href="#contacto" target="_blank">acá</a> para más información.</td></tr>
<tr><td>99900</td><td>El pago fue realizado exitosamente</td></tr>
<tr><td>99901</td><td>No hemos encontrado tarjetas vinculadas a tu Billetera. Podés  adherir medios de pago desde www.todopago.com.ar</td></tr>
<tr><td>99902</td><td>No se encontro el medio de pago seleccionado</td></tr>
<tr><td>99903</td><td>Lo sentimos, hubo un error al procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99970</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99971</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99977</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99978</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99979</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99980</td><td>Ya realizaste un pago en este sitio por el mismo importe. Si querés realizarlo nuevamente esperá 5 minutos.</td></tr>
<tr><td>99982</td><td>En este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99983</td><td>Lo sentimos, el medio de pago no permite la cantidad de cuotas ingresadas. Por favor intentá más tarde.</td></tr>
<tr><td>99984</td><td>Lo sentimos, el medio de pago seleccionado no opera en cuotas.</td></tr>
<tr><td>99985</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99986</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99987</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99988</td><td>Lo sentimos, momentaneamente el medio de pago no se encuentra disponible. Por favor intentá más tarde.</td></tr>
<tr><td>99989</td><td>La tarjeta ingresada no está habilitada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente.</td></tr>
<tr><td>99990</td><td>La tarjeta ingresada está vencida. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99991</td><td>Los datos informados son incorrectos. Por favor ingresalos nuevamente.</td></tr>
<tr><td>99992</td><td>La fecha de vencimiento es incorrecta. Por favor seleccioná otro medio de pago o actualizá los datos.</td></tr>
<tr><td>99993</td><td>La tarjeta ingresada no está vigente. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99994</td><td>El saldo de tu tarjeta no te permite realizar esta operacion.</td></tr>
<tr><td>99995</td><td>La tarjeta ingresada es invalida. Seleccioná otra tarjeta para realizar el pago.</td></tr>
<tr><td>99996</td><td>La operación fué rechazada por el medio de pago porque el monto ingresado es inválido.</td></tr>
<tr><td>99997</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99998</td><td>Lo sentimos, la operación fue rechazada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
<tr><td>99999</td><td>Lo sentimos, la operación no pudo completarse. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
</table>

<a name="availableversions"></a>
## Versiones Disponibles##
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
