<div class="paynetz-wrapper">

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset>
		<legend>{l s='Configure your existing Paynetz ATOM Accounts' mod='paynetz'}</legend>

		{* Determine which currencies are enabled on the store and supported by Authorize.net & list one credentials section per available currency *}
		{foreach from=$currencies item='currency'}
			{if (in_array($currency.iso_code, $available_currencies))}
				{assign var='configuration_api_merchant_url_name' value="PAYNETZ_API_MERCHANT_URL"}
 				{assign var='configuration_api_server_port' value="PAYNETZ_API_SERVER_PORT"}
				{assign var='configuration_api_login_id' value="PAYNETZ_API_LOGIN_ID"}
				{assign var='configuration_api_password' value="PAYNETZ_API_PASSWORD"}
				{assign var='configuration_api_product_id' value="PAYNETZ_API_PRODUCT_ID"}
				<table>
					<tr>
						<td>
							<p>{l s='Credentials for' mod='paynetz'}<b> {$currency.iso_code}</b> {l s='currency' mod='paynetz'}</p>
							<label for="paynetz_api_smerchant_url">{l s='API Merchant Url' mod='paynetz'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="paynetz_api_merchant_url" name="api_merchant_url" value="{${$configuration_api_merchant_url_name}}" /></div>
							<label for="paynetz_api_server_port">{l s='API Server Port' mod='paynetz'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="api_server_port" name="api_server_port" value="{${$configuration_api_server_port}}" /></div>
							<label for="paynetz_api_login_id">{l s='API Login Id' mod='paynetz'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="api_login_id" name="api_login_id" value="{${$configuration_api_login_id}}" /></div>
							<label for="paynetz_api_password">{l s='API Password' mod='paynetz'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="api_password" name="api_password" value="{${$configuration_api_password}}" /></div>
							<label for="paynetz_api_product_id">{l s='API Product ID' mod='paynetz'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="api_product_id" name="api_product_id" value="{${$configuration_api_product_id}}" /></div>
						</td>
					</tr>
				</table><br />
			{/if}
		{/foreach}

		<br />
		<center>
			<input type="submit" name="submitModule" value="{l s='Update settings' mod='paynetz'}" class="button" />
		</center>
		<sub>{l s='* Subject to region' mod='paynetz'}</sub>
	</fieldset>
</form>
</div>