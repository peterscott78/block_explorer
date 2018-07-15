<?php

class controller_block { 

public function __construct($parts = array()) { 

	// Initialize
	global $template, $config;

	// Perform checks
	$ok = true;
	if (!isset($parts[1])) { $ok = false; }
	elseif (preg_match("/[\W\s]/", $parts[1])) { $ok = false; }

	// Get from DB
	if ($ok === true) { 
		if (!$row = DB::queryFirstRow("SELECT * FROM blocks WHERE id = %s OR block_hash = %s", $parts[1], $parts[1])) { $ok = false; }
	}

	// Get tx, if needed
	if ($ok === true) { 

		// Load library
		include_once(SITE_PATH . '/data/lib/jsonRPCClient.php');

		// Init RPC client
		$rpc_url = 'http://' . RPC_USER . ':' . RPC_PASS . '@' . RPC_HOST . ':' . RPC_PORT;
		$client = new jsonRPCClient($rpc_url);

		// Get block
		try {
			$block = $client->getblock($row['block_hash'], 1);
		} catch (Exception $e) { $ok = false; }
	}

	// Display error, if needed
	if ($ok === false) { 
		$template = new template('index');
		$template->add_message("Either an invalid or non-existent block number / hash specified.", 'info');
		echo $template->parse(); exit(0);
	}

	// Set variables
	$row['total_sent'] = preg_replace("/\.$/", "", preg_replace("/0+$/", "", $row['total_sent']));
	$row['date_added'] = date('Y-m-d H:i:s', $row['date_added']);

	// Go through txs
	$num = 1; $tx = array();
	foreach ($block['tx'] as $txid) { 
		$vars = array(
			'num' => $num, 
			'txid' => $txid
		);
		array_push($tx, $vars);
	$num++; }

	// Parse template
	$template = new template('block');
	$template->assign('block', $row);
	$template->assign('tx', $tx);
	echo $template->parse(); exit(0); 

}

}

?>
