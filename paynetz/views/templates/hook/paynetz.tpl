<p class="payment_module" >	{if $isFailed == 1}		
<p style="color: red;">			
	{if !empty($smarty.get.message)}
		{l s='Error detail from Paynetz : ' mod='paynetz'}
		{$smarty.get.message|htmlentities}
	{else}	
		{l s='Error, please verify the payment information' mod='paynetz'}
	{/if}
</p>
{/if}
<div class="row">
	<div class="col-xs-12 col-md-6">
	<p class="payment_module">
	<a href="{$link->getModuleLink('paynetz', 'payment')|escape:'html'}" title="{l s='Paynetz' mod='paynetz'}">
		{l s='Paynetz' mod='paynetz'}&nbsp;<span>{l mod='paynetz'}</span>
	</a>
</p>
</div>
</div>