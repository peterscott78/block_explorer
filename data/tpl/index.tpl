
<div class="row">
	<div class="col-md-6">
		<h3>Latest Blocks</h3>

		<table class="table table-bordered table-striped table-hover">
		<thead><tr>
			<th>Height</th>
			<th>Age</th>
			<th>Transactions</th>
			<th>Total Sent</th>
			<th>Size (kb)</th>
			<th>Weight</th>
		</tr></thead>

		<tbody>
		{section name=block loop=$blocks}
		<tr>
			<td><a href="{$site_uri}/block/{$blocks[block].height}">{$blocks[block].height}</a></td>
			<td>{$blocks[block].age}</td>
			<td>{$blocks[block].transactions}</td>
			<td>{$blocks[block].total_sent}</td>
			<td>{$blocks[block].size}</td>
			<td>{$blocks[block].weight}</td>
		</tr>
		{/section}
		</tbody></table><br />

	</div>

	<div class="col-md-6">
		<h3>Network</h3>

		<table class="table table-bordered table-striped table-hover">
		<tr>
			<td><b>Currency:</b></td>
			<td>Bitcoin (BTC)</td>
		</tr><tr>
			<td><b>Current Rate:</b></td>
			<td>{$current_rate}</td>
		</tr><tr>
			<td><b>24 Hour Volume:</b></td>
			<td>{$lastday_volume}</td>
		</tr><tr>
			<td><b>Market Cap:</b></td>
			<td>{$market_cap}</td>
		</tr><tr>
			<td><b>Total Supply:</b></td>
			<td>{$total_supply}</td>
		</tr><tr>
			<td><b>24 Hour Change (%):</b></td>
			<td>{$percent_change_24h}</td>
		</tr><tr>
			<td><b>7 Day Change (%):</b></td>
			<td>{$percent_change_7d}</td>
		</tr></table><br />

	</div>

</div>

<div class="row">
	<div class="col-md-12">

		<h3>Latest Transactions</h3>

		<table class="table table-bordered table-striped table-hover">
		<thead><tr>
			<th>TxID</th>
			<th>Age</th>
			<th>Amount Sent</th>
			<th>Confirmations</th>
		</tr></thead>

		<tbody>
		{section name=tx loop=$tx}
		<tr>
			<td><a href="{$site_uri}/tx/{$tx[tx].txid}">{$tx[tx].txid}</a></td>
			<td>{$tx[tx].age}</td>
			<td>{$tx[tx].amount_sent}</td>
			<td>{$tx[tx].confirmations}</td>
		</tr>
		{/section}
		</tbody></table><br />

	</div>
</div>


