
<h3>Viewing Address: {$address}</h3>

<hr />


<div class="row">
	<div class="col-md-6">

		<table class="table table-bordered table-striped table-hover">
		<tr>
			<td><b>Transactions:</b></td>
		<td>{$transactions}</td>
		</tr><tr>
			<td><b>Input Amount:</b></td>
			<td>{$input_amount} {$config['currency']}</td>
		</tr><tr>
			<td><b>Output Amount:</b></td>
			<td>{$output_amount} {$config['currency']}</td>
		</tr><tr>
			<td><b>Final Balance:</b></td>
			<td>{$balance} {$config['currency']}</td>
		</tr></table><br />

	</div>

	<div class="col-md-6">&nbsp;</div>
</div><br />

<
<div class="row">
	<div class="col-md-12">

		<h3>Received</h3>

		<table class="table table-bordered table-striped table-hover">
		<tbody>

		{section name=in loop=$inputs}
		<tr>
			<td colspan="4"><b><a href="{$site_uri}/tx/{$inputs[in].txid}">{$inputs[in].txid}</a></b></td>
		</tr><tr>
			<td><b>Amount:</b></td>
			<td>{$inputs[in].amount} {$config['currency']}</td>
			<td><b>Time:</b></td>
			<td>{$inputs[in].time}</td>
		</tr><tr>
			<td colspan="2" valign="top">
				<b>Inputs:</b><br />
				{$inputs[in].inaddr}
			</td>

			<td colspan="2" valign="top">
				<b>Outputs:</b><br />
				{$inputs[in].outaddr}
			</td>
		</tr><tr>
			<td colspan="4"><br /><hr /></td>
		</tr>

		{/section}

		</tbody></table><br />

	</div>
</div><br />

<div class="row">
	<div class="col-md-12">

		<h3>Sent</h3>

		<table class="table table-bordered table-striped table-hover">
		<tbody>

		{section name=out loop=$outputs}
		<tr>
			<td colspan="4"><b><a href="{$site_uri}/tx/{$outputs[out].txid}">{$outputs[out].txid}</a></b></td>
		</tr><tr>
			<td><b>Amount:</b></td>
			<td>{$outputs[out].amount} {$config['currency']}</td>
			<td><b>Time:</b></td>
			<td>{$outputs[out].time}</td>
		</tr><tr>
			<td colspan="2" valign="top">
				<b>Inputs:</b><br />
				{$outputs[out].inaddr}
			</td>

			<td colspan="2" valign="top">
				<b>Outputs:</b><br />
				{$outputs[out].outaddr}
			</td>
		</tr><tr>
			<td colspan="4"><br /><hr /></td>
		</tr>

		{/section}

		</tbody></table><br />

	</div>
</div>





