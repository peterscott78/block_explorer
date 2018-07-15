
<h3>Showing Block: {$block['block_hash']}</h3>
<hr />

<div class="row">
	<div class="col-md-6">

		<h3>Block Details</h3>

		<table class="table table-bordered table-striped table-hover">
		<tr>
			<td><b>Height:</b></td>
			<td>{$block['id']}</td>
		</tr><tr>
			<td><b>Transactions:</b></td>
			<td>{$block['transactions']}</td>
		</tr><tr>
			<td><b>Total Sent:</b></td>
			<td>{$block['total_sent']} {$config['currency']}</td>
		</tr><tr>
			<td><b>Time Seen:</b></td>
			<td>{$block['date_added']}</td>
		</tr><tr>
			<td><b>Block Hash:</b></td>
			<td>{$block['block_hash']}</td>
		</tr></table><br />

	</div>

	<div class="col-md-6">&nbsp;</div>
</div><br />

<hr />

<div class="row">
	<div class="col-md-12">

		<h3>Transactions</h3>

		<table class="table table-bordered table-striped table-hover">
		<thead><tr>
			<th>#</th>
			<th>TxID</th>
		</tr></thead>

		<tbody>
		{section name=tx loop=$tx}
		<tr>
			<td align="center">{$tx[tx].num}</td>
			<td><a href="{$site_uri}/tx/{$tx[tx].txid}">{$tx[tx].txid}</a></td>
		</tr>
		{/section}

		</tbody></table><br />

	</div>
</div>





