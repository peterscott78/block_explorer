<?php

// Load
require("../../load.php");
global $config;

// Check block
$client = new Explorer();
$client->check_mempool();
$client->check_block();

// Delete existing home page rows
DB::query("DELETE FROM home_blocks");
DB::query("DELETE FROM home_tx");

// Add home blocks
$rows = DB::query("SELECT * FROM blocks ORDER BY id DESC LIMIT 0,10");
foreach ($rows as $row) { 
	DB::insert('home_blocks', array(
		'id' => $row['id'], 
		'weight' => $row['weight'], 
		'size' => $row['size'], 
		'transactions' => $row['transactions'], 
		'total_sent' => $row['total_sent'], 
		'block_hash' => $row['block_hash'], 
		'date_added' => $row['date_added'])
	);
}

// Add home tx
$rows = DB::query("SELECT tx.id AS id, tx.txid AS txid, tx.date_added AS date_added, sum(tx_outputs.amount) AS amount_sent, tx.blocknum AS blocknum FROM tx, tx_outputs WHERE tx.id = tx_outputs.txid GROUP BY tx_outputs.txid ORDER BY tx.id DESC LIMIT 0,15");
foreach ($rows as $row) {
	DB::insert('home_tx', array(
		'id' => $row['id'], 
		'blocknum' => $row['blocknum'],
		'amount_sent' => $row['amount_sent'], 
		'txid' => $row['txid'], 
		'date_added' => $row['date_added'])
	);
}

?>
