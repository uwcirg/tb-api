<?php

/**
    *
    * Copyright 2014 University of Washington, School of Nursing.
    * All rights reserved.  Contact cirg@uw.edu for licensing information.  Do not distribute source or compiled code without permission.
    *
*/
App::import('Model', 'Patient');

class PatientMpowerProvider extends Patient
{

    var $name = 'Patient';

    var $useTable = 'patients';

    var $validate = array(
        'MRN' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'allowEmpty' => true,
                'message' => 'This MRN already exists in the system.'
            )
        )
    );

    /**
     *
     */
    function beforeValidate($options = Array()){
        foreach($this->data['Patient'] as $field => $value){

            // When doing updates, both the new and old values are in $value array, we only need the latest
            if (is_array($value))
                $value = $value[1];

            $this->data['Patient'][$field] = trim($value);
        }
        return true;
    }




}

