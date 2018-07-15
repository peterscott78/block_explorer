<?php

// Check for address
if ($row = DB::queryFirstRow("SELECT * FROM addresses WHERE address = %s", $_POST['search'])) { 
	header("Location: /address/$_POST[search]");

// Check for txid
} elseif ($row = DB::queryFirstField("SELECT * FROM tx WHERE txid = %s", $_POST['search'])) { 
	header("Location: /tx/$_POST[search]");

// Block
} elseif ($row = DB::queryFirstField("SELECT * FROM blocks WHERE id = %s OR block_hash = %s", $_POST['search'], $_POST['search'])) { 
	header("Location: /block/$row[block_hash]");

} else { 
	$template = new template('index');
	$template->add_message("No txid, address or block matches your search query.  Please try again.", 'info');
	echo $template->parse(); exit(0);
}

?>
