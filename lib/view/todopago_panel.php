<?php
    update_option('post', $_GET['post']);
?>
    <button type="button" id="jigoshop_todopagopayment_btnGetStatus" class="button button-primary">Estado transacción </button>
    <button type="button" id="jigoshop_todopagopayment_btnDevolution" class="button button-warning">Devolución</button>
    <input type="text" id="jigoshop_todopagopayment_amount" class="text">
    <button type="button" id="jigoshop_todopagopayment_btnDevolutionPartial" class="button button-warning"><- Devolución parcial</button>
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
            <td>Card number</td> <td id="card-number"></td>
        </tr>
        <tr>
            <td>Card holder name</td> <td id="card-holdername"></td>
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
            <td>Refunds</td><td id="refunds"></td>
        </tr>
    </table>
    <div>
        <span id="error_message" style="display:none;"></span>
    </div>