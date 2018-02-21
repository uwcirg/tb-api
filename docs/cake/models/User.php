<?php
/** 
    * User class
    *
    * Implements user login for all system users: patients, clinicians, etc.
    * DB fields: username, password
    * Currently only used for auth
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class User extends AppModel
{
    var $name = "User";
    var $useTable = 'users';
    var $hasOne = array("Patient" => array(
                            'foreignKey' => 'id', // default would be user_id
		                    'dependent' => true),
                        'IdentityProvider',  // really a hasMany relationship,
                        // but userModel->find doesn't include in a join
                        // as a hasMany, so it lives here for login access
                        );
    var $hasMany = array('UserAclLeaf' => array('dependent' => true), 
                         'SurveySession' => array('dependent' => true), 
                         'Consentee' => array(
                            'className' => 'Patient',
                            'foreignKey' => 'consenter_id',
                         ),
                         'Note' => array('foreignKey' => 'author_id'), 
                         'PatientViewNote' => 
                            array('foreignKey' => 'author_id'),
                         'Webkey',
                         'Relationship' => array(
                              'className' => 'Relationship', 
                              'foreignKey' => false,
                              'conditions' => array(
                                                'OR' => array (
                                                  array('Relationship.user_a_id' => '{$__cakeID__$}'),
                                                  array('Relationship.user_b_id' => '{$__cakeID__$}'),
                                                ),
                                              ),/* see: http://stackoverflow.com/questions/6087684/cakephp-model-relationship-with-multiple-foreign-keys
                              'finderQuery' => 'SELECT *
                                                FROM `relationships`
                                                WHERE `user_a_id` = {$__cakeID__$}
                                                OR `user_b_id` = {$__cakeID__$}'
                              */
                              ),
