<?php

class controller_address { 

public function __construct($parts = array()) { 

	// Initialize
	global $template, $config;

	// Perform checks
	$ok = true;
	if (!isset($parts[1])) { $ok = false; }
	elseif (preg_match("/[\s\W]/", $parts[1])) { $ok = false; }
	elseif (strlen($parts[1]) > 40) { $ok = false; }

	// Validate address
	$bip32 = new BIP32();
		//if (!$bip32->validate_address($parts[1])) { $ok = false; }

	// Get address
	if (!$address_id = DB::queryFirstField("SELECT id FROM addresses WHERE address = %s", $parts[1])) { $ok = false; }

	// Check for errors
	if ($ok === false) { 
		$template = new template('index');
		$template->add_message("Invalid or non-existent address specified.", 'info');
		echo $template->parse(); exit(0);
	}

	// Set variables
	$transactions = 0;
	$input_amount = 0.00;
	$output_amount = 0.00;

	// Go through inputs
	$inputs = array(); $input_txids = array();
	$rows = DB::query("SELECT tx.id AS id, tx.txid AS txid, tx.date_added AS date_added, tx_outputs.amount AS amount FROM tx, tx_outputs WHERE tx_outputs.address_id = %d AND tx.id = tx_outputs.txid ORDER BY tx_outputs.id DESC", $address_id);
	foreach ($rows as $row) {

		// Add to amounts
		$transactions++;
		$input_amount += $row['amount']; 
		$input_txids[$row['id']] = $row['amount'];

		// Go through secondary txs
		$inaddr = ''; $outaddr = '';
		$trows = DB::query("SELECT tx_outputs.txid AS txid, tx_outputs.input_txid AS input_txid, tx_outputs.amount AS amount, addresses.address AS address FROM tx_outputs, addresses WHERE (tx_outputs.txid = %d OR tx_outputs.input_txid = %d) AND addresses.id = tx_outputs.address_id ORDER BY tx_outputs.vout", $row['id'], $row['id']);
		foreach ($trows as $trow) { 

			// Get HTML
			$html = "<a href=\"/address/$trow[address]\">$trow[address]</a> (" . preg_replace("/\.$/", "", preg_replace("/0+$/", "", $trow['amount'])) . ' ' . $config['currency'] . ')<br />';

			// Add to array
			if ($trow['txid'] == $row['id']) { $outaddr .= $html; }
			else { $inaddr .= $html; }
		}

		// Set vars
		$vars = array(
			'txid' => $row['txid'], 
			'amount' => preg_replace("/\./", "", preg_replace("/0+$/", "", $row['amount'])), 
			'time' => date('Y-m-d H:i:s', $row['date_added']), 
			'inaddr' => $inaddr, 
			'outaddr' => $outaddr
		);
		array_push($inputs, $vars);
	}

	// Go through outputs
	$outputs = array();
	if (count($input_txids) == 0) { $input_txids[0] = 0; }
	$rows = DB::query("SELECT id, txid, date_added FROM tx WHERE id IN (" . implode(",", array_keys($input_txids)) . ") ORDER BY date_added DESC");
	foreach ($rows as $row) { 

		// Update amounts
		$transactions++;
		$output_amount += $input_ids[$row['id']];

		// Go through outputs
		$inaddr = ''; $outaddr = '';
		$trows = DB::query("SELECT tx_outputs.amount AS amount, addresses.address AS address FROM tx_outputs, addresses WHERE (tx_outputs.txid = %d OR tx_outputs.input_txid = %d) AND tx_outputs.address_id = addresses.id ORDER BY tx_outputs.vout", $$row['id'], $row['id']);
		foreach ($trows as $trow) { 

			// Get HTML
			$html = "<a href=\"/address/$trow[address]\">$trow[address]</a> (" . preg_replace("/\.$/", "", preg_replace("/0+$/", "", $trow['amount'])) . ' ' . $config['currency'] . ')<br />';

			// Add to results
			if ($trow['txid'] == $row['id']) { $outaddr .= $html; }
			else { $inaddr .= $html; }
		}

		// Set vars
		$vars = array(
			'txid' => $row['txid'], 
			'amount' => preg_replace("/\./", "", preg_replace("/0+$/", "", $input_txids[$row['id']])), 
			'time' => date('Y-m-d H:i:s', $row['date_added']), 
			'inaddr' => $inaddr, 
			'outaddr' => $outaddr
		);
		array_push($outputs, $vars);
	}


	// Parse template
	$template = new template('address');
	$template->assign('transactions', $transactions);
	$template->assign('balance', preg_replace("/\.$/", "", preg_replace("/0+$/", "", ($input_amount - $output_amount))));
	$template->assign('input_amount', preg_replace("/\.$/", "", preg_replace("/0+$/", "", $input_amount)));
	if ($output_amount == 0.00) { $template->assign('output_amount', 0); }
	else { $template->assign('output_amount', preg_replace("/\.$/", "", preg_replace("/0+$/", "", $output_amount))); }
	$template->assign('inputs', $inputs);
	$template->assign('outputs', $outputs);
	echo $template->parse(); exit(0);

}

}

?>
