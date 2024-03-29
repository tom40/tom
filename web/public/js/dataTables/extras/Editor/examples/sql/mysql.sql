--
--
-- MySQL tables used to run the examples.
-- 
-- NOTE: The examples use MySQL and PHP as the server-side platform in order to
-- show the client-side interacting with the server. The Editor library is 100%
-- decoupled from this backend, and if you wish to use a different server platform
-- then that is absolutely do able. Indeed, even these MySQL and PHP examples won't
-- be directly applicatable to all MySQL and PHP deployments!
--
-- For more information about how the client and server-sides interact, please refer
-- to the Editor documentation: http://editor.datatables.net .
--
--

--
-- To do list examples
--
DROP TABLE IF EXISTS todo;

CREATE TABLE `todo` (
  `id` int(10) NOT NULL auto_increment,
  `item` varchar(255) NOT NULL default '',
  `done` boolean NOT NULL default 0,
  `priority` integer NOT NULL default 1,
  PRIMARY KEY  (`id`)
);


--
-- Web browser examples
--
DROP TABLE IF EXISTS browsers;

CREATE TABLE `browsers` (
  `id` int(10) NOT NULL auto_increment,
  `engine` varchar(255) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `platform` varchar(255) NOT NULL default '',
  `version` float,
  `grade` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'Internet Explorer 4.0', 'Win 95+', '4', 'X' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'Internet Explorer 5.0', 'Win 95+', '5', 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'Internet Explorer 5.5', 'Win 95+', '5.5', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'Internet Explorer 6', 'Win 98+', '6', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'Internet Explorer 7', 'Win XP SP2+', '7', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Trident', 'AOL browser (AOL desktop)', 'Win XP', '6', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Firefox 1.0', 'Win 98+ / OSX.2+', '1.7', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Firefox 1.5', 'Win 98+ / OSX.2+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Firefox 2.0', 'Win 98+ / OSX.2+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Firefox 3.0', 'Win 2k+ / OSX.3+', '1.9', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Camino 1.0', 'OSX.2+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Camino 1.5', 'OSX.3+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Netscape 7.2', 'Win 95+ / Mac OS 8.6-9.2', '1.7', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Netscape Browser 8', 'Win 98SE+', '1.7', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Netscape Navigator 9', 'Win 98+ / OSX.2+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.0', 'Win 95+ / OSX.1+', '1', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.1', 'Win 95+ / OSX.1+', '1.1', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.2', 'Win 95+ / OSX.1+', '1.2', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.3', 'Win 95+ / OSX.1+', '1.3', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.4', 'Win 95+ / OSX.1+', '1.4', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.5', 'Win 95+ / OSX.1+', '1.5', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.6', 'Win 95+ / OSX.1+', '1.6', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.7', 'Win 98+ / OSX.1+', '1.7', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Mozilla 1.8', 'Win 98+ / OSX.1+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Seamonkey 1.1', 'Win 98+ / OSX.2+', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Gecko', 'Epiphany 2.20', 'Gnome', '1.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'Safari 1.2', 'OSX.3', '125.5', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'Safari 1.3', 'OSX.3', '312.8', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'Safari 2.0', 'OSX.4+', '419.3', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'Safari 3.0', 'OSX.4+', '522.1', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'OmniWeb 5.5', 'OSX.4+', '420', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'iPod Touch / iPhone', 'iPod', '420.1', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Webkit', 'S60', 'S60', '413', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 7.0', 'Win 95+ / OSX.1+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 7.5', 'Win 95+ / OSX.2+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 8.0', 'Win 95+ / OSX.2+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 8.5', 'Win 95+ / OSX.2+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 9.0', 'Win 95+ / OSX.3+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 9.2', 'Win 88+ / OSX.3+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera 9.5', 'Win 88+ / OSX.3+', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Opera for Wii', 'Wii', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Nokia N800', 'N800', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Presto', 'Nintendo DS browser', 'Nintendo DS', '8.5', 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'KHTML', 'Konqureror 3.1', 'KDE 3.1', '3.1', 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'KHTML', 'Konqureror 3.3', 'KDE 3.3', '3.3', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'KHTML', 'Konqureror 3.5', 'KDE 3.5', '3.5', 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Tasman', 'Internet Explorer 4.5', 'Mac OS 8-9', NULL, 'X' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Tasman', 'Internet Explorer 5.1', 'Mac OS 7.6-9', '1', 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Tasman', 'Internet Explorer 5.2', 'Mac OS 8-X', '1', 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'NetFront 3.1', 'Embedded devices', NULL, 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'NetFront 3.4', 'Embedded devices', NULL, 'A' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'Dillo 0.8', 'Embedded devices', NULL, 'X' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'Links', 'Text only', NULL, 'X' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'Lynx', 'Text only', NULL, 'X' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'IE Mobile', 'Windows Mobile 6', NULL, 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Misc', 'PSP browser', 'PSP', NULL, 'C' );
INSERT
    INTO browsers ( engine, browser, platform, version, grade ) 
    VALUES ( 'Other browsers', 'All others', '', NULL, 'U' );


--
-- Users table examples
--
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  `id` mediumint(8) unsigned NOT NULL auto_increment, 
  `title` varchar(255) default NULL,
  `first_name` varchar(255) default NULL,
  `last_name` varchar(255) default NULL,
  `phone` varchar(100) default NULL,
  `city` varchar(50) default NULL,
  `zip` varchar(10) default NULL,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `registered_date` datetime default NULL,
  `active` boolean default NULL,
  `comments` TEXT default NULL,
  PRIMARY KEY (`id`)
); 


INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Quynn','Contreras','1-971-977-4681','Slidell','81080','2012-04-06 18:53:00',0,'purus, in molestie tortor nibh sit amet orci. Ut sagittis lobortis mauris. Suspendisse');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mr','Kaitlin','Smith','1-436-523-6103','Orlando','U5G 7J3','2012-11-20 05:58:25',1,'vel arcu. Curabitur ut odio vel est tempor');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Cruz','Reynolds','1-776-102-6352','Lynn','EJ89 9DQ','2011-12-31 23:34:03',0,'velit. Quisque varius. Nam porttitor scelerisque neque. Nullam nisl. Maecenas malesuada fringilla est. Mauris eu turpis.');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Dr','Sophia','Morris','1-463-224-1405','Belleville','T1F 2X1','2012-08-04 02:55:53',0,'elementum, lorem');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Kamal','Roberson','1-134-408-5227','Rehoboth Beach','V7I 6T5','2012-12-23 00:17:03',1,'arcu. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec tincidunt. Donec vitae erat vel pede');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Dr','Dustin','Rosa','1-875-919-3188','Jersey City','E4 8ZE','2012-10-05 22:18:59',0,'adipiscing fringilla, porttitor vulputate, posuere vulputate, lacus. Cras interdum. Nunc sollicitudin commodo ipsum. Suspendisse non leo. Vivamus');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Dr','Xantha','George','1-106-884-4754','Billings','Y2I 6J7','2012-11-25 12:50:16',0,'nec, leo. Morbi neque tellus, imperdiet non, vestibulum nec, euismod in,');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Bryar','Long','1-918-114-8083','San Bernardino','82983','2012-05-14 23:32:25',0,'Sed congue, elit sed consequat auctor, nunc nulla vulputate dui, nec tempus');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Kuame','Wynn','1-101-692-4039','Truth or Consequences','21290','2011-06-21 16:27:07',1,'sem. Pellentesque ut ipsum ac mi eleifend egestas. Sed pharetra, felis eget varius ultrices, mauris ipsum porta elit, a feugiat tellus lorem eu metus. In lorem. Donec elementum, lorem ut');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Ms','Indigo','Brennan','1-756-756-8161','Moline','NO8 3UY','2011-02-19 12:51:08',1,'mi felis, adipiscing fringilla, porttitor vulputate, posuere vulputate, lacus. Cras interdum. Nunc sollicitudin commodo ipsum. Suspendisse non leo. Vivamus nibh dolor, nonummy ac, feugiat');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Avram','Allison','1-751-507-2640','Rancho Palos Verdes','I7Q 8H4','2012-12-30 17:02:10',0,'Maecenas malesuada fringilla');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mr','Martha','Burgess','1-971-722-1203','Toledo','Q5R 9HI','2011-02-04 17:25:55',1,'nunc interdum feugiat. Sed nec metus facilisis lorem tristique aliquet. Phasellus fermentum convallis ligula. Donec luctus aliquet odio.');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Lael','Kim','1-626-697-2194','Lake Charles','34209','2012-07-24 06:44:22',1,'pellentesque eget, dictum placerat, augue. Sed molestie. Sed id risus quis diam luctus lobortis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Mauris ut');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Dr','Lyle','Lewis','1-231-793-3520','Simi Valley','H9B 2H4','2012-08-30 03:28:54',0,'Duis elementum, dui quis accumsan convallis, ante lectus convallis');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Veronica','Marks','1-750-981-6759','Glens Falls','E3C 5D1','2012-08-14 12:09:24',1,'eget tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel, venenatis vel, faucibus');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Wynne','Ruiz','1-983-744-5362','Branson','L9E 6E2','2012-11-06 01:04:07',0,'Nulla aliquet. Proin velit. Sed malesuada augue ut lacus. Nulla');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Ms','Jessica','Bryan','1-949-932-6772','Boulder City','F5P 6NU','2013-02-01 20:22:33',0,'Nunc pulvinar arcu et pede. Nunc sed orci lobortis augue scelerisque mollis. Phasellus libero mauris, aliquam eu, accumsan sed, facilisis vitae,');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Ms','Quinlan','Hyde','1-625-664-6072','Sheridan','Y8A 1LQ','2011-10-25 16:53:45',1,'vitae semper egestas, urna justo faucibus lectus, a sollicitudin orci sem eget massa. Suspendisse eleifend. Cras sed leo. Cras vehicula aliquet libero. Integer in magna. Phasellus dolor elit, pellentesque a,');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Mona','Terry','1-443-179-7343','Juneau','G62 1OF','2012-01-15 09:26:59',0,'quam.');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Medge','Patterson','1-636-979-0497','Texarkana','I5U 6E0','2012-10-20 16:26:18',1,'Fusce fermentum fermentum arcu. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus ornare. Fusce mollis. Duis sit');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Perry','Gamble','1-440-976-9560','Arcadia','98923','2012-06-06 02:03:49',1,'gravida sit amet, dapibus id, blandit at, nisi. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin vel nisl. Quisque fringilla');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Pandora','Armstrong','1-197-431-4390','Glendora','34124','2011-08-29 01:45:06',0,'parturient montes, nascetur ridiculus mus. Aenean');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mr','Pandora','Briggs','1-278-288-9221','Oneida','T9M 4H9','2012-07-16 08:44:41',1,'Mauris eu turpis. Nulla aliquet. Proin velit. Sed malesuada augue ut lacus. Nulla tincidunt, neque');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Maris','Leblanc','1-936-114-2921','Cohoes','V1H 6Z7','2011-05-04 13:07:04',1,'Sed nec metus facilisis lorem tristique aliquet. Phasellus fermentum');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Ishmael','Crosby','1-307-243-2684','Midwest City','T6 8PS','2011-07-02 23:11:11',0,'nisl. Nulla eu neque pellentesque massa');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Quintessa','Pickett','1-801-122-7471','North Tonawanda','09166','2013-02-05 10:33:22',1,'sem, consequat nec, mollis vitae, posuere at, velit. Cras lorem lorem, luctus ut, pellentesque');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Miss','Ifeoma','Mays','1-103-883-0962','Parkersburg','87377','2011-08-22 12:19:09',0,'orci sem eget massa. Suspendisse eleifend. Cras sed leo. Cras vehicula aliquet libero. Integer in magna. Phasellus dolor elit, pellentesque a, facilisis non, bibendum sed, est. Nunc');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Basia','Harrell','1-528-238-4178','Cody','LJ54 1IU','2012-05-07 14:42:55',1,'Nulla eget metus eu');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Hamilton','Blackburn','1-676-857-1423','Delta Junction','X5 9HE','2011-05-19 07:39:48',0,'neque. In ornare sagittis felis. Donec tempor, est ac mattis semper, dui lectus rutrum');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Ms','Dexter','Burton','1-275-332-8186','Gainesville','65914','2013-02-01 16:21:20',1,'nulla. In tincidunt congue turpis.');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Quinn','Mccall','1-808-916-4497','Fallon','X4 8UB','2012-03-24 19:31:51',0,'placerat, augue. Sed molestie. Sed id risus quis diam luctus lobortis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mr','Alexa','Wilder','1-727-307-1997','Johnson City','16765','2011-10-14 08:21:14',0,'vel arcu eu odio tristique pharetra. Quisque ac libero nec ligula consectetuer rhoncus. Nullam velit dui, semper');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Ms','Rhonda','Harrell','1-934-906-6474','Minnetonka','I2R 1H2','2011-11-15 00:08:02',1,'ipsum porta elit, a feugiat tellus lorem eu metus. In lorem. Donec elementum, lorem ut aliquam iaculis, lacus pede sagittis augue, eu tempor erat neque non quam. Pellentesque habitant morbi');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Jocelyn','England','1-826-860-7773','Chico','71102','2012-05-31 18:01:43',1,'enim. Sed nulla');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Dr','Vincent','Banks','1-225-418-0941','Palo Alto','03281','2011-08-07 07:22:43',0,'consequat purus. Maecenas libero est, congue a, aliquet vel, vulputate eu, odio. Phasellus at augue id ante dictum cursus. Nunc mauris elit, dictum eu, eleifend nec, malesuada ut, sem. Nulla');
INSERT INTO `users` (`title`,`first_name`,`last_name`,`phone`,`city`,`zip`,`registered_date`,`active`,`comments`) VALUES ('Mrs','Stewart','Chan','1-781-793-2340','Grand Forks','L1U 3ED','2012-11-01 23:14:44',1,'taciti sociosqu');