/**
FIXME these next two Tag comments to be removed after initial dev (instead, see hasAndBelongsToMany below)
                         'Tag' => array(
                              'className' => 'Tag', 
                              'foreignKey' => 'creator_id'
                              ),
*/
/**
                         'Tag' => array(
                              'className' => 'Tag', 
                              'foreignKey' => false,
                              'conditions' => array(
                                                'OR' => array (
                                                  array('user_a_id' => '{$__cakeID__$}'),
                                                  array('Relationship.user_b_id' => '{$__cakeID__$}'),
                                                ),
                                              )// see: http://stackoverflow.com/questions/6087684/cakephp-model-relationship-with-multiple-foreign-keys
                              ),
*/
    );
    var $belongsTo = array(
                        'Clinic'
                        );
    var $hasAndBelongsToMany = array(
            'Tag' => array(
                'joinTable' => 'tags_users',
                'associationForeignKey' => 'tag_id',
                'foreignKey' => 'subject_id',
/*
    TODO delete this comment, I think this would make it too complex. Instead, query against tags
                'foreignKey' => false,
                'conditions' => array(
                    'OR' => array (
                        array('TagsUsers.subject_id' => '{$__cakeID__$}'),
                        array('TagsUsers.assigner_id' => '{$__cakeID__$}'),
                    ),
                ),// see: http://stackoverflow.com/questions/6087684/cakephp-model-relationship-with-multiple-foreign-keys
*/
                '' => '',
                '' => '',


            ));
 
    var $validate = array(
        'username' => array(
            'minLength' => array(
                'rule' => array('minLength', '2'),
                'message' => 'This username is not long enough (must be at least 2 characters).'
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This username has already been taken.'
            )
        ),
        'email' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'Another patient has that email address; please enter a different one.',
                // 'on' => 'create'
            ),
            // 'allowEmpty' => true
        ),
            // email validation seems problematic here so I'm disabling it, since we also use jquery validation on the front end.
            // 'email' => array(
                //'rule' => array('email', true), // 2nd param: attempt to verify mail server
                // 'rule' => array('email',
                            // whether to attempt to verify mail server
                            // failed for u.washington.edu !
                            // but treated others as expected...
                            // false),
                // 'allowEmpty' => true,
                // 'message' => 'This does not appear to be a valid email address.'
            // )
    );

   var $bindModels = true;


    /**
     *
     */
    function beforeSave($options = Array()) {
        
        // If there's only one clinic available, and clinic id isn't passed here, assign the user to that clinic.
        // This adjustment was made in part because until 5/7/15 the schema didn't allow null for
        // clinic_id (defaulted to 1); on 5/7/15 we changed clinic_id to default null
        $clinics = $this->Clinic->find('all', array('recursive' => -1));
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "; clinics: " . print_r($clinics, true), LOG_DEBUG);
        if (sizeof($clinics) == 1){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "; size of clinics == 1", LOG_DEBUG);
            if (empty($this->data['User']['clinic_id'])
                || !array_key_exists($this->data['User']['clinic_id'],$clinics)) {
                $this->data['User']['clinic_id'] = $clinics[0]['Clinic']['id'];
            }
        }

        $this->bindLocaleSelection();

        return true;
    }

    /**
     *
     */
    function afterSave($created){
        //$this->log(__CLASS__ . "=>" . __FUNCTION__ . "($created), heres this->data:" . print_r($this->data, true), LOG_DEBUG);

        if (isset($this->data['TagsUsers']) && !empty($this->data['TagsUsers'])){
            // data[TagsUsers=>array[(tag_id=>x, user_id=>null), (tag_id=>y, user_id=>null), ]]

            $tagsUsersRef = ClassRegistry::init('TagsUsers');
            $userId = $this->getLastInsertId();
            foreach ($this->data['TagsUsers'] as $key => $val){
                $this->data['TagsUsers'][$key]['subject_id'] = $userId;
                $this->data['TagsUsers'][$key]['assigner_id'] = $userId;
            }
            $tagsUsersRef->saveMany($this->data['TagsUsers']);
        }
    }//function afterSave($created){

    /**
     *
     */
    function bindLocaleSelection(){
        if ($this->bindModels){ // if bindModels hasn't been disabled for perf reasons
            if (in_array('locale_selections',
                       Configure::read('modelsInstallSpecific'))){
                $this->bindModel(
                  array('hasMany' =>
                    array(
                        'LocaleSelection' =>
                        array('className' => 'LocaleSelection',
                           'dependent' => 'true')
                    )),
                  false);
            }
        }
    }

    // Transform empty string values into nulls (to allow isUnique validation)
    function beforeValidate($options = Array()){
        foreach($this->data[$this->name] as $field => &$value){

            // When doing updates, both the new and old values are in $value array, we only need the latest
            if (is_array($value))
                $value = $value[1];

            if (trim($value) === '')
                unset($this->data[$this->name][$field]);

        }
        return true;
    }


    /**
     *
     */
    function beforeFind($queryData){
        $this->bindLocaleSelection();
        return $queryData;
    }

    // User authorization methods
    // things too complicated to put in the ACL
    function canStartTicket($user_id, $ticket_id) {
        return true;
    }

    /**
    * find all UserAclLeafs with alias matching fromLeaf; change their
    *   aliases to toLeaf
    * Currently only used to switch aclPatient to aclParticipant<study group>
    * 
    * Seems like this should be in the DhairAuth component, 
    *   but components aren't accessible to models...
    */
    function swapUsersAclLeaf($userID, $fromLeaf, $toLeaf){

        //$this->log("swapUsersAclLeaf($userID, $fromLeaf, $toLeaf)", LOG_DEBUG);

        $user = $this->findById($userID);
        
        //$this->log("swapUsersAclLeaf(...), here's user: " . print_r($user, true), LOG_DEBUG);
    
        foreach ($user['UserAclLeaf'] as $userAclLeaf) {
            if ($userAclLeaf['acl_alias'] == $fromLeaf){
                $this->UserAclLeaf->id = $userAclLeaf['id'];
                $this->UserAclLeaf->saveField(
                                'acl_alias', $toLeaf);
            }
        }
    }

    /**
    *
    */
    function setPassword($userId, $hash){
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "(userId: " . $userId . ", hash: " . $hash, LOG_DEBUG);
        $success = false;
        if ($userId && $hash){
            $this->id = $userId;
            $success = $this->saveField('password', $hash); 

	    if ($success) {
	        $this->saveField('change_pw_flag', 0);
            }
        }

        return $success;
    }

    function findOrCreateAssociateUserForEmail($email) {
        $user = $this->find(array("User.email" => $email));
        if(!$user) {
            return $this->createAssociateUser($email);
        } else {
            return $user;
        }
    }

    function createAssociateUser($email) {
        $user = array("User" => array("username" => $email, "email" => $email));
        $this->create($user);
        if($this->validates()) {
            $this->save($user);
            return $this->findById($this->getLastInsertId());
        } else {
            $invalidFields = $this->invalidFields();
            $errString = "";
            foreach ($invalidFields as $invalidField){
                $errString .= $invalidField . "<br>";    
            }
            $this->log("error in createAssociateUser: $errString");
            return false;
        }
    }


    /**
     * Find all staff that have access to a particular user
     * central support can see all;
     * research staff can see all patients at the same site
     *      (to clarify: can't see patient's clinic who have null clinic_id);
     * clinical staff can see all patients at the same clinic
     *      (to clarify: can't see patient's clinic who have null clinic_id);
     * If patient doesn't have a clinic assigned
     * @param id User's id
     * @return Staff described, in the usual $this->data format
     */
    function getStaff($id) {
        /* users1, clinics1 and user_acl_leafs represent the staff; 
           users2 and clinics2 represent the user
         */
        $id = intval($id);

        $queryStr = "SELECT * FROM users
                    WHERE users.id = $id
                    LIMIT 1";
        $user2 = $this->query($queryStr);
//        $this->log(__CLASS__ . "->" . __FUNCTION__ . "; user2: " . print_r($user2, true), LOG_DEBUG);

        if (empty($user2[0]['users']['clinic_id'])) {
            $queryStr = "SELECT users1.id, users1.username from users as users1
                JOIN user_acl_leafs, users as users2
                WHERE users2.id = $id
                AND users1.id = user_acl_leafs.user_id
                AND (user_acl_leafs.acl_alias = 'aclCentralSupport' 
                OR (user_acl_leafs.acl_alias = 'aclAdmin'))";
        }
        else {
            $queryStr = "SELECT users1.id, users1.username from users as users1
                JOIN clinics as clinics1, clinics as clinics2, user_acl_leafs, 
                    users as users2
                WHERE users2.id = $id
                AND clinics2.id = users2.clinic_id
                AND clinics1.id = users1.clinic_id
                AND users1.id = user_acl_leafs.user_id
                AND (user_acl_leafs.acl_alias = 'aclCentralSupport' 
                OR (user_acl_leafs.acl_alias = 'aclAdmin')
                OR (user_acl_leafs.acl_alias = 'aclResearchStaff'
                    AND clinics1.site_id = clinics2.site_id)
                OR (user_acl_leafs.acl_alias = 'aclClinicStaff'
                    AND users1.clinic_id = users2.clinic_id))";
        }

        return $this->query($queryStr);
    }

    function getAcessibleUsers($user_id, $extra_conditions=array()){
        $conditions = array_merge(
            array(
                // Remove patients from results
                'Patient.id' => null,
                'User.id' => $user_id,
            ),
            $extra_conditions
        );

        $options = array(
            'fields' => array(
                'Clinic.*',
                'Site.*',
                'Staff.*',
                'Role.*',
                'roles_tmp.*',
                // '*',
            ),
            'joins' => array(
                // Necessary to get site info
                array(
                    'table' => 'clinics',
                    'alias' => 'temp_clinic',
                    'conditions' => array(
                        'temp_clinic.id = User.clinic_id',
                    ),
                ),
                array(
                    'table' => 'sites',
                    'alias' => 'Site',
                    'conditions' => array(
                        'Site.id = temp_clinic.site_id',
                    ),
                ),

                // Join again to get all clinics part of user's site
                array(
                    'table' => 'clinics',
                    'alias' => 'Clinic',
                    'conditions' => array(
                        'Clinic.site_id = Site.id',
                    ),
                ),

                // Join users back to clinics belonging to original user's site
                array(
                    'table' => 'users',
                    'alias' => 'Staff',
                    'conditions' => array(
                        'Staff.clinic_id = Clinic.id',
                    ),
                ),

                // Used to remove patients from results
                array(
                    'table' => 'patients',
                    'type' => 'left outer',
                    'alias' => 'Patient',
                    'conditions' => array(
                        'Patient.id = Staff.id',
                    ),
                ),

                array(
                    'table' => 'user_acl_leafs',
                    'alias' => 'Role',
                    'conditions' => array(
                        'Role.user_id = Staff.id',
                    ),
                ),
                array(
                   'table' => "(
                       select
                           user_id,
                           group_concat(acl_alias) as aliases
                       from user_acl_leafs ua
                       group by user_id
                   )",
                   'alias' => 'roles_tmp',
                   'conditions' => array('Staff.id = roles_tmp.user_id'),
               ),
            ),
            'conditions' => $conditions,
            'recursive' => -1,
        );

        return $this->find('all', $options);
    }

    /**
     * Get non-admin users
     */
    function getNonAdmin() {
        $this->Behaviors->attach('Containable');
        $results = $this->find('all', 
                                array(
                                    'contain' => 'UserAclLeaf.acl_alias',
                                    'fields' => 
                                        array('User.id', 'User.username'))); 
        //$this->log("User.getNonAdmin(), here's the search result: " . print_r($results, true), LOG_DEBUG);
        foreach ($results as $key => $result){
            foreach($result['UserAclLeaf'] as $aro){
                if ($aro['acl_alias'] == 'aclCentralSupport' ||
                    $aro['acl_alias'] == 'aclAdmin'){
                    unset($results[$key]);
                }
            }
        }
        //$this->log("User.getNonAdmin(), returning: " . print_r($results, true), LOG_DEBUG);
        return $results;
    }

    
    function getClinicStaffForClinic($clinic_id){

        // look for all users with the ClinicStaff role
        $leaves = 
            $this->UserAclLeaf->find('all', array( 
                                'conditions' => array(
                                    'UserAclLeaf.acl_alias' => 
                                        'aclClinicStaff')/**,
                                'recursive' => 2*/)); // didn't return User for some reason...
        //$this->log("getClinicStaffForClinic($clinic_id), here's leaves: " . print_r($leaves, true), LOG_DEBUG);

        $clinicStaff = array();
        foreach($leaves as $leaf){

            $user = $this->find('first', array(
                            'conditions' => array(
                                'User.id' => $leaf['UserAclLeaf']['user_id']),
                            'recursive' => -1));

            if ($user['User']['clinic_id'] == $clinic_id){
                $clinicStaff[] = $user;
            }
        }
        //$this->log("getClinicStaffForClinic($clinic_id), here's clinicStaff: " . print_r($clinicStaff, true), LOG_DEBUG);
        return $clinicStaff;
    }

    /**
     *
     */
    function findTagsForUser($user_id){

        $userWTags = $this->Tag->find('all', array(
                            'recursive' => -1,
                            'joins' => array(
                                array(
                                'table' => 'tags_users',
                                'alias' => 'TagsUsers',
                                'conditions' => array(
                                    'TagsUsers.tag_id = Tag.id',
                                    'TagsUsers.subject_id' => $user_id),
                                )
                            ),
        ));
        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), heres userWTags:" . print_r($userWTags, true), LOG_DEBUG);
        $tagsReturnVal = array();
        foreach ($userWTags as $tag){
            $tagsReturnVal[] = $tag['Tag'];
        }

        //$this->log(__CLASS__ . "." . __FUNCTION__ . "(), returning:" . print_r($tagsReturnVal, true), LOG_DEBUG);
        return $tagsReturnVal;
    }

}
