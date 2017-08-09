<?php
    update_option('post', $_GET['post']);
    $recargo = get_option('AMOUNTBUYER'.$_GET['post']) - get_option('AMOUNT'.$_GET['post']);
?>
<br />
<table>
  <tr>
<td align='center'><h1>Costo producto</h1></td><td align='center'><h1>+</h1></td><td align='center'><h1>Recargos</h1></td><td align='center'><h1>=</h1></td><td align='center'><h1>Total</h1></td>
</tr>
<tr>
  <td align='center'><h1> <font color="green">$<?php echo get_option('AMOUNT'.$_GET['post']); ?></font></h1></td><td align='center'><h1>+</h1></td><td align='center'><h1><font color="green">$<?php echo $recargo ?></font></h1></td><td align='center'><h1>=</h1></td><td align='center'><h1><font color="green">$<?php echo get_option('AMOUNTBUYER'.$_GET['post']) ?></font></h1></td>
</tr>
</table>
<br />
    <button type="button" id="jigoshop_todopagopayment_btnGetStatus" class="button button-primary">Estado transacción </button>
    <p>------</p>
    <input type="text" id="jigoshop_todopagopayment_amount" style="border:1px solid #8BA870" class="text">
    <button type="button" id="jigoshop_todopagopayment_btnDevolution" class="button button-warning">Devolución</button>
    <button type="button" id="jigoshop_todopagopayment_btnDevolutionPartial" class="button button-warning">Devolución parcial</button><i>    * El monto de devolución se calcula en base al costo original del producto sin los impuestos agregados.</i>
    <p>------</p>
    <h2>Otros cargos: </h2><input type="text" id="jigoshop_todopagopayment_amount" placeholder=$<?php echo $recargo ?> style="border:1px solid #8BA870" class="text" disabled>
    <br />
    <br />
    <table style="display:none" id="getStatus_table">
        <tr>
            <td>Result code</td> <td id="result-code"></td>
        </tr>
        <tr>
            <td>Mensaje key</td> <td id="result-message"></td>
        </tr>
        <tr>
            <td>Date time</td> <td id="date-time"></td>
        </tr>
        <tr>
            <td>Operation id</td> <td id="operation-id"></td>
        </tr>
        <tr>
            <td>Currency code</td> <td id="currency-code"></td>
        </tr>
        <tr>
            <td>Amount</td> <td id="amount"></td>
        </tr>
        <tr>
            <td>Amount buyer</td> <td id="amount-buyer"></td>
        </tr>
        <tr>
            <td>Bank id</td> <td id="bank-id"></td>
        </tr>
        <tr>
            <td>Promotion id</td> <td id="promotion-id"></td>
        </tr>
        <tr>
            <td>Type</td> <td id="type"></td>
        </tr>
        <tr>
            <td>Installment Payments</td> <td id="installment-payments"></td>
        </tr>
        <tr>
            <td>Customer email</td> <td id="customer-email"></td>
        </tr>
        <tr>
            <td>Identification type</td> <td id="identification-type"></td>
        </tr>
        <tr>
            <td>Identification</td> <td id="identification"></td>
        </tr>
        <tr>
            <td>Card holder name</td> <td id="card-holdername"></td>
        </tr>
        <tr>
            <td>Card number</td> <td id="card-number"></td>
        </tr>
        <tr>
            <td>Comision</td> <td id="comision"></td>
        </tr>
        <tr>
            <td>Ticket number</td> <td id="ticket-number"></td>
        </tr>
        <tr>
            <td>Authorization code</td> <td id="authorization-code"></td>
        </tr>
        <tr>
            <td>Bar code</td> <td id="bar-code"></td>
        </tr>
        <tr>
            <td>Id contracargo</td> <td id="id-contracargo"></td>
        </tr>
        <tr>
            <td>Estado contracargo</td> <td id="estado-contracargo"></td>
        </tr>
        <tr>
            <td>Fecha notificación cuenta</td> <td id="fecha-notificacion"></td>
        </tr>
        <tr>
            <td>Fee amount</td> <td id="fee-amount"></td>
        </tr>
        <tr>
            <td>Fee amount buyer</td> <td id="fee-amountbuyer"></td>
        </tr>
        <tr>
            <td>Coupon exp date</td> <td id="coupon-expdate"></td>
        </tr>
        <tr>
            <td>Coupon sec exp date</td> <td id="coupon-secexpdate"></td>
        </tr>
        <tr>
            <td>Coupon suscriber</td> <td id="coupon-suscriber"></td>
        </tr>
        <tr>
            <td>Payment method code</td> <td id="payment-mehod-code"></td>
        </tr>
        <tr>
            <td>Payment method name</td> <td id="payment-method-name"></td>
        </tr>
        <tr>
            <td>Payment method type</td> <td id="payment-method-type"></td>
        </tr>
        <tr>
            <td>Refunded</td> <td id="refunded"></td>
        </tr>
        <tr>
            <td>Push notify method</td> <td id="push-notify-method"></td>
        </tr>
        <tr>
            <td>Push notify endpoint</td> <td id="push-notify-endpoint"></td>
        </tr>
        <tr>
            <td>Push notify states</td> <td id="push-notify-states"></td>
        </tr>
        <tr>
            <td>Service charge amount</td> <td id="service-charge-amount"></td>
        </tr>
        <tr>
            <td>Tax amount</td> <td id="tax-amount"></td>
        </tr>
        <tr>
            <td>Tax amount buyer</td> <td id="tax-amount-buyer"></td>
        </tr>
        <tr>
            <td>Refunds</td><td id="refunds"></td>
        </tr>
    </table>
    <div>
        <span id="error_message" style="display:none;"></span>
    </div>
