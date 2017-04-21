<div class="payfortstart-wrapper">
    <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
        <fieldset>
            <legend>{l s='Configure your Payfort Start Payment Gateway' mod='startpayments'}</legend>
            {assign var='configuration_live_open_key' value="PAYFORT_START_LIVE_OPEN_KEY"}
            {assign var='configuration_live_secret_key' value="PAYFORT_START_LIVE_SECRET_KEY"}
            {assign var='configuration_test_open_key' value="PAYFORT_START_TEST_OPEN_KEY"}
            {assign var='configuration_test_secret_key' value="PAYFORT_START_TEST_SECRET_KEY"}
            <table>
                <tr>
                    <td>
                        <p>{l s='Credentials for' mod='startpayments'}</p>
                        <label for="startpayments_login_id">{l s='Live Open Key' mod='startpayments'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_START_LIVE_OPEN_KEY" name="PAYFORT_START_LIVE_OPEN_KEY" value="{$PAYFORT_START_LIVE_OPEN_KEY}" /></div>
                        <label for="startpayments_key">{l s='Live Secret Key' mod='startpayments'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_START_LIVE_SECRET_KEY" name="PAYFORT_START_LIVE_SECRET_KEY" value="{${$configuration_live_secret_key}}" /></div>
                        <label for="startpayments_login_id">{l s='Test Open Key' mod='startpayments'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_START_TEST_OPEN_KEY" name="PAYFORT_START_TEST_OPEN_KEY" value="{${$configuration_test_open_key}}" /></div>
                        <label for="startpayments_key">{l s='Test Secret Key' mod='startpayments'}:</label>
                        <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_START_TEST_SECRET_KEY" name="PAYFORT_START_TEST_SECRET_KEY" value="{${$configuration_test_secret_key}}" /></div>
                    </td>
                </tr>
            </table><br />
            <hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />

            <label for="payfort_start_mode"> {l s='Environment:' mod='startpayments'}</label>
            <div class="margin-form" id="payfortstart_mode">
                <input type="radio" name="payfort_start_mode" value="0" style="vertical-align: middle;" {if !$PAYFORT_START_TEST_MODE}checked="checked"{/if} />
                <span>{l s='Live mode' mod='payfortstart'}</span><br/>
                <input type="radio" name="payfort_start_mode" value="1" style="vertical-align: middle;" {if $PAYFORT_START_TEST_MODE}checked="checked"{/if} />
                <span>{l s='Test mode' mod='payfortstart'}</span><br/>
            </div>
            <label for="payfort_start_action">{l s='Payment Action:' mod='payfortstart'}</label>
            <div class="margin-form" id="payfortstart_mode">
                <input type="radio" name="payfort_start_action" value="0" style="vertical-align: middle;" {if !$PAYFORT_START_CAPTURE}checked="checked"{/if} />
                <span>{l s='Capture' mod='payfortstart'}</span><br/>
                <input type="radio" name="payfort_start_action" value="1" style="vertical-align: middle;" {if $PAYFORT_START_CAPTURE}checked="checked"{/if} />
                <span>{l s='Authorize Only' mod='payfortstart'}</span><br/>
            </div>
            <label for="payfort_start_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='payfortstart'}</label>
            <div class="margin-form">
                <select id="payfort_start_hold_review_os" name="PAYFORT_START_HOLD_REVIEW_OS">';
                    // Hold for Review order state selection
                    {foreach from=$order_states item='os'}
                        <option value="{$os.id_order_state|intval}" {if $os.id_order_state|intval eq $PAYFORT_START_HOLD_REVIEW_OS} selected {/if}>
                            {$os.name|stripslashes}
                        </option>
                    {/foreach}
                </select>
            </div>
            <br />
            <center>
                <input type="submit" name="submitModule" value="{l s='Update settings' mod='payfortstart'}" class="button" />
            </center>
            <sub>{l s='* Subject to region' mod='payfortstart'}</sub>
        </fieldset>
    </form>
</div>
