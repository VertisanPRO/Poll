<style>
	.select-proc {
		background-color: rgba(74, 74, 74, 0.53);
		padding: 10px !important;

	}

	.field:hover {
		cursor: pointer;
		background: rgba(74, 74, 74, 0.53);
	}

	.field {
		transition: all 250ms ease-out;
		padding: 5px;
		border-radius: 3px;
	}

	.select-proc,
	.field:hover {
		color: white !important;
		transition: all 250ms ease-out;
	}
</style>

{foreach from=$POLL_DATA item=item key=key}
	<div class="card card-default" id="widget-onlineStaff">
		<div class="card-header h4">
			<a href="{$VIEW_URL}{$POLL_DATA[$key]['poll']['id']}">{$POLL_DATA[$key]['poll']['subject']}</a>
			{if isset($NO_LOGIN_TEXT)}
				<p>{$NO_LOGIN_TEXT}</p>
			{/if}
		</div>


		<form id="pollFrm" action="" method="post" name="pollFrm">
			<div class="card-body">


				{foreach from=$POLL_DATA[$key]['options'] item=opt}

					{$val = (int)($POLL_RESULT[$key][$opt['id']] / $TOTAL_VOTES[$key] * 100)}

					<div class="field" id="field{$opt['id']}" onclick="radioSelect({$opt['id']});">
						<label for="proccccess{$opt['id']}">{$opt['name']}</label>
						<div id="proccccess{$opt['id']}" class="progress">
							<div class="progress-bar" role="progressbar" style="width: {$val}%;" aria-valuenow="{$val}" aria-valuemin="0"
								aria-valuemax="{$TOTAL_VOTES[$key]}">
								{if empty($val)}0
								{else}{$val}
								{/if}%
							</div>
						</div>
					</div>

					<input class="radio-select" style="display: none;" id="radio{$opt['id']}" type="radio" value="{$opt['id']}"
						name="voteOpt">

					<input type="hidden" id="proc" value="{$opt['id']}">

				{/foreach}



			</div>
			<div class="card-footer">
				<input type="hidden" name="pollID" value="{$POLL_DATA[$key]['poll']['id']}">
				<input style="width: 100%;" type="submit" name="voteSubmit" class="btn btn-sm btn-success" value="{$VOTE_LABEL}">
			</div>
		</form>
	</div>

{/foreach}


<script>
	function radioSelect(id) {
		var radio = document.getElementById('radio' + id);
		var field = document.getElementById('field' + id);
		radio.checked = true;
		document.querySelectorAll('.field').forEach(n => n.classList.remove('select-proc'));
		field.classList.add('select-proc');
	}
</script>