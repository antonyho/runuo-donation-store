
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";



CREATE TABLE IF NOT EXISTS `redeemable_gift` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `type_id` smallint(5) unsigned NOT NULL,
  `account_name` varchar(60) NOT NULL,
  `donate_time` int(11) unsigned NOT NULL,
  `paypal_txn_id` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account_name` (`account_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;




CREATE TABLE IF NOT EXISTS `redeemed_gift` (
  `id` bigint(20) unsigned NOT NULL,
  `type_id` smallint(5) unsigned NOT NULL,
  `account_name` varchar(60) NOT NULL,
  `donate_time` int(11) unsigned NOT NULL,
  `redeem_time` int(11) unsigned NOT NULL,
  `serial` varchar(80) NOT NULL,
  `paypal_txn_id` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account_name` (`account_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




CREATE TABLE IF NOT EXISTS `gift_type` (
  `type_id` smallint(5) unsigned NOT NULL auto_increment,
  `type_name` varchar(200) NOT NULL,
  `class_name` varchar(60) NOT NULL,
  `price` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;




CREATE TABLE IF NOT EXISTS `paypal_transaction` (
  `mc_gross` double unsigned NOT NULL,
  `protection_eligibility` varchar(20) NOT NULL,
  `payer_id` varchar(20) NOT NULL,
  `tax` double unsigned NOT NULL default '0',
  `payment_date` varchar(30) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `charset` varchar(30) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `option_selection1` varchar(40) default NULL,
  `notify_version` varchar(8) NOT NULL,
  `custom` varchar(80) default NULL,
  `payer_status` varchar(20) NOT NULL,
  `business` varchar(80) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `verify_sign` varchar(70) NOT NULL,
  `payer_email` varchar(80) NOT NULL,
  `option_name1` varchar(40) default NULL,
  `txn_id` varchar(25) NOT NULL,
  `payment_type` varchar(25) NOT NULL,
  `btn_id` mediumint(8) unsigned NOT NULL,
  `last_name` varchar(60) default NULL,
  `receiver_email` varchar(80) NOT NULL,
  `shipping_discount` double unsigned NOT NULL default '0',
  `insurance_amount` double unsigned NOT NULL default '0',
  `receiver_id` varchar(20) NOT NULL,
  `pending_reason` varchar(50) NOT NULL,
  `txn_type` varchar(50) NOT NULL,
  `item_name` varchar(80) NOT NULL,
  `discount` double unsigned NOT NULL default '0',
  `mc_currency` varchar(5) NOT NULL,
  `item_number` int(10) unsigned NOT NULL,
  `residence_country` varchar(5) NOT NULL,
  `test_ipn` bit(1) NOT NULL,
  `receipt_id` varchar(30) NOT NULL,
  `handling_amount` double NOT NULL default '0',
  `shipping_method` varchar(20) NOT NULL,
  `transaction_subject` varchar(80) NOT NULL,
  `payment_gross` double default NULL,
  `shipping` double NOT NULL default '0',
  `mc_fee` double unsigned NOT NULL default '0',
  `payment_fee` double unsigned default NULL,
  PRIMARY KEY  (`txn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




CREATE TABLE IF NOT EXISTS `paypal_processed_txn` (
  `txn_id` varchar(25) NOT NULL,
  `create_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`txn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




-- Below records are examples, you may delete or not to execute them. Use the web admin panel to add your own gifts.
INSERT INTO `gift_type` (`type_name`, `class_name`, `price`) VALUES
('225 Stat Ball', 'StatBall', 100),
('Ethereal Horse', 'EtherealHorse', 150),
('Ethereal Beetle', 'EtherealBeetle', 150),
('Ethereal Ostard', 'EtherealOstard', 150),
('Ethereal Ridgeback', 'EtherealRidgeback', 150),
('Ethereal Swamp Dragon', 'EtherealSwampDragon', 150),
('Ethereal Llama', 'EtherealLlama', 200),
('Ethereal Ki-rin', 'EtherealKirin', 200),
('Ethereal Unicorn', 'EtherealUnicorn', 200),
('Ridable Polar Bear', 'RideablePolarBear', 400);