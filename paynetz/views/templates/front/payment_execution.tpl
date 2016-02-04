{capture name=path}{l s='Paynetz payment.' mod='paynetz'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='paynetz'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='paynetz'}</p>
{else}

<h3>{l s='Paynetz payment.' mod='paynetz'}</h3>
<form action="/modules/paynetz/validation.php" method="post">
<input type="hidden" name="x_invoice_num" value="{$x_invoice_num|escape:'htmlall':'UTF-8'}" />
<p>
	<img src="{$this_path_bw}paynetz.jpg" alt="{l s='Paynetz' mod='paynetz'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Paynetz.' mod='paynetz'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='paynetz'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='paynetz'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='paynetz'}
    {/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We allow several currencies to be sent via Paynetz.' mod='paynetz'}
		<br /><br />
		{l s='Choose one of the following:' mod='paynetz'}
		<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
	{else}
		{l s='We allow the following currency to be sent via Paynetz:' mod='paynetz'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	{/if}
</p>
<p>
	{l s='Paynetz account information will be displayed on the next page.' mod='paynetz'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "I confirm my order."' mod='paynetz'}.</b>
</p>
<p class="cart_navigation" id="cart_navigation">
	<input type="submit" value="{l s='I confirm my order' mod='paynetz'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='paynetz'}</a>
</p>
</form>
{/if}
