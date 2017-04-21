<script src="https://beautiful.start.payfort.com/checkout.js"></script>
<form name="payfortstart_form" id="payfortstart_form" action="{$this_path_start_payments}process.php" method="post">
    <p class="cart_navigation" id="cart_navigation">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i>Other Payment Methods</a>
        <button type="submit" id="click_payfortstart" class="button btn btn-default button-medium"><span>I confirm my order<i class="icon-chevron-right right"></i></span></button>
    </p>
    <input name="x_invoice_num" type="hidden" value="{$x_invoice_num}">
    <input name="amount" type="hidden" value="{$amount}">
</form>
<script type="text/javascript">
    function submitFormWithToken(param) {
        removePaymentToken();
        $('#payfortstart_form').append("<span class='start_response'></span>");
        $('#payfortstart_form').parent().find(".start_response").append("<input type = 'hidden' name='payfortToken' value = " + param.token.id + ">");
        $('#payfortstart_form').trigger('submit');
    }
    function removePaymentToken() {
        $('#payfortstart_form').find(".start_response").remove();
        $('#payfort_start_error').remove();
    }
    StartCheckout.config({
        key: "{$configuration_open_key}",
        complete: function (params) {
            submitFormWithToken(params);
        },
        cancel: function () {
            window.location.replace("index.php?controller=order&step=3");
        }
    });
    StartCheckout.open({
        amount: "{$amount_in_cents}",
        currency: "{$currency}",
        email: "{$email}"
    });
</script>