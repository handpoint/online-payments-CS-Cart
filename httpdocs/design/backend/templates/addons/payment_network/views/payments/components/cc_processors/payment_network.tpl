<div class="control-group">
    <label class="control-label cm-required" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="access_code">{__("preshared_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][passphrase]" id="access_code" value="{$processor_params.passphrase}"  size="60">
    </div>
</div>

<div class="control-group">
    <label for="elm_banner_type" class="control-label cm-required">{__("type")}</label>
    <div class="controls">
        <select name="payment_data[processor_params][integration_type]">
            <option {if $processor_params.integration_type == "hosted"}selected="selected"{/if} value="hosted">Hosted</option>
            <option {if $processor_params.integration_type == "hosted_v2"}selected="selected"{/if} value="hosted_v2">Hosted Modal</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][currencycode]" id="currency" value="{$processor_params.currencycode}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("country")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][countrycode]" id="vendor" value="{$processor_params.countrycode}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label for="elm_banner_type" class="control-label cm-required">Responsive:</label>
    <div class="controls">
        <select name="payment_data[processor_params][responsive]">
            <option {if $processor_params.responsive == "Y"}selected="selected"{/if} value="Y">Yes</option>
            <option {if $processor_params.responsive == "N"}selected="selected"{/if} value="N">No</option>
        </select>
    </div>
</div>