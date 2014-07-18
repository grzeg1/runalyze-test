-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: runalyze
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.14.04.1-log
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `runalyze_conf`
--

INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (148,'JD_USE_VDOT_CORRECTOR','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (147,'VDOT_HF_METHOD','logarithmic',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (146,'RECHENSPIELE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (145,'DESIGN_BG_FILE','img/backgrounds/Strasse.jpg',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (144,'DESIGN_BG_FIX_AND_STRETCH','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (143,'DB_SHOW_DIRECT_EDIT_LINK','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (142,'DB_SHOW_CREATELINK_FOR_DAYS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (141,'DB_DISPLAY_MODE','week',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (140,'DB_HIGHLIGHT_TODAY','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (139,'TRAINING_MAP_PUBLIC_MODE','always',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (138,'TRAINING_LIST_STATISTICS','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (137,'TRAINING_LIST_ALL','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (136,'TRAINING_LIST_PUBLIC','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (135,'TRAINING_MAKE_PUBLIC','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (134,'FORMULAR_SHOW_GPS','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (133,'FORMULAR_SHOW_ELEVATION','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (132,'FORMULAR_SHOW_PUBLIC','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (131,'FORMULAR_SHOW_NOTES','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (130,'FORMULAR_SHOW_OTHER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (129,'FORMULAR_SHOW_WEATHER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (128,'FORMULAR_SHOW_SPLITS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (127,'FORMULAR_SHOW_DISTANCE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (126,'FORMULAR_SHOW_GENERAL','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (125,'FORMULAR_SHOW_SPORT','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (124,'TRAINING_SHOW_MAP','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (123,'TRAINING_SHOW_PLOT_TEMPERATURE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (122,'TRAINING_SHOW_PLOT_POWER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (121,'TRAINING_SHOW_PLOT_CADENCE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (120,'TRAINING_SHOW_PLOT_COLLECTION','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (119,'TRAINING_SHOW_PLOT_PACEPULSE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (118,'TRAINING_SHOW_PLOT_SPLITS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (117,'TRAINING_SHOW_PLOT_ELEVATION','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (116,'TRAINING_SHOW_PLOT_PULSE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (115,'TRAINING_SHOW_PLOT_PACE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (114,'TRAINING_SHOW_GRAPHICS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (113,'TRAINING_SHOW_ROUNDS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (112,'TRAINING_SHOW_ZONES','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (111,'TRAINING_SHOW_DETAILS','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (110,'TRAINING_MAP_MARKER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (109,'TRAINING_MAP_BEFORE_PLOTS','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (108,'ELEVATION_MIN_DIFF','3',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (107,'ELEVATION_METHOD','treshold',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (106,'TRAINING_MAPTYPE','GoogleMaps',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (105,'TRAINING_MAP_COLOR','#FF5500',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (104,'TRAINING_DECIMALS','1',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (103,'PACE_HIDE_OUTLIERS','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (102,'PACE_Y_AXIS_REVERSE','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (101,'PACE_Y_LIMIT_MAX','780',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (100,'PACE_Y_LIMIT_MIN','120',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (99,'TRAINING_PLOT_PRECISION','300points',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (98,'GMAP_PATH_PRECISION','5',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (97,'GMAP_PATH_BREAK','15',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (96,'TRAINING_PLOT_MODE','collection',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (95,'LL_TYPID','7',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (94,'WK_TYPID','5',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (93,'RUNNINGSPORT','1',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (92,'MAINSPORT','1',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (91,'PULS_MODE','bpm',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (90,'GENDER','m',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (149,'ATL_DAYS','7',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (150,'CTL_DAYS','42',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (151,'VDOT_DAYS','21',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (152,'VDOT_MANUAL_CORRECTOR','',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (153,'VDOT_MANUAL_VALUE','',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (154,'JD_USE_VDOT_CORRECTION_FOR_ELEVATION','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (155,'VDOT_CORRECTION_POSITIVE_ELEVATION','3',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (156,'VDOT_CORRECTION_NEGATIVE_ELEVATION','-0.5',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (157,'MAX_ATL','109',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (158,'MAX_CTL','44',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (159,'MAX_TRIMP','720',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (160,'VDOT_FORM','34.27781',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (161,'VDOT_CORRECTOR','0.84647132548655',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (162,'BASIC_ENDURANCE','34',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (163,'START_TIME','1381933471',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (164,'HF_MAX','182',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (165,'HF_REST','50',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (166,'TRAINING_ELEVATION_SERVER','geonames',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (167,'TRAINING_SHOW_AFTER_CREATE','false',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (168,'TRAINING_CREATE_MODE','upload',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (169,'COMPUTE_KCAL','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (170,'COMPUTE_POWER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (171,'TRAINING_DO_ELEVATION','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (172,'PLZ','Cracow, Poland',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (173,'TRAINING_LOAD_WEATHER','true',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (174,'TRAINING_SORT_SPORTS','id-asc',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (175,'TRAINING_SORT_TYPES','id-asc',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (176,'TRAINING_SORT_SHOES','id-asc',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (177,'GARMIN_IGNORE_IDS','',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (178,'RESULTS_AT_PAGE','15',0);
INSERT INTO `runalyze_conf` (`id`, `key`, `value`, `accountid`) VALUES (179,'TRAINING_LEAFLET_LAYER','OpenStreetMap',0);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-07-18 14:18:50
