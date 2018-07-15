<?php

// Load
require("../../load.php");
global $config;

// Get rate
$vars = json_decode(file_get_contents('https://api.coinmarketcap.com/v1/ticker/bitcoin/?convert=USD'), 1)[0];
if (!is_array($vars)) { exit(0); }
if (!isset($vars['price_usd'])) { exit(0); }

// Update config vars
update_config_var('current_rate', $vars['price_usd']);
update_config_var('24hour_volume', $vars['24h_volume_usd']);
update_config_var('market_cap', $vars['market_cap_usd']);
update_config_var('total_supply', $vars['total_supply']);
update_config_var('percent_change_24h', $vars['percent_change_24h']);
update_config_var('percent_change_7d', $vars['percent_change_7d']);

// Exit
exit(0);


?>
