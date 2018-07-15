<?php

class controller_default { 

public function __construct($parts = array()) { 

	// Initialize
	global $template;

	// Parse template
	$template = new template();
	echo $template->parse();

	// Exit
	exit(0);
	

}

}

?>
