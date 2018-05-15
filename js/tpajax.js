jQuery(document).ready(function() {
  jQuery("#jigoshop_todopagopayment_maxcuotas").attr('disabled', 'disabled');
  jQuery("#jigoshop_todopagopayment_timeout").attr('disabled', 'disabled');

      if (jQuery("#jigoshop_todopagopayment_timeout_enabled").is(':checked')) {
            jQuery("#jigoshop_todopagopayment_timeout").attr('disabled', false);
      } else {

          jQuery("#jigoshop_todopagopayment_timeout").attr('disabled', 'disabled');
      }

      if (jQuery("#jigoshop_todopagopayment_maxinstallments_enabled").is(':checked')) {
            jQuery("#jigoshop_todopagopayment_maxcuotas").attr('disabled', false);
      } else {

          jQuery("#jigoshop_todopagopayment_maxcuotas").attr('disabled', 'disabled');
      }

    jQuery("#jigoshop_todopagopayment_btncredentials_dev").attr("type",
        "button");
    jQuery("#jigoshop_todopagopayment_password_dev").attr("type",
        "password");
    jQuery("#jigoshop_todopagopayment_password_prod").attr("type",
        "password");
    jQuery("#jigoshop_todopagopayment_btncredentials_dev").attr("value",
        "obtener credenciales");
    jQuery("#jigoshop_todopagopayment_btncredentials_dev").click(function() {
        var user = jQuery("#jigoshop_todopagopayment_user_dev").val();
        var password = jQuery(
            "#jigoshop_todopagopayment_password_dev").val();
        //var mode = jQuery("#jigoshop_todopagopayment_mode").val();
        getCredentials(user, password, 'test', jQuery("#jigoshop_todopagopayment_value_check_wpnonce").val());
    });

    jQuery("#jigoshop_todopagopayment_btncredentials_prod").attr("type",
        "button");
    jQuery("#jigoshop_todopagopayment_btncredentials_prod").attr("value",
        "obtener credenciales");

    jQuery("#jigoshop_todopagopayment_btncredentials_prod").click(function() {
        console.log('nonce: '+jQuery("#jigoshop_todopagopayment_value_check_wpnonce").val());


        var user = jQuery("#jigoshop_todopagopayment_user_prod").val();
        var password = jQuery(
            "#jigoshop_todopagopayment_password_prod").val();
        //var mode = jQuery("#jigoshop_todopagopayment_mode").val();

        getCredentials(user, password, 'prod', jQuery("#jigoshop_todopagopayment_value_check_wpnonce").val());
    });





    jQuery("#jigoshop_todopagopayment_btnGetStatus").click(function() {

        var operationId = $_GET("post");
        getStatus(operationId);
    });
    jQuery("#jigoshop_todopagopayment_btnDevolution").click(function() {
        voidRequest();
    });
    jQuery("#jigoshop_todopagopayment_btnDevolutionPartial").click(function() {
        voidRequest();
    });    
    /*
    jQuery("#jigoshop_todopagopayment_btnDevolutionPartial").click(function() {
        var amount = jQuery("#jigoshop_todopagopayment_amount").val();
        returnRequest(amount);
    });
    */


    jQuery("#jigoshop_todopagopayment_maxinstallments_enabled").click(function() {
        if (jQuery("#jigoshop_todopagopayment_maxinstallments_enabled").is(':checked')) {
            jQuery("#jigoshop_todopagopayment_maxcuotas").attr('disabled', false);
        } else {
            jQuery("#jigoshop_todopagopayment_maxcuotas").attr('disabled', 'disabled');
        }
    });

    jQuery("#jigoshop_todopagopayment_timeout_enabled").click(function() {
        if (jQuery("#jigoshop_todopagopayment_timeout_enabled").is(':checked')) {
            jQuery("#jigoshop_todopagopayment_timeout").attr('disabled', false);
        } else {

            jQuery("#jigoshop_todopagopayment_timeout").attr('disabled', 'disabled');
        }
    });

    jQuery("#jigoshop_todopagopayment_value_check_wpnonce").hide();
});

