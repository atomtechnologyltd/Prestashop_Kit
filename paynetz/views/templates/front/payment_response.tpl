{if $status == 'Ok'}
	<p>{l s='Your order on' mod='paynetz'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='paynetz'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='paynetz'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='paynetz'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='authorizeaim'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='paynetz'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='paynetz'}</a>.
	</p>
{/if}
