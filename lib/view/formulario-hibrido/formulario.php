<?php
if ($this->todopago_environment == "prod"){
?>
<script type="text/javascript" src="https://forms.todopago.com.ar/resources/v2/TPBSAForm.min.js"></script>
<?php
}else{
?>
<script type="text/javascript" src="https://developers.todopago.com.ar/resources/v2/TPBSAForm.min.js"></script>
<?php
} ?>

<div id='formualrio_hibrido' class='table-responsive' >
<table class='table table-bordered table-hover'>

	<tr>
		<td colspan="2">
			<div id="tp-logo"></div>
			<p class="tp-label">Elegí tu forma de pago </p>
		</td>
	</tr>
<tbody>

	<tr>
		<td>
			<!-- Para los casos en el que el comercio opera con PEI -->
			<input id="peiCbx"/><label id="peiLbl"></label>
		</td>
	</tr>

	<tr>
		<td>
			<div>
			<select id="formaPagoCbx" class="select-control"></select>
			</div>
		</td>
		<td>
		</tr>
		<tr>
			<td>
				<div>
				<input id="numeroTarjetaTxt" class="form-control">
				<label id="numeroTarjetaLbl"></label>
				</div>
			</td>
		</tr>
			<tr>
				<td>
				<select id="medioPagoCbx" class="select-control"></select>
			</td>
							<td>
			<select id="bancoCbx" class="form-control"></select>
		</td>
 	</tr>

	<tr>
		<td colspan="2">
			<div>
			<select id="promosCbx" class="form-control"></select><label id="promosLbl"></label>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<select  id="mesCbx" class="select-control" placeholder="MM" ></select>
			<select  id="anioCbx" class="select-control" placeholder="AA"></select>
			<label id="fechaLbl"></label>

		</td>
	</tr>
	<tr>
		<td>
	<input id="codigoSeguridadTxt" type="password" class="form-control" placeholder="Cód. de seguridad" maxlength="4">
		<label id="codigoSeguridadLbl"></label>

</td>
</tr>
<tr>
	<td colspan="2">
<input id="nombreTxt" placeholder="Nombre y Apellido" maxlength="50" autocomplete="off" class="form-control cleanChangeMP"/>
<label id="nombreLbl"></label>

</td>
</tr>
    <tr>
        <td>
            <select id="tipoDocCbx" class="form-control" ></select>
        </td>
        <td>
            <input id="nroDocTxt" class="form-control" />
            <label id="nroDocLbl"></label>

        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div ><input id="emailTxt" class="left form-control" /><br/></div>
            <label id="emailLbl"></label>
        </td>
    </tr>


<tr>
    <td colspan="2">
        <!-- Para los casos en el que el comercio opera con PEI -->
        <label id="tokenPeiLbl"></label>
        <input id="tokenPeiTxt"/>
    </td>
=======
	<tr>
		<td>
			<select id="tipoDocCbx" class="form-control" ></select>
		</td>
		<td>
			<input id="nroDocTxt" class="form-control" />
			<label id="nroDocLbl"></label>

		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div ><input id="emailTxt" class="left form-control" /><br/></div>
			<label id="emailLbl"></label>
		</td>
	</tr>


<tr>
	<td colspan="2">
		<!-- Para los casos en el que el comercio opera con PEI -->
		<label id="tokenPeiLbl"></label>
		<input id="tokenPeiTxt"/>
	</td>
</tr>
</tbody>
<tfoot>
</tfoot>
</table>

<div  class="pull-right">
<input type='button' class='btn btn-primary' id='MY_btnConfirmarPago' class='tp-button button alt' value="Pagar" style="padding: 10px 30px 11px;" onclick="
inicializarMensajesError()
" />
<input type='button' class='btn btn-primary' id='MY_btnPagarConBilletera' class='tp-button button alt' value="Pagar con Billetera" style="padding: 10px 30px 11px;" />
</div>
</div>

