<?php

if (!defined('_PS_VERSION_'))
    exit;

class StartPayments extends PaymentModule {

    public function __construct() {
        $this->name = 'startpayments';
        $this->tab = 'payments_gateways';
        $this->version = '1.2';
        $this->author = 'manish';

        parent::__construct();
        $this->displayName = 'Start Payments';
        $this->description = $this->l('Receive payment with Credit or Debit Card');


        /* For 1.4.3 and less compatibility */
        $updateConfig = array(
            'PS_OS_CHEQUE' => 1,
            'PS_OS_PAYMENT' => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING' => 4,
            'PS_OS_DELIVERED' => 5,
            'PS_OS_CANCELED' => 6,
            'PS_OS_REFUND' => 7,
            'PS_OS_ERROR' => 8,
            'PS_OS_OUTOFSTOCK' => 9,
            'PS_OS_BANKWIRE' => 10,
            'PS_OS_PAYPAL' => 11,
            'PS_OS_WS_PAYMENT' => 12);

        foreach ($updateConfig as $u => $v)
            if (!Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                else
                    Configuration::updateValue($u, $v);
            }

        /* Check if cURL is enabled */
        if (!is_callable('curl_exec'))
            $this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

        /* Backward compatibility */
        /* require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php'); */
    }

    public function install() {
        return parent::install() &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('payment') &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                Configuration::updateValue('PAYFORT_START_SANDBOX', 1) &&
                Configuration::updateValue('PAYFORT_START_TEST_MODE', 0) &&
                Configuration::updateValue('PAYFORT_START_HOLD_REVIEW_OS', _PS_OS_ERROR_);
    }

    public function uninstall() {
        Configuration::deleteByName('PAYFORT_START_SANDBOX');
        Configuration::deleteByName('PAYFORT_START_TEST_MODE');
        Configuration::deleteByName('PAYFORT_START_LIVE_OPEN_KEY');
        Configuration::deleteByName('PAYFORT_START_TEST_OPEN_KEY');
        Configuration::deleteByName('PAYFORT_START_LIVE_SECRET_KEY');
        Configuration::deleteByName('PAYFORT_START_TEST_SECRET_KEY');
        Configuration::deleteByName('PAYFORT_START_HOLD_REVIEW_OS');
        return parent::uninstall();
    }

    public function hookOrderConfirmation($params) {
        if ($params['objOrder']->module != $this->name)
            return;

        if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR')) {
            Configuration::updateValue('PAYFORTSTART_CONFIGURATION_OK', true);
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
        } else
            $this->context->smarty->assign('status', 'failed');

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    public function hookBackOfficeHeader() {
        $this->context->controller->addJQuery();
        if (version_compare(_PS_VERSION_, '1.5', '>='))
            $this->context->controller->addJqueryPlugin('fancybox');

        $this->context->controller->addJS($this->_path . 'js/payfortstart.js');
        $this->context->controller->addCSS($this->_path . 'css/payfortstart.css');
    }

    public function getContent() {
        $html = '';
        if (Tools::isSubmit('submitModule')) {
            $payfort_start_mode = (int) Tools::getvalue('payfort_start_mode');
            if ($payfort_start_mode == 1) {
                Configuration::updateValue('PAYFORT_START_TEST_MODE', 1);
            } else {
                Configuration::updateValue('PAYFORT_START_TEST_MODE', 0);
            }
            $payfort_start_action = (int) Tools::getvalue('payfort_start_action');
            if ($payfort_start_action == 1) {
                Configuration::updateValue('PAYFORT_START_CAPTURE', 1);
            } else {
                Configuration::updateValue('PAYFORT_START_CAPTURE', 0);
            }
            foreach ($_POST as $key => $value) {
                if ($key != "tab" && $key != "submitModule") {
                    Configuration::updateValue(strtoupper($key), $value);
                }
            }
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }
        // For "Hold for Review" order status
        $order_states = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'order_states' => $order_states,
            'PAYFORT_START_TEST_MODE' => Configuration::get('PAYFORT_START_TEST_MODE'),
            'PAYFORT_START_HOLD_REVIEW_OS' => (int) Configuration::get('PAYFORT_START_HOLD_REVIEW_OS'),
            'PAYFORT_START_CAPTURE' => Configuration::get('PAYFORT_START_CAPTURE')
        ));
        $configuration_live_open_key = 'PAYFORT_START_LIVE_OPEN_KEY';
        $configuration_live_secret_key = 'PAYFORT_START_LIVE_SECRET_KEY';
        $configuration_test_open_key = 'PAYFORT_START_TEST_OPEN_KEY';
        $configuration_test_secret_key = 'PAYFORT_START_TEST_SECRET_KEY';
        $this->context->smarty->assign($configuration_live_open_key, Configuration::get($configuration_live_open_key));
        $this->context->smarty->assign($configuration_live_secret_key, Configuration::get($configuration_live_secret_key));
        $this->context->smarty->assign($configuration_test_open_key, Configuration::get($configuration_test_open_key));
        $this->context->smarty->assign($configuration_test_secret_key, Configuration::get($configuration_test_secret_key));
        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/configuration.tpl');
    }

    public function hookPayment($params) {
        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
        $isFailed = Tools::getValue('payfortstarterror');
        if (method_exists('Tools', 'getShopDomainSsl'))
            $url = 'https://' . Tools::getShopDomainSsl() . __PS_BASE_URI__ . '/modules/' . $this->name . '/';
        else
            $url = 'https://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/' . $this->name . '/';

        if (Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_MODE'))) {
            $configuration_open_key = Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_OPEN_KEY'));
        } else {
            $configuration_open_key = Tools::safeOutput(Configuration::get('PAYFORT_START_LIVE_OPEN_KEY'));
        }
        $this->context->smarty->assign('configuration_open_key', $configuration_open_key);

        $cart = Context::getContext()->cart;
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
        $this->context->smarty->assign('email', $customer->email);
        $this->context->smarty->assign('currency', $currency->iso_code);
        $this->context->smarty->assign('amount', $amount);
        $this->context->smarty->assign('amount_in_cents', $amount_in_cents);
        $this->context->smarty->assign('isFailed', $isFailed);
        $this->context->smarty->assign('ps_vesion', _PS_VERSION_);
        $this->context->smarty->assign('x_invoice_num', (int) $params['cart']->id);
        $this->context->smarty->assign('this_path_start_payments', $this->_path);
        return $this->display(__FILE__, 'views/templates/hook/payfortstart.tpl');
    }

}
