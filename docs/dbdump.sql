SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `Sheet1`;
CREATE TABLE IF NOT EXISTS `Sheet1` (
  `location` int(4) DEFAULT NULL,
  `source` text,
  `target` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `access_tokens`;
CREATE TABLE IF NOT EXISTS `access_tokens` (
  `oauth_token` varchar(40) NOT NULL,
  `client_id` char(36) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `acos`;
CREATE TABLE IF NOT EXISTS `acos` (
`id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=360 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `activity_diary_entries`;
CREATE TABLE IF NOT EXISTS `activity_diary_entries` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dt_created` datetime NOT NULL,
  `dt_last_edit` datetime NOT NULL,
  `date` date NOT NULL,
  `fatigue` int(1) DEFAULT NULL,
  `type` enum('Walking','Biking','Running','Other','None') COLLATE utf8_unicode_ci DEFAULT NULL,
  `typeOther` text COLLATE utf8_unicode_ci,
  `minutes` int(4) unsigned DEFAULT NULL,
  `steps` int(6) unsigned DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE IF NOT EXISTS `alerts` (
`id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `target_type` enum('item','subscale','scale') NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `comparison` enum('<','>','=') NOT NULL,
  `value` float NOT NULL,
  `message` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `answers`;
CREATE TABLE IF NOT EXISTS `answers` (
`id` int(11) NOT NULL,
  `survey_session_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `state` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body_text` text COLLATE utf8_unicode_ci,
  `value` text COLLATE utf8_unicode_ci,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `iteration` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `created_staff_id` int(11) DEFAULT NULL COMMENT 'This is slightly misnamed, it indicates the most recent editor'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `aros`;
CREATE TABLE IF NOT EXISTS `aros` (
`id` int(10) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `aros_acos`;
CREATE TABLE IF NOT EXISTS `aros_acos` (
`id` int(10) NOT NULL,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=416 DEFAULT CHARSET=utf8;
DROP VIEW IF EXISTS `aros_acos_interpreted_view`;
CREATE TABLE IF NOT EXISTS `aros_acos_interpreted_view` (
`id` int(10)
,`aro_id` int(10)
,`aros_alias` varchar(255)
,`aco_id` int(10)
,`acos_alias` varchar(255)
,`_create` varchar(2)
,`_read` varchar(2)
,`_update` varchar(2)
,`_delete` varchar(2)
);
DROP TABLE IF EXISTS `aros_backup`;
CREATE TABLE IF NOT EXISTS `aros_backup` (
  `id` int(10) NOT NULL DEFAULT '0',
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `associates`;
CREATE TABLE IF NOT EXISTS `associates` (
`id` int(11) NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'REMOVE THIS JUNK',
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'OBSOLETE, see patients_associates.has_entered_secret_phrase',
  `webkey` int(10) unsigned NOT NULL COMMENT 'OBSOLETE; SEE patients_associates'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `auth_codes`;
CREATE TABLE IF NOT EXISTS `auth_codes` (
  `code` varchar(40) NOT NULL,
  `client_id` char(36) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `redirect_uri` varchar(200) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cake_sessions`;
CREATE TABLE IF NOT EXISTS `cake_sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `expires` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `client_id` char(20) NOT NULL,
  `client_secret` char(40) NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP VIEW IF EXISTS `clinicians`;
CREATE TABLE IF NOT EXISTS `clinicians` (
`user_id` int(10)
);
DROP TABLE IF EXISTS `clinics`;
CREATE TABLE IF NOT EXISTS `clinics` (
`id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `friendly_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `irb_contact` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `patient_status_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `one_usual_care_session` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, after the 1st survey session the patient is either moved to "usual care" or "off-project"'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `conditions`;
CREATE TABLE IF NOT EXISTS `conditions` (
`id` int(11) NOT NULL,
  `target_type` enum('Page','Questionnaire','Question','Option') DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `condition` text
) ENGINE=MyISAM AUTO_INCREMENT=482 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `consents`;
CREATE TABLE IF NOT EXISTS `consents` (
  `id` int(11) NOT NULL COMMENT ' ',
  `patient_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='used for synchronization when assigning patients to control/';

DROP TABLE IF EXISTS `identity_providers`;
CREATE TABLE IF NOT EXISTS `identity_providers` (
`id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `idp` enum('uwnetid','truenth','sof') DEFAULT NULL,
  `external_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` varchar(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'uuid',
  `answer_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `filename` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'original filename as uploaded',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
`id` int(11) NOT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name_es_MX` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `question_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `base` smallint(6) NOT NULL DEFAULT '0' COMMENT 'NOT USED ANYMORE!',
  `range` float DEFAULT NULL COMMENT 'for mean or weighted sum, num options - 1; for sum, ignored',
  `critical` smallint(6) DEFAULT NULL,
  `sequence` smallint(6) DEFAULT NULL COMMENT 'sequence w/in subscale; only known use: default sequence w/in p3p factor group'
) ENGINE=MyISAM AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a join on questions & subscales';
DROP VIEW IF EXISTS `itemsThroughScalesByProject`;
CREATE TABLE IF NOT EXISTS `itemsThroughScalesByProject` (
`item_id` int(11)
,`question_id` int(11)
,`subscale_id` int(11)
,`subscale_name` varchar(60)
,`subscale_order` smallint(6)
,`scale_id` int(11)
,`scale_name` varchar(60)
,`scale_order` smallint(6)
,`questionnaire_id` int(11)
,`project_id` int(11)
);
DROP TABLE IF EXISTS `journal_entries`;
CREATE TABLE IF NOT EXISTS `journal_entries` (
`id` int(11) NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `display` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `locale_selections`;
CREATE TABLE IF NOT EXISTS `locale_selections` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `locale` enum('en_US','es_MX') COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `patient_id` int(11) unsigned DEFAULT NULL COMMENT 'Primary use: recording which patient a staff member is acting on. If user is a patient, can still have null here',
  `controller` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `params` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL,
  `ip_address` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(200) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP VIEW IF EXISTS `logs_intervention_non_test`;
CREATE TABLE IF NOT EXISTS `logs_intervention_non_test` (
`id` int(10) unsigned
,`user_id` int(11) unsigned
,`controller` varchar(20)
,`action` varchar(64)
,`params` varchar(512)
,`time` datetime
);
DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `created` datetime NOT NULL,
  `lastmod` datetime NOT NULL,
  `flagged` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if flagged, 0 if not',
  `flag_type` enum('Identifiers in note','Participant distress','Participant feedback','Provider feedback','Technical issue','Data integrity','Report to IRB') COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `options`;
CREATE TABLE IF NOT EXISTS `options` (
`id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `OptionType` enum('radio','checkbox','select','dropdown','text','textbox','combo-radio','combo-check','button-yes','button-no','imagemap','none','image','month','day','year','event','slider') NOT NULL DEFAULT 'radio',
  `Height` int(11) NOT NULL DEFAULT '0',
  `Width` int(11) NOT NULL DEFAULT '0',
  `MaxCharacters` int(11) DEFAULT NULL,
  `AnalysisValue` varchar(32) DEFAULT NULL,
  `ValueRestriction` varchar(128) DEFAULT NULL,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextType` enum('visible','invisible') NOT NULL DEFAULT 'visible',
  `AncillaryText` text COMMENT 'extra text used for combo-radio and combo-checkbox',
  `Sequence` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=7523 DEFAULT CHARSET=utf8 PACK_KEYS=0;

DROP TABLE IF EXISTS `p3p_teachings`;
CREATE TABLE IF NOT EXISTS `p3p_teachings` (
`id` int(11) NOT NULL,
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'if null, items.name will be used',
  `label_es_MX` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` enum('statistics','factors','control','next_steps') COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci,
  `video` int(3) DEFAULT NULL,
  `Sequence` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
`id` int(11) NOT NULL,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) DEFAULT NULL,
  `Title_es_MX` text,
  `Header` text,
  `Header_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `NavigationType` enum('prev-next','next','none','prev') NOT NULL DEFAULT 'prev-next',
  `TargetType` varchar(32) DEFAULT NULL,
  `ProgressType` enum('text','graphical','none') NOT NULL DEFAULT 'graphical',
  `LayoutType` enum('basic','embedded','col2') NOT NULL DEFAULT 'basic',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `iterable` tinyint(1) DEFAULT NULL COMMENT 'whether a page can have multiple iterations'
) ENGINE=MyISAM AUTO_INCREMENT=1568 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `participant_demographics`;
CREATE TABLE IF NOT EXISTS `participant_demographics` (
  `patient id` int(11) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `clinic` varchar(40) DEFAULT NULL,
  `survey_session` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `patient_T1s`;
CREATE TABLE IF NOT EXISTS `patient_T1s` (
  `patient` int(11) DEFAULT NULL,
  `clinic` varchar(40) DEFAULT NULL,
  `survey_session` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `patient_extensions`;
CREATE TABLE IF NOT EXISTS `patient_extensions` (
  `patient_id` int(11) NOT NULL,
  `surgery_date` date DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `surgery_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surgery_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `procedure` text COLLATE utf8_unicode_ci COMMENT 'surgery JSON field',
  `data_use` tinyint(4) DEFAULT NULL COMMENT '0 for false, 1 for true, null for not set',
  `dr_city` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dr_state` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dr_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patient_view_notes`;
CREATE TABLE IF NOT EXISTS `patient_view_notes` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `lastmod` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
`id` int(11) NOT NULL,
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
  `farthestStepInIntervention` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ie ''priorities'', or ''factors.46'''
) ENGINE=MyISAM AUTO_INCREMENT=2000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients_associates`;
CREATE TABLE IF NOT EXISTS `patients_associates` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients_associates_deleted`;
CREATE TABLE IF NOT EXISTS `patients_associates_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients_associates_subscales`;
CREATE TABLE IF NOT EXISTS `patients_associates_subscales` (
`id` int(11) NOT NULL,
  `patient_associate_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `shared` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients_clinics_vw`;
CREATE TABLE IF NOT EXISTS `patients_clinics_vw` (
  `name` varchar(40) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  `MRN` varchar(10) DEFAULT NULL,
  `test_flag` tinyint(1) DEFAULT NULL,
  `phone1` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `mailing_address` varchar(255) DEFAULT NULL,
  `study_participation_flag` tinyint(1) DEFAULT NULL,
  `user_type` enum('Home/Independent','Clinic/Assisted') DEFAULT NULL,
  `consent_status` enum('usual care','elements of consent','pre-consent','consented','declined','ineligible','off-project') DEFAULT NULL,
  `consent_date` date DEFAULT NULL,
  `consenter_id` int(11) DEFAULT NULL,
  `consent_checked` tinyint(1) DEFAULT NULL,
  `hipaa_consent_checked` tinyint(1) DEFAULT NULL,
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') DEFAULT NULL,
  `treatment_start_date` date DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `ethnicity` varchar(15) DEFAULT NULL,
  `eligible_flag` tinyint(1) DEFAULT NULL,
  `study_group` enum('Control','Treatment') DEFAULT NULL,
  `check_again_date` date DEFAULT NULL,
  `no_more_check_agains` tinyint(1) DEFAULT NULL,
  `off_study_status` enum('On study','Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') DEFAULT NULL,
  `off_study_reason` varchar(500) DEFAULT NULL,
  `off_study_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `patients_clinics_vw_count`;
CREATE TABLE IF NOT EXISTS `patients_clinics_vw_count` (
  `COUNT(*)` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `patients_deleted`;
CREATE TABLE IF NOT EXISTS `patients_deleted` (
`id` int(11) NOT NULL,
  `MRN` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailing_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `72_hr_follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `farthestStepInIntervention` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ie ''priorities'', or ''factors.46'''
) ENGINE=MyISAM AUTO_INCREMENT=508 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `patients_p3p_teachings`;
CREATE TABLE IF NOT EXISTS `patients_p3p_teachings` (
`id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `p3p_teaching_id` int(11) NOT NULL,
  `visited` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
`id` int(11) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `roles_that_can_assess` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'php just looks for strings in here, eg ''aroPatient|aroClinicStaff'' is fine',
  `session_rules_fxn` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `initializable_until` time DEFAULT NULL,
  `resumable_until` time DEFAULT NULL,
  `contextLimiter` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If set, project start/resume links are not presented from other controllers/actions. Null applies no restriction.',
  `single_stage_finalization` int(1) NOT NULL DEFAULT '0' COMMENT 'If true, SurveySession .partial_finalization and .finished are set at the same time',
  `can_close_partial` int(1) DEFAULT '0',
  `skipped_questions_page` int(20) DEFAULT NULL,
  `complete_btn_page` int(20) DEFAULT NULL,
  `header` varchar(511) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_survey_txt` varchar(511) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `taken_break_txt` varchar(511) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `continue_survey_txt` varchar(511) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `concise_session_launch_txt` smallint(1) DEFAULT '0',
  `finish_survey_txt` varchar(511) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `elective_sessions` int(1) NOT NULL DEFAULT '0',
  `ui_small` int(1) NOT NULL DEFAULT '0' COMMENT 'if 1, survey items are smaller. Use when more questions are needed per page',
  `qnr_nav_menu` tinyint(1) NOT NULL DEFAULT '0',
  `persistent_nav` tinyint(1) NOT NULL DEFAULT '0',
  `email_reminder` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of email template file; if null, none sent.',
  `resume_at_latest` enum('answer','page') NOT NULL DEFAULT 'answer',
  `confirm_skipped_qs_on_page` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `projects_questionnaires`;
CREATE TABLE IF NOT EXISTS `projects_questionnaires` (
`id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Sequence` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `questionnaires`;
CREATE TABLE IF NOT EXISTS `questionnaires` (
`id` int(11) NOT NULL,
  `Title` varchar(128) DEFAULT NULL,
  `Title_es_MX` text,
  `BodyText` text,
  `FriendlyTitle` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `FriendlyTitle_es_MX` text
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
`id` int(11) NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) DEFAULT NULL,
  `ShortTitle_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextPosition` enum('above','left') NOT NULL DEFAULT 'left',
  `Orientation` enum('vertical','horizontal','matrix-top','matrix','matrix-bottom','horizontal-matrix','horizontal-matrix-left','horizontal-matrix-right') NOT NULL DEFAULT 'vertical',
  `Groups` int(11) NOT NULL DEFAULT '1',
  `Style` enum('normal','hidden','inline','inline-last','cardsort') NOT NULL DEFAULT 'normal',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `has_conditional_options` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_skipped` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."',
  `RangeLeft` text,
  `RangeRight` text
) ENGINE=MyISAM AUTO_INCREMENT=2178 DEFAULT CHARSET=utf8 PACK_KEYS=0;

DROP TABLE IF EXISTS `refresh_tokens`;
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` char(36) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `relationships`;
CREATE TABLE IF NOT EXISTS `relationships` (
`id` int(11) NOT NULL,
  `user_a_id` int(11) NOT NULL DEFAULT '0',
  `user_b_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('is patient of','is caregiver of','is family member for patient cared by','is teammember for','is following') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `scales`;
CREATE TABLE IF NOT EXISTS `scales` (
`id` int(11) NOT NULL,
  `invert` tinyint(1) DEFAULT NULL,
  `combination` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ignored, calculated by simple summation of session_subscales'' values',
  `questionnaire_id` int(11) NOT NULL COMMENT 'Used to test inclusion in project; note: scales do not always map 1:1 questionnaires',
  `range` smallint(6) DEFAULT NULL COMMENT 'not being used, see subscales',
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL COMMENT 'Only used in results controller: > 0 for inclusion there and to designate order',
  `base` smallint(6) DEFAULT '1' COMMENT 'not being used, see subscales',
  `critical` smallint(5) unsigned DEFAULT NULL COMMENT 'used by sparklines in view my reports'
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `scales_subscales_view`;
CREATE TABLE IF NOT EXISTS `scales_subscales_view` (
  `subscale_id` int(11) DEFAULT NULL,
  `subscale_name` varchar(60) DEFAULT NULL,
  `scale_id` int(11) DEFAULT NULL,
  `scale_name` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `session_items`;
CREATE TABLE IF NOT EXISTS `session_items` (
`id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `survey_session_id` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `session_subscale_id` int(11) NOT NULL COMMENT 'Not populated... exists to allow SessionSubscale => SessionItem relationship',
  `subscale_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `session_scales`;
CREATE TABLE IF NOT EXISTS `session_scales` (
`id` int(11) NOT NULL,
  `survey_session_id` int(11) NOT NULL,
  `scale_id` int(11) NOT NULL,
  `value` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `session_subscales`;
CREATE TABLE IF NOT EXISTS `session_subscales` (
`id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `session_scale_id` int(11) NOT NULL COMMENT 'JM asks: junk?',
  `survey_session_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sites`;
CREATE TABLE IF NOT EXISTS `sites` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `friendly_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `research_staff_email_alias` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alias to email all associated research staff for the site',
  `research_staff_signature` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'signature to attach to email from the research staff',
  `new_aim_consent_mod_date` date DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `subscales`;
CREATE TABLE IF NOT EXISTS `subscales` (
`id` int(11) NOT NULL,
  `scale_id` int(11) NOT NULL,
  `range` smallint(6) NOT NULL,
  `invert` tinyint(1) NOT NULL,
  `base` smallint(6) NOT NULL DEFAULT '0',
  `critical` smallint(6) NOT NULL DEFAULT '2' COMMENT 'for results/show, at least',
  `combination` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `internal_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` smallint(6) NOT NULL COMMENT 'within the scale, as displayed in "View My/Others Reports"',
  `category_id` int(11) DEFAULT NULL COMMENT 'Which category represents this on the coding form'
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `survey_sessions`;
CREATE TABLE IF NOT EXISTS `survey_sessions` (
`id` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'The user who initially launched the survey session',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `partial_finalization` tinyint(1) NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `patient_closed_partial` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Can only be set with finished and partial_finalization.',
  `reportable_datetime` datetime DEFAULT NULL,
  `external_id` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_id_last_interaction` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `survey_sessions_appt_dt`;
CREATE TABLE IF NOT EXISTS `survey_sessions_appt_dt` (
  `id` int(11) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `started` datetime DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `partial_finalization` tinyint(1) DEFAULT NULL,
  `finished` tinyint(1) DEFAULT NULL,
  `appt_dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `survey_sessions_deleted`;
CREATE TABLE IF NOT EXISTS `survey_sessions_deleted` (
`id` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'Don''t use anymore!',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `partial_finalization` tinyint(1) NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `patient_closed_partial` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Can only be set with finished and partial_finalization.',
  `reportable_datetime` datetime DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=462 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `survey_sessions_non_test_view`;
CREATE TABLE IF NOT EXISTS `survey_sessions_non_test_view` (
  `id` int(11) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `started` datetime DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `finished` tinyint(1) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `partial_finalization` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `survey_sessions_non_test_view_count`;
CREATE TABLE IF NOT EXISTS `survey_sessions_non_test_view_count` (
  `COUNT(*)` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
`id` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `creator_id` int(11) NOT NULL,
  `type` enum('care_team') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tags_users`;
CREATE TABLE IF NOT EXISTS `tags_users` (
`id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `assigner_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `targets`;
CREATE TABLE IF NOT EXISTS `targets` (
`id` int(11) NOT NULL,
  `type` enum('T1','T2') COLLATE utf8_unicode_ci NOT NULL,
  `month` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `target` int(5) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `teaching_tips`;
CREATE TABLE IF NOT EXISTS `teaching_tips` (
`id` smallint(6) NOT NULL,
  `subscale_id` smallint(6) NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If this is set, it will be used instead of subscales.name when displaying'
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `teaching_tips_percentages`;
CREATE TABLE IF NOT EXISTS `teaching_tips_percentages` (
`id` int(10) unsigned NOT NULL,
  `clinic_id` int(11) NOT NULL,
  `teaching_tip_id` int(11) NOT NULL,
  `after_treatment` tinyint(1) NOT NULL,
  `percentage` tinyint(4) NOT NULL,
  `clinical_service` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Transplant'
) ENGINE=MyISAM AUTO_INCREMENT=486 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `teaching_tips_subs_scales_view`;
CREATE TABLE IF NOT EXISTS `teaching_tips_subs_scales_view` (
  `subscale_id` int(11) DEFAULT NULL,
  `subscale_name` varchar(60) DEFAULT NULL,
  `scale_id` int(11) DEFAULT NULL,
  `scale_name` varchar(60) DEFAULT NULL,
  `teaching_tips_id` smallint(6) DEFAULT NULL,
  `text` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tours`;
CREATE TABLE IF NOT EXISTS `tours` (
`id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `section` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `step` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `user_acl_leafs`;
CREATE TABLE IF NOT EXISTS `user_acl_leafs` (
`id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `acl_alias` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1063 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_pw_flag` tinyint(1) DEFAULT NULL COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) DEFAULT NULL,
  `language` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last4ssn` smallint(6) DEFAULT NULL,
  `registered` datetime DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP VIEW IF EXISTS `users_acl_easy_to_read`;
CREATE TABLE IF NOT EXISTS `users_acl_easy_to_read` (
`id` int(11)
,`username` varchar(255)
,`first_name` varchar(64)
,`last_name` varchar(64)
,`email` varchar(40)
,`clinic_id` int(11)
,`acl_alias` varchar(255)
);
DROP TABLE IF EXISTS `users_deleted`;
CREATE TABLE IF NOT EXISTS `users_deleted` (
`id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_pw_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) NOT NULL DEFAULT '1',
  `language` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registered` datetime DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=721 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `webkeys`;
CREATE TABLE IF NOT EXISTS `webkeys` (
`id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `purpose` enum('self-register','login_assist','anonymous_access') COLLATE utf8_unicode_ci NOT NULL,
  `sent_on` datetime DEFAULT NULL,
  `used_on` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `aros_acos_interpreted_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER VIEW `aros_acos_interpreted_view` AS select `aros_acos`.`id` AS `id`,`aros_acos`.`aro_id` AS `aro_id`,`aros`.`alias` AS `aros_alias`,`aros_acos`.`aco_id` AS `aco_id`,`acos`.`alias` AS `acos_alias`,`aros_acos`.`_create` AS `_create`,`aros_acos`.`_read` AS `_read`,`aros_acos`.`_update` AS `_update`,`aros_acos`.`_delete` AS `_delete` from ((`aros_acos` join `aros` on((`aros_acos`.`aro_id` = `aros`.`id`))) join `acos` on((`aros_acos`.`aco_id` = `acos`.`id`)));
DROP TABLE IF EXISTS `clinicians`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER VIEW `clinicians` AS select `user_acl_leafs`.`user_id` AS `user_id` from `user_acl_leafs` where (`user_acl_leafs`.`acl_alias` like 'aclClinician');
DROP TABLE IF EXISTS `itemsThroughScalesByProject`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER VIEW `itemsThroughScalesByProject` AS select `items`.`id` AS `item_id`,`items`.`question_id` AS `question_id`,`items`.`subscale_id` AS `subscale_id`,`subscales`.`name` AS `subscale_name`,`subscales`.`order` AS `subscale_order`,`scales`.`id` AS `scale_id`,`scales`.`name` AS `scale_name`,`scales`.`order` AS `scale_order`,`scales`.`questionnaire_id` AS `questionnaire_id`,`projects_questionnaires`.`project_id` AS `project_id` from (((`items` join `subscales` on((`items`.`subscale_id` = `subscales`.`id`))) join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) join `projects_questionnaires` on((`projects_questionnaires`.`questionnaire_id` = `scales`.`questionnaire_id`))) order by `scales`.`order`,`subscales`.`order` limit 0,999;
DROP TABLE IF EXISTS `logs_intervention_non_test`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER VIEW `logs_intervention_non_test` AS select `logs`.`id` AS `id`,`logs`.`user_id` AS `user_id`,`logs`.`controller` AS `controller`,`logs`.`action` AS `action`,`logs`.`params` AS `params`,`logs`.`time` AS `time` from (`logs` join `patients` on((`logs`.`user_id` = `patients`.`id`))) where ((`patients`.`test_flag` = 0) and ((`logs`.`controller` = _utf8'results') or (`logs`.`controller` = _utf8'teaching') or (`logs`.`controller` = _utf8'journals') or (`logs`.`controller` = _utf8'associates')) and (not((`logs`.`action` like _utf8'%end%')))) order by `logs`.`user_id`;
DROP TABLE IF EXISTS `users_acl_easy_to_read`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER VIEW `users_acl_easy_to_read` AS select `users`.`id` AS `id`,`users`.`username` AS `username`,`users`.`first_name` AS `first_name`,`users`.`last_name` AS `last_name`,`users`.`email` AS `email`,`users`.`clinic_id` AS `clinic_id`,`user_acl_leafs`.`acl_alias` AS `acl_alias` from (`users` join `user_acl_leafs` on((`users`.`id` = `user_acl_leafs`.`user_id`)));


ALTER TABLE `access_tokens`
 ADD PRIMARY KEY (`oauth_token`);

ALTER TABLE `acos`
 ADD PRIMARY KEY (`id`), ADD KEY `acos_idx1` (`lft`,`rght`), ADD KEY `acos_idx2` (`alias`);

ALTER TABLE `activity_diary_entries`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `patient_date_index` (`patient_id`,`date`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `alerts`
 ADD PRIMARY KEY (`id`), ADD KEY `page_id` (`page_id`);

ALTER TABLE `answers`
 ADD PRIMARY KEY (`id`), ADD KEY `question_id` (`survey_session_id`,`question_id`,`option_id`);

ALTER TABLE `appointments`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `aros`
 ADD PRIMARY KEY (`id`), ADD KEY `aros_idx1` (`lft`,`rght`), ADD KEY `aros_idx2` (`alias`);

ALTER TABLE `aros_acos`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`);

ALTER TABLE `associates`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_codes`
 ADD PRIMARY KEY (`code`);

ALTER TABLE `cake_sessions`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `clients`
 ADD PRIMARY KEY (`client_id`);

ALTER TABLE `clinics`
 ADD PRIMARY KEY (`id`), ADD KEY `site_id` (`site_id`);

ALTER TABLE `conditions`
 ADD PRIMARY KEY (`id`), ADD KEY `condition_id` (`id`), ADD KEY `target_id` (`target_id`), ADD KEY `target_type` (`target_type`);

ALTER TABLE `consents`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `identity_providers`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `images`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `items`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `journal_entries`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `locale_selections`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id_index` (`user_id`);

ALTER TABLE `logs`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id_index` (`user_id`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `notes`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`), ADD KEY `author_id` (`author_id`);

ALTER TABLE `options`
 ADD PRIMARY KEY (`id`), ADD KEY `DATA_OUTPUT` (`question_id`,`Sequence`);

ALTER TABLE `p3p_teachings`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `pages`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `page_id` (`id`);

ALTER TABLE `patient_extensions`
 ADD PRIMARY KEY (`patient_id`);

ALTER TABLE `patient_view_notes`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`), ADD KEY `author_id` (`author_id`);

ALTER TABLE `patients`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `patients_associates`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`);

ALTER TABLE `patients_associates_deleted`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `patients_associates_subscales`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_associate_id` (`patient_associate_id`,`subscale_id`);

ALTER TABLE `patients_deleted`
 ADD PRIMARY KEY (`id`), ADD KEY `birthdate` (`birthdate`), ADD KEY `off_study_status` (`off_study_status`), ADD KEY `off_study_timestamp` (`off_study_timestamp`), ADD KEY `consent_checked` (`consent_checked`), ADD KEY `consent_status` (`consent_status`), ADD KEY `hipaa_consent_checked` (`hipaa_consent_checked`);

ALTER TABLE `patients_p3p_teachings`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `projects`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `project_id` (`id`);

ALTER TABLE `projects_questionnaires`
 ADD PRIMARY KEY (`id`), ADD KEY `project_questionnaire_id` (`id`,`project_id`);

ALTER TABLE `questionnaires`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `questions`
 ADD PRIMARY KEY (`id`), ADD KEY `DATA_OUTPUT` (`page_id`,`Sequence`);

ALTER TABLE `refresh_tokens`
 ADD PRIMARY KEY (`refresh_token`);

ALTER TABLE `relationships`
 ADD PRIMARY KEY (`id`), ADD KEY `user_a_id` (`user_a_id`), ADD KEY `user_b_id` (`user_b_id`);

ALTER TABLE `scales`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `session_items`
 ADD PRIMARY KEY (`id`), ADD KEY `survey_session_id` (`survey_session_id`);

ALTER TABLE `session_scales`
 ADD PRIMARY KEY (`id`), ADD KEY `survey_session_id` (`survey_session_id`);

ALTER TABLE `session_subscales`
 ADD PRIMARY KEY (`id`), ADD KEY `survey_session_id` (`survey_session_id`);

ALTER TABLE `sites`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `subscales`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `survey_sessions`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`), ADD KEY `modified` (`modified`);

ALTER TABLE `survey_sessions_deleted`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`patient_id`), ADD KEY `modified` (`modified`);

ALTER TABLE `tags`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `tags_users`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `targets`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`,`month`);

ALTER TABLE `teaching_tips`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `teaching_tips_percentages`
 ADD PRIMARY KEY (`id`), ADD KEY `site_id` (`clinic_id`,`teaching_tip_id`);

ALTER TABLE `tours`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `user_acl_leafs`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD KEY `clinic_id` (`clinic_id`), ADD KEY `last_name` (`last_name`);

ALTER TABLE `users_deleted`
 ADD PRIMARY KEY (`id`), ADD KEY `clinic_id` (`clinic_id`), ADD KEY `last_name` (`last_name`);

ALTER TABLE `webkeys`
 ADD PRIMARY KEY (`id`), ADD KEY `patient_id` (`user_id`);


ALTER TABLE `acos`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=360;
ALTER TABLE `activity_diary_entries`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `alerts`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
ALTER TABLE `answers`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `appointments`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `aros`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=32;
ALTER TABLE `aros_acos`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=416;
ALTER TABLE `associates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `clinics`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
ALTER TABLE `conditions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=482;
ALTER TABLE `identity_providers`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=77;
ALTER TABLE `items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=117;
ALTER TABLE `journal_entries`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `locale_selections`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `logs`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `notes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `options`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7523;
ALTER TABLE `p3p_teachings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=45;
ALTER TABLE `pages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1568;
ALTER TABLE `patient_view_notes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `patients`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2000;
ALTER TABLE `patients_associates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `patients_associates_subscales`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `patients_deleted`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=508;
ALTER TABLE `patients_p3p_teachings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `projects`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
ALTER TABLE `projects_questionnaires`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=79;
ALTER TABLE `questionnaires`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=100;
ALTER TABLE `questions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2178;
ALTER TABLE `relationships`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=194;
ALTER TABLE `scales`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
ALTER TABLE `session_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `session_scales`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `session_subscales`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sites`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
ALTER TABLE `subscales`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55;
ALTER TABLE `survey_sessions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `survey_sessions_deleted`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=462;
ALTER TABLE `tags`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
ALTER TABLE `tags_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=157;
ALTER TABLE `targets`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=31;
ALTER TABLE `teaching_tips`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=46;
ALTER TABLE `teaching_tips_percentages`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=486;
ALTER TABLE `tours`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_acl_leafs`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1063;
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2000;
ALTER TABLE `users_deleted`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=721;
ALTER TABLE `webkeys`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
