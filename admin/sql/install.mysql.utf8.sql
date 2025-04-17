CREATE TABLE IF NOT EXISTS `#__alfcontact` (
		`id` int NOT NULL AUTO_INCREMENT,
		`name` varchar(255) NOT NULL,
		`email` varchar(255) NOT NULL,
		`bcc` varchar(255) NOT NULL,
		`prefix` varchar(255) NOT NULL,
		`extra` text NOT NULL,
		`defsubject` varchar(255) NOT NULL,
		`ordering` int NOT NULL DEFAULT 0,
		`access` int unsigned NOT NULL DEFAULT 0,
		`language` char(7) NOT NULL DEFAULT '',
		`published` int NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		) ENGINE=innoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__alfcontact` VALUES (1, 'Sales', 'sales@mysite.com', 'archive@mysite.com', '[Sales]', 'Client No:\nOrder No:\nItem No:', 'Order inquiry', 1, 1, 'en-GB', 1);
INSERT INTO `#__alfcontact` VALUES (2, 'Verkoop', 'verkoop@mijnsite.nl', 'archief@mijnsite.nl', '[Verkoop]', 'Klant Nr:\nOrder Nr:\nItem Nr:', 'Order navraag', 2, 1, 'nl-NL', 1);
INSERT INTO `#__alfcontact` VALUES (3, 'Webmaster', '', 'webmaster@mysite.com', '[Webmaster]', '', '', 3, 1, '*', 1);
INSERT INTO `#__alfcontact` VALUES (4, 'Support', '', 'support1@mysite.com\nsupport2@mysite.com\nsupport3@mysite.com', '[Support]', '', 'Question', 4, 2, '*', 0);
