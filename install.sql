
DROP TABLE IF EXISTS config;
DROP TABLE IF EXISTS blocks;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS tx;
DROP TABLE IF EXISTS tx_outputs;

CREATE TABLE config (
	name VARCHAR(255) NOT NULL, 
	value VARCHAR(255) NOT NULL
);
INSERT INTO config VALUES ('blocknum', '0');
INSERT INTO config VALUES ('current_rate', '0');
INSERT INTO config VALUES ('24hour_volume', '0');
INSERT INTO config VALUES ('market_cap', '0');
INSERT INTO config VALUES ('total_supply', '0');
INSERT INTO config VALUES ('percent_change_24h', '0');
INSERT INTO config VALUES ('percent_change_7d', '0');
INSERT INTO config VALUES ('currency', 'BTC');


CREATE TABLE blocks (
	id INT NOT NULL PRIMARY KEY, 
	weight INT NOT NULL, 
	size INT NOT NULL, 
	transactions INT NOT NULL, 
	total_sent DECIMAL(16,8) NOT NULL, 
	block_hash VARCHAR(255) NOT NULL, 
	date_added INT NOT NULL
);

CREATE TABLE addresses (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
	address VARCHAR(80) NOT NULL UNIQUE 
);

CREATE TABLE tx (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
	blocknum INT NOT NULL DEFAULT 0,
	version DECIMAL(2,1) NOT NULL DEFAULT 1.0, 
	size INT NOT NULL DEFAULT 0, 
	locktime VARCHAR(10) NOT NULL DEFAULT '00000000', 
	txid VARCHAR(100) NOT NULL UNIQUE, 
	txhash VARCHAR(255) NOT NULL, 
	date_added INT NOT NULL
);

CREATE TABLE tx_outputs (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
	is_spent TINYINT(1) NOT NULL DEFAULT 0, 
	txid INT NOT NULL, 
	vout SMALLINT NOT NULL, 
	amount DECIMAL(16,8) NOT NULL, 
	input_txid INT NOT NULL DEFAULT 0,  
	address_id INT NOT NULL
);

CREATE TABLE home_blocks (
	id INT NOT NULL PRIMARY KEY, 
	weight INT NOT NULL, 
	size INT NOT NULL, 
	transactions INT NOT NULL, 
	total_sent DECIMAL(16,8) NOT NULL, 
	block_hash VARCHAR(255) NOT NULL, 
	date_added INT NOT NULL
);

CREATE TABLE home_tx (
	id INT NOT NULL PRIMARY KEY, 
	blocknum INT NOT NULL DEFAULT 0,
	amount_sent DECIMAL(16,8) NOT NULL, 
	txid VARCHAR(100) NOT NULL UNIQUE, 
	date_added INT NOT NULL
);

CREATE TABLE mempool (
	txid VARCHAR(100) NOT NULL UNIQUE, 
	date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX fk_tx_outputs ON tx_outputs (txid, input_txid, address_id);


