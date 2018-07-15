<?php

class controller_tx { 

public function __construct($parts = array()) { 

	// Initialize
	global $template, $config;

	// Perform checks
	$ok = true;
	if (!isset($parts[1])) { $ok = false; }
	if (preg_match("/\W/", $parts[1]) || strlen($parts[1]) > 100) { $ok = false; }

	// Get tx, if needed
	if ($ok === true) { 

		// Load library
		include_once(SITE_PATH . '/data/lib/jsonRPCClient.php');

		// Init RPC client
		$rpc_url = 'http://' . RPC_USER . ':' . RPC_PASS . '@' . RPC_HOST . ':' . RPC_PORT;
		$client = new jsonRPCClient($rpc_url);

		// Get transaction
		try {
			$trans = $client->getrawtransaction($parts[1], 1);
		} catch (Exception $e) { $ok = false; }
	}

	// Get tx from db
	if ($ok === true) { 
		if (!$txrow = DB::queryFirstRow("SELECT * FROM tx WHERE txid = %s", $parts[1])) { $ok = false; }
		else { $dbid = $txrow['id']; }
	}

	// Display error, if needed
	if ($ok === false) { 
		$template = new template('index');
		$template->add_message("Either an invalid a non-existent txid specified.", 'info');
		echo $template->parse(); exit(0);
	}

	// Format variables
	$trans['version'] = sprintf("%.1f", $trans['version']);
	$trans['size'] = number_format($trans['size']) . ' bytes';
	$trans['time'] =date('Y-m-d H:i:s', $txrow['date_added']);
	$trans['confirmations'] = $txrow['blocknum'] == 0 || $txrow['blocknum'] == $config['blocknum'] ? 'Unconfirmed' : ($config['blocknum'] - $txrow['blocknum']) . ' Confirmations';
	$trans['hex'] = implode("<br />", str_split($trans['hex'], 100));

	// Get input and output amounts
	$trans['output_amount'] = DB::queryFirstField("SELECT sum(amount) FROM tx_outputs WHERE txid = %d", $dbid);
	$trans['input_amount'] = DB::queryFirstField("SELECT sum(amount) FROM tx_outputs WHERE input_txid = %d", $dbid);
	$trans['fee'] = preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format(($trans['input_amount'] - $trans['output_amount']), 8))) . ' ' . $config['currency'];
	$trans['output_amount'] = preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format($trans['output_amount'], 8))) . ' ' . $config['currency'];
	$trans['input_amount'] = preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format($trans['input_amount'], 8))) . ' ' . $config['currency'];

	// Input addresses
	$inaddr = array();
	$rows = DB::query("SELECT addresses.address AS address, tx_outputs.amount AS amount FROM tx_outputs, addresses WHERE tx_outputs.input_txid = %d AND addresses.id = tx_outputs.address_id ORDER BY vout", $dbid);
	foreach ($rows as $row) { 
		$vars = array(
			'address' => $row['address'], 
			'amount' => preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format($row['amount'], 8)))
		);
		array_push($inaddr, $vars);
	}

	// Output addresses
	$outaddr = array();
	$rows = DB::query("SELECT addresses.address AS address, tx_outputs.amount AS amount FROM tx_outputs, addresses WHERE tx_outputs.txid = %d AND addresses.id = tx_outputs.address_id ORDER BY vout", $dbid);
	foreach ($rows as $row) { 
		$vars = array(
			'address' => $row['address'], 
			'amount' => preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format($row['amount'], 8)))
		);
		array_push($outaddr, $vars);
	}

	// Go through inputs
	$inputs = array();
	if (!is_array($trans['vin'])) { $trans['vin'] = array(); }
	foreach ($trans['vin'] as $input) { 
		if (!isset($input['txid'])) { $input['txid'] = 'N/A'; }
		if (!isset($input['vout'])) { $input['vout'] = 'N/A'; }
		if (!is_array($input['scriptSig'])) { $input['scriptSig'] = array(); }

		// Get amount
		$amount = DB::queryFirstField("SELECT tx_outputs.amount FROM tx, tx_outputs WHERE tx.txid = %s AND tx.id = tx_outputs.txid AND tx_outputs.vout = %d", $input['txid'], $input['vout']);
		if ($amount == '') { $amount = 0; }

		// Set hex / ASM
		$hex = isset($input['scriptSig']['hex']) ? implode("<br />", str_split($input['scriptSig']['hex'], 60)) : 'N/A';
		$asm = isset($input['scriptSig']['asm']) ? implode("<br />", str_split($input['scriptSig']['asm'], 60)) : 'N/A';

// Set vars
		$vars = array(
			'txid' => $input['txid'], 
			'vout' => $input['vout'], 
			'amount' => preg_replace("/\.$/", "", preg_replace("/0+$/", "", number_format($amount, 8))), 
			'hex' => $hex, 
			'asm' => $asm
		);
		array_push($inputs, $vars);
	}

	// Go through outputs
	$outputs = array();
	if (!is_array($trans['vout'])) { $trans['vout'] = array(); }
	foreach ($trans['vout'] as $out) { 
		if (!isset($out['scriptPubKey'])) { continue; }

		// Set vars
		$vars = array(
			'address' => $out['scriptPubKey']['addresses'][0], 
			'amount' => $out['value'],
			'hex' => implode("<br />", str_split($out['scriptPubKey']['hex'], 80)), 
			'asm' => implode("<br />", str_split($out['scriptPubKey']['asm'], 80))
		);
		array_push($outputs, $vars);
	}




	// Parse template
	$template = new template('tx');
	$template->assign('trans', $trans);
	$template->assign('inaddr', $inaddr);
	$template->assign('outaddr', $outaddr);
	$template->assign('inputs', $inputs);
	$template->assign('outputs', $outputs);
	echo $template->parse();

	// Exit
	exit(0);
	

}

}

?>