/** Metodos Credenciales **/
function getCredentials(user, password, mode, wpnonce) {

    jQuery.ajax({
        type: 'POST',
        url: "../wp-admin/admin-ajax.php",
        data: {
            'action': 'todopago_credentials',
            'user': user,
            'password': password,
            'mode': mode,
            '_wpnonce' : wpnonce
        },
        success: function(data) {
            console.log('Resultado: '+data);

            jQuery('#jigoshop_todopagopayment_password_dev').val('');
            jQuery('#jigoshop_todopagopayment_password_prod').val('');
            console.log('dio todo ok! ');
            console.log(data);
            str = JSON.stringify(data);
            str = str.replace(/[.*+"!?^${}()|[\]\\]/g, "");
            str = Java_to_Latin1(str);

            resultMessage = str.split(":");
            console.log('En getCredentials - Resultado que llegó ' + resultMessage);

            if (resultMessage && resultMessage != '' && resultMessage[0] == 'mensajeResultado') {
                alert(resultMessage[1]);
                return 0;
            }

            setCredentials(data, mode);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            switch (xhr.status) {
                case 404:
                    alert(
                        "Verifique la correcta instalación del plugin"
                    );
                    break;
                default:
                    alert(
                        "Se ingresó un usuario o contraseña no válida, por favor intente nuevamente."
                    );
                    break;
            }
        },
        timeout: 8000
    });
}

function setCredentials(data, mode) {
    var response = JSON.parse(data);
    //var response = data;
    console.log('en setCredentials - '+response);

    console.log('en setCredentials - '+response.apikey);

    //  var security = response.ApiKey.substring(9, 42);
    if (mode == 'test') {
        jQuery("#jigoshop_todopagopayment_authorization_dev").val(response.apikey);
        jQuery("#jigoshop_todopagopayment_merchantid_dev").val(response.merchandid);
        jQuery("#jigoshop_todopagopayment_security_dev").val(response.security);
        jQuery("#jigoshop_todopagopayment_password_dev").val('');

    } else {
        jQuery("#jigoshop_todopagopayment_authorization_prod").val(response.apikey);
        jQuery("#jigoshop_todopagopayment_merchantid_prod").val(response.merchandid);
        jQuery("#jigoshop_todopagopayment_security_prod").val(response.security);
        jQuery("#jigoshop_todopagopayment_password_prod").val('');
    }

}

/** Métodos getStatus **/
function getStatus(datos) {
    var datosJson = JSON.parse(datos);
    var merchantId = datosJson.merchantId;
    var operationId = datosJson.operationId;


    jQuery.ajax({
        type: 'POST',
        url: "../wp-admin/admin-ajax.php",
        data: {
            'action': 'todopago_getStatus',
            'merchantId': merchantId,
            'operationId': operationId
        },
        success: function(data) {
          //console.log(data);

            setStatus(data);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            switch (xhr.status) {
                case 404:
                    alert(
                        "Verifique la correcta instalación del plugin"
                    );
                    break;
                default:
                    alert(
                        "El estado de la orden solicitada no se encuentra disponible"
                    );
                    break;
            }
        },
        timeout: 8000
    });
}

function setStatus(data) {
        //response = JSON.parse(data);
        console.log('respuesta: ' + data);
        jQuery("#tp_get_status_html_completo").html(data );


        /*
        if(response !=''){
            jQuery("#amount-buyer").html(response.Operations.AMOUNTBUYER);
            jQuery("#authorization-code").html(response.Operations.AUTHORIZATIONCODE);
            jQuery("#bank-id").html(response.Operations.BANKID);
            jQuery("#bar-code").html(response.Operations.BARCODE);
            jQuery("#card-holdername").html(response.Operations.CARDHOLDERNAME);
            jQuery("#card-number").html(response.Operations.CARDNUMBER);
            jQuery("#comision").html(response.Operations.COMISION);
            jQuery("#coupon-expdate").html(response.Operations.COUPONEXPDATE);
            jQuery("#coupon-secexpdate").html(response.Operations.COUPONSECEXPDATE);
            jQuery("#coupon-suscriber").html(response.Operations.COUPONSUBSCRIBER);
            jQuery("#amount").html(response.Operations.AMOUNT);
            jQuery("#identification-type").html(response.Operations.AMOUNTBUYER);
            jQuery("#currency-code").html(response.Operations.CURRENCYCODE);
            jQuery("#customer-email").html(response.Operations.CUSTOMEREMAIL);
            jQuery("#date-time").html(response.Operations.DATETIME);
            jQuery("#identification").html(response.Operations.IDENTIFICATION);
            jQuery("#identification-type").html(response.Operations.IDENTIFICATIONTYPE);
            jQuery("#installment-payments").html(response.Operations.INSTALLMENTPAYMENTS);
            jQuery("#operation-id").html(response.Operations.OPERATIONID);
            jQuery("#payment-mehod-code").html(response.Operations.PAYMENTMETHODCODE);
            jQuery("#payment-method-name").html(response.Operations.PAYMENTMETHODNAME);
            jQuery("#payment-method-type").html(response.Operations.PAYMENTMETHODNAMETYPE);
            jQuery("#push-notify-endpoint").html(response.Operations.PUSHNOTIFYENDPOINT);
            jQuery("#push-notify-method").html(response.Operations.PUSHNOTIFYMETHOD);
            jQuery("#push-notify-states").html(response.Operations.PUSHNOTIFYSTATES);
            jQuery("#refunded").html(response.Operations.REFUNDED);
            jQuery("#result-code").html(response.Operations.RESULTCODE);
            jQuery("#result-message").html(response.Operations.RESULTMESSAGE);
            jQuery("#id-contracargo").html(response.Operations.IDCONTRACARGO);
            jQuery("#estado-contracargo").html(response.Operations.ESTADOCONTRACARGO);
            jQuery("#fecha-notificacion").html(response.Operations.FECHANOTIFICACIONCUENTA);
            jQuery("#fee-amount").html(response.Operations.FEEAMOUNT);
            jQuery("#fee-amountbuyer").html(response.Operations.FEEAMOUNTBUYER);
            jQuery("#promotion-id").html(response.Operations.PROMOTIONID);
            jQuery("#type").html(response.Operations.TYPE);
            jQuery("#card-number").html(response.Operations.CARDNUMBER);
            jQuery("#ticket-number").html(response.Operations.TICKETNUMBER);
            jQuery("#authorization-code").html(response.Operations.AUTHORIZATIONCODE);
            jQuery("#service-charge-amount").html(response.Operations.SERVICECHARGEAMOUNT);
            jQuery("#tax-amount").html(response.Operations.TAXAMOUNT);
            jQuery("#tax-amount-buyer").html(response.Operations.TAXAMOUNTBUYER);
            jQuery("#cft").html(response.Operations.CFT);
            jQuery("#tea").html(response.Operations.TEA);

            var refunds = response.Operations.REFUNDS;
            strRefunds = JSON.stringify(refunds);
            jsonRefunds = JSON.parse(strRefunds);

            var htmlCode= '';
            if("REFUND" in jsonRefunds){

                    htmlCode += '<tr>';
                    htmlCode += '<td></td>';
                    htmlCode += '<td></td>';
                    htmlCode += '<td align="center" style="font-weight:bold"> Amount </td>';
                    htmlCode += '<td align="center" style="font-weight:bold"> Id </td>';
                    htmlCode += '<td align="center" style="font-weight:bold"> Date Time </td>';
                    htmlCode += '</tr>';
                 var d = 0;
                 var haveIndex = 0;
                 if("AMOUNT" in jsonRefunds.REFUND){
                 d++;
                 htmlCode += '<tr>';
                 htmlCode += '<td align="center">'+ 'Devolucion Nº' +'</td>';
                 htmlCode += '<td align="center" style="font-weight:bold">'+ d +'</td>';
                 htmlCode += '<td align="center">' + jsonRefunds.REFUND.AMOUNT + '</td>';
                 htmlCode += '<td align="center">' +jsonRefunds.REFUND.ID + '</td>';
                 htmlCode += '<td align="center">' +jsonRefunds.REFUND.DATETIME + '</td>';
                 htmlCode += '</tr>';
               }else{
                for( var i = 0 ; i < jsonRefunds.REFUND.length ; i++){
                    d++;
                    htmlCode += '<tr>';
                    htmlCode += '<td align="center">' + 'Devolucion Nº' + '</td>';
                    htmlCode += '<td align="center" style="font-weight:bold">' + d + '</td>';
                    htmlCode += '<td align="center">' + jsonRefunds.REFUND[i].AMOUNT + '</td>';
                    htmlCode += '<td align="center">' + jsonRefunds.REFUND[i].ID + '</td>';
                    htmlCode += '<td align="center">' + jsonRefunds.REFUND[i].DATETIME + '</td>';
                    htmlCode += '</tr>';
                }
            }
        } else {
            htmlCode += 'No existen devoluciones';
        }
        jQuery("#refunds").html(htmlCode);
        jQuery("#getStatus_table").attr("style", "display:block");

    } else {
        //error_message
        jQuery("#error_message").css("display", "block").html("La operacion no esta registrada");
    }
    */

}

/** Metodos devoluciones **/
function voidRequest() {

    jQuery.ajax({
        type: 'POST',
        url: "../wp-admin/admin-ajax.php",
        data: {
            'action': 'todopago_voidRequest',
            'amount': jQuery('#jigoshop_todopagopayment_amount').val()
        },
        success: function(data) {
            console.log('Enviando setVoidRequest: ' + data);
            setVoidRequest(data);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            switch (xhr.status) {
                case 404:
                    alert(
                        "Verifique la correcta instalación del plugin"
                    );
                    break;
                default:
                    alert(
                        "Verifique la conexion a internet y su proxy"
                    );
                    break;
            }
        },
        timeout: 8000
    });
}

function setVoidRequest(data) {
    console.log(data);
    var response = JSON.parse(data);
    if (response.StatusCode == '2011') {
        alert("Se ha reintegrado el monto solicitado");
    } else {
        alert(response.StatusMessage);
    }

}

/** Metodos devoluciones **/
function returnRequest(datos) {
    var amount = datos;

    jQuery.ajax({
        type: 'POST',
        url: "../wp-admin/admin-ajax.php",
        data: {
            'action': 'todopago_returnRequest',
            'amount': amount
        },
        success: function(data) {
            setReturnRequest(data);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            switch (xhr.status) {
                case 404:
                    alert(
                        "Verifique la correcta instalación del plugin"
                    );
                    break;
                default:
                    alert(
                        "Verifique la conexion a internet y su proxy"
                    );
                    break;
            }
        },
        timeout: 8000
    });
}

function setReturnRequest(data) {
    console.log(data);
    var response = JSON.parse(data);
    if (response.StatusCode == '2011') {
        alert("Se ha reintegrado el monto solicitado");
    } else {
        alert(response.StatusMessage);
    }
}

function $_GET(param) {
    /* Obtener la url completa */
    url = document.URL;
    /* Buscar a partir del signo de interrogación ? */
    url = String(url.match(/\?+.+/));
    /* limpiar la cadena quitándole el signo ? */
    url = url.replace("?", "");
    /* Crear un array con parametro=valor */
    url = url.split("&");
    /*
Recorrer el array url
obtener el valor y dividirlo en dos partes a través del signo =
0 = parametro
1 = valor
Si el parámetro existe devolver su valor
*/
    x = 0;
    while (x < url.length) {
        p = url[x].split("=");
        if (p[0] == param) {
            return decodeURIComponent(p[1]);
        }
        x++;
    }
}

function Java_to_Latin1(str) {
    str = str.trim();
    str = str.replace(/u00a0/g, "");
    str = str.replace(/u00a1/g, "¡");
    str = str.replace(/u00a2/g, "¢");
    str = str.replace(/u00a3/g, "£");
    str = str.replace(/u00a4/g, "¤");
    str = str.replace(/u00a5/g, "¥");
    str = str.replace(/u00a6/g, "¦");
    str = str.replace(/u00a7/g, "§");
    str = str.replace(/u00a8/g, "¨");
    str = str.replace(/u00a9/g, "©");
    str = str.replace(/u00aa/g, "ª");
    str = str.replace(/u00ab/g, "«");
    str = str.replace(/u00ac/g, " ");
    str = str.replace(/u00ad/g, "­");
    str = str.replace(/u00ae/g, "®");
    str = str.replace(/u00af/g, "¯");
    str = str.replace(/u00b0/g, "°");
    str = str.replace(/u00b1/g, "±");
    str = str.replace(/u00b2/g, "²");
    str = str.replace(/u00b3/g, "³");
    str = str.replace(/u00b4/g, "´");
    str = str.replace(/u00b5/g, "µ");
    str = str.replace(/u00b6/g, " ");
    str = str.replace(/u00b7/g, "·");
    str = str.replace(/u00b8/g, "¸");
    str = str.replace(/u00b9/g, "¹");
    str = str.replace(/u00ba/g, "º");
    str = str.replace(/u00bb/g, "»");
    str = str.replace(/u00bc/g, "¼");
    str = str.replace(/u00bd/g, "½");
    str = str.replace(/u00be/g, "¾");
    str = str.replace(/u00bf/g, "¿");
    str = str.replace(/u00c0/g, "À");
    str = str.replace(/u00c1/g, "Á");
    str = str.replace(/u00c2/g, "Â");
    str = str.replace(/u00c3/g, "Ã");
    str = str.replace(/u00c4/g, "Ä");
    str = str.replace(/u00c5/g, "Å");
    str = str.replace(/u00c6/g, "Æ");
    str = str.replace(/u00c7/g, "Ç");
    str = str.replace(/u00c8/g, "È");
    str = str.replace(/u00c9/g, "É");
    str = str.replace(/u00ca/g, "Ê");
    str = str.replace(/u00cb/g, "Ë");
    str = str.replace(/u00cc/g, "Ì");
    str = str.replace(/u00cd/g, "Í");
    str = str.replace(/u00ce/g, "Î");
    str = str.replace(/u00cf/g, "Ï");
    str = str.replace(/u00d0/g, "Ð");
    str = str.replace(/u00d1/g, "Ñ");
    str = str.replace(/u00d2/g, "Ò");
    str = str.replace(/u00d3/g, "Ó");
    str = str.replace(/u00d4/g, "Ô");
    str = str.replace(/u00d5/g, "Õ");
    str = str.replace(/u00d6/g, "Ö");
    str = str.replace(/u00d7/g, "×");
    str = str.replace(/u00d8/g, "Ø");
    str = str.replace(/u00d9/g, "Ù");
    str = str.replace(/u00da/g, "Ú");
    str = str.replace(/u00db/g, "Û");
    str = str.replace(/u00dc/g, "Ü");
    str = str.replace(/u00dd/g, "Ý");
    str = str.replace(/u00de/g, "Þ");
    str = str.replace(/u00df/g, "ß");
    str = str.replace(/u00e0/g, "à");
    str = str.replace(/u00e1/g, "á");
    str = str.replace(/u00e2/g, "â");
    str = str.replace(/u00e3/g, "ã");
    str = str.replace(/u00e4/g, "ä");
    str = str.replace(/u00e5/g, "å");
    str = str.replace(/u00e6/g, "æ");
    str = str.replace(/u00e7/g, "ç");
    str = str.replace(/u00e8/g, "è");
    str = str.replace(/u00e9/g, "é");
    str = str.replace(/u00ea/g, "ê");
    str = str.replace(/u00eb/g, "ë");
    str = str.replace(/u00ec/g, "ì");
    str = str.replace(/u00ed/g, "í");
    str = str.replace(/u00ee/g, "î");
    str = str.replace(/u00ef/g, "ï");
    str = str.replace(/u00f0/g, "ð");
    str = str.replace(/u00f1/g, "ñ");
    str = str.replace(/u00f2/g, "ò");
    str = str.replace(/u00f3/g, "ó");
    str = str.replace(/u00f4/g, "ô");
    str = str.replace(/u00f5/g, "õ");
    str = str.replace(/u00f6/g, "ö");
    str = str.replace(/u00f7/g, "÷");
    str = str.replace(/u00f8/g, "ø");
    str = str.replace(/u00f9/g, "ù");
    str = str.replace(/u00fa/g, "ú");
    str = str.replace(/u00fb/g, "û");
    str = str.replace(/u00fc/g, "ü");
    str = str.replace(/u00fd/g, "ý");
    str = str.replace(/u00fe/g, "þ");
    str = str.replace(/u00ff/g, "ÿ");

    return str;
}
