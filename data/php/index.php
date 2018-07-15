<?php

// Initialize
global $template, $config;

// Set variables
$blocks = array();
$start = 0;

// Go through blocks
$rows = DB::query("SELECT * FROM home_blocks ORDER BY id DESC ");
foreach ($rows as $row) { 

	$vars = array(
		'height' => str_pad($row['id'], 6, '0', STR_PAD_LEFT), 
		'age' => get_time_since($row['date_added']), 
		'transactions' => $row['transactions'], 
		'total_sent' => preg_replace("/0+$/", "", number_format($row['total_sent'], 8)), 
		'size' => number_format(($row['size'] / 1000), 2), 
		'weight' => $row['weight']
	);
	$vars['total_sent'] = preg_replace("/\.$/", "", $vars['total_sent']);
	array_push($blocks, $vars);
}

// Recent transactions
$tx = array();
$rows = DB::query("SELECT * FROM home_tx ORDER BY id DESC");
foreach ($rows as $row) { 
	$confirmations = $config['blocknum'] == $row['blocknum'] || $row['blocknum'] == 0 ? 'Unconfirmed' : ($config['blocknum'] - $row['blocknum']);

	// Set vars
	$vars = array(
		'txid' => $row['txid'],
		'age' => get_time_since($row['date_added']), 
		'amount_sent' => preg_replace("/0+$/", "", $row['amount_sent']), 
		'confirmations' => $confirmations
	);
	array_push($tx, $vars);

}
// Template variables
$template->assign('blocks', $blocks);
$template->assign('tx', $tx);
$template->assign('current_rate', '$' . number_format($config['current_rate'], 2));
$template->assign('lastday_volume', '$' . number_format($config['24hour_volume'], 2));
$template->assign('market_cap', '$' . number_format($config['market_cap'], 2));
$template->assign('total_supply', number_format($config['total_supply']));
$template->assign('percent_change_24h', sprintf("%.2f", $config['percent_change_24h']) . '%');
$template->assign('percent_change_7d', sprintf("%.2f", $config['percent_change_7d']) . '%');



?>
