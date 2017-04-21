{if $status == 'ok'}
	<p>{l s='Your order on' mod='payfortstart'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='payfortstart'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='payfortstart'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='payfortstart'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortstart'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='payfortstart'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortstart'}</a>.
	</p>
{/if}