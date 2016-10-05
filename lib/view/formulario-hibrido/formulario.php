<?php	
if ($this->todopago_environment == "prod"){
?>
	<script type="text/javascript" src="https://forms.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
<?php
}else{
?>
<script type="text/javascript" src="https://developers.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
<?php
}			
echo "<div id='formualrio_hibrido' class='table-responsive' >";
echo "<table class='table table-bordered table-hover'>";
echo ' <tr>';
echo '<td colspan="2">';
echo '<div id="tp-logo"></div>';
echo '<p class="tp-label">Elegí tu forma de pago </p>';
echo '</td>';
echo '</tr>';
echo '<tbody>';
echo ' <tr>';
echo '  <td>';
echo '   <div>';
echo '     <select id="formaDePagoCbx" class="form-control"></select>';
echo ' </div>';
echo '</td>';
echo ' <td>';
echo '     <div>';
echo ' <select id="bancoCbx" class="form-control"></select>';
echo '  </div>';
echo ' </td>';
echo ' </tr>';
echo '<tr>';
echo '<td colspan="2">';
echo ' <div>';
echo '<select id="promosCbx" class="form-control"></select>';
echo '<label id="labelPromotionTextId" class="left"></label>';
echo '</div>';
echo '</td>';
echo '  </tr>';
echo ' <tr>';
echo '  <td>';
echo ' <div>';
echo '  <input id="numeroTarjetaTxt"  class="form-control left" />';
echo ' </div>';
echo '</td>';
echo '<td>';
echo ' <div>';
echo '  <input id="codigoSeguridadTxt" class="form-control left" />';
echo ' </div>';
echo ' <div>';
echo '<label id="labelCodSegTextId" ></label>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo ' <td colspan="2">';
echo '  <input id="mesTxt" >';
echo ' /';
echo ' <input id="anioTxt" >';
echo '</td>';
echo '  </tr>';
echo ' <tr>';
echo '  <td colspan="2">';
echo ' <div>';
echo '     <input id="apynTxt" class="left form-control" />';
echo ' </div>';
echo '</td>';
echo '</tr>';
echo ' <tr>';
echo '  <td>';
echo ' <select id="tipoDocCbx" class="form-control" ></select>';
echo '</td>';
echo ' <td>';
echo '   <input id="nroDocTxt" class="form-control" />	';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2">';
echo '<div >';
echo '<input id="emailTxt" class="left form-control" /><br/>';
echo '</div>';
echo '  </td>';
echo ' </tr>';
echo ' </tbody>';
echo '<tfoot>';
echo ' </tfoot>';
echo ' </table>';
echo '<div  class="pull-right">';
echo "<input type='button' class='btn btn-primary' id='MY_btnConfirmarPago' class='button' echo value='Pagar'/>";
echo ' </div>';
echo '</div>';
                
?>
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
		console.log("My wallet callback");
		console.log(response.ResultCode + " : " + response.ResultMessage);
	}

	function customPaymentSuccessResponse(response) {
		console.log("My custom payment success callback");
		console.log(response.ResultCode + " : " + response.ResultMessage);
		document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
	}

	function customPaymentErrorResponse(response) {
		console.log("Mi custom payment error callback");
		console.log(response.ResultCode + " : " + response.ResultMessage);
		document.location = "<?php echo "$return_URL_ERROR&Answer="; ?>" + response.AuthorizationKey;
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

    