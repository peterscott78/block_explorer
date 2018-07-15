
<h3>Showing TxID: {$trans['txid']}</h3>

<hr />

<div class="row">
	<div class="col-md-4">

		<h3>Transaction Details</h3>

		<table class="table table-bordered table-striped table-hover">
		<tr>
			<td><b>Version:</b></td>
			<td>{$trans['version']}</td>
		</tr><tr>
			<td><b>Size:</b></td>
			<td>{$trans['size']}</td>
		</tr><tr>
			<td><b>Confirmations:</b></td>
			<td>{$trans['confirmations']}</td>
		</tr><tr>
			<td><b>Input Amount:</b></td>
			<td>{$trans['input_amount']}</td>
		</tr><tr>
			<td><b>Output Amount:</b></td>
			<td>{$trans['output_amount']}</td>
		</tr><tr>
			<td><b>Fee:</b></td>
			<td>{$trans['fee']}</td>
		</tr><tr>
			<td><b>Time Seen:</b></td>
			<td>{$trans['time']}</td>
		</tr></table><br />

	</div>

	<div class="col-md-8">
			<h3>Addresses</h3>

		<table class="table table-bordered table-striped table-hover">
		<thead><tr>
			<th>Inputs</th>
			<th>Outputs</th>
		</tr></thead>

		<tbody><tr>
			<td valign="top">{section name=inaddr loop=$inaddr}
				<a href="{$site_uri}/address/{$inaddr[inaddr].address}">{$inaddr[inaddr].address}</a> ({$inaddr[inaddr].amount} {$config['currency']})<br />
			{/section}</td>

			<td valign="top">{section name=outaddr loop=$outaddr}
				<a href="{$site_uri}/address/{$outaddr[outaddr].address}">{$outaddr[outaddr].address}</a> ({$outaddr[outaddr].amount} {$config['currency']})<br />
			{/section}</td>

		</tr></tbody></table><br />

	</div>
</div>

<hr />

<div class="row">
	<div class="col-md-12">
		<h3>Inputs</h3>

		<table class="table table-bordered table-striped table-hover">
		<tbody>

		{section name=in loop=$inputs}
		<tr>
			<td><b>TxID:</b></td>
			<td colspan="3"><a href="{$site_uri}/tx/{$inputs[in].txid}">{$inputs[in].txid}</a></td>
		</tr><tr>
			<td><b>Amount:</b></td>
			<td>{$inputs[in].amount} {$config['currency']}</td>
			<td><b>Vout:</b></td>
			<td>{$inputs[in].vout}</td>
		</tr><tr>
			<td><b>ScriptSig (hex):</b></td>
			<td colspan="3">{$inputs[in].hex}</td>
		</tr><tr>
			<td><b>ScriptSig (ASM):</b></td>
			<td colspan="3">{$inputs[in].asm}</td>
		</tr><tr>
			<td colspan="2"><hr /><br /></td>
		</tr>
		{/section}

		</tbody></table><br />

	</div>
</div><br />


<div class="row">
	<div class="col-md-12">

		<h3>Outputs</h3>

		<table class="table table-bordered table-striped table-hover">
		<tbody>

		{section name=out loop=$outputs}
		<tr>
			<td><b>Sent To:</b></td>
			<td><a href="{$site_uri}/address/{$outputs[out].address}">{$outputs[out].address}</a></td>
			<td><b>Amount:</b></td>
			<td>{$outputs[out].amount} {$config['currency']}</td>
		</tr><tr>
			<td valign="top"><b>Script (hex):</b></td>
			<td colspan="3" valign="top">{$outputs[out].hex}</td>
		</tr><tr>
			<td valign="top"><b>Script (ASM):</b></td>
			<td colspan="3" valign="top">{$outputs[out].asm}</td>
		</tr><tr>
			<td colspan="4"><br /><hr /></td>
		</tr>
		{/section}

		</tbody></table><br />

	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3>Hex Code</h3>

		<p>{$trans['hex']}</p>

	</div>
</div>




