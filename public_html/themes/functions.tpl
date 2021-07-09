{strip}
	{function name=kwork_price kwork=0 actor=0 filterPrice=false}
		{assign var=price value=$kwork.price}
		{$price|zero}<span class="rouble">Р</span>
	{/function}
{/strip}
