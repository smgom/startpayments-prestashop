<?php

class StartpaymentsPaymentModuleFrontController extends ModuleFrontController {

    public $ssl = true;
    public $display_column_left = false;
    public $_path = "";

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {

        parent::initContent();
        $this->_path = _MODULE_DIR_ . "startpayments/";
        $cart = $this->context->cart;
        if (!$cart->id)
            Tools::redirect('order');

        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
        if (Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_MODE'))) {
            $configuration_open_key = Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_OPEN_KEY'));
        } else {
            $configuration_open_key = Tools::safeOutput(Configuration::get('PAYFORT_START_LIVE_OPEN_KEY'));
        }


        $customer = new Customer((int) $cart->id_customer);
        $invoiceAddress = new Address((int) $cart->id_address_invoice);
        $currency = new Currency((int) $cart->id_currency);
        $amount = number_format((float) $cart->getOrderTotal(true, 3), 2, '.', '');
        if (file_exists(dirname(__FILE__) . '/data/currencies.json')) {
            $currency_json_data = json_decode(file_get_contents(dirname(__FILE__) . '/data/currencies.json'), 1);
            $currency_multiplier = $currency_json_data[$currency->iso_code];
        } else {
            $currency_multiplier = 100;
        }
        $amount_in_cents = $amount * $currency_multiplier;
        $this->context->smarty->assign('configuration_open_key', $configuration_open_key);
        $this->context->smarty->assign('email', $customer->email);
        $this->context->smarty->assign('currency', $currency->iso_code);
        $this->context->smarty->assign('amount', $amount);
        $this->context->smarty->assign('amount_in_cents', $amount_in_cents);
        $this->context->smarty->assign('x_invoice_num', (int) $cart->id);
        $this->context->smarty->assign('this_path_start_payments', $this->_path);
        $this->setTemplate('payment_execute.tpl');
    }

}
