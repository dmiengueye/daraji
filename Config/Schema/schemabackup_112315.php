<?php
App::uses('Categorie', 'Model');
App::uses('Subject', 'Model');
App::uses('ClassRegistry', 'Utility');
class AppSchema extends CakeSchema {

//http://stackoverflow.com/questions/18216235/cakephp-console-cake-schema-create-always-inserts-records-to-the-default-c
//http://book.cakephp.org/2.0/en/console-and-shells/schema-management-and-migrations.html

// Works. Creates the tables and inserts records successfully to the default

  //Console/cake schema create -s 1   This is the one I used to build my DB and insert records into categories and subjects tables
 //C:\wamp\www\cakephp-2.4.5\> cake schema generate  to generate schema.php  under app/Config then do the following
 // C:\wamp\www\cakephp-2.4.5\> cake schema create -s to insert records in Database test

// Breaks. Creates the tables under test but attempts to insert record in the default database
// Console/cake schema create -s 1 --connection test

    public $connection = 'test';

      public function before($event = array()) {
           $db = ConnectionManager::getDataSource($this->connection);
   		$db->cacheSources = false;
           return true;
       }

	   	public function after($event = array()) {
       //$categorie = ClassRegistry::init("Categorie");
	      	      //$categorie->useDbConfig = $this->connection;
	      	      //$subject = ClassRegistry::init("Subject");
	      	      //$subject->useDbConfig = $this->connection;

	      	  	    if (isset($event['create'])) {
	      	  	        switch ($event['create']) {
	      	  	            case 'categories':

	      	  	                $categorie = ClassRegistry::init('Categorie');
	      	  	                $categorie->useDbConfig = $this->connection;
	      	  	                $categorie->create();
	      	  	                $records = array(
	      	  	                                // array('Categorie' => array('name' => 'Sports', 'category_id' => 'Sports')),
	      	  	                                // array('Categorie' => array('name' => 'Arts', 'category_id' => 'Arts')),
	      	  	                                // array('Categorie' => array('name' => 'Special Needs', 'category_id' => 'SpecialNeeds')),
	      	  	                                // array('Categorie' => array('name' => 'Business & Finance', 'category_id' => 'BusinessFinance')),
	      	  	                                // array('Categorie' => array('name' => 'Foreign Languages', 'category_id' => 'ForeignLanguages')),
	      	  	                                // array('Categorie' => array('name' => 'Geography', 'category_id' => 'Geography')),
	      	  	                                // array('Categorie' => array('name' => 'History', 'category_id' => 'History')),
	      	  	                                // array('Categorie' => array('name' => 'Music', 'category_id' => 'Music')),
	      	  	                                // array('Categorie' => array('name' => 'Elementary School', 'category_id' => 'ElemSchool')),
	      	  	                                // array('Categorie' => array('name' => 'Middle School', 'category_id' => 'MiddleSchool')),
	      	  					                // array('Categorie' => array('name' => 'High School', 'category_id' => 'HighSchool')),
	      	  					                // array('Categorie' => array('name' => 'Test Preparation', 'category_id' => 'TestPrep')),
	      	  					                // array('Categorie' => array('name' => 'Computer Technology', 'category_id' => 'ComputerTech')),
	      	  					                // array('Categorie' => array('name' => 'Computer Science', 'category_id' => 'ComputerScience')),

	      	  					                 array('Categorie' => array('name' => 'Science', 'category_id' => 'Science')),
	      	  					                 array('Categorie' => array('name' => 'Technology', 'category_id' => 'Technology')),
	      	  					                 array('Categorie' => array('name' => 'Engineering', 'category_id' => 'Engineering')),
	      	  					                 array('Categorie' => array('name' => 'Math', 'category_id' => 'Math'))
	      	  						      );
	      	                 $categorie->saveAll($records);
	      	                 break;
	      	                 case 'subjects':
	      	  					$subject = ClassRegistry::init('Subject');
	      	  					$scategorie = ClassRegistry::init('Categorie');
	      	  					$subject->useDbConfig = $this->connection;
	      	  					$categories_var = $scategorie->find('all', array('conditions' => array('name' => 'Math')));
	      	  					//debug($categories_var); die();
	      	  				    $subject->create();
	      	  				    $cat_id = null;
	      	  				    if(!empty($categories_var)) {
	      	  				        $cat_id =  $categories_var[0]['Categorie']['id'];
	      	  				        //debug($cat_id); die();
	      	  				    }
	      	  					$records = array(
	      	  								   array('Subject' => array('name' => 'Pre-Algebra', 'subject_id' => 'PreAlg', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Algebra', 'subject_id' => 'Algebra','category_name' => 'Math','category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Algebra 1', 'subject_id' => 'Alg1', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),

	      	  								   array('Subject' => array('name' => 'Algebra 2', 'subject_id' => 'Alg2', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),

	      	  								   array('Subject' => array('name' => 'Pre-Calculus', 'subject_id' => 'PreCalc', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Calculus-I', 'subject_id' => 'Calc1','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Calculus-II', 'subject_id' => 'Calc2','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Calculus-III', 'subject_id' => 'Calc3','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Advanced Calculus', 'subject_id' => 'AdvCalc', 'category_name' => 'Math','category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Discrete Math', 'subject_id' => 'DisMath','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Linear Algebra', 'subject_id' => 'LinAlg','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Trigonometry', 'subject_id' => 'Trig','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Statistics', 'subject_id' => 'Stats','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Differential Equations', 'subject_id' => 'DiffEqu','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Econometry', 'subject_id' => 'Econometry', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'Numerical Analysis', 'subject_id' => 'NumAnalysis','category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'SAT-Math', 'subject_id' => 'SATMath', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id)),
	      	  								   array('Subject' => array('name' => 'ACT-Math', 'subject_id' => 'ACTMath', 'category_name' => 'Math', 'category_id' => 'Math', 'categorie_id' => $cat_id))
	      	  					  		);

	      	                      $subject->saveAll($records);
	      	                      $categories_var = $scategorie->find('all', array('conditions' => array('name' => 'Science')));
	      						 // debug($categories_var); die();
	      						  $cat_id = null;
	      						  if(!empty($categories_var)) {
	      						  $cat_id =  $categories_var[0]['Categorie']['id'];
	      						 //debug($cat_id); die();
	      	  				    }
	      	                      $records = array(
	      						  	  	    array('Subject' => array('name' => 'Biology', 'subject_id' => 'Biology', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Physics', 'subject_id' => 'Physics','category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Chemistry', 'subject_id' => 'Chem', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Biochemistry', 'subject_id' => 'BioChem', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Microbiology', 'subject_id' => 'MicroBio', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Geology', 'subject_id' => 'Geology', 'category_name' => 'Science', 'category_id' => 'Science','categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Physiology', 'subject_id' => 'Physiology', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Pharmacology', 'subject_id' => 'Pharma', 'category_name' => 'Science','category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Astronomy', 'subject_id' => 'Astronomy', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Astrophysics', 'subject_id' => 'AstroPhys', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Natural Sciences', 'subject_id' => 'NatSci', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Physical Science', 'subject_id' => 'PhySci', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Anatomy', 'subject_id' => 'Anatomy', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Nursing', 'subject_id' => 'Nursing', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Biostatistics', 'subject_id' => 'BioStat', 'category_name' => 'Science','category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'ACT-Science', 'subject_id' => 'ACTSci', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'SAT-Science', 'subject_id' => 'SATSci', 'category_name' => 'Science','category_id' => 'Science', 'categorie_id' => $cat_id)),
	      						  	  		array('Subject' => array('name' => 'Environmental Sciences', 'subject_id' => 'EnvSci', 'category_name' => 'Science', 'category_id' => 'Science', 'categorie_id' => $cat_id))
	      						  	 );

	      	                      $subject->saveAll($records);
	      	                      $categories_var = $scategorie->find('all', array('conditions' => array('name' => 'Technology')));
	      						 // debug($categories_var); die();
	      						  $cat_id = null;
	      						  if(!empty($categories_var)) {
	      						  	 $cat_id =  $categories_var[0]['Categorie']['id'];
	      						  	//debug($cat_id); die();
	      						  }
	      					$records = array(
	      						  	 array('Subject' => array('name' => 'Computer Technology', 'subject_id' => 'CompTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Cardiovascular Technology', 'subject_id' => 'CardioVascSci', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Engineering & Technology', 'subject_id' => 'EngTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Nanotechnology', 'subject_id' => 'NanoTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Veterinary Technology', 'subject_id' => 'VetTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Nuclear Medicine Technology', 'subject_id' => 'NucMedTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Surgical Technologist', 'subject_id' => 'SurgTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Business & Technology', 'subject_id' => 'BusTech', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id)),
	      						  	 array('Subject' => array('name' => 'Technology Patent Process', 'subject_id' => 'TPP', 'category_name' => 'Technology', 'category_id' => 'Technology', 'categorie_id' => $cat_id))
	      						 );

	      	             $subject->saveAll($records);
	      	             $categories_var = $scategorie->find('all', array('conditions' => array('name' => 'Engineering')));
	      				  //debug($categories_var); die();
	      				 $cat_id = null;
	      				 if(!empty($categories_var)) {
	      				 	 $cat_id =  $categories_var[0]['Categorie']['id'];
	      				 	//debug($cat_id); die();
	      				 }
	      				 $records = array(
	      				 	  array('Subject' => array('name' => 'Computer Engineering', 'subject_id' => 'CompEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Electrical Engineering', 'subject_id' => 'ElecEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Mechanical Engineering', 'subject_id' => 'MechEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Electronic Engineering', 'subject_id' => 'ElncEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Chemical Engineering', 'subject_id' => 'ChemEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Civil Engineering', 'subject_id' => 'CivilEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Aeronautical Engineering','subject_id' => 'AeroNEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Biomedical Engineering', 'subject_id' => 'BioMedEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Indutrial Engineering','subject_id' => 'IndEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Aerospace Engineering', 'subject_id' => 'AeroSEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	      				 	  array('Subject' => array('name' => 'Systems Engineering', 'subject_id' => 'SysEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	                          array('Subject' => array('name' => 'Architectural Engineering', 'subject_id' => 'ArchEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id)),
	                          array('Subject' => array('name' => 'Software Engineering', 'subject_id' => 'SoftEng', 'category_name' => 'Engineering', 'category_id' => 'Engineering', 'categorie_id' => $cat_id))
	      				 	);

	      	               $subject->saveAll($records);
	      	  	           break;
	      	  	        }
	      	    }
   		return true;
	}

	public $cake_sessions = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'expires' => array('type' => 'integer', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $categories = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'category_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'category_id' => array('column' => 'category_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);


	public $preferences = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'pref_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'user_type' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);


	public $student_preferences = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'student_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'new_features' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'promos' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'key' => 'index'),
		'daily_digest' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'new_tutor' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'lesson_review' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'sms_alerts' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'phone_number' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'carrier' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_PROFILE_PROPERTY' => array('column' => array('promos', 'student_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $student_profiles = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'student_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'gender' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'education' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'school' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'address_1' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'address_2' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'city' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'state' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'zip_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'primary_phone' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'pphone_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'secondary_phone' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'sphone_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'basic_profile_status' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_PROFILE_PROPERTY' => array('column' => 'student_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $students = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'password' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'zip_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'password_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email_verified' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'email_token' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => null, 'key' => 'unique'),
		'referal' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'last_action' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'role' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1),
			'email_token_expires' => array('column' => 'email_token_expires', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $subjects = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'categorie_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'subject_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'category_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'category_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'subject_id' => array('column' => 'subject_id', 'unique' => 1),
			'name' => array('column' => 'name', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutor_categories = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'category_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);
    
    public $tutor_ratings = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'overall_ratings' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'reviews' => array('type' => 'integer', 'null' => false, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);


	public $tutor_images = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'thumb_image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'thumb_medium' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'data_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'status' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'featured' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutor_photos = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'thumb_image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'thumb_medium' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'status' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutor_preferences = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'new_features' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'promos' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'key' => 'index'),
		'daily_digest' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'new_students' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'lesson_submission' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'sms_alerts' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'phone_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'carrier' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_PROFILE_PROPERTY' => array('column' => array('promos', 'tutor_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutor_profiles = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'gender' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'education' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'degree' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'school' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'hourly_rate' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'cancel_policy' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'travel_radius' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'address_1' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'address_2' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'city' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'state' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'zip_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'maddress_1' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'maddress_2' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'mcity' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'mstate' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'mzip_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'primary_phone' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'pphone_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'secondary_phone' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'sphone_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'mktplace_status' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'basicProfile_status' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'publicProfile_status' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'ica_status' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'mkt_place_rules' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'terms_of_use' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'work_auth' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'signed_agreement' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'tutor_signature' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'profile_status_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 1),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_PROFILE_PROPERTY' => array('column' => 'tutor_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutor_subjects = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_categorie_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'subject_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'subject_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'delete_status' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 1, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'approval_status' => array('type' => 'string', 'null' => false, 'default' => 'N/A', 'length' => 6, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'searchable_status' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 1),
		'credentials_status' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 1),
		'subject_credentials' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'opt_out' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'subject_category_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'subject_category_id' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $tutors = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'password' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'zip_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'password_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email_verified' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'email_token' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => null, 'key' => 'unique'),
		'referal' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'last_action' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'role' => array('type' => 'string', 'null' => true, 'default' => 'NULL', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'email_token_expires' => array('column' => 'email_token_expires', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

     public $student_search_agents = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'student_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'agent_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'search_query' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'student_id' => array('column' => 'student_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);
    
    public $student_watch_list = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'student_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'tutor_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 225, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'student_id' => array('column' => 'student_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);


}
