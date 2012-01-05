CREATE TABLE  `ortemij`.`p_converter` (
	`script` VARCHAR( 32 ) NOT NULL ,
	`time` INT NOT NULL ,
	`execution` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	PRIMARY KEY (  `script` )
) ENGINE = MYISAM ;