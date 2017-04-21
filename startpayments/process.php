<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/startpayments.php');
$payfortstart = new StartPayments();
$cart = Context::getContext()->cart;
$customer = new Customer((int) $cart->id_customer);
$currency = new Currency((int) $cart->id_currency);
$delivery_address = new Address(intval($cart->id_address_delivery));
$invoiceAddress = new Address((int) $cart->id_address_invoice);
$shipping_address = array(
    "first_name" => $delivery_address->firstname,
    "last_name" => $delivery_address->lastname,
    "country" => $delivery_address->country,
    "city" => $delivery_address->city,
    "address_1" => $delivery_address->address1,
    "address_2" => $delivery_address->address2,
    "phone" => $delivery_address->phone,
    "postcode" => $delivery_address->postcode
);
$billing_address = array(
    "first_name" => $invoiceAddress->firstname,
    "last_name" => $invoiceAddress->lastname,
    "country" => $invoiceAddress->country,
    "city" => $invoiceAddress->city,
    "address_1" => $invoiceAddress->address1,
    "address_2" => $invoiceAddress->address2,
    "phone" => $invoiceAddress->phone,
    "postcode" => $invoiceAddress->postcode
);

include (dirname(__FILE__) . '/vendor/payfort/start/Start.php');
if (file_exists(dirname(__FILE__) . '/data/currencies.json')) {
    $currency_json_data = json_decode(file_get_contents(dirname(__FILE__) . '/data/currencies.json'), 1);
    $currency_multiplier = $currency_json_data[$currency->iso_code];
} else {
    $currency_multiplier = 100;
}
$customer = new Customer((int) $cart->id_customer);
$registered_at = ($customer->is_guest == 0) ? date(DATE_ISO8601, strtotime(date("Y-m-d H:i:s"))) : date(DATE_ISO8601, strtotime($customer->date_add));
$products = $cart->getProducts(true);
$order_items_array_full = array();
foreach ($products as $key => $items) {
    $order_items_array['title'] = $items['name'];
    $order_items_array['amount'] = round($items['price'], 2) * $currency_multiplier;
    $order_items_array['quantity'] = $items['quantity'];
    array_push($order_items_array_full, $order_items_array);
}
if (Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_MODE'))) {
    $start_payments_secret_api = Tools::safeOutput(Configuration::get('PAYFORT_START_TEST_SECRET_KEY'));
} else {
    $start_payments_secret_api = Tools::safeOutput(Configuration::get('PAYFORT_START_LIVE_SECRET_KEY'));
}
if (Tools::safeOutput(Configuration::get('PAYFORT_START_CAPTURE'))) {
    $capture = 0;
} else {
    $capture = 1;
}
$userAgent = 'Prestashop ' . _PS_VERSION_ . ' / Start Plugin ' . $payfortstart->version;
Start::setUserAgent($userAgent);
Start::setApiKey($start_payments_secret_api);
$amount = $cart->getOrderTotal(true);
$amount_in_cents = $amount * $currency_multiplier;
$shipping_cost =  $cart->getTotalShippingCost()*$currency_multiplier;
$shopping_cart_array = array(
    'user_name' => $customer->firstname,
    'registered_at' => $registered_at,
    'items' => $order_items_array_full,
    'billing_address' => $billing_address,
    'shipping_address' => $shipping_address
);
$charge_args = array(
    'description' => 'prestashop test', // only 255 chars
    'card' => $_POST['payfortToken'],
    'currency' => $currency->iso_code,
    'email' => $customer->email,
    'ip' => $_SERVER["REMOTE_ADDR"],
    'amount' => $amount_in_cents,
    'capture' => $capture,
    'shopping_cart' => $shopping_cart_array,
    'shipping_amount' => $shipping_cost,
    'metadata' => array('reference_id' => $_POST['x_invoice_num'])
);
try {
    $charge = Start_Charge::create($charge_args);
    $url = 'index.php?controller=order-confirmation&';
    $payfortstart->validateOrder((int) $cart->id, Configuration::get('PAYFORT_START_HOLD_REVIEW_OS'), (float) $amount, "credit/debit card", "message", NULL, NULL, false, $customer->secure_key);
    $auth_order = new Order($payfortstart->currentOrder);
    Tools::redirect($url . 'id_module=' . (int) $payfortstart->id . '&id_cart=' . (int) $cart->id . '&key=' . $auth_order->secure_key);
} catch (Exception $e) {
    if ($e->getErrorCode() == "card_declined") {
        $error_message = "Card declined. Please use another card";
    } else {
        $error_message = $e->getMessage();
    }
    $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
            'order-opc' : 'order';
    $url = _PS_VERSION_ >= '1.5' ?
            'index.php?controller=' . $checkout_type . '&' : $checkout_type . '.php?';
    $url .= 'step=3&starterror=1&message=' . $error_message;
    if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order'))
        Tools::redirect($url);
    else if (strstr($_SERVER['HTTP_REFERER'], '?'))
        Tools::redirect('index.php?controller=order&starterror=1&message=' . $error_message, '');
    else
        Tools::redirect('index.php?controller=order&step=3&starterror=1&message=' . $error_message, '');
    exit;
}Tools::redirect('index.php?controller=order&step=1');
