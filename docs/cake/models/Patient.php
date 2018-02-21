<?php

/** 
    * Patient class
    *
    * Store data related to an actual patient in the survey, who may log in
    * by him/herself as a user, or may be logged in by a research/clinic user
    *
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

//require_once(APPLIBS . 'PatientProjectState.php');

class Patient extends AppModel {

    public $_schema = array(
        'external_study_id' => array(
            'type' => 'string',
            'length' => 10
        ),
    );

    var $hasMany = array(
            'SurveySession' => array('dependent' => true), 
            'Consent' => array('dependent' => true), 
            'Note' => array('order' => 'Note.created DESC',
                            'dependent' => true),
            'PatientViewNote' => 
                array('order' => 'PatientViewNote.lastmod DESC',
                        'dependent' => true),
            "Appointment" => 
                array('order' => "Appointment.datetime ASC", 
                       'dependent' => 'true')
            );

    var $hasOne = array(
        'PatientExtension' => array('dependent' => true)
    );   
 
    var $belongsTo = array(
        'User' => array(
            'className'    => 'User',
            'foreignKey'    => 'id'
        )
    ); 

/**
    function __construct ($id = false, $table = null, $ds = null ){
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), this is " . get_class($this) . "; calling parent " . __FUNCTION__ . "() next", LOG_DEBUG);
        parent::__construct($id, $table, $ds);
        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), done.", LOG_DEBUG);
    }
*/

    public function consentStatus($check){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), here's this->data: " . print_r($this->data, true) . ", and heres check: " . print_r($check, true), LOG_DEBUG);
       
        if (is_array($this->data['Patient']['consent_status'])){

            $consentStatuses = $this->data['Patient']['consent_status'];
            if ($consentStatuses[0] == Patient::PRECONSENT 
                || $consentStatuses[0] == Patient::ELEMENTS_OF_CONSENT){
                return true;
            } 
            else return false;
        } 
        else return true;
    }
 
    public function consentDatePerStatus($check){
        //TODO Invalidate attempts to set consent_date if consent_status 'usual care' or 'pre-consent'
        return true;
    }
 
    /** Value for study_group field that indicates patient is in the treatment
       group */
    const TREATMENT = 'Treatment';

    /** Value for study_group field that indicates patient is in the control
       group */
    const CONTROL = 'Control';

    /** Default / initial consent_status indicates patient has not yet qualified for pre-consent*/
    const USUAL_CARE = 'usual care';

    /** consent_status indicates patient will not be put thru consent process*/
    const OFF_PROJECT = 'off-project';

    /** consent_status indicating that the patient has a FINS score at or above threshold, but has not yet consented or declined to join the study */
    const PRECONSENT = 'pre-consent';

    /** Value for consent_status field that indicates patient has indicated willingness to consent to join the study, but paperwork not done yet */
    const ELEMENTS_OF_CONSENT = 'elements of consent';

    /** Value for consent_status field that indicates patient has consented 
       to join the study */
    const CONSENTED = 'consented';

    /** Value for consent_status field that indicates patient has declined
       to join the study */
    const DECLINED = 'declined';

    // off_study_status
    const CONSENTED_BUT_INELIGIBLE = 'Consented but ineligible';

    const PROJECTS_STATES = 'PROJECTS_STATES';
  
    var $projectsStates = array();

    var $bindModels = true;

    /**
     *
     */
    function beforeFind($queryData){
        //$this->log(__CLASS__ . "->" . __FUNCTION__ . '('.print_r($queryData, true).')', LOG_DEBUG);

        // Replace erroneous references to fields in Patient model with correct PatientExtension fields
        $replaceMap = array();
        foreach(array_keys((array)$this->PatientExtension->schema()) as $field)
            $replaceMap["Patient.$field"] = "PatientExtension.$field";

        unset($replaceMap[$this->name.'.'.$this->PatientExtension->primaryKey]);
        foreach($queryData as $optionKey => &$optionValue){
            if (is_array($optionValue)){
                foreach($optionValue as $tableField => $value){
                    $replacementField = strtr($tableField, $replaceMap);

                    // for 'order' field
                    if (is_string($value))
                        $value = strtr($value, $replaceMap);
                    // for 'conditions'
                    $optionValue[$replacementField] = $value;
                    if ($replacementField != $tableField)
                        unset($optionValue[$tableField]);
                }
            }
        }

        // if bindModels hasn't been disabled for perf reasons
        if ($this->bindModels and Configure::check('modelsInstallSpecific')){

            $models = Configure::read('modelsInstallSpecific');
            if (in_array('coded_items', $models))
                $this->bindModel(array('hasOne' => array(
                    'AudioFile' => array('dependent' => true),
                    'Chart' => array('dependent' => true)
                )), false);

            if (in_array('journals', $models))
                $this->bindModel(array('hasMany' => array(
                    'JournalEntry' => array('dependent' => true)
                )), false);

            if (in_array('associates', $models))
                $this->bindModel(array('hasAndBelongsToMany' => array(
                    'Associate' => array(
                        'joinTable' => 'patients_associates',
                        'foreign_key' => 'patient_id',// TODO key OK???
                        // 'conditions' => array('Associate.verified = true'),
                        'associationForeignKey' => 'associate_id'
                ))), false);

            if (in_array('activity_entries', $models))
                $this->bindModel(array('hasMany' => array(
                    'ActivityDiaryEntry' => array('dependent' => true)
                )), false);

            if (in_array('medications', $models))
                $this->bindModel(array('hasMany' => array(
                    'Medday' => array('dependent' => true)
                )), false);

        }
        return $queryData;
    }


    /** 
     * Return the allowed values for setting the consent_status field
     * @param patient cakephp data array 
     * @return array, or null if the field should be disabled
     */
    function getConsentStatusSelections($patient) {

        $returnVal;

        if (!defined('STUDY_SYSTEM') || !STUDY_SYSTEM){
            $returnVal = null;
        }
        else {

            $consent_status = $patient["Patient"]['consent_status'];
            if ($consent_status == Patient::PRECONSENT){
                $returnVal = array(self::PRECONSENT => self::PRECONSENT,
                            self::CONSENTED => self::CONSENTED,
		                    self::DECLINED => self::DECLINED);
            }
            elseif ($consent_status == Patient::ELEMENTS_OF_CONSENT){
                $returnVal = array(self::ELEMENTS_OF_CONSENT 
                                        => self::ELEMENTS_OF_CONSENT,
                                    self::CONSENTED => self::CONSENTED,
		                            self::DECLINED => self::DECLINED);
            }
            elseif ($consent_status == Patient::USUAL_CARE){
                $returnVal = array(Patient::USUAL_CARE => Patient::USUAL_CARE,
                                Patient::OFF_PROJECT => Patient::OFF_PROJECT);
            }
            else $returnVal = array($consent_status);
        }

//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(), returning: " . print_r($returnVal, true), LOG_DEBUG);
        return $returnVal;
    }

    /** Value for the user_type field */
    const HOME = 'Home/Independent';

    /** Value for the user_type field */
    const CLINIC = 'Clinic/Assisted';

    /** 
      Return the allowed values for the user_type field 
      This should also be a class constant
     */
    function getUserTypes() {
       return array(self::HOME => self::HOME,
                    self::CLINIC => self::CLINIC);
    }

    /** Value for clinical_service field */
    const MEDONC = 'MedOnc';

    /** Value for clinical_service field */
    const RADONC = 'RadOnc';

    /** Value for clinical_service field */
    const TRANSPLANT = 'Transplant';

    /** Value for clinical_service field */
    const SURGERY = 'Surgery';

    /** 
      Return the allowed values for the clinical_service field 
      This should also be a class constant
     */
    function getClinicalServices() {
       return array(self::MEDONC => self::MEDONC);
    }

    function firstSession($patient_id) {
        return $this->sessions($patient_id) < 1;
    }

    function sessions($patient_id) {
        $sessions = $this->query(
         "SELECT count( * ) AS count
             FROM `patients`
             LEFT JOIN `survey_sessions` ON `patients`.`id` = `survey_sessions`.`patient_id`
             AND `patient_id` = $patient_id");
        return $sessions[0][0]["count"];
    }


    function finishedSurveySessions($patient_id) {
        $patient = $this->findById($patient_id);
		$sessions = $patient["SurveySession"];
		
		return $this->SurveySession->filterFinished($sessions);
    }

    function isParticipant($patient_id){
        $patient = $this->findById($patient_id);
        return ($patient['Patient']['consent_status'] 
                    == self::CONSENTED);
    }


    /**
     *
     */
    function randomize($test_flag){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        return self::TREATMENT;
    }

    /**
     * Consent patent and randomize to a study group based on their user 
     * type and whether they are a test patient
     * @param patientId Id of the patient
     * @param userType User type (see getUserTypes)
     * @param test_flag Is the user a test patient
     */
    function setToParticipantAndRandomize($patientId, $userType, $test_flag) {
//         $this->log(__CLASS__ . "." . __FUNCTION__ . "()", LOG_DEBUG);

        $this->id = $patientId;
	    $success = false;
        $studyGroup = null;

        while (empty($success)) {
	        // for optimistic concurrency:  read highest consent id
    	    $nextConsentId = $this->Consent->getLastId() + 1;

            $studyGroup = $this->randomize($test_flag);

            $this->data['Patient']['id'] = $patientId;
	        $this->data['Patient']['study_group'] = $studyGroup;
	        $this->data['Consent'][0]['id'] = $nextConsentId;
            $this->data['Consent'][0]['patient_id'] = $patientId;

            /* Optimistic concurrency:  Consent.id is unique, so this 
	       should fail if someone else added a consenting patient after 
	       we read the highest consent id */
	        $success = $this->saveAll($this->data);
        }

        /* change existing acl_alias from aclPatient to 
	   aclParticipantControl/Treatment */
        $this->swapPatientsAclLeaf(
                        $patientId, 
                        'aclPatient', 
                        'aclParticipant' . $studyGroup);
    }

    function setConsentStatus($patientId, $studyGroup){
        
        $this->data['Patient']['study_group'] = $studyGroup;
        $this->data['Patient']['id'] = $patientId;

        $this->id = $patientId;
        $this->swapPatientsAclLeaf(
            $patientId, 
            'aclPatient', 
            'aclParticipant' . $studyGroup
        );    
    }

    /** 
     * Check whether an appointment-based survey session can be started or continued right now.
     * Applies time-based rules.
     * @param patient Patient to check
     * @return The ID of the appointment, or NULL if no session is allowed per time=based rules
     */
    function appt_for_session_init_or_resume($patient) {

//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patient), here's patient: " . print_r($patient, true), LOG_DEBUG);


//        $this->log(__CLASS__ . "." . __FUNCTION__ . "($patient_id), returning null because no appts matched time criteria", LOG_DEBUG);
        return;

    } // function appt_for_session_init_or_resume($patient) {

    /** 
     * Given a patient, determine what survey session to administer.
     * See also: DhairAuthComponent->filterProjectsStatesByRole, which should be called after this.
     * @param $patient a cakephp data arrayy
     */
     function analyzeCurrentApptAndSessionState($patient) {
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(); here's the patient arg:" . print_r($patient, true), LOG_DEBUG);
        if (empty($patient)) {   // not a patient
//            $this->log( __CLASS__ .".". __FUNCTION__ . "(); patient empty so returning NULL", LOG_DEBUG);
            return;
        }
            
        $patientId = $patient['Patient']['id'];
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); for patient $patientId, stack trace: " . Debugger::trace(), LOG_DEBUG);

        $thisProject = ClassRegistry::init('Project');
        $projects = $thisProject->find('all', array('recursive' => -1));

        foreach ($projects as $project){

            $projectId = $project['Project']['id'];
            $this->projectsStates[$projectId]
                = new PatientProjectState($patientId, $project);

            // eg eligibility_session_rules, simple_session_rules, interval_based_session_rules
            if (!isset($project['Project']['session_rules_fxn'])) 
                $project['Project']['session_rules_fxn'] = 'simple';
            call_user_func(array($this, $project['Project']['session_rules_fxn']
                    . '_session_rules'), $patient, $project); 

            if ($project['Project']['elective_sessions']
                && !$this->projectsStates[$projectId]->apptForResumableSession
                && !$this->projectsStates[$projectId]->apptForNewSession){
//                $this->log(__CLASS__ . "." . __FUNCTION__ . "(); !apptForResumableSession && !apptForNewSession, but ELECTIVE_SESSIONS", LOG_DEBUG);

                $lastSession = $this->lastSession($patientId, $projectId);

                if ($lastSession 
                    && $lastSession['SurveySession']['type'] == ELECTIVE){

                    if ($lastSession['SurveySession']['finished'] != 1)
                        $this->projectsStates[$projectId]->resumableNonApptSession = $lastSession;
                    else 
                        $this->projectsStates[$projectId]->finishedNonApptSession = $lastSession;
                }
                else{
                    $this->projectsStates[$projectId]->initableNonApptSessionType = ELECTIVE; 
                }
            }

            $linkSettings = array('controller' => 'surveys');

            if ($this->projectsStates[$projectId]->resumableNonApptSession){
                $linkSettings += array(
                    'action' => 'restart',
                    $this->projectsStates[$projectId]->resumableNonApptSession['SurveySession']['id']
                );
            }
            else if ($this->projectsStates[$projectId]->initableNonApptSessionType 
                    || $this->projectsStates[$projectId]->apptForNewSession){
                $linkSettings += array(
                    'action' => 'new_session',
                    $projectId
                );
            }
            else if ($this->projectsStates[$projectId]->apptForResumableSession){
                $linkSettings += array(
                    'action' => 'restart',
                    $this->projectsStates[$projectId]->apptForResumableSession['SurveySession']['id']
                );
            }
            $this->projectsStates[$projectId]->sessionLink = $linkSettings;

        }// foreach ($projects as $project){
        //$this->log(__CLASS__ ."." . __FUNCTION__ . "(); projects:" . print_r($projects, true), LOG_DEBUG);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); projectsStates:" . print_r($this->projectsStates, true), LOG_DEBUG);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(); returning at bottom", LOG_DEBUG);
    }// function analyzeCurrentApptAndSessionState($patient) {

    

    /**
     *
     */
    function simple_session_rules($patient, $project){

        $projectId = $project['Project']['id'];

        foreach ($patient['SurveySession'] as $session){
            if ($session['project_id'] == $projectId){

                if ($session['finished'] != 1){
                    $this->projectsStates[$projectId]->resumableNonApptSession
                        = array('SurveySession' => $session); 
                }
                else {
                    $this->projectsStates[$projectId]->finishedNonApptSession
                        = array('SurveySession' => $session); 
                }
                return; 
            }
        }
        $this->projectsStates[$projectId]->initableNonApptSessionType 
            = NON_APPT; 
    }// function simple_session_rules(){


    function elective_session_rules($patient, $project){
        $states = $this->projectsStates[$project['Project']['id']];

        // Reverse sessions to get descending reportable_datetime
        $sessions = array_reverse(Hash::extract(
            $patient,
            "SurveySession.{n}[project_id={$project['Project']['id']}]"
        ));

        foreach($sessions as $session){
            // Set the latest finished session only
            if ($session['finished'] and !$states->finishedNonApptSession)
                $states->finishedNonApptSession = array('SurveySession' => $session);
            elseif (!$session['finished']){
                $states->resumableNonApptSession = array('SurveySession' => $session);
                return;
            }
        }


        $states->initableNonApptSessionType = ELECTIVE;
        return;
    }

    function link_session_rules($patient, $project){
        $this->elective_session_rules($patient, $project);
        $states = $this->projectsStates[$project['Project']['id']];
        if (
            $states->initableNonApptSessionType and
            $states->finishedNonApptSession
        ){
            // Todo: lookup timezone from session info
            $timezone = new DateTimeZone($this->getTimeZone($patient));

            $finished = new DateTime(
                $states->finishedNonApptSession['SurveySession']['reportable_datetime'],
                $timezone
          );
        }
        $linkSessionRules = Configure::read('link_session_rules');  
        $linkSessionRule = $linkSessionRules[$project['Project']['id']];
        $pass = null;
        if (array_key_exists($linkSessionRule['requestPassKey'], $_REQUEST)) {
          $pass = $_REQUEST[$linkSessionRule['requestPassKey']];
        } 
//      $this->log(__CLASS__ . "." . __FUNCTION__ . " checking : " . $pass . " against configured passPhrase: " . $linkSessionRule['passPhrase'] , LOG_DEBUG);

        //echo print_r($patient, true);
        // traverse the condition tree structure
        $conditions = array_key_exists('conditions',$linkSessionRule) ? $linkSessionRule['conditions'] : null;
        $passedPatientConditions = $this->link_session_rules_helper($conditions, $patient);

        $requestParams = Router::getRequest()->params;
        if ( ( empty($pass) || // no passphrase in the $_REQUEST
             $pass != $linkSessionRule['passPhrase'] || // mismatch in passphrase
             !$passedPatientConditions  // $patient does not match the criteria
             ) && (// hide ONLY when on the index page
              $requestParams['controller'] == 'users' &&
              $requestParams['action'] == 'index'
             )) {
          $states->initableNonApptSessionType = null;
          return;
        }
        return;
    }

    // This helper recurses through the conditions for link_session_rules
    // and matches them against the $patient value to make sure the values match
    // false is returned if there is a mismatch or if the key doesn't exist
    // e.g. the condition for ['Patient']['study_group'] would return false if
    // either study_group doesn't match the config value (e.g. Treatment)
    // or if there is no such key
    function link_session_rules_helper($condition, $current) {
      if (empty($condition)) return true;
      if (!is_array($condition)) {
        //echo "checking $current vs $condition<br/>";
        return $current == $condition;
      } else {
        $returnVal = true;
        foreach ($condition as $key => $val) {
          if (array_key_exists($key, $current)) { 
            //echo "recursing onto $key. <br/>";
            $returnVal &= $this->link_session_rules_helper($val, $current[$key]);
          } else {
            $this->log(__CLASS__ . "." . __FUNCTION__ . " condition check for : " . $key . " key not found. " , LOG_ERROR);
            $returnVal = false;
          }
        }
        return $returnVal;
      }
    }

    function demo_session_rules($patient, $project){

        $this->elective_session_rules($patient, $project);
        $states = $this->projectsStates[$project['Project']['id']];
        if (
            $states->initableNonApptSessionType and
            $states->finishedNonApptSession
        ){
            // Todo: lookup timezone from session info
            $timezone = new DateTimeZone($this->getTimeZone($patient));

            $finished = new DateTime(
                $states->finishedNonApptSession['SurveySession']['reportable_datetime'],
                $timezone
            );

            // Only allow new session if no previous one has been done within the past day
            // commented out
            //if ($finished->diff(new DateTime())->days < 1)
                //$states->initableNonApptSessionType = null;
        }
    }

    function daily_resumable_until_session_rules($patient, $project){
        $this->elective_session_rules($patient, $project);
        $states = $this->projectsStates[$project['Project']['id']];
        if (
            $states->initableNonApptSessionType and
            $states->finishedNonApptSession
        ){
            $timezone = new DateTimeZone($this->getTimeZone($patient));

            $finished = new DateTime(
                $states->finishedNonApptSession['SurveySession']['reportable_datetime'],
                $timezone
            );

            // Only allow new session if no previous one has been done within the past day
            if ($finished->diff(new DateTime())->days < 1)
                $states->initableNonApptSessionType = null;

        }
        if (
            $states->resumableNonApptSession
        ) {
            // if there is a resumable session, we will check it against the resumable_until rule

            $timezone = new DateTimeZone($this->getTimeZone($patient));
            $resumable_started = new DateTime(
                $states->resumableNonApptSession['SurveySession']['started'],
                $timezone);
            $resumable_started = $resumable_started->getTimestamp();
            $resumable_until = $project['Project']['resumable_until'];
            sscanf($resumable_until, "%d:%d:%d", $hours, $minutes, $seconds);
            $seconds = $seconds + ($minutes * 60) + ($hours * 60 * 60);
            $resumable_expired = $resumable_started + $seconds;

            // Only allow resumable session if within 'resumable_until' 
            //  else, replace this resumable with a new session option (in this condition,
            //  we know that they have not completed anything within 30 days, so the 'daily'
            //  rule does not apply.
            $nowTs = new DateTime("now", $timezone);
            if ($nowTs->getTimestamp() > $resumable_expired) {
                $states->resumableNonApptSession = null;
                $states->initableNonApptSessionType = ELECTIVE; 
            }
        }
    }

    function daily_session_rules($patient, $project){

        $this->elective_session_rules($patient, $project);
        $states = $this->projectsStates[$project['Project']['id']];
        if (
            $states->initableNonApptSessionType and
            $states->finishedNonApptSession
        ){
            // Todo: lookup timezone from session info
            $timezone = new DateTimeZone($this->getTimeZone($patient));

            $finished = new DateTime(
                $states->finishedNonApptSession['SurveySession']['reportable_datetime'],
                $timezone
            );

            // Only allow new session if no previous one has been done within the past day
            if ($finished->diff(new DateTime())->days < 1)
                $states->initableNonApptSessionType = null;
        }
    }

    /**
     *
     */
    function eligibility_session_rules($patient, $project){
        if ($patient['Patient']['eligible_flag'] == null) {
            $this->simple_session_rules($patient, $project);
        }
    }// function eligibilitySessionRules(){

    /**
     *
     */
    function patient_designated_appts_session_rules($patient, $project){
        $this->appointments_relevant_session_rules($patient, $project);
    }// function eligibilitySessionRules(){

    function randomized_session_rules($patient, $project){
        if ($patient['Patient']['eligible_flag'] !== null)
            $this->simple_session_rules($patient, $project);
    }

    /**
     *
     */
    function appointments_relevant_session_rules($patient, $project){
        $projectId = $project['Project']['id'];
        $patientId = $patient['Patient']['id'];
        $timezone = $this->User->getTimeZone($patientId);
        // look at each appt to see whether its datetime permits survey now
        foreach($patient["Appointment"] as $appt) {
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(" . $patientId . "); here are some vars: MIN_SECONDS_BETWEEN_APPTS:" . MIN_SECONDS_BETWEEN_APPTS . ". looking at appointment " . $appt['id'], LOG_DEBUG);

            $apptTime = $appt['datetime'];
            $secondsUntilAppt = 
                $this->secondsAfterNow($apptTime, $timezone);
            $session = null;

            // find this appt's session, if any
            foreach ($patient['SurveySession'] as $aSession){
                if ($aSession['appointment_id'] == $appt['id']){
                    $session = $aSession;
                    break;
                }
            }
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(" . $patientId . "); vars for the appt in considerationi (" . $appt['id'] . "): apptTime:" . $apptTime . "; secondsUntilAppt:" . $secondsUntilAppt . ". timezone:" . $timezone, LOG_DEBUG);
 
            if ((($secondsUntilAppt < MIN_SECONDS_BETWEEN_APPTS) 
                 && $this->currentlyBeforeSomeTime(
                        $apptTime, $timezone,
                        $project['Project']['initializable_until']))
                ||
                (($secondsUntilAppt < MIN_SECONDS_BETWEEN_APPTS)
                 && $this->currentlyBeforeSomeTime(
                        $apptTime, $timezone,
                        $project['Project']['resumable_until'])))
            {
                // this appt meets time-based criteria
                //$this->current_session_appt = $appt; 
                //$timezone = parent::getTimeZone($patientId);
                $apptDate = $this->gmtToLocal($appt['datetime'], 
                                        $timezone, false, 'F jS'); 
                $appt['Appointment']['dateUserTZ'] = $apptDate;

                if ($session) {
                    if ($session['finished'] != 1){
                        $this->projectsStates[$projectId]->apptForResumableSession['Appointment'] 
                            = $appt;
                        $this->projectsStates[$projectId]->apptForResumableSession['SurveySession'] 
                            = $session;
                    }
                    else{ 
                        $this->projectsStates[$projectId]->apptForFinishedSession['Appointment'] 
                            = $appt;
                        $this->projectsStates[$projectId]->apptForFinishedSession['SurveySession'] 
                            = $session;
                    }
                }
                else $this->projectsStates[$projectId]->apptForNewSession['Appointment'] = $appt;
                break; // appt iteration
                //return;
            }
            // elseif appt in the past but w/in MIN_SECONDS_BETWEEN_APPTS and finished, set $this->apptForFinishedSession = appt 
            elseif($secondsUntilAppt > (0 - MIN_SECONDS_BETWEEN_APPTS)
                    && $session){
                if ($session['finished'] == 1){
                    $this->projectsStates[$projectId]->apptForFinishedSession['Appointment'] = $appt;
                    $this->projectsStates[$projectId]->apptForFinishedSession['SurveySession'] 
                        = $session;
                }
                else {
                    $this->projectsStates[$projectId]->apptForResumableSession['Appointment'] 
                        = $appt;
                    $this->projectsStates[$projectId]->apptForResumableSession['SurveySession'] 
                        = $session;
                } 
                break; // appt iteration
                //return;
            } 

        } // foreach($patient["Appointment"] as $appt) {

    }// function appointments_relevant_session_rules

    /**
     *
     */
    function interval_based_session_rules($patient, $project){
        $projectId = $project['Project']['id'];

        $this->_initializeIntervalSessions($patient);

        $this->projectsStates[$projectId]->currentSession = $this->currentWindow;
        $this->projectsStates[$projectId]->nextSession = $this->nextWindow;

        if ($this->currentWindow)
            $this->projectsStates[$projectId]->initableNonApptSessionType = $this->currentWindow['type'];

        $lastSession = $this->lastSession($patient['Patient']['id'], $projectId);
        // $this->log(__CLASS__ . '.' . __FUNCTION__ . '(); here\'s the lastSession:' . print_r($lastSession, true), LOG_DEBUG);

        if ($lastSession){
            if (!$lastSession['SurveySession']['finished'])
                $this->projectsStates[$projectId]->resumableNonApptSession = $lastSession;
            else
                $this->projectsStates[$projectId]->finishedNonApptSession = $lastSession;
        }
    }// function baselineClinicalRules(){

    /**
     * @param $patient a cakephp data arrayy
    */
    function getCurrentIntervalSessionType($patient){
        return null;
    }

    /*
     *
     */
    function lastSession($patientId, $projectId = null) {

        $conditions = array("SurveySession.patient_id" => $patientId);
        if (isset($projectId)){
            $conditions['SurveySession.project_id'] = $projectId;
        } 

        $session = $this->SurveySession->find('first',
                    array(
                        'conditions' => $conditions,
                        'recursive' => -1,
                        'order' => "SurveySession.id DESC"));
        //$this->log("lastSession : " . print_r($session, true), LOG_DEBUG);

        return $session;
    }// function lastSession($id) {


    /** 
      * Try to find a patient with a given MRN at a particular site
      * @param mrn MRN
      * @param clinicId Id of the clinic (used to determine the site)
      * @return A patient that matches, or null if there are none
     */
    function findPatient($mrn, $clinicId) {
        $candidates = $this->findAllByMrn($mrn);

        if (!empty($candidates)) {
            $site1 = $this->User->Clinic->findById($clinicId);

            foreach($candidates as $candidate) {
                $site2 = $this->User->Clinic->findById(
                                            $candidate['User']['clinic_id']);
	            if ($site1['Clinic']['site_id'] == $site2['Clinic']['site_id'])
                {
    	            return $candidate;
                }
            }
        }

        return null;
    }
    
    /** 
      * Find a patient by id, if that staff member has access to this patient.
      * By default, patients limited to the staff member's clinic.
      * @param patientId id of the patient 
      * @param requester - user record for requester
      * @param allPatients bool If true, no site or clinic limit applied
      * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
      * @return A patient that matches, or null if there are none
     */

    function findPatientById($patientId, $requester, $allPatients = false, $sameSite = false) {
        $patient = $this->find('first', array(
                'conditions' => array(
                    'Patient.id' => $patientId
                ),
                'recursive' => 1));

        if (!empty($patient)) {
            if ($allPatients) 
                return $patient;
            elseif ($patient['User']['clinic_id'] == $requester['User']['clinic_id'])
                return $patient;
            elseif ($sameSite) {
                $siteRequester = $this->User->Clinic->findById($requester['User']['clinic_id']);
                $sitePatient = $this->User->Clinic->findById(
                                            $patient['User']['clinic_id']);
	            if ($siteRequester['Clinic']['site_id'] == $sitePatient['Clinic']['site_id'])
                    return $patient;
            }
        }

        return null;
    }
    
    /**
     * Get a query to find all patients a particular staff member can see.
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param noTest True if test patients should be excluded
     * @param count true if a count should be included
     * @return an array ($select, $join, $whereClause)
     */
    function getAccessiblePatientsQuery3($id, $allPatients, $sameSite, 
                                         $noTest = false, $count = false) 
    {
        $id = intval($id);
        $countTxt = '';
        if ($count){$countTxt = "COUNT(DISTINCT(Patient.id)) AS count, ";}
	    $select = 'SELECT '.$countTxt.'User.first_name, User.last_name, 
          Patient.consent_status, Patient.id, Clinic.name, Patient.MRN,
          Patient.check_again_date, Patient.off_study_status,
          Patient.off_study_timestamp, Patient.off_study_reason,
          Patient.consenter_id, Patient.clinical_service,
          Patient.birthdate, User.last4ssn,
          PatientExtension.*,
          User.email, User.username, Patient.phone1, Patient.phone2';
	    $fromAndJoin = 
            ' FROM patients AS Patient' 
            . ' LEFT JOIN patient_extensions AS PatientExtension ON (PatientExtension.patient_id = Patient.id)'
            . ' LEFT JOIN users AS User ON (User.id = Patient.id)' 
            . ' LEFT JOIN clinics AS Clinic ON ( User.clinic_id = Clinic.id)';
	    $whereClause = 
            ' WHERE 1';

        if ($noTest) {
	        $whereClause .= ' AND Patient.test_flag <> 1';
        }

        // above query works for allPatients as is
        if ($allPatients) {
        } else if ($sameSite) {
	        // sameSite needs to add restriction that site ids match
            $fromAndJoin .= ' JOIN clinics AS clinics2, users AS users2';
            $whereClause .= " AND clinics2.id = users2.clinic_id
                            AND Clinic.site_id = clinics2.site_id
                            AND users2.id = $id";
        } else {
	    // everyone else needs to add restriction that clinic ids match
            $fromAndJoin .= ' JOIN users AS users2';
            $whereClause .= " AND User.clinic_id = users2.clinic_id
	                      AND users2.id = $id";
        }

	    return array($select, $fromAndJoin, $whereClause);
    } // function getAccessiblePatientsQuery3

    /**
     * Get the first part (select + join) and second part (where clause) 
     * of the query to find all patients a particular staff member can
     * see
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param noTest True if test patients should not be included
     * @param count true if a count should be included
     * @return an array ($selectAndJoin, $whereClause)
     */
    function getAccessiblePatientsQuery($id, $allPatients, $sameSite, 
                                        $noTest = false, $count = false) 
    {
        $query = $this->getAccessiblePatientsQuery3($id, $allPatients, 
	                                            $sameSite, $noTest, 
                                                $count);
        return array($query[0] . $query[1], $query[2]);
    }

    /**
     * Return all patients a particular staff member can see (without patient/viewAll-related data)
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @return all patients the staff member can see, in an array
     */
    function getAccessiblePatients($id, $allPatients, $sameSite) {

        $query = join($this->getAccessiblePatientsQuery(
            $id,
            $allPatients,
            $sameSite
        ));
//        $this->log(__CLASS__ . '.' . __FUNCTION__ . "(), returning query: $query", LOG_DEBUG);

        return $this->query($query);
    }

    /**
     * Return all patients a particular staff member can see
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @return all patients the staff member can see, in an array
     */
    function findAccessiblePatients($id, $allPatients, $sameSite) {
        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $patients = $this->query($query[0] . $query[1]);

        $patients = $this->afterFind($patients);

        $thisProject = ClassRegistry::init('Project');
        $allProjects = 
            $thisProject->find('all', 
                            array('recursive' => -1));
        // re-key to Project.id
        $allProjects = Hash::combine($allProjects, '{n}.Project.id', '{n}');
//        $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), allProjects: ' . print_r($allProjects, true), LOG_DEBUG);

        $currentGmt = $this->currentGmt();

        // retrieve appts and add to data model
        foreach ($patients as &$patient){

            $appt = $this->Appointment->find('first', array(
                'conditions' => array(
                    'Appointment.patient_id' => $patient['Patient']['id'],
                    'Appointment.datetime > "' . $currentGmt . '"'
                ),
                'order' => 'Appointment.datetime DESC',
                'recursive' => -1));
            if ($appt) {
                $patient['Patient']['next_appt_dt'] 
                            = $appt['Appointment']['datetime'];
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found upcoming appt for patient id ' . $patient['Patient']['id'] . ': ' . print_r($appt, true), LOG_DEBUG);
            }
            else $patient['Patient']['next_appt_dt'] = '';
            
            $latestAppt = $this->Appointment->find('first', array(
                'conditions' => array(
                    'Appointment.patient_id' => $patient['Patient']['id']
                ),
                'order' => 'Appointment.datetime DESC',
                'recursive' => -1));
            if ($latestAppt) {
                $patient['Patient']['latest_appt_dt'] 
                            = $latestAppt['Appointment']['datetime'];
            }
            else $patient['Patient']['latest_appt_dt'] = '';            

            $patient['SurveySession'] = array();
            $patient['SurveySession']['last_session_proj'] = '--';
            $patient['SurveySession']['last_session_date'] = '--';
            $patient['SurveySession']['last_session_status'] = '--';

            $lastSession = $this->SurveySession->find('first', array(
                'conditions' => array(
                    'SurveySession.patient_id' => $patient['Patient']['id']
                ),
                'order' => 'SurveySession.id DESC',
                'recursive' => -1
            ));
//            $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), lastSession for patient id ' . $patient['Patient']['id'] . ': ' . print_r($lastSession, true), LOG_DEBUG);

            if (!$lastSession)
                continue;

            $lastSession = $lastSession['SurveySession'];

            $lastSessionsProjectId = $lastSession['project_id'];
            if (!isset($allProjects[$lastSessionsProjectId]))
                continue; // on the rare occasion that this project id no longer exists 
//            $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), lastSessionsProjectId for patient id ' . $patient['Patient']['id'] . ': ' . print_r($lastSessionsProjectId, true), LOG_DEBUG);

            $patient['SurveySession']['last_session_proj'] 
                = $allProjects[$lastSessionsProjectId]['Project']['Title'];
            $patient['SurveySession']['last_session_date'] 
                = $lastSession['reportable_datetime'];
            
            if ($lastSession['finished'] == 1)
                $patient['SurveySession']['last_session_status'] = 'finished';
            elseif ($lastSession['partial_finalization'] == 1)
                $patient['SurveySession']['last_session_status'] 
                    = 'partially finished';
            else
                $patient['SurveySession']['last_session_status'] = 'in process';

        }// foreach ($patients as &$patient){

        return $patients;
        //return $this->afterFind($patients); // not needed for the fields the calling fxn cares about
    }// function findAccessiblePatients()

    /**
      * Check that a date or datetime is in the proper 
      * format
      * @param date date or datetime
      * @return whether the date/datetime is in the proper format
      */
    private function checkDate($date) {
        // date/time
	$datetimePattern1 = '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/';
        // date
	$datetimePattern2 = '/^\d\d\d\d-\d\d-\d\d$/';
        return (preg_match($datetimePattern1, $date) ||
                preg_match($datetimePattern2, $date));
    }

    /**
      * Check that a start and end dates or datetimes are in the proper 
      * format
      * @param startdate Start date or datetime
      * @param enddate End date or date/time
      * @return whether the two parameters are in the proper format
      */
    private function checkDates($startdate, $enddate) {
        return $this->checkDate($startdate) && $this->checkDate($enddate);
    }

    /**
     * Return all patients a particular staff member can see that
     * have a check again date in a particular date range.
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param startdate Start of the datetime range; optional.
     * @param enddate End of the datetime range; optional.
     * @return all patients the staff member can see whose check-again date
     *    is in the datetime range, in an array
     */
    function findCheckAgains($id, $allPatients, $sameSite, $startdate = null,
                             $enddate = null) 
    {

        $checkAgainWhere = " AND Patient.no_more_check_agains != 1";

        if ($startdate && $enddate){

            if (!$this->checkDates($startdate, $enddate)) {
	            $this->log(
	            "findCheckAgains: bad date parameters $startdate $enddate");
                return array();
            }

            $checkAgainWhere .= " AND Patient.check_again_date >= '$startdate'
	                     AND Patient.check_again_date <= '$enddate'";
        }

        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $orderBy = " ORDER BY Patient.check_again_date";
	                   
        $results = $this->query($query[0] . $query[1] . $checkAgainWhere .
	                        $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Return all participants a particular staff member can see that
     * have no check again date after a particular date
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param startdate Start of the datetime range
     * @return all patients the staff member can see who don't have a 
     *    check-again date after the startdate, in an array
     */
    function findNoCheckAgain($id, $allPatients, $sameSite, $startdate) {
        if (!$this->checkDate($startdate)) {
	    $this->log(
	        "findNoCheckAgain: bad date parameter $startdate");
            return array();
        }

        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $noCheckAgainWhere = " AND (Patient.check_again_date < '$startdate'
	                            OR Patient.check_again_date is null)
			       AND Patient.study_group is not null 
			       AND Patient.no_more_check_agains <> 1";
        $orderBy = " ORDER BY User.last_name";
	                   
        $results = $this->query($query[0] . $query[1] . $noCheckAgainWhere .
	                        $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Return all patients a particular staff member can see that
     * expressed interest in participating, but who have not yet
     * consented or declined
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @return all patients the staff member can see who have expressed interest in participating, but who have not yet consented or declined, in an array
     */
    function findInterested($id, $allPatients, $sameSite) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $interestedWhere = " AND Patient.study_participation_flag = 1
	                     AND Patient.consent_status= 'pre-consent'";
	                   
        $results = $this->query($query[0] . $query[1] . $interestedWhere);
        return $this->afterFind($results);
    }

    /**
     * Return all off-study patients a particular staff member can see 
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @return all off-study patients the staff member can see 
     */
    function findOffStudy($id, $allPatients, $sameSite) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite, true);
        $offStudyWhere = " AND Patient.off_study_status is not NULL";
	                   
        $results = $this->query($query[0] . $query[1] . $offStudyWhere);
        return $this->afterFind($results);
    }

    /**
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @return array of off-study reason counts 
     */
    function countOffStudyEnums($id, $allPatients, $sameSite) 
    {
        $offStudyEnums = $this->getEnumValues('off_study_status');

        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite, true, true);
        
        $offStudyEnumsCount = array();
        foreach($offStudyEnums as $enum){
            $offStudyWhere = " AND Patient.off_study_status = '" . $enum . 
                "' GROUP BY Patient.off_study_status";

            $results = $this->query($query[0] . $query[1] . $offStudyWhere);
       
            if (isset($results[0][0]['count']))
                $offStudyEnumsCount[$enum] = $results[0][0]['count'];
 
            //$this->log('results = ' . print_r($results, true), LOG_DEBUG);

        }
        
        
        return $offStudyEnumsCount;

        /**
        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite, true);
        $offStudyWhere = " AND Patient.off_study_status is not NULL";
	                   
        $results = $this->query($query[0] . $query[1] . $offStudyWhere);
        return $this->afterFind($results);
        */
    }

    /**
     * Return all patients a particular staff member can see that
     * match a search criteria
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param searchParams patient fields to match
     * @return all patients the staff member can see who match the search criteria
     */
    function search($id, $allPatients, $sameSite, $searchParams) 
    {
        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $searchWhere = '';
       
        foreach($searchParams['User'] as $paramName => $paramVal){
            if ($paramVal != ''){
                $searchWhere .= " AND User.$paramName LIKE '%$paramVal%'";
            }
        }
        foreach($searchParams['Patient'] as $paramName => $paramVal){
            if ($paramVal != ''){
                $searchWhere .= " AND Patient.$paramName LIKE '%$paramVal%'";
            }
        }
        $phone = $searchParams['Phone']['phone'];
        if ($phone != ''){
            $searchWhere .= " AND (Patient.phone1 LIKE '%$phone%' OR "
                        . "Patient.phone2 LIKE '%$phone%')";
        }
	                   
        if ($searchWhere == ''){
            return array();
        }
        $results = $this->query($query[0] . $query[1] . $searchWhere);
        return $this->afterFind($results);
    }

    /**
     * Get timezone for a patient during a save operation
     * @param data passed in data
     */
    function getTimeZone($data) {
        /* try to get timezone based on clinic_id in the data 
	   (in case it was just changed) */
	if (!empty($data['User']) && !empty($data['User']['clinic_id'])) {
            $timezone = $this->User->Clinic->getTimeZone(
	        $data['User']['clinic_id']);
        }
	
	/* if that doesn't work, get it from the clinic_id in the db */
	if (empty($timezone)) {
            //$timezone = $this->User->getTimeZone($data['Patient']['id']);
            $timezone = parent::getTimeZone($data['Patient']['id']);
        }

	return $timezone;
    }


    /**
    *  Add Clinic data to find results, since Clinic is associated with User, not Patient
    */
    function addClinicToFindResult(&$data){

        $clinic = $this->User->Clinic->find('first',
                    array('recursive' => -1,
                        'conditions' =>
                        array('Clinic.id' => 
                            $data['User']['clinic_id'])));

        $data['Clinic'] = $clinic['Clinic'];
    }


    function updateSecretPhrase($patient, $phrase) {
        $phrase = strip_tags($phrase);
        $patient = $this->getRecord($patient);
        $patient["Patient"]["secret_phrase"] = $phrase;
        $this->save($patient);
        return $patient;
    }

    /*
     * Callback function to get a site id from a row of a db query
     * @param row Array containing the row
     * @return The site id from the row
     */
    private function getSiteId($row) {
        return $row['sites']['id'];
    }

    /**
     * Callback function to get site data from a row of a db query
     * @param row Array containing the row
     * @return The entire row
     */
    private function getSiteData($row) {
        return $row;
    }


    /** Value for patientType parameter indicating we only want 
        non-participants */
    const PATIENT = 'Patient';

    /** Value for patientType parameter indicating we only want participants */
    const PARTICIPANT = 'Participant';

    /** Value for the off_study_status field */
    const ON_STUDY = 'On study';

    /** Value for the off_study_status field */
    const COMPLETED = 'Completed all study requirements';

    /** Value for the off_study_status field */
    const OSINELIGIBLE = 'Consented but ineligible';

    /** Value for the off_study_status field */
    const WITHDRAWN = 'Voluntary withdrawal';

    /** Value for the off_study_status field */
    const LOST = 'Lost to follow-up';

    /** Value for the off_study_status field */
    const ADVERSE_EVENTS = 'Adverse events';

    /** Value for the off_study_status field */
    const OTHER = 'Other';

    /** 
      Return the allowed values for the off_study_status field 
      This should be a class constant
     */
    function getOffStudyStatuses() {
       return array(self::ON_STUDY => self::ON_STUDY,
                    self::COMPLETED => self::COMPLETED,
                    self::OSINELIGIBLE => self::OSINELIGIBLE,
                    self::WITHDRAWN => self::WITHDRAWN,
                    self::LOST => self::LOST,
                    self::ADVERSE_EVENTS => self::ADVERSE_EVENTS,
                    self::OTHER => self::OTHER);
    }

    /**
     * Get a basic count query for one of the accrual functions
     * @param startdate Start of the month to check, as a Unix timestamp
     * @param siteId Id of the site
     * @param table Name of the table whose foreign key is the patient_id
     * @param timeField Name of the field with the timestamp we need to check
     */
    private function basicCountQuery($startdate, $siteId, $table, $timeField) {
        $startString = gmdate(MYSQL_DATETIME_FORMAT, $startdate);
        $oneMonthLater = gmdate(MYSQL_DATETIME_FORMAT, 
	                        strtotime('+1 month', $startdate));
        return "SELECT count(DISTINCT(patients.id)) AS count
                FROM patients
                JOIN users, clinics " . 
               // join with $table unless it is 'patients'
               ($table == 'patients' ? 
                "WHERE " : 
                ", $table WHERE patients.id = $table.patient_id AND ") .
	        "   patients.id = users.id
	        AND users.clinic_id = clinics.id
	        AND clinics.site_id = $siteId
		AND patients.test_flag <> 1
	        AND $table.$timeField >= '$startString'
	        AND $table.$timeField < '$oneMonthLater'";
    }

    /**
     * Get the number of patients who went off-study during a given month at a 
     *   given site
     * @param startdate Start of the month to check, as a Unix timestamp
     * @param siteId Id of the site
     * @return the number of patients who went off-study
     */
    function countOffStudy($startdate, $siteId) {
        $query = $this->basicCountQuery($startdate, $siteId, 'patients', 
                                        'off_study_timestamp');
        $query .= " AND patients.off_study_status IS NOT NULL 
	            AND patients.off_study_status <> ''";
        $patients = $this->query($query);
        return $patients[0][0]['count'];
    }

    /**
     * Return all patients a particular staff member can see that are
     * consented but whose consent has not been checked (verified)
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @return all patients the staff member can see that are consented
     *    but whose consent has not been verified
     */
    function findUncheckedConsents($id, $allPatients) {
        $query = $this->getAccessiblePatientsQuery3($id, $allPatients, true);
        $select = ", Patient.consent_checked, Patient.hipaa_consent_checked, 
                   Clinic.site_id";
        $hipaaSiteClause = '';
        if (defined('HIPAA_CONSENT_SITE_ID')){
            $hipaaSiteClause = " OR (Patient.hipaa_consent_checked != 1" . 
                    " AND Clinic.site_id = " . HIPAA_CONSENT_SITE_ID . ")";
        }
        $uncheckedConsents = " AND Patient.consent_status = '" . 
	                           self::CONSENTED . "'
	                     AND (Patient.consent_checked != 1" 
                             . $hipaaSiteClause . ")";
        $orderBy = " ORDER BY User.last_name";
	                   
        $results = $this->query($query[0] . $select . $query[1] . $query[2] .
                                $uncheckedConsents . $orderBy);
        return $this->afterFind($results);
    }

    // change T-times to GMT before save
    function beforeSave($options = Array()) {
//        $this->log(__FUNCTION__.'(), this->data: '.print_r($this->data, true), LOG_DEBUG);
        if (!empty($this->data['Patient']['off_study_timestamp'])) {
            $timezone = $this->getTimeZone($this->data);
            $this->data['Patient']['off_study_timestamp'] = 
	        $this->localToGmt(
		    $this->data['Patient']['off_study_timestamp'], $timezone);
        }

        if (!empty($this->data['Patient']['consent_status'])
            && $this->data['Patient']['consent_status'] == self::CONSENTED) {
            $this->data['Patient']['off_study_status'] = self::ON_STUDY; 
        }

        if (!empty($this->data['Patient']['gender'])){
            $this->data['Patient']['gender']
                = strToLower($this->data['Patient']['gender']);
        }

        // Check if we have PatientExtension data
        if (isset($this->data['PatientExtension'])){

            // Get the PatientExtension subset of data from Patient
            $intersect = array_intersect_key(
                $this->data['Patient'],
                $this->data['PatientExtension']
            );

            // Add back in patient_id that would be missing from intersect
            $intersect['patient_id'] = $this->data['Patient']['id'];

            ksort($intersect);
            ksort($this->data['PatientExtension']);

            // Check if the data we previously added to the Patient array during Patient->afterFind() has been changed
            if ($intersect != $this->data['PatientExtension'])
                $this->PatientExtension->save($intersect);
        }
        else{
            $patient_ext_keys =  array_diff(
                array_keys($this->data['Patient']),
                // It may make sense to define and use $this->_schema instead
                array_keys($this->schema())
            );
            if ($patient_ext_keys){
                $patient_ext = array_intersect_key(
                    $this->data['Patient'],
                    array_flip($patient_ext_keys)
                );
                $patient_ext['patient_id'] = $this->data['Patient']['id'];
                $this->PatientExtension->save($patient_ext);
            }
        }

        return true;
    }// function beforeSave

    // change T-times back to local time after save
    function afterSave($created) {

        if (!empty($this->data['Patient']['off_study_timestamp'])) {
            $timezone = $this->getTimeZone($this->data);
            $this->data['Patient']['off_study_timestamp'] = 
	        $this->gmtToLocal(
		    $this->data['Patient']['off_study_timestamp'], $timezone);
        }

        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), heres all session data before deleting the patient from it: ' . print_r($_SESSION, true), LOG_DEBUG);
        // Delete the patient session var so it's requeried at next request
        //unset($_SESSION['patient' . $this->data['Patient']['id']]);
        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), heres session data after deleting the patient from it: ' . print_r($_SESSION, true), LOG_DEBUG);

        // Ensure that user_acl_leafs table
        // matches study_group. 
        // null => aroPatient // This is an assumption
        // Treatment => aroTreatment
        // Control => aroControl
        if (!$created && array_key_exists("study_group",$this->data["Patient"])) {
          $acl = $this->User->UserAclLeaf->find('first', array(
                  'conditions' => array('UserAclLeaf.user_id' => $this->id,
                                        'UserAclLeaf.acl_alias' => array("aclPatient", 
                                                                         "aclParticipantControl", 
                                                                         "aclParticipantTreatment")),
                  'recursive' => -1
            ));

          // Corner case: If there is no prior acl, do nothing
          if (!empty($acl)) {
            $old_acl_alias = $acl["UserAclLeaf"]["acl_alias"];
            $new_acl_alias = "";
            if ($this->data["Patient"]["study_group"] == "") {
              $new_acl_alias = "aclPatient";
            } else {
              $new_acl_alias = "aclParticipant".$this->data["Patient"]["study_group"];
            }
            $this->User->swapUsersAclLeaf($this->id, $old_acl_alias, $new_acl_alias);
          }
        }

        return true;
    }// function afterSave($created) {

    /**
     *
     */
    function afterFind($results, $primary=false) {
        $results = parent::afterFind($results, $primary);
        if (array_key_exists(0, $results) and is_array($results[0])) {
            foreach ($results as $key => &$val) {
                // change T-times to local time after retrieved
                // If there is no patient id (e.g., find('count')), timezone conversion is irrelevant
                if (
                    !empty($val['Patient']) and
                    !empty($val['Patient']['id'])
                ){
                    $timezone = parent::getTimeZone($val['Patient']['id']);

                    if (
                        isset($val['Patient']['off_study_timestamp']) and
                        $val['Patient']['off_study_timestamp']
                    ){
                        $results[$key]['Patient']['off_study_timestamp'] = $this->gmtToLocal(
                            $val['Patient']['off_study_timestamp'],
                            $timezone
                        );
                    }
                    // ensures that timezone set if User.clinic_id is null
                    $results[$key]['Site']['timezone'] = $timezone;
                }
            }
        }
        if (in_array('tags', Configure::read('modelsInstallSpecific')))
            $results = $this->findTagsForPatients($results);
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning " . print_r($results, true), LOG_DEBUG);
        return $results;
    }//function afterFind


    /**
     *
     */
    function findTagsForPatients ($results){
        
        if (!in_array('tags', Configure::read('modelsInstallSpecific'))) return $results;

        if (array_key_exists(0, $results) and is_array($results[0])) {
            foreach ($results as $key => &$val) {
                if (
                    !empty($val['Patient']) and
                    !empty($val['Patient']['id'])
                ){
                        $tags = $this->User->findTagsForUser($val['Patient']['id']);
                        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), heres tags:" . print_r($tags, true), LOG_DEBUG);
                        $results[$key]['Tag'] = $tags;
                }
            }
        }
        else {// results are from find 'first'
            $tags = $this->User->findTagsForUser($results['Patient']['id']);
            //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), heres tags:" . print_r($tags, true), LOG_DEBUG);
            $results['Tag'] = $tags;
        }

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning " . print_r($results, true), LOG_DEBUG);
        return $results;
    }


    /**
     *
     */
    function next_page_clicked($pageId, $patient){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning", LOG_DEBUG);
        return;
    }

    /*
     *
     */
    function swapPatientsAclLeaf($patientID, $fromLeaf, $toLeaf){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for patientID $patientID fromLeaf:$fromLeaf and toLeaf:$toLeaf", LOG_DEBUG);

        $this->User->swapUsersAclLeaf($patientID, $fromLeaf, $toLeaf);  
 
        App::import('Component', 'SessionComponent');
        $authd_user_id = SessionComponent::read('Auth.User.id');
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "() for patientID $patientID, and authd_user_id from session = " . $authd_user_id, LOG_DEBUG);
        if ($patientID == $authd_user_id){
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), before deleting CONTROLLERS_ACTIONS_AUTHZN, heres a check on it: " . SessionComponent::check(CONTROLLERS_ACTIONS_AUTHZN), LOG_DEBUG);
            SessionComponent::delete(CONTROLLERS_ACTIONS_AUTHZN);
