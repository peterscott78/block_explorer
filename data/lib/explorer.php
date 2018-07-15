<?php

class Explorer { 

////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////

public function __construct() {
	$this->address_version = '00';
	$this->op_code = array(	
		'00' => 'OP_FALSE', 	'61' => 'OP_NOP',			'6a' => 'OP_RETURN',
		'76' => 'OP_DUP',		'87' => 'OP_EQUAL',		'88' => 'OP_EQUALVERIFY',
		'51' => 'OP_TRUE',		'a6' => 'OP_RIPEMD160',		'a7' => 'OP_SHA1',
		'a8' => 'OP_SHA256',	'a9' => 'OP_HASH160',		'aa' => 'OP_HASH256',
		'ac' => 'OP_CHECKSIG',	'ae' => 'OP_CHECKMULTISIG'
	);

 

// Initialize
	global $config;
	include_once(SITE_PATH . '/data/lib/jsonRPCClient.php');

	// Init RPC client
	$rpc_url = 'http://' . RPC_USER . ':' . RPC_PASS . '@' . RPC_HOST . ':' . RPC_PORT;
	$this->client = new jsonRPCClient($rpc_url);

}

////////////////////////////////////////////////////////////
// Check transaction
////////////////////////////////////////////////////////////

public function check_transaction($trans, $blocknum = 0) { 
	if (!isset($trans['txid'])) { return; }
	if ($trans['txid'] == '') { return; }
	if (!isset($trans['hash'])) { $trans['hash'] = ''; }

	// Set variables
	$txid = $trans['txid'];
	$size = strlen($trans['hex']) / 2;

	// Check if already exists
	if ($row = DB::queryFirstRow("SELECT id,blocknum FROM tx WHERE txid = %s", $txid)) { 
		if ($row['blocknum'] == 0 && $blocknum > 0) {
			DB::query("UPDATE tx SET blocknum = $blocknum WHERE txid = %s", $txid);
		}
		return;
	} 

	// Initial checks
	if (!isset($trans['vout'])) { return false; }
	if (!is_array($trans['vout'])) { return false; }

	// Get blocknum
	if (isset($trans['blockhash']) && $trans['blockhash'] != '' && $blocknum == 0) { 
		$blocknum = DB::queryFirstField("SELECT id FROM blocks WHERE block_hash = %s", $trans['blockhash']);
		if ($blocknum == '') { $blocknum = 0; }
	}

	// Add tx to database
	if (!isset($trans['time'])) { $trans['time'] = time(); }
	DB::insert('tx', array(
		'blocknum' => $blocknum, 
		'version' => $trans['version'], 
		'size' => $size, 
		'locktime' => $trans['locktime'], 
		'txid' => $txid, 
		'txhash' => $trans['hash'], 
		'date_added' => $trans['time'])
	);
	$dbid = DB::insertId();

	// Go through inputs
	foreach ($trans['vin'] as $input) {
		if (!isset($input['txid'])) { continue; }
		if (!isset($input['vout'])) { $input['vout'] = 0; }

		// Get output row
		if (!$orow = DB::queryFirstRow("SELECT tx_outputs.id AS id, tx_outputs.amount AS amount, tx_outputs.address_id AS address_id FROM tx, tx_outputs WHERE tx.txid = %s AND tx_outputs.vout = %d AND tx.id = tx_outputs.txid LIMIT 0,1", $input['txid'], $input['vout'])) { 
			continue;
		}

		// Update database
		DB::update('tx_outputs', array('input_txid' => $dbid, 'is_spent' => 1), "id = %d", $orow['id']);

	}

	// Go through outputs
	foreach ($trans['vout'] as $output) { 
		if (!isset($output['scriptPubKey'])) { continue; }
		if (!isset($output['scriptPubKey']['addresses'])) { continue; }
		if (!isset($output['scriptPubKey']['addresses'][0])) { continue; }

		// Get address
		$address = $output['scriptPubKey']['addresses'][0];
		if (!$address_id = DB::queryFirstField("SELECT id FROM addresses WHERE address = %s", $address)) { 
			DB::insert('addresses', array(
				'address' => $address) 
			);
			$address_id = DB::insertId();
		}

		// Add to database
		DB::insert('tx_outputs', array(
			'txid' => $dbid, 
			'vout' => $output['n'], 
			'amount' => $output['value'], 
			'address_id' => $address_id)
		);
	}
}


////////////////////////////////////////////////////////////
// Check mempool
////////////////////////////////////////////////////////////

public function check_mempool() { 

	// Delete 3+ hours old txs
	DB::query("DELETE FROM mempool WHERE date_added < date_sub(now(), interval 3 hour)");

	// Get mempool
	$current_mempool = DB::queryFirstColumn("SELECT txid FROM mempool");

	// Get mempool
	$txids = $this->client->getrawmempool();
	foreach ($txids as $txid) {
		if (in_array($txid, $current_mempool)) { continue; } 

		$hexcode = $this->client->getrawtransaction($txid);
		$payload = hex2bin(trim($hexcode));
		list($trans, $x) = $this->_decode_transaction($payload);

		$this->check_transaction($trans);
		DB::insert('mempool', array('txid' => $txid));
	}

}



////////////////////////////////////////////////////////////
// Check blocks
////////////////////////////////////////////////////////////

public function check_block() { 

	// Initialize
	global $config;
	if (!isset($config['blocknum'])) { return; }

	// Get current block num
	try {
		$blocknum = $this->client->getblockcount();
	} catch (Exception $e) { return false; }

	// Process blocks
	while ($blocknum > $config['blocknum']) { 
		$block_hash = $this->client->getblockhash((int) $config['blocknum']);
		$this->process_block($block_hash, $config['blocknum']);
		
		$config['blocknum']++;
		update_config_var('blocknum', $config['blocknum']);
	}


}

////////////////////////////////////////////////////////////
// Process block
////////////////////////////////////////////////////////////

public function process_block($block_hash, $blocknum) { 


	// Check if block exists
	$exists = DB::queryFirstField("SELECT count(*) FROM blocks WHERE block_hash = %s", $block_hash);
	//if ($exists > 0) { return; }

	// Get block
	try {
		$hexcode = $this->client->getblock($block_hash, 0);
		$vars = $this->client->getblock($block_hash);
	} catch (Exception $e) { echo "NO BLOCK\n"; return false; }

	// Change to binary
	$payload = hex2bin(trim($hexcode));

	// Decode block header
	$x=0;
	$version = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	$prev_hash = bin2hex(substr($payload, $x, 32)); $x += 32;
	$merkel_root = bin2hex(substr($payload, $x, 32)); $x += 32;
	$time = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	$nbits = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	$nonce = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	list($length, $txcount) = $this->get_varint($payload, $x); $x += $length;

	// Go through txs
	for ($c=0; $c < $txcount; $c++) { 
		list($x, $tx) = $this->_decode_transaction($payload, $x);
		$this->check_transaction($tx, $blocknum);
	}

	// Get # of transactions
	$num = DB::queryFirstField("SELECT count(*) FROM tx WHERE blocknum = %d", $blocknum);
	if ($num == '') { $num = 0; }

	// Get total sent
	$total_sent = DB::queryFirstField("SELECT sum(tx_outputs.amount) FROM tx,tx_outputs WHERE tx.blocknum = $blocknum AND tx.id = tx_outputs.txid");
	if ($total_sent == '') { $total_sent = 0; }

	// Add block to db
	DB::insert('blocks', array(
		'id' => $blocknum,
		'weight' => $vars['weight'], 
		'size' => $vars['size'],  
		'transactions' => $num, 
		'total_sent' => $total_sent, 
		'block_hash' => $block_hash, 
		'date_added' => $vars['time'])
	);

}


////////////////////////////////////////////////////////////
// Decode transaction
////////////////////////////////////////////////////////////

public function _decode_transaction($payload, $start = 0) { 

	// Initialize
	$tx = array(
		'hex' => '', 
		'txid' => '', 
		'version' => '', 
		'locktime' => '', 
		'vin' => array(), 
		'vout' => array()
	);
	$x = $start;
	$start_x = $x;

	// Start transaction			
	$tx['version'] = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	list($length, $txin_count) = $this->get_varint($payload, $x); $x += $length;
			
	// Get inputs
	for ($i = 0; $i < $txin_count; $i++) { 			
		$input = array();
		$input['txid'] = bin2hex(strrev(substr($payload, $x, 32))); $x += 32;
		$input['vout'] = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
		list($slength, $sig_length) = $this->get_varint($payload, $x); $x += $slength;
		$signature = bin2hex(substr($payload, $x, $sig_length)); $x += $sig_length;
		
		if ($input['txid'] == '0000000000000000000000000000000000000000000000000000000000000000') { 
			$input = array('coinbase' => $signature);
		
		} else { 
			$input['scriptSig'] = array(
				'asm' => $this->_decode_script($signature), 
				'hex' => $signature
			);
		}
		$input['sequence'] = unpack('v', substr($payload, $x, 4))[1]; $x += 4;
		array_push($tx['vin'], $input);
	}
			
	// Get ouput count
	list($length, $txout_count) = $this->get_varint($payload, $x); $x += $length;
			
	// Go through outputs
	for ($i = 0; $i < $txout_count; $i++) { 
		$output = array();
		$output['value'] = number_format(base_convert(bin2hex(strrev(substr($payload, $x, 8))), 16, 10) / 1e8, 8); $x += 8;
		$output['n'] = $i;
				
		// Get pk script
		list($length, $pk_length) = $this->get_varint($payload, $x); $x += $length;
		$pk_script = bin2hex(substr($payload, $x, $pk_length)); $x += $pk_length;

		// Begin building scriptPubKey
		$scriptPubKey = array(
			'asm' => $this->_decode_scriptPubKey($pk_script),
			'hex' => $pk_script
		);

		// Try to decode the scriptPubKey['asm'] to learn the transaction type.
		$txn_info = $this->_get_transaction_type($scriptPubKey['asm']);
		if ($txn_info !== false) { 
			$scriptPubKey = array_merge($scriptPubKey, $txn_info);
		} else {  
			$scriptPubKey['message'] = 'unable to decode tx type!';
		}
		$output['scriptPubKey'] = $scriptPubKey;
		array_push($tx['vout'], $output);
	}
			
	// Finish
	$tx['locktime'] = unpack('l', substr($payload, $x, 4))[1]; $x += 4;
	$tx['txid'] = bin2hex(strrev(hash('sha256', hash('sha256', substr($payload, $start_x, ($x - $start_x)), true), true)));
	$tx['hex'] = bin2hex(substr($payload, $start_x, ($x - $start_x)));
	
	// Return
	return array($x, $tx);

}


////////////////////////////////////////////////////////////
// Get var int
////////////////////////////////////////////////////////////

function get_varint($payload, $start = 0) { 

	// Check var int
	$x=1;
	$varint = strtolower(bin2hex(substr($payload, $start, 1)));
	if ($varint == 'fd') { 
		$num = unpack('v', substr($payload, ($start + 1), 2))[1];
		$x += 2;
	} elseif ($varint == 'fe') { 
		$num = unpack('v', substr($payload, ($start + 1), 4))[1];
		$x += 4;
	} elseif ($varint == 'ff') { 
		$num = unpack('V', substr($payload, ($start + 1), 8))[1];
		$x += 8;
	} else { 
		$num = unpack('C', hex2bin($varint))[1];
	}

	// Return
	return array($x, $num);

}
////////////////////////////////////////////////////////////
// Decode script
////////////////////////////////////////////////////////////

public function _decode_script($script) { 

	// Decode
	$pos = 0;
	$data = array();
	while( $pos < strlen($script) ) {
		$code = hexdec(substr($script, $pos, 2));
		$pos += 2;

		// OP_FALSE
		if($code < 1) {
			$push = '0';

		// PUSHDATA
		} else if($code <= 75) {
			$push = substr($script, $pos, ($code*2));
			$pos += $code*2;
	
		// Bytes to PUSH
		} else if($code <= 78) {
			$szsz = 2^($code-76);
			$sz = hexdec( substr($script, $pos, ($szsz*2)));
			$pos += $szsz;
			$push = substr($script, $pos, ($pos+$sz*2));
			$pos += $sz*2;

		// OP_x, where x = $code-80
		} else if($code <= 96) {
			$push = ($code-80);
		
		} else {
			$push = $code;
		}
		$data[] = $push;
	}
	
	// Return
	return implode(" ",$data);

}

////////////////////////////////////////////////////////////
// Get transaction type
////////////////////////////////////////////////////////////

public function _get_transaction_type($data) { 

	// Initialize
	$data = explode(" ", $data);
	$define = array();
	$rule = array();
		
	// Standard: pay to pubkey hash
	$define['p2ph'] = array(
		'type' => 'pubkeyhash',
		'reqSigs' => 1,
		'data_index_for_hash' => 2
	);
	$rule['p2ph'] = array(
		'0' => '/^OP_DUP/',
		'1' => '/^OP_HASH160/',
		'2' => '/^[0-9a-f]{40}$/i', // 2
		'3' => '/^OP_EQUALVERIFY/',
		'4' => '/^OP_CHECKSIG/'
	);

	// Pay to script hash
	$define['p2sh'] = array(
		'type' => 'scripthash',
		'reqSigs' => 1,
		'data_index_for_hash' => 1
	);
	$rule['p2sh'] = array(
		'0' => '/^OP_HASH160/',
		'1' => '/^[0-9a-f]{40}$/i', // pos 1
		'2' => '/^OP_EQUAL/'
	);
		
	// Work out how many rules are applied in each case
	$valid = array();
	foreach ($rule as $tx_type => $def) {
		$valid[$tx_type] = count($def);
	}
		
	// Attempt to validate against each of these rules. 
	foreach ($data as $index => $test) {
		foreach ($rule as $tx_type => $def) {
			$matches[$tx_type] = array();
			if (isset($def[$index])) {
				preg_match($def[$index], $test, $matches[$tx_type]);
				if (count($matches[$tx_type]) == 1) {
					$valid[$tx_type]--;
					break;
				}
			}
		}	
	}
		
	// Loop through rules, check if any transaction is a match.
	foreach($rule as $tx_type => $def) {

		// Load predefined info for this transaction type if detected.
		if($valid[$tx_type] == 0) {
			$return = $define[$tx_type];
			$return['hash160'] = $data[$define[$tx_type]['data_index_for_hash']];

			$client = new BIP32();
			$magic_byte = ($return['type'] == 'scripthash') ? str_pad(gmp_strval(gmp_add(gmp_init($this->address_version), gmp_init('05',16))), 2, '0', STR_PAD_LEFT) : $this->address_version;
			if ($return['type'] == 'scripthash' && BITCOINS_TESTNET == 1) { $magic_byte = 'c4'; }
			$return['addresses'][0] = $client->base58_encode_checksum($magic_byte . $return['hash160']);
			unset($return['data_index_for_hash']);
		}
	}
		
	// Return
	return (!isset($return)) ? false : $return;

}

////////////////////////////////////////////////////////////
// Decode script pubkey
////////////////////////////////////////////////////////////

public function _decode_scriptPubKey($script) { 

	// Decode
	$data = array(); $x=0;
	while(strlen($script) >= $x) {			
		$byte = substr($script, $x, 2); $x += 2;

		// Check if byte is constant opcode		
		if(isset($this->op_code[$byte])) {
			$data[] = $this->op_code[$byte];
		
		// Check for PUSHDATA
		} else if ($byte >= 0x01 && $byte <= 0x4b) {
			$data[] = substr($script, $x, (hexdec($byte) * 2)); $x += (hexdec($byte) * 2);

		// Ensure legit range
		} else if ($byte >= 0x52 && $byte <= 0x60) {	
			$data[] = 'OP_' . ($byte - 0x52);
		}
	}
	
	// Return
	return implode(" ",$data);
}






}

?>
