<?php
/**
 * Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


class ZipSearch  extends AppModel {

public $name = 'ZipSearch';

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(

			    'zip_code' => array(
			        'rule' => array('postal', null, 'us'),
                    'required' => true, 'allowEmpty' => true,
			        'message' => 'required field.'),

               'distance' => array(
			        'rule' => 'notEmpty', //array('nonEmpty'),
                    'required' => true, 'allowEmpty' => false,
			        'message' => 'Please select a minimum distance.')


   ); //end validates array


public function no_submit_search($cur_zip_code, $distance) {

         $tutor = new Tutor();
          if(!empty($cur_zip_code)) {

           if(
                  (!$rs = $this->find(
                            'first', array(
                            'conditions' => array(
                                'ZipSearch.zip_code' => $cur_zip_code))))
                     || (count($rs) == 0)
                  )
            {

               // throw new NotFoundException(__('Zip Code Not Found'));
                   return false;
            } else {

                    // debug("test"); die();
                    $lat1 = $rs['ZipSearch']['latitude'];
                    $lon1 = $rs['ZipSearch']['longitude'];
                    $d=20;
                    if(!empty($distance)) {
                        $zip_search_distance = $distance; //$postData['distance'];
                        $d = $zip_search_distance; //$postData['ZipSearch']['distance'];
                     } else {
                        $zip_search_distance = 20; //$postData['distance'];
                        $d = $zip_search_distance; //$postData['ZipSearch']['distance'];
                     }
                    //earth's radius in miles
                    $r = 3959;
                     // debug("test ahd");
                      //debug("test MMM");
                      //debug($zip_search_distance); die();
                    //compute max and min latitudes / longitudes for search square
                    $latN = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(0))));
                    $latS = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(180))));
                    $lonE = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(90)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));
                    $lonW = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(270)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));


                   // $conditions = array(
                   //     'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                   //     'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                   //     'ZipSearch.latitude !=' => $lat1,
                   //     'ZipSearch.longitude !=' => $lon1,
                   //     'ZipSearch.city !=' => ''
                   //     );

                   $conditions = array(
                       'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                       'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                      'ZipSearch.city !=' => ''
                       );

                  
                 if (
                        (!$rs = $this->find('all',array('conditions' => $conditions,'fields' => array('ZipSearch.zip_code'))))
                        || (count($rs) == 0)
                    )
                    {
                        return false;

                    } else {
                        // debug($rs); die();
                        return $rs;
                    }
                 //chop it off  ends
            }
          } else {

              //$rs = $tutor->find('all');
              //return $rs;
               return false;

              }       
    }
public function search($postData = array(), $cur_zip_code) {

   // debug($cur_zip_code); 
    //debug($postData['distance']); //die();
   // $distance = $postData['distance'];
    $tutor = new Tutor();
    $this->set(array('zip_code' => $cur_zip_code));
    if ($this->validates(array('fieldList' => array('zip_code'))))
        {
         if(!empty($cur_zip_code)) {

           if(
                  (!$rs = $this->find(
                            'first', array(
                            'conditions' => array(
                                'ZipSearch.zip_code' => $cur_zip_code))))
                     || (count($rs) == 0)
                  )
            {

               // throw new NotFoundException(__('Zip Code Not Found'));
              // debug("test"); die();
                   return false;
            } else {

                    // debug("test"); die();
                    $lat1 = $rs['ZipSearch']['latitude'];
                    $lon1 = $rs['ZipSearch']['longitude'];
                   // debug($postData['distance']); //die();
                    $zip_search_distance = $postData['distance'];
                    $d = $zip_search_distance; //$postData['ZipSearch']['distance'];
                    //earth's radius in miles
                   // debug($zip_search_distance); //die();
                    $r = 3959;

                    //compute max and min latitudes / longitudes for search square
                    $latN = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(0))));
                    $latS = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(180))));
                    $lonE = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(90)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));
                    $lonW = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(270)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));


                   // $conditions = array(
                   //     'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                   //     'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                   //     'ZipSearch.latitude !=' => $lat1,
                   //     'ZipSearch.longitude !=' => $lon1,
                   //     'ZipSearch.city !=' => ''
                   //     );

                   $conditions = array(
                       'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                       'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                      'ZipSearch.city !=' => ''
                       );

                  //Not using this for now
                  /**
                  $this->Paginator->settings = array(
                    'limit' => 100,
                    'conditions' => $conditions,
                    'order' => array(
                        'ZipSearch.state' => 'asc',
                        'ZipSearch.city' => 'asc',
                        'ZipSearch.latitude' => 'asc',
                        'ZipSearch.longitude' => 'asc'
                     ));
                  **/

                 // chop this off

                 if (
                        (!$rs = $this->find('all',array('conditions' => $conditions,'fields' => array('ZipSearch.zip_code'))))
                        || (count($rs) == 0)
                    )
                    {
                        return false;

                    } else {
                        return $rs;
                    }
                 //chop it off  ends
            }
          } else {

              $rs = $tutor->find('all');
              return $rs;

              }
  // // allethiopia.org@gmail.com

        } else {
             return false;
            // throw new NotFoundException(__('Invalid Zip Code'));
        } //end of validates
}



