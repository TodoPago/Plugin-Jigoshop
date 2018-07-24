<?php

require_once dirname(__FILE__) . '/todopagopayment.php';

class todopagopaymentbilletera extends todopagopayment
{
    public function __construct()
    {
        parent::__construct();
        $this->id = 'todopagopaymentbilletera';
        $this->title = "Billetera Virtual Todo Pago";
        $this->icon = Jigoshop_Base::get_options()->get_option('jigoshop_todopagopayment_billetera');
        add_action('receipt_todopagopaymentbilletera', array(
            &$this,
            'receipt_page',
        ));
    }
}
