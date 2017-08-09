<?php
if ($this->todopago_environment == "prod"){
?>
	<script type="text/javascript" src="https://forms.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
<?php
}else{
?>
	<script type="text/javascript" src="https://developers.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
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
			<div>
			<select id="formaDePagoCbx" class="form-control"></select>
			</div>
		</td>
		<td>
			<div>
			<select id="bancoCbx" class="form-control"></select>
			</div>
		</td>
 	</tr>
	<tr>
		<td colspan="2">
			<div>
			<select id="promosCbx" class="form-control"></select>
			<label id="labelPromotionTextId" class="left"></label>
			</div>
		</td>
	</tr>
  	<tr><!-- Para los casos en el que el comercio opera con PEI -->
		<td>
			<label id="labelPeiCheckboxId"></label>
			<input id="peiCbx"/>
		</td>
 	</tr>
	<tr>
		<td>
			<div>
			<input id="numeroTarjetaTxt"  class="form-control left" />
			</div>
		</td>
		<td>
			<div>
			<input id="codigoSeguridadTxt" class="form-control left" />
			</div>
			<div>
			<label id="labelCodSegTextId" ></label>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input id="mesTxt" >
			/
			<input id="anioTxt" >
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div><input id="apynTxt" class="left form-control" /></div>
		</td>
	</tr>
	<tr>
		<td>
			<select id="tipoDocCbx" class="form-control" ></select>
		</td>
		<td>
			<input id="nroDocTxt" class="form-control" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div ><input id="emailTxt" class="left form-control" /><br/></div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<!-- Para los casos en el que el comercio opera con PEI -->
			<label id="labelPeiTokenTextId"></label>
			<input id="peiTokenTxt"/>
		</td>
	</tr>
</tbody>
<tfoot>
</tfoot>
</table>

<div  class="pull-right">
<input type='button' class='btn btn-primary' id='MY_btnConfirmarPago' class='tp-button button alt' value="Pagar"/>
<input type='button' class='btn btn-primary' id='MY_btnPagarConBilletera' class='tp-button button alt' value="Pagar con Billetera" />
</div>
</div>

<script>

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

	function validationCollector(parametros) {
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
