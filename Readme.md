Block Explorer
==============

A simple, yet effective block explorer for the bitcoin network.  Very straight forward, typical block explorer, easy to modify theme / templates, processes blocks in about 1 second per-block as they are solved, etc.


## Requirements

* LINUX server / VPS capable of running Bitcoin Core
* Bitcoin Core v0.10.0+
* One (1) clean mySQL database
* ~15MB of HD space


## Installation

Installation is quite simple, and just complete the following steps:

* Upload the contents of the archive to your server.
* Modify the /data/config.php file with your mySQL info and bitcoin RPC info.
* Import the /install.sql file into the blank mySQL database.
* Modify Nginx / Apache configuration and add a rewrite rule so all requests to non-existent files / directories goes to "/index.php?route=$URI?$ARGS".  If using Nginx, add this line within the "location { }" directive of the virtual host:
** try_files $uri $uri/ /index.php?route=$uri&$args; 
* In SSH, manually execute the /data/cron/check_blocks.php file.  This will take a while as it starts at block #1, and goes through each block summarizing information and inserting it into the database.
* Add the following crontab jobs:
** "* * * * * cd /path/to/script/data/cron; /usr/bin/php -q check_blocks.php > /dev/null"
** "*/5 * * * * cd /path/to/script/data/cron; /usr/bin/php -q get_rate.php > /dev/null"  
* That's it!  Open your web browser to the install URL, and the block explorer should come up.


## Usage

Below shows the URLs available:
* / = Home page, showig recent blocks and transactions.
* /tx/TXID = View a single tx
* /address/ADDRESS = List all txs assigned to a payment address.
* /block/BLOCKNUM/HASH = Accepts either blocknum or blockhash, and displays all txs within a block.



## Theme / Template Modifications


- CSS, header and footer files are in /theme/ directory.
- Uses Bootstrap 4 as requested, with Smarty template engine.
- Content pages are in /data/tpl/ directory, and should be straight forward.
- TPL filenames are relative to the URI.  For example, http;//DOMAIN/about will display the template at /data/tpl/public/about.tpl, and so on.

-- END --



