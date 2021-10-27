{include file='header.tpl'}
{include file='navbar.tpl'}

<div
	class="ui {if count($WIDGETS_LEFT) && count($WIDGETS_RIGHT) }four wide tablet eight wide computer{elseif count($WIDGETS_LEFT) || count($WIDGETS_RIGHT)}ten wide tablet twelve wide computer{else}sixteen wide{/if} column">
</div>


<h2 class="">{$VIEW_POLL->subject}</h2>

<ul class="nav nav-pills nav-fill">
	{foreach from=$VIEW_POLL_OPTIONS key=key item=opt}
		<li class="nav-item {if $key == 0}active{/if}">
			<a class="nav-link" data-toggle="tab" href="#opt{$opt->id}">{$opt->name}</a>
		</li>
	{/foreach}
</ul>
<div class="tab-content" style="padding: 10px;">
	{foreach from=$VIEW_POLL_OPTIONS key=key item=opt}
		<div class="tab-pane {if $key == 0}active{/if}" id="opt{$opt->id}">
			{foreach from=$VIEW_POLL_VOTES[$opt->id] item=user}
				<span style="padding: 10px;"> {$user}, </span>

			{/foreach}
		</div>
	{/foreach}
</div>








{include file='footer.tpl'}