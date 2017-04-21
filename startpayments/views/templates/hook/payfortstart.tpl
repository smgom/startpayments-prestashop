<p class="payment_module">
    <script> var error = "{if isset($smarty.get.starterror)}{$smarty.get.message}{/if}";
        if (error != "") {
            alert(error);
        }
    </script>
    <a href="{$link->getModuleLink('startpayments', 'payment', [], true)|escape:'html'}" title="{l s='Pay With Debit/Credit Card' mod='startpayments'}">
        <img src="{$this_path_start_payments}img/cc.png" alt="{l s='Pay With Debit/Credit Card' mod='startpayments'}" />
        {l s='Pay by card' mod='startpayments'} {l s='(instant order processing)' mod='startpayments'}
    </a>
</p>