<?php
/** 
    * Clinician class
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

class Clinician extends AppModel
{
    var $belongsTo = array('Clinic');

    var $hasMany = 
        array('ClinicianNote' => 
                  array('order' => 'ClinicianNote.created DESC', 
	                'dependent' => true),
              'ClinicianRace' => array('dependent' => true));

    var $validate = array(
        'email' => array(
            'email' => array(
                'rule' => array('email', false), 
                'allowEmpty' => true,
                'message' => 'This does not appear to be a valid email address.'
            )
        ),
        'webkey' => array(
            array(
                'rule' => 'notEmpty',
                'message' => 'webkey is required.'
            ),
        ),
    );

    /** 
      * Try to find a clinician with a given first name and last name
      * @param firstName first name
      * @param lastName last name
      * @param email email
      * @return A clinician that matches, or null if there are none
     */
    function findClinician($firstName, $lastName, $email) {
        $candidate = $this->find('first', array('conditions' => array(
	    'Clinician.first_name' => $firstName,
	    'Clinician.last_name' => $lastName,
            'Clinician.email' => $email)));
	                  
        if (empty($candidate)) {
	    return null;
        } else {
	    return $candidate;
        }

    }
    
    /**
     * Get the first part (select + join) and second part (where clause) 
     * of the query to find all clinicians a particular staff member can
     * see
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @return an array ($selectAndJoin, $whereClause)
     */
    function getAccessibleCliniciansQuery($id, $centralSupport) {
        $id = intval($id);
	$selectAndJoin = 'SELECT Clinician.first_name, Clinician.last_name, 
	    Clinician.consent_status, Clinician.id, Clinic.name, 
	    Clinician.priority, Clinician.check_again_date, 
        Clinician.demo_survey_complete_flag 
	    from clinicians as Clinician, clinics as Clinic';
	$whereClause = ' WHERE Clinic.id = Clinician.clinic_id';

        // above query works for centralSupport as is
        if ($centralSupport) {
        } else { // research staff
	    // researchStaff needs to add restriction that site ids match
	    $selectAndJoin .= ' JOIN clinics as clinics2, users as users2';
	    $whereClause .= " AND clinics2.id = users2.clinic_id
	                      AND Clinic.site_id = clinics2.site_id
	                      AND users2.id = $id";
        }

	return (array($selectAndJoin, $whereClause));
    }

    /**
     * Return all clinicians a particular staff member can see
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @return all patients the staff member can see, in an array
     */
    function findAccessibleClinicians($id, $centralSupport) {
        $query = $this->getAccessibleCliniciansQuery($id, $centralSupport);
        $results = $this->query($query[0] . $query[1]);
        return $this->afterFind($results);
    }

    /**
     * Get the timezone for a clinician, as a string
     * @param id Id of the clinician
     * @return the timezone
     */
    function getTimeZone($id){
        $id = intval($id);

        if (empty($id)) {
	    $this->log("getTimeZone: clinician Id empty!");
	    return date_default_timezone_get();
        }

        $queryStr = "SELECT timezone from sites 
                     JOIN clinics, clinicians
                     WHERE clinicians.id = $id
                     AND clinics.site_id = sites.id
                     AND clinicians.clinic_id = clinics.id
                     LIMIT 1";
        $result = $this->query($queryStr);

	if (empty($result)) {
	    $this->log("getTimeZone: clinician $id has no timezone!");
	    return date_default_timezone_get();
	} else {
            return $result[0]['sites']['timezone'];
        }
    }

    /** Value for the type field */
    const MD_ATTENDING = 'MD-Attending';

    /** Value for the type field */
    const MD_RESIDENT = 'MD-Resident';

    /** Value for the type field */
    const MD_FELLOW = 'MD-Fellow';

    /** Value for the type field */
    const PA = 'PA';

    /** Value for the type field */
    const NP = 'NP';

    /** Value for the type field */
    const RN = 'RN';

    /** Value for the type field (and other fields) */
    const OTHER = 'Other';

    /** 
      Return the allowed values for the type field 
     */
    function getTypes() {
       return array(self::MD_ATTENDING => self::MD_ATTENDING,
                    self::MD_RESIDENT => self::MD_RESIDENT,
                    self::MD_FELLOW => self::MD_FELLOW,
                    self::PA => self::PA,
                    self::NP => self::NP,
                    self::RN => self::RN,
                    self::OTHER => self::OTHER);
    }


    /** Value for the age_group field */
    const TWENTIES = '20-29';

    /** Value for the age_group field */
    const THIRTIES = '30-39';

    /** Value for the age_group field */
    const FORTIES = '40-49';

    /** Value for the age_group field */
    const FIFTIES = '50-59';

    /** Value for the age_group field */
    const SIXTIES = '60 and above';

    /** 
      Return the allowed values for the age_group field 
     */
    function getAgeGroups() {
       return array(self::TWENTIES => self::TWENTIES,
                    self::THIRTIES => self::THIRTIES,
                    self::FORTIES => self::FORTIES,
                    self::FIFTIES => self::FIFTIES,
                    self::SIXTIES => self::SIXTIES);
    }

    /** Value for the gender field */
    const MALE = 'Male';

    /** Value for the gender field */
    const FEMALE = 'Female';

    /** 
      Return the allowed values for the gender field 
     */
    function getGenders() {
       return array(self::MALE => self::MALE,
                    self::FEMALE => self::FEMALE);
    }

    /** Value for the specialty field */
    const TRANSPLANT = 'Heme/Stem Cell Transplant';

    /** Value for the specialty field */
    const RADONC = 'Radiation Oncology';

    /** Value for the specialty field */
    const MEDONC = 'Medical Oncology';

    /** Value for the specialty field */
    const SURGONC = 'Surgical Oncology';

    /** 
      Return the allowed values for the specialty field 
     */
    function getSpecialties() {
       return array(self::TRANSPLANT => self::TRANSPLANT,
                    self::RADONC => self::RADONC,
                    self::MEDONC => self::MEDONC,
                    self::SURGONC => self::SURGONC,
                    self::OTHER => self::OTHER);
    }

    /** Value for the position title field */
    const ATTENDING = 'Attending MD';

    /** Value for the position title field */
    const RESIDENT = 'Resident/Fellow';

    /** Value for the position title field */
    const ARNP = 'ARNP or RN';

    /** Value for the position title field */
    const PHYSICIAN_ASSISTANT = 'Physician Assistant';

    /** 
      Return the allowed values for the position Title field 
     */
    function getPositionTitles() {
       return array(self::ATTENDING => self::ATTENDING,
                    self::RESIDENT => self::RESIDENT,
                    self::ARNP => self::ARNP,
                    self::PHYSICIAN_ASSISTANT => self::PHYSICIAN_ASSISTANT,
                    self::OTHER => self::OTHER);
    }

    /**
     * Find all staff that have access to a particular clinician
     * @param id Clinician's id
     * @return Staff described, in the usual $this->data format
     */
    function getStaff($id) {
        /* users, clinics1 and user_acl_leafs represent the staff; 
           clinicians and clinics2 represent the clinician

           Final 'AND' clause implements the usual clinic/site logic:
              central support can see all, research staff can see all
              patients at the same site
         */
	$id = intval($id);

        $queryString = "SELECT users.id, users.username from users 
	    JOIN clinics as clinics1, clinics as clinics2, user_acl_leafs, 
	        clinicians
	    WHERE clinicians.id = $id
	    AND clinics2.id = clinicians.clinic_id
	    AND clinics1.id = users.clinic_id
	    AND users.id = user_acl_leafs.user_id
            AND (user_acl_leafs.acl_alias = 'aclCentralSupport' 
	         OR (user_acl_leafs.acl_alias = 'aclResearchStaff'
	             AND clinics1.site_id = clinics2.site_id))";
        return $this->query($queryString);
    }

    // create a webkey if there isn't one already
    function beforeValidate() {
        if (empty($this->data['Clinician']['webkey'])) {
            $this->data['Clinician']['webkey'] = rand(1, 1000000000);
        }
        
        return true;
    }

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
     * Return all clinicians a particular staff member can see that
     * have a check again date in a particular date range.
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @param startdate Start of the datetime range
     * @param enddate End of the datetime range
     * @return all clinicans the staff member can see whose check-again date
     *    is in the datetime range, in an array
     */
    function findCheckAgains($id, $centralSupport, $startdate, $enddate) 
    {
        if (!$this->checkDates($startdate, $enddate)) {
	    $this->log(
	        "findCheckAgains: bad date parameters $startdate $enddate");
            return array();
        }

        $query = $this->getAccessibleCliniciansQuery($id, $centralSupport);
        $checkAgainWhere = " AND Clinician.check_again_date >= '$startdate'
	                     AND Clinician.check_again_date <= '$enddate'";
        $orderBy = " ORDER BY Clinician.check_again_date";
	                   
        $results = $this->query($query[0] . $query[1] . $checkAgainWhere .
	                        $orderBy);
        return $this->afterFind($results);
    }

    /**
     * Return all clinicians a particular staff member can see that are
     * consented but whose consent has not been checked (verified)
     * @param id id of the staff member
     * @param centralSupport True if they are CentralSupport
     * @return all clinicans the staff member can see that are consented
     *    but whose consent has not been verified
     */
    function findUncheckedConsents($id, $centralSupport) {
        $query = $this->getAccessibleCliniciansQuery($id, $centralSupport);
        $uncheckedConsents = " AND Clinician.consent_status = '" . 
	                     Patient::CONSENTED . "'
	                     AND Clinician.consent_checked != 1";
        $orderBy = " ORDER BY Clinician.last_name";
	                   
        $results = $this->query($query[0] . $query[1] . $uncheckedConsents .
	                        $orderBy);
        return $this->afterFind($results);
    }
}
