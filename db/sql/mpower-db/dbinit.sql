-- MySQL dump 10.16  Distrib 10.1.26-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mpower_demo
-- ------------------------------------------------------
-- Server version	10.1.26-MariaDB-0+deb9u1

--
-- Current Database: `mpower_demo`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mpower_demo` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `mpower_demo`;

--
-- Table structure for table `identity_providers`
--

DROP TABLE IF EXISTS `identity_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `identity_providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `idp` enum('uwnetid','truenth') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `identity_providers`
--

LOCK TABLES `identity_providers` WRITE;
/*!40000 ALTER TABLE `identity_providers` DISABLE KEYS */;
INSERT INTO `identity_providers` VALUES (1,9,'uwnetid'),(2,10,'uwnetid'),(3,122,'uwnetid'),(4,198,'uwnetid'),(8,518,'uwnetid'),(9,519,'uwnetid'),(10,522,'uwnetid'),(11,523,'uwnetid'),(12,524,'uwnetid'),(13,528,'uwnetid'),(24,570,'uwnetid'),(25,572,'uwnetid'),(26,576,'uwnetid'),(27,609,'uwnetid'),(28,620,'uwnetid'),(29,621,'uwnetid'),(30,644,'uwnetid');
/*!40000 ALTER TABLE `identity_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MRN` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailing_address` text COLLATE utf8_unicode_ci,
  `study_participation_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('usual care','elements of consent','pre-consent','consented','declined','ineligible','off-project') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'usual care' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date DEFAULT NULL,
  `consenter_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') COLLATE utf8_unicode_ci DEFAULT 'MedOnc',
  `treatment_start_date` date DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Settable by survey or patient editor',
  `ethnicity` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(2) DEFAULT NULL,
  `study_group` enum('Control','Treatment') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `check_again_date` date DEFAULT NULL COMMENT 'date when someone should view this patient record and take action',
  `no_more_check_agains` tinyint(1) NOT NULL DEFAULT '0',
  `alt_contact_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alternative contact name',
  `alt_contact_relation` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alternative contact''s relationship to patient',
  `alt_contact_phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alternative contact phone number',
  `alt_contact_email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alternative contact email address',
  `t2a_subscale_id` int(11) DEFAULT NULL,
  `t2b_subscale_id` int(11) DEFAULT NULL,
  `off_study_status` enum('On study','Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_reason` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_timestamp` timestamp NULL DEFAULT NULL,
  `declined_reason` enum('The study does not interest me','I prefer not to participate in research','I am too busy','I do not use computers/the Internet','Other (describe in Notes)','Prefer not to answer/no answer given') COLLATE utf8_unicode_ci DEFAULT NULL,
  `72_hr_follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `farthestStepInIntervention` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ie ''priorities'', or ''factors.46''',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=647 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (586,'8888888',0,NULL,NULL,NULL,0,NULL,'usual care',NULL,NULL,0,0,'MedOnc',NULL,'1975-09-18','female','',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-21 20:00:57',NULL,0,NULL),(574,'9999999',0,'','206-555-9988',NULL,0,NULL,'consented',NULL,NULL,0,0,NULL,NULL,'1975-02-28','male','',NULL,'Treatment',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'On study',NULL,'2015-05-22 18:23:17',NULL,0,NULL);
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_acl_leafs`
--

DROP TABLE IF EXISTS `user_acl_leafs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_acl_leafs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `acl_alias` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=675 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_acl_leafs`
--

LOCK TABLES `user_acl_leafs` WRITE;
/*!40000 ALTER TABLE `user_acl_leafs` DISABLE KEYS */;
INSERT INTO `user_acl_leafs` VALUES (594,9,'aclAdmin'),(2,10,'aclAdmin'),(4,9,'aclSurveyEditor'),(11,9,'aclClinicStaff'),(148,10,'aclSurveyEditor'),(201,198,'aclClinicStaff'),(202,198,'aclSurveyEditor'),(203,198,'aclAdmin'),(632,609,'aclClinician'),(631,609,'aclAdmin'),(630,609,'aclClinicStaff'),(629,608,'aclParticipantTreatment'),(628,607,'aclParticipantTreatment'),(626,606,'aclParticipantTreatment'),(622,602,'aclParticipantTreatment'),(621,601,'aclParticipantTreatment'),(619,599,'aclParticipantTreatment'),(512,518,'aclAdmin'),(513,518,'aclClinicStaff'),(514,519,'aclAdmin'),(515,519,'aclClinicStaff'),(516,522,'aclAdmin'),(517,522,'aclClinicStaff'),(518,523,'aclAdmin'),(519,523,'aclClinicStaff'),(520,524,'aclAdmin'),(521,524,'aclClinicStaff'),(523,528,'aclClinicStaff'),(524,528,'aclAdmin'),(611,591,'aclParticipantTreatment'),(604,585,'aclParticipantTreatment'),(596,9,'aclClinician'),(591,576,'aclClinician'),(605,586,'aclParticipantTreatment'),(587,122,'aclClinician'),(607,122,'aclAdmin'),(585,574,'aclParticipantTreatment'),(606,587,'aclParticipantTreatment'),(583,523,'aclClinician'),(582,524,'aclClinician'),(581,519,'aclClinician'),(580,522,'aclClinician'),(579,518,'aclClinician'),(556,560,'aclParticipantTreatment'),(558,519,'aclResearcher'),(559,9,'aclResearcher'),(568,569,'aclClinician'),(569,569,'aclCentralSupport'),(565,567,'aclParticipantTreatment'),(570,570,'aclAdmin'),(571,570,'aclSurveyEditor'),(572,570,'aclResearcher'),(573,570,'aclClinician'),(574,10,'aclClinicStaff'),(575,10,'aclClinician'),(578,198,'aclClinician'),(593,572,'aclClinician'),(643,620,'aclClinicStaff'),(644,620,'aclClinician'),(645,620,'aclClinicAdmin'),(646,621,'aclClinicStaff'),(647,621,'aclAdmin'),(648,621,'aclClinician'),(671,644,'aclAdmin'),(674,122,'aclSurveyEditor');
/*!40000 ALTER TABLE `user_acl_leafs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_pw_flag` tinyint(1) DEFAULT NULL COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) DEFAULT NULL,
  `language` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last4ssn` smallint(6) DEFAULT NULL,
  `registered` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinic_id` (`clinic_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM AUTO_INCREMENT=647 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,'mcjustin','','Justin','McReynolds','mcjustin@uw.edu',0,1,NULL,NULL,'2013-12-12 17:05:28'),(10,'mark47','','Mark','Stewart','mark47@uw.edu',0,1,NULL,NULL,'2013-12-11 18:01:26'),(576,'cirg','','Bill \"the clinician\"','Lober','cirg@uw.edu',0,1,NULL,NULL,NULL),(122,'ivanc','84c140a34c8ec3951f629f5f3a55d45ae2e44a5c','Ivan','Cvitkovic','ivanc@uw.edu',0,1,NULL,NULL,'2013-12-12 19:42:58'),(198,'lober','','Bill','Lober','lober@uw.edu',0,1,NULL,NULL,NULL),(601,'Aalami','cf8581a3b2382ecea66de32fc860cdfad3df8495','Oliver','Aalami','aalami@stanford.edu',0,1,NULL,NULL,NULL),(602,'Evanjelita','5c245b3e2b750f58994b1264ad53bca50f6d0342','Evanjelita','Alaniz',NULL,0,1,NULL,NULL,NULL),(518,'hlevans','','Heather','Evans','hlevans@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(519,'psanger','','Patrick','Sanger','psanger@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(599,'cojosh74','15508bb8d0922e86bb2fdc1a26682eaafb7e8cb5','josh','Miller',NULL,0,1,NULL,NULL,NULL),(522,'andreah','','Andrea','Hartzler','andreah@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(523,'smhan91','','Sarah','Han','smhan91@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(524,'carmst','','Cheryl','Armstrong','carmst@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(528,'algadaan','','Ahmad','Aljadaan','algadaan@uw.edu',0,1,NULL,NULL,'2014-05-11 00:00:00'),(585,'cindy2a','0e2f90b53364b6393efacf117ed5110fd57b7ebf','Cindy','Jacobs',NULL,0,1,NULL,NULL,NULL),(586,'patient2','9823b3031f0c4778392346725d0bfd31e4b0db93','(graphic wound photos)','Joan Smith','mpowerit+patient2@uw.edu',0,1,NULL,NULL,NULL),(587,'jfoote','04ff7c72d8320e983b8453900b626ac6ce1dde3d','Jack','Foote',NULL,0,1,NULL,NULL,NULL),(591,'rmiller','c32265ffc1123558f17474b8f13efd7ae4a78d86','Ric','Miller','info@floatinghome.com',0,1,NULL,NULL,NULL),(574,'patient1','f024c02a5ba73cda77ced9177f059fb560b5918a','(Graphic wound photos)','John Smith','mpowerit+patient1@uw.edu',0,1,NULL,NULL,NULL),(560,'Patricktest','b7959cb5473f6f23bee02441aadfc784ef447331','Patricktest','Patricktest',NULL,0,1,NULL,NULL,'2015-01-04 01:53:55'),(569,'pbugni-clinician','eb045ccf572f23ec6f397f90182ca3948c981284','Paul','Bugni','pbugni+clinician@uw.edu',0,1,NULL,NULL,NULL),(570,'pbugni','','Paul','Bugni','pbugni@uw.du',0,1,NULL,NULL,NULL),(572,'clinician','7372436c6193863b30a5ae64e73d35cdc69621b1','Dr. Demo','Welby','justin.emcee+clinician@gmail.com',0,1,NULL,NULL,NULL),(567,'mtesta','e45a9ea8de2d4a2835d18cc2f26e0f75b319cdab','Mark','Test',NULL,0,1,NULL,NULL,'2015-02-20 19:37:17'),(606,'sfoote','9811184090a5acdbeb98fa37e5db144d07601996','September','Foote','markstewart+30@gmail.com',0,1,NULL,1234,NULL),(607,'jm090415a','e05d1ac77e47ce3c1564559905f7b632f0b9af77','jm090415a','jm090415a','justin.emcee+jm090415a@gmail.com',0,1,NULL,1234,NULL),(608,'jm091615a','0ea4ba84d9be31764ed0f7f7790999c03fa66480','jm091615a','jm091615a','justin.emcee+jm091615a@gmail.com',0,1,NULL,1234,NULL),(609,'kthelps','fd640944eef340d2ad7dd8cd16bde6676028f263','Kristin','Helps','kthelps@uw.edu',0,1,NULL,NULL,NULL),(620,'abc',NULL,'Abc','Testa','markstewart+0301@gmail.com',1,1,NULL,NULL,NULL),(621,'zechj',NULL,'Jennifer','Zech','zechj@uw.edu',0,1,NULL,NULL,NULL),(644,'clundell',NULL,'Cole','Lundell','clundell@uw.edu',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `mpower_demo`
--

USE `mpower_demo`;

-- Dump completed on 2018-02-17 21:26:38