//            $this->log(__CLASS__ . "." . __FUNCTION__ . "(), deleted CONTROLLERS_ACTIONS_AUTHZN, heres a check on it: " . SessionComponent::check(CONTROLLERS_ACTIONS_AUTHZN), LOG_DEBUG);
        }  
//        else $this->log(__CLASS__ . "." . __FUNCTION__ . "(), NOT deleting CONTROLLERS_ACTIONS_AUTHZN", LOG_DEBUG);

    }// function swapPatientsAclLeaf($patientID, $fromLeaf, $toLeaf){

    /**
     * List of email templates that staff should be able to send to the patient from patients/edit, filtered by the patient's current state.
     *  @param $userPatient User and Patient data
     *  @return array of permitted filenames from Views/Emails/html, keyed by friendly text.
     */
    function getEmailTemplateList($patient){
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), patient:" . print_r($patient, true), LOG_DEBUG);
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), patient projectsStates:" . print_r($this->projectsStates, true), LOG_DEBUG);

        $emailTemplates = array();

        if ($patient['User']['email'] &&
            (!defined('ELIGIBILITY_WORKFLOW') || !ELIGIBILITY_WORKFLOW)
                ||
            (defined('ELIGIBILITY_WORKFLOW') && ELIGIBILITY_WORKFLOW && 
                ($patient['Patient']['eligible_flag'] == '1'))
        ) {
            if (PATIENT_SELF_REGISTRATION){
                $sufficientDataForSelfReg = false;

                //PW_RESET_FIELDS_ORDERED are also required for self registration
                if (!is_null($PW_RESET_FIELDS_ORDERED = Configure::read('PW_RESET_FIELDS_ORDERED'))){
                    foreach($PW_RESET_FIELDS_ORDERED as $classAndField){
                        $pieces = explode('.', $classAndField);
                        $model = $pieces[0];
                        $field = $pieces[1];
                        if (!$patient[$model][$field]){
                            $sufficientDataForSelfReg = false;
                            break;
                        }
                        else
                            $sufficientDataForSelfReg = true;
                    }
                }
                elseif(
                    $patient['User']['email'] and
                    $patient['User']['first_name'] and
                    $patient['User']['last_name'] and
                    $patient['Patient']['birthdate'] 
                ){
                    $sufficientDataForSelfReg = true;
                }
                if ($sufficientDataForSelfReg){
                    $sendable = !$patient['User']['registered'];
                    $emailTemplates += array(
                        'self_register' => 
                            array('text' => 
                                    __('Registration Invitation for') 
                                    . ' ' . SHORT_TITLE,
                                'sendable' => $sendable),
                        'registration_reminder' => 
                            array('text' => 
                                    __('Registration Reminder for') 
                                    . ' ' . SHORT_TITLE,
                                'sendable' => $sendable),
                    );
                }
            } // if (PATIENT_SELF_REGISTRATION){
            foreach($this->projectsStates as $projId => $projectState){

                if (isset($projectState->project['Project']['email_reminder'])){

                    $templateFileName = CProUtils::getInstanceSpecificEmailName(
                        $projectState->project['Project']['email_reminder']
                    );
                   // $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), templateFileName for proj $projId: " . $templateFileName, LOG_DEBUG);
                    if ($templateFileName)
                        $emailTemplates[$templateFileName]  = array(
                            'text' =>  $projectState->project['Project']['Title'] . ' reminder',
                            'sendable' => false,
                        );
                    // Set the sendable key to true if the assessment is available now
                    if (isset($projectState->sessionLink['action']))
                        $emailTemplates[$templateFileName]['sendable'] = true;
                }
            }
        }