<script>
    var arrMensajesError=[];


    /************* CONFIGURACION DEL API ************************/
    window.TPFORMAPI.hybridForm.initForm({
        callbackValidationErrorFunction: 'validationCollector',
        callbackBilleteraFunction: 'billeteraPaymentResponse',
        botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
        modalCssClass: 'tp-modal-class',
        modalContentCssClass: 'tp-modal-content',
        beforeRequest: 'initLoading',
        afterRequest: 'stopLoading',
        callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
        callbackCustomErrorFunction: 'customPaymentErrorResponse',
        botonPagarId: 'MY_btnConfirmarPago',
    });



    /************* CONFIGURACION DEL API *********************/
    function loadScript(url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (script.readyState) {  //IE
            script.onreadystatechange = function () {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //et al.
            script.onload = function () {
                callback();
            };
        }
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    loadScript('<?php echo $env_url ?>', function () {
        loader();
    });


    function loader() {
        tpformJquery("#loading-hibrid").css("width", "50%");
        setTimeout(function () {
            ignite();
        }, 100);
        setTimeout(function () {
            tpformJquery("#loading-hibrid").css("width", "100%");
        }, 1000);
        setTimeout(function () {
            tpformJquery(".progress").hide('fast');
        }, 2000);
        setTimeout(function () {
            tpformJquery("#contenedor-formulario").show('slow');
            tpformJquery("#tp-form").fadeTo('fast', 1);
            desplegarForm();
        }, 2200);
    }

    function desplegarForm() {
        console.log("Llegué");
        if (formaDePago.value === "1") {
            detector();
            peiRowHaven();
            $(formaDePago).hide('fast');
            $(".loaded-form").show('fast');
        }
    }

    function ignite() {

        window.TPFORMAPI.hybridForm.initForm({
            callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'MY_btnConfirmarPago',
            botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
            modalCssClass: 'modal-class',
            modalContentCssClass: 'modal-content',
            beforeRequest: 'initLoading',
            afterRequest: 'stopLoading'
        });

        /************* SETEO UN ITEM PARA COMPRAR ******************/
        window.TPFORMAPI.hybridForm.setItem({
            publicKey: '<?php echo $responseSAR->PublicRequestKey; ?>',
            defaultNombreApellido: '<?php echo $nombre_completo; ?>',
            defaultNumeroDoc: '',
            defaultMail: '<?php echo $email; ?>',
            defaultTipoDoc: 'DNI'
        });
    }

    /************ FUNCIONES CALLBACKS ************/



    function inicializarMensajesError(){
        console.log('inicializarMensajesError');
        /*
        jQuery('#emailLbl').hide();
        jQuery('#nroDocLbl').hide();
        jQuery('#nombreLbl').hide();
        jQuery('#codigoSeguridadLbl').hide();
        jQuery('#fechaLbl').hide();
        */

        
        document.getElementById('emailLbl').innerHTML = '';
        document.getElementById('nroDocLbl').innerHTML = '';
        document.getElementById('nombreLbl').innerHTML = '';
        document.getElementById('codigoSeguridadLbl').innerHTML = '';
        document.getElementById('fechaLbl').innerHTML = '';
        

        arrMensajesError=[];

    }


    function validationCollector(parametros) {
        //arrMensajesError=[];

	var arrMensajesError=[];


	/************* CONFIGURACION DEL API ************************/
	window.TPFORMAPI.hybridForm.initForm({
		callbackValidationErrorFunction: 'validationCollector',
		callbackBilleteraFunction: 'billeteraPaymentResponse',
		botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
		modalCssClass: 'tp-modal-class',
		modalContentCssClass: 'tp-modal-content',
		beforeRequest: 'initLoading',
		afterRequest: 'stopLoading',
		callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
		callbackCustomErrorFunction: 'customPaymentErrorResponse',
		botonPagarId: 'MY_btnConfirmarPago',
	});

	window.TPFORMAPI.hybridForm.setItem({
		publicKey: '<?php echo $prk ?>',
		defaultNombreApellido: '<?php echo "$firstname $lastname" ?>',
		defaultMail: '<?php echo "$email"; ?>'
	});


	function inicializarMensajesError(){
		console.log('inicializarMensajesError');
		/*
		jQuery('#emailLbl').hide();
		jQuery('#nroDocLbl').hide();
		jQuery('#nombreLbl').hide();
		jQuery('#codigoSeguridadLbl').hide();
		jQuery('#fechaLbl').hide();
		*/

		
		document.getElementById('emailLbl').innerHTML = '';
		document.getElementById('nroDocLbl').innerHTML = '';
		document.getElementById('nombreLbl').innerHTML = '';
		document.getElementById('codigoSeguridadLbl').innerHTML = '';
		document.getElementById('fechaLbl').innerHTML = '';
		

		arrMensajesError=[];

	}


	function validationCollector(parametros) {
		//arrMensajesError=[];
        console.log("Validando los datos");
        console.log(parametros.field + " -> " + parametros.error);
        var input = parametros.field;
        if (input.search("Txt") !== -1) {
            label = input.replace("Txt", "Lbl");
        } else {
            label = input.replace("Cbx", "Lbl");
        }

        if (document.getElementById(label) !== null){
            document.getElementById(label).innerHTML = parametros.error; 
        }
        
        arrMensajesError.push(label);        

        console.log('En el array hay: ' + arrMensajesError );

        /*
        console.log("My validator collector");
        console.log(parametros.field + " ==> " + parametros.error);
        //Si está "limpio" puede ser porque ya se ejecutó el método _clean_errors() o porque no hubo cambios desde la vez anterior en la que se tocó el botón, en ese caso el div pending_errors debería contener los errores previos
        if (jQuery('#errors_clean').val() == "true" && jQuery("#pending_errors").children().length == 0) {
            jQuery('.woocommerce-error').append("<li>"+parametros.error+"</li>");
            jQuery('.woocommerce-error').show();
            jQuery('#'+parametros.field).parent().addClass("woocommerce-invalid woocommerce-invalid-required-field").removeClass("woocommerce-validated");
        }

        //Agrego los errores al div de errores pendientes, siempre y cuando no estén aún (Esto puede ocurrir si el usuario volvió a intentar pagar sin hacer cambios en los campos del formulario)
        if (jQuery("#error_"+parametros.field).length == 0) {
            jQuery("#pending_errors").append('<input type="hidden" id="error_'+parametros.field+'" value="'+parametros.error+'" data-element="#'+parametros.field+'" />');
        }
        */
    }

    function billeteraPaymentResponse(response) {
        console.log("Iniciando billetera");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = urlSuccess + "&Answer=" + response.AuthorizationKey;
        } else {
            window.location.href = urlError + "&Error=" + response.ResultMessage;
        }
    }

    function customPaymentSuccessResponse(response) {
        console.log("Success");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        window.location.href = urlSuccess + "&Answer=" + response.AuthorizationKey;
    }

    function customPaymentErrorResponse(response) {
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = urlSuccess + "&Answer=" + response.AuthorizationKey;
        } else {
            window.location.href = urlError + "&Error=" + response.ResultMessage;
        }
    }

    formaDePago.addEventListener("click", function () {
        if (formaDePago.value === "1") {
            detector();
            peiRowHaven();
            tpformJquery(".loaded-form").show('fast');
        } else {
            tpformJquery(".loaded-form").hide('fast');
        }
    });

    function initLoading() {
        console.log('Loading...');
    }

    numeroDeTarjeta.change(function () {
        peiRowHaven()
    });

    function peiRowHaven() {
        if (peiCbx.css('display') !== 'none') {
            peiRow.show('fast');
        }
        if (peiCbx.css('display') === 'none') {
            peiRow.hide('fast');
        }
        if (tokenPeiTxt.css('display') !== 'none') {
            tokenPeiBloque.css('height', "%100");
            tokenPeiRow.css('height', "%100");
        } else {
            tokenPeiBloque.css('height', "%0");
            tokenPeiRow.css('height', "%0");
        }
    }

    function stopLoading() {
        console.log('Stop loading...');
        peiRowHaven();
    }


        if (document.getElementById(label) !== null){
            document.getElementById(label).innerHTML = parametros.error; 
        }
        
		arrMensajesError.push(label);        

        console.log('En el array hay: ' + arrMensajesError );

		/*
		console.log("My validator collector");
		console.log(parametros.field + " ==> " + parametros.error);
		//Si está "limpio" puede ser porque ya se ejecutó el método _clean_errors() o porque no hubo cambios desde la vez anterior en la que se tocó el botón, en ese caso el div pending_errors debería contener los errores previos
		if (jQuery('#errors_clean').val() == "true" && jQuery("#pending_errors").children().length == 0) {
			jQuery('.woocommerce-error').append("<li>"+parametros.error+"</li>");
			jQuery('.woocommerce-error').show();
			jQuery('#'+parametros.field).parent().addClass("woocommerce-invalid woocommerce-invalid-required-field").removeClass("woocommerce-validated");
		}

		//Agrego los errores al div de errores pendientes, siempre y cuando no estén aún (Esto puede ocurrir si el usuario volvió a intentar pagar sin hacer cambios en los campos del formulario)
		if (jQuery("#error_"+parametros.field).length == 0) {
			jQuery("#pending_errors").append('<input type="hidden" id="error_'+parametros.field+'" value="'+parametros.error+'" data-element="#'+parametros.field+'" />');
		}
		*/
	}

	function billeteraPaymentResponse(response) {
		if(response.AuthorizationKey)
			document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
		else
			document.location = "<?php echo "$return_URL_OK&Error="; ?>" + response.ResultMessage;

	}

	function customPaymentSuccessResponse(response) {
		console.log("My custom payment success callback");
		console.log(response.ResultCode + " : " + response.ResultMessage);
		document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
	}

	function customPaymentErrorResponse(response) {
		if(response.AuthorizationKey)
			document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
		else
			document.location = "<?php echo "$return_URL_OK&Error="; ?>" + response.ResultMessage;
	}

	function initLoading() {
		console.log('Cargando');
	}

	function stopLoading() {
		console.log('Stop loading...');
	}
</script>
<style type="text/css">
	#tp-logo{
		background-image: url("https://portal.todopago.com.ar/app/images/logo.png");
		background-repeat: no-repeat;
		height:40px;
		width:110px;
		margin: 0 0 0 14px;
	}
</style>