public function no_submit_searchs($cur_zip_code) {

    $tutor = new Tutor();
    $this->set(array('zip_code' => $cur_zip_code));
    if ($this->validates(array('fieldList' => array('zip_code'))))
        {
         if(!empty($cur_zip_code)) {

           if(
                  (!$rs = $this->find(
                            'first', array(
                            'conditions' => array(
                                'ZipSearch.zip_code' => $cur_zip_code))))
                     || (count($rs) == 0)
                  )
            {

               // throw new NotFoundException(__('Zip Code Not Found'));
                   return false;
            } else {

                    // debug("test"); die();
                    $lat1 = $rs['ZipSearch']['latitude'];
                    $lon1 = $rs['ZipSearch']['longitude'];

                    $zip_search_distance = 20; //$postData['distance'];
                    $d = $zip_search_distance; //$postData['ZipSearch']['distance'];
                    //earth's radius in miles
                    $r = 3959;

                    //compute max and min latitudes / longitudes for search square
                    $latN = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(0))));
                    $latS = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(180))));
                    $lonE = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(90)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));
                    $lonW = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(270)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));


                   // $conditions = array(
                   //     'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                   //     'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                   //     'ZipSearch.latitude !=' => $lat1,
                   //     'ZipSearch.longitude !=' => $lon1,
                   //     'ZipSearch.city !=' => ''
                   //     );

                   $conditions = array(
                       'ZipSearch.latitude BETWEEN ? and ?' => array($latS, $latN),
                       'ZipSearch.longitude BETWEEN ? and ?' => array($lonW, $lonE),
                      'ZipSearch.city !=' => ''
                       );

                  //Not using this for now
                  /**
                  $this->Paginator->settings = array(
                    'limit' => 100,
                    'conditions' => $conditions,
                    'order' => array(
                        'ZipSearch.state' => 'asc',
                        'ZipSearch.city' => 'asc',
                        'ZipSearch.latitude' => 'asc',
                        'ZipSearch.longitude' => 'asc'
                     ));
                  **/

                 // chop this off

                  if (
                        ( !$rs = $this->find('all', array('conditions' => $conditions,'fields' => array('ZipSearch.zip_code'))))
                           || 
                        (count($rs) == 0)
                     )
                    {
                        return false;

                    } else {
                        debug($rs); die();
                        return $rs;
                    }
                 //chop it off  ends
            }
          } else {

              //$rs = $tutor->find('all');
              //return $rs;
               return false;

              }


        } else {
             throw new NotFoundException(__('Invalid Zip Code'));
        } //end of validates
  }
  
public function find_city_ByZipCode ($cur_zip_code) {
    
        $city = "";
        
         if(!empty($cur_zip_code)) {
             if  (
                      (!$rs = $this->find(
                                'first', array(
                                'conditions' => array(
                                    'ZipSearch.zip_code' => $cur_zip_code))))
                         || (count($rs) == 0)
                 )
            {

               // throw new NotFoundException(__('Zip Code Not Found'));
                   return false;
            } else {
                 $city = $rs['city'];
                }
            } else {
                return false;
            } 
        
        return $city;
    }
    
    
    
    
    
}