//        $this->log(__CLASS__ . "." . __FUNCTION__ . "(patient), returning: " . print_r($emailTemplates, true), LOG_DEBUG);
        return $emailTemplates;
    } // function getEmailTemplateList

    /**
     * Return all eligible, consented participants that a particular staff 
     * member can see that have $fieldName within a date range
     * By default, patients limited to the staff member's clinic.
     * @param $staff_id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param $fieldName eg "Patient.birthdate", or "PatientExtension.primary_randomization_date"
     * @param $startdate Start of the datetime range (optional, if not included no lower limit applied)
     * @param $enddate End of the datetime range (optional, if not included no end date limit applied)
     * @param $exclude Array of patient id's to exclude
     * @return all patients the staff member can see whose randomization date
     *    is in the datetime range, in an array
     */
    function findWFieldInDateRange($staff_id, $allPatients, $sameSite, 
                    $fieldName, $startdate = null, $enddate = null, $exclude) 
    {

        $query = $this->getAccessiblePatientsQuery($staff_id, $allPatients, 
	                                           $sameSite);
        $findNMonthFuWhere = '';

        if (isset($startdate)) 
            $findNMonthFuWhere 
                .= (' AND ' . $fieldName . ' > \'' 
                    . $startdate->format(MYSQL_DATETIME_FORMAT) . '\'');

        if (isset($enddate)) 
            $findNMonthFuWhere 
                .= (' AND ' . $fieldName . ' < \'' 
                    . $enddate->format(MYSQL_DATETIME_FORMAT) . '\'');

        if (sizeof($exclude) > 0){
            $findNMonthFuWhere 
                .= ' AND Patient.id NOT IN (' . implode($exclude, ',') . ')';
        }

        $requireActiveParticipants = ' AND Patient.off_study_status = \'' . self::ON_STUDY . '\' AND Patient.eligible_flag <> \'0\'';

        $orderBy = " ORDER BY " . $fieldName . " ASC";

        $query = $query[0] . $query[1]
                                . $requireActiveParticipants
                                . $findNMonthFuWhere . $orderBy;
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), query: $query", LOG_DEBUG);
        $results = $this->query($query); 

        return $this->afterFind($results);
    }

    /**
     * Retrieve patients for the P3P one week f/u report 
     * By default, patients limited to the staff member's clinic.
     * @param id id of the staff member
     * @param allPatients bool If true, no site or clinic limit applied
     * @param sameSite bool Patients limited to those from this staff member's site, widened from the default limit by clinic.
     * @param days Max number of days in the past the most recent appt can be
     * @return all patients the staff member can see, in an array
     */
    function oneWeekFollowup($id, $allPatients, 
        $sameSite, $days = null) {

        $query = $this->getAccessiblePatientsQuery($id, $allPatients, 
	                                           $sameSite);
        $patients = $this->query($query[0] . $query[1] . ' AND PatientExtension.wtp_status IS NULL AND Patient.eligible_flag <> 0');

        $GMT = new DateTimeZone('GMT');

        $windowClose = new DateTime('now', $GMT);
        $windowClose->sub(
            new DateInterval('P'. $days .'D'));

        $sixDays = new DateInterval('P6D');
        // note that we want to look 14 days after the appt, but since DateTime->add acts on the object, we only need to add 8 more days here.
        $eightDays = new DateInterval('P8D');

        //$this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found patients ' . print_r($patients, true), LOG_DEBUG);

        // retrieve appts and add to data model
        foreach ($patients as $key => &$patient){

            $appt = $this->Appointment->find('first', array(
                'conditions' => array(
                    'Appointment.patient_id' => $patient['Patient']['id'],
                    'Appointment.datetime < "' . $windowClose->format(MYSQL_DATETIME_FORMAT) . '"'
                ),
                'order' => 'Appointment.datetime DESC',
                'limit' => '1',
                'recursive' => -1));
            if ($appt) {
                $timezone = new DateTimeZone(
                    parent::getTimeZone($patient['Patient']['id']));

                $apptDateTime = new DateTime(
                    $appt['Appointment']['datetime'], $GMT);

                $apptDateTime->setTimeZone($timezone);

                $apptDateTime->add($sixDays);
                $patient['Patient']['window_open'] = $apptDateTime->format('m/d/Y'); 
                $apptDateTime->add($eightDays);
                $patient['Patient']['window_close'] = $apptDateTime->format('m/d/Y'); 
                $patient['Patient']['appt_dt'] = $appt['Appointment']['datetime'];
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found past appt for patient id ' . $patient['Patient']['id'], LOG_DEBUG);
//                $this->log(__CLASS__ . '.' . __FUNCTION__ . '(), found upcoming appt for patient id ' . $patient['Patient']['id'] . ': ' . print_r($appt, true), LOG_DEBUG);
            }
            else unset($patients[$key]);
        }
        return $this->afterFind($patients);
    }// function oneWeekFollowup


    /**
     * Would have named this 'create', but cakephp has that reserved for model prep
     * @param $requestData Typical cakephp request data, for Patient
     */
    function createPatient($requestData) {

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), given request data:" . print_r($requestData, true), LOG_DEBUG);

        $NEW_SELF_PATIENT_FIELDS_ORDERED =
            Configure::read('NEW_SELF_PATIENT_FIELDS_ORDERED');

        foreach($NEW_SELF_PATIENT_FIELDS_ORDERED as $classAndField => $required){
            if ($required){
                $pieces = explode('.', $classAndField);
                $model = $pieces[0];
                $field = $pieces[1];
                if (empty($requestData[$model][$field])){
                    throw new Exception("Missing required fields.");
                }
            }
        }

        if (defined('USERNAME_OR_EMAIL') && USERNAME_OR_EMAIL){
            if ((!isset($requestData['User']['email'])
                    || empty($requestData['User']['email']))
                &&
                (!isset($requestData['User']['username'])
                    || empty($requestData['User']['username']))){

                throw new Exception(__("Either username or email needs to be entered."));
            }
        }

        // ensure another patient doesn't clash
        $userConditions = array();
        if (!is_null($PW_RESET_FIELDS_ORDERED = Configure::read('PW_RESET_FIELDS_ORDERED'))){
            foreach($PW_RESET_FIELDS_ORDERED as $classAndField){
                $pieces = explode('.', $classAndField);
                $model = $pieces[0];
                $field = $pieces[1];
                $userConditions[$classAndField] = $requestData[$model][$field];
            }
            $user = $this->User->find('first', array(
                        'conditions' => $userConditions,
                        'recursive' => 1));
            if (!empty($user)) {
                throw new Exception("Existing Account Found.");
            }
        }

        // new user - create base objects
        $userSaveResult = $this->User->save($requestData);
        if(!$userSaveResult){
            $msg;
            $errors = $this->User->validationErrors; // eg email clash
            foreach($errors as $error){
                $msg = $error[0] . " ";
            }
            $this->log("save error" . print_r($msg, true), LOG_DEBUG);
            throw new Exception($msg);
        }

        // If this was initiated by PatientController->registerPatient(), password will have been validated. If it's null, the patient is probably being created by the patient having logged in for the first time via an external application (eg True NTH USA portal)
        if (isset($requestData['User']['password'])){
            $this->User->setPassword(
                $this->User->id,
                $requestData['User']['password']); // previously hashed
        }

        $id = $this->User->id;
        $requestData['Patient']['id'] = $id;
        $this->save($requestData);

        $this->PatientExtension->create();
        if (!isset($requestData['PatientExtension']))
            $requestData['PatientExtension'] = array();
        $requestData['PatientExtension']['patient_id'] = $id;
        $this->PatientExtension->save($requestData);

        if (defined('DEFAULT_LOCALE') and DEFAULT_LOCALE != 'en_US') {
            //$this->loadModel('LocaleSelection');
            $this->User->LocaleSelection->save(array(
                'LocaleSelection' => array(
                    'user_id' => $id,
                    'locale' => DEFAULT_LOCALE,
                    'time' => $this->DhairDateTime->usersCurrentTimeStr(),
                )
            ));
        }

        // search for the UserAclLeaf to make sure it doesn't already exist
        $initialPatientRole = 'Patient';
        if (defined('INITIAL_PATIENT_ROLE'))
            $initialPatientRole = INITIAL_PATIENT_ROLE;

        $patientAclLeafExists = $this->User->UserAclLeaf->find('count', array('conditions' => array('user_id'=>$id,
            'acl_alias'=>
            'acl' . $initialPatientRole)));
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(...), user $id, for acl$initialPatientRole, patientAclLeafExists : $patientAclLeafExists", LOG_DEBUG);
        if (!$patientAclLeafExists){
            $this->User->UserAclLeaf->create();
            $this->User->UserAclLeaf->save(array('user_id'=>$id,
                'acl_alias'=>
                'acl' . $initialPatientRole));
        }
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(...), just created UserAclLeaf w/ acl_alias : acl$initialPatientRole", LOG_DEBUG);

        return $id;
    }// function createPatient($requestData) {

    /**
     * Make an existing user record a patient. 
     * Idempotent: If patient record already exists, simply return.
     */
    function makeUserPatient($user_id) {

        $patientExists = $this->find('count', array('conditions' =>array('Patient.id'=>$user_id)));
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "($user_id), patientExists: $patientExists", LOG_DEBUG);
        if ($patientExists > 0) return;

        $saveArray = array('Patient' => array());
        $saveArray['Patient']['id'] = $user_id;
        $this->create();
        $this->save($saveArray);

        $this->PatientExtension->create();
        $saveArray['PatientExtension'] = array();
        $saveArray['PatientExtension']['patient_id'] = $user_id;
        $this->PatientExtension->save($saveArray);

        $initialPatientRole = 'Patient';
        if (defined('INITIAL_PATIENT_ROLE'))
            $initialPatientRole = INITIAL_PATIENT_ROLE;

        // search for the UserAclLeaf to make sure it doesn't already exist
        $patientAclLeafExists = $this->User->UserAclLeaf->find('count', 
            array('conditions' => array('UserAclLeaf.user_id'=>$user_id,
                'UserAclLeaf.acl_alias'=> 'acl' . $initialPatientRole)));
        if (!$patientAclLeafExists){
            $this->User->UserAclLeaf->create();
            $this->User->UserAclLeaf->save(array('UserAclLeaf.user_id'=>$user_id,
                'UserAclLeaf.acl_alias'=> 'acl' . $initialPatientRole));
        }
    }

    /**
     * Retrieve the latest answer for a given question as answered by this patient. E.g. if a patient has taken a questionnaire three times, the latest answer is returned. Uses SurveySession->surveyData which cannot handle checkboxes. Only radio.
     */
    function latestAnswers($patientId) {
      // Get Sessions
      $sessions = $this->SurveySession->find('all', array(
        'conditions' => array(
          'SurveySession.patient_id' => $patientId,
          'SurveySession.finished' => true,
        ),
        'order' => array(
          'SurveySession.reportable_datetime DESC',
        )));

      // Get All Questionnaires in the DB
      $questionnaireModel = ClassRegistry::init('Questionnaire');
      $questionnaires = $questionnaireModel->find('all', array(
        'fields' => array('id'),
        'recursive' => -1));

      // This data structure will hold all the latest answers by questionnaire
      $questionnaireHash = array();
      foreach ($questionnaires as $questionnaire) {
        $questionnaireHash[$questionnaire["Questionnaire"]["id"]] = null;
      }

      // Loop through each session
      foreach ($sessions as $session) {
        // Get survey data (answers) from the session

        //$surveyData = $this->SurveySession->questionnaireResponseData($session["SurveySession"]["id"]);
        $surveyData = $this->SurveySession->surveyData($session["SurveySession"]["id"]); // this is currently reliable, but will need a refactor to qRD plus transforming text below. Affects the Substitutional stuff for Module D in PTSM Paintracker.
        //$questionnaireResourcesFHIR = FHIR::questionnaireAsFHIR( $surveyData, $session, true);
        //echo ("size: " . sizeof($questionnaireResourcesFHIR[0]));

        // Sort the answers by questionnaire
        $surveyDataByQuestionnaire = array();
        foreach ($surveyData as $answerObj) {
          $questionnaireId = $answerObj["Questionnaire"]["id"];
          $questionId = $answerObj["Answer"]["question_id"];
          $bareAnswer = array(
            "Answer" => $answerObj["Answer"],
            "Option" => $answerObj["Option"],
          );
          // Save data in surveyDataByQuestionnaire, create new array if necessary
          if (!array_key_exists($questionnaireId, $surveyDataByQuestionnaire)) {
            $surveyDataByQuestionnaire[$questionnaireId] = array();
          }
          $surveyDataByQuestionnaire[$questionnaireId][$questionId] = $bareAnswer;
          //array_push($surveyDataByQuestionnaire[$questionnaireId], $bareAnswer);
        }
        // Move latest answers into questionnaireHash 
        foreach ($questionnaireHash as $qnid => $qn) {
          if (sizeof($qn) == null && array_key_exists($qnid, $surveyDataByQuestionnaire)) {
            $questionnaireHash[$qnid]  = $surveyDataByQuestionnaire[$qnid];
          }
        }
      }

      return $questionnaireHash;
    }

} // class Patient extends AppModel

