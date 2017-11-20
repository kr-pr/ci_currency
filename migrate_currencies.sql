USE naturalthrone;
DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
    id       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code     VARCHAR(5) UNIQUE,
	symbol   VARCHAR(3),
	name_en  VARCHAR(255),
	name_pt  VARCHAR(255),
	name_es  VARCHAR(255),
	name_fr  VARCHAR(255),
    rate     FLOAT,
	time_set BIGINT UNSIGNED DEFAULT 0  
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8;
LOAD DATA LOCAL INFILE './currencies.csv' INTO TABLE currencies FIELDS TERMINATED BY ',' 
LINES TERMINATED BY '\n' IGNORE 1 ROWS (code,symbol,name_en,name_pt,name_es, name_fr);