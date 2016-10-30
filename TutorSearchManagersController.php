<?php
/*
	* Reference Controller class
	*
	*
	* PHP version 5.3.13
	* @filesource
	* @author	Amit Kumar
*	 @link       http://www.smartdatainc.net/
	* @version 0.0.1 
	*   - Initial release
*/
class TutorSearchManagersController extends AppController
{
	public $name= 'TutorSearchManagers';
	public $uses =  array('TutorSearchManager','Student');
	//public $helpers = array('html','text','javascript','ajax','utility','pagination');
	//public $components = array('imageAuth','Pagination','RequestHandler','Cookie');
	
	/*
	* @Method      :createSearchAgent
	* @Description :for create search agent
	* @access      :registered User Group
	* @param       : 
	* @return      :null
	*/
	function createSearchAgent($agent_id=null,$agent_type=null){
		//$this->chkSession('JobSeeker');
		$this->layout='student';
		//$admin=$this->Auth->user();
		$seekerInfo = $this->Auth->user();
		$this->set('seekerInfo',$seekerInfo);
		
		$emailFormat = array("HTML"=>"HTML","TEXT"=>"TEXT");
		$emailFrequency = array("Daily"=>"Daily","Weekly"=>"Weekly");
		$this->set('emailFormat',$emailFormat);
		$this->set('emailFrequency',$emailFrequency);
		
		if(isset($agent_type) && $agent_type!="")
		$this->set('agent_type',$agent_type);
		else
		$this->set('agent_type',"");
		
		if(isset($agent_id) && $agent_id>0)
		$this->set('agent_id',$agent_id);
		else
		$this->set('agent_id',0);
		
		App::import('Model','JobAlertAgent');
		$this->JobAlertAgent = new JobAlertAgent();
		
		if(!empty($this->data))
		{
			if(isset($this->data['TutorSearchManager']['id']) && $this->data['TutorSearchManager']['id']!="")
			{
				$agent_id = $this->data['TutorSearchManager']['id'];
				$this->set('agent_id',$agent_id);
			} else {
				$agent_id = 0;
				$this->set('agent_id',$agent_id);
			}
			
			if(isset($this->data['TutorSearchManager']['agent_type']) && ($this->data['TutorSearchManager']['agent_type']=="s" || $this->data['TutorSearchManager']['agent_type']=="b"))
			{
				//check the uniqueness of search agent_name 
				if (!$this->JobSearchManager->isUnique('agent_name', $this->data['TutorSearchManager']['agent_name'], $agent_id))
				{
					$err['refno']=__('Search agent with this name is already exists. Please try another!',true);
				}
			}
			
			if(isset($this->data['TutorSearchManager']['agent_type']) && ($this->data['TutorSearchManager']['agent_type']=="s" || $this->data['TutorSearchManager']['agent_type']=="b") )
			{
				//check the uniqueness of job alert agent_name 
				if (!$this->JobAlertAgent->isUnique('agent_name', $this->data['TutorSearchManager']['agent_name'], $agent_id))
				{
					$err['refno']=__('Job alert agent with this name is already exists. Please try another!',true);
				}
			}
			
			if(isset($err) && count($err)>0)
			{
				$this->set('msgs',$err);
			} else {
				$session_search_criteria = $this->Session->read("session_search_criteria");
				
				if(is_array($session_search_criteria) && isset($session_search_criteria) &&  count($session_search_criteria)>0)
				{
					if(isset($session_search_criteria['keyword']) && $session_search_criteria['keyword']!="")
					{$this->data['TutorSearchManager']['keyword'] = $session_search_criteria['keyword'];}
					else
					{$this->data['TutorSearchManager']['keyword'] = "";}
					
					
					if(isset($session_search_criteria['match']) && $session_search_criteria['match']!="")
					{$this->data['TutorSearchManager']['match'] = $session_search_criteria['match'];}
					else
					{$this->data['TutorSearchManager']['match'] = "";}
					
					if(isset($session_search_criteria['country_id']) && $session_search_criteria['country_id']>0)
					{$this->data['TutorSearchManager']['country_id'] = $session_search_criteria['country_id'];}
					else
					{$this->data['TutorSearchManager']['country_id'] = "";}
					
					if(isset($session_search_criteria['state_id']) && $session_search_criteria['state_id']>0)
					{$this->data['TutorSearchManager']['state_id'] = $session_search_criteria['state_id'];}
					else
					{$this->data['TutorSearchManager']['state_id'] = "";}
					
					if(isset($session_search_criteria['city']) && $session_search_criteria['city']!="")
					{$this->data['TutorSearchManager']['city'] = $session_search_criteria['city'];}
					else
					{$this->data['TutorSearchManager']['city'] = "";}
					
					if(isset($session_search_criteria['postal_code']) && $session_search_criteria['postal_code']!="")
					{$this->data['TutorSearchManager']['postal_code'] = $session_search_criteria['postal_code'];}
					else
					{$this->data['TutorSearchManager']['postal_code'] = "";}
					
					if(isset($session_search_criteria['job_category_id']) && $session_search_criteria['job_category_id']!="" && $session_search_criteria['job_category_id']>0)
					{$this->data['TutorSearchManager']['job_category_id'] = $session_search_criteria['job_category_id'];}
					else
					{$this->data['TutorSearchManager']['job_category_id'] = "";}
						
					if(isset($session_search_criteria['industry_id']) && $session_search_criteria['industry_id']>0)
					{$this->data['TutorSearchManager']['industry_id'] = $session_search_criteria['industry_id'];}
					else
					{$this->data['TutorSearchManager']['industry_id'] = "";}
					
					if(isset($session_search_criteria['job_posted']) && $session_search_criteria['job_posted']!="0000-00-00")
					{$this->data['TutorSearchManager']['job_posted'] = $session_search_criteria['job_posted'];}
					else
					{$this->data['TutorSearchManager']['job_posted'] = "";}
					
					if(isset($session_search_criteria['posted_within']) && $session_search_criteria['posted_within']!="")
					{$this->data['TutorSearchManager']['posted_within'] = $session_search_criteria['posted_within'];}
					else
					{$this->data['TutorSearchManager']['posted_within'] = "";}
					
					if(isset($session_search_criteria['job_type_id']) && $session_search_criteria['job_type_id']>0)
					{$this->data['TutorSearchManager']['job_type_id'] = $session_search_criteria['job_type_id'];}
					else
					{$this->data['TutorSearchManager']['job_type_id'] = "";}
					
					if(isset($session_search_criteria['experience']) && $session_search_criteria['experience']!="")
					{$this->data['TutorSearchManager']['experience'] = $session_search_criteria['experience'];}
					else
					{$this->data['TutorSearchManager']['experience'] = "";}
					
				}
			
				if(isset($this->data['TutorSearchManager']['agent_type']) && $this->data['TutorSearchManager']['agent_type']!="")
				{
					$agent_type = $this->data['TutorSearchManager']['agent_type'];
				}
				
				//pr($this->data);
				//die;
				
				if(!isset($this->data['TutorSearchManager']['base_type']) || $this->data['JobSearchManager']['base_type']=="")
				{
					$this->data['TutorSearchManager']['base_type'] = $agent_type;
				}
				
				if(isset($this->data['TutorSearchManager']['base_type']) && ($this->data['TutorSearchManager']['base_type']=='s' || $this->data['TutorSearchManager']['base_type'] == 'b') )
				{
					
					if(isset($agent_type) &&  $agent_type=='a')
					{
						unset($this->data['TutorSearchManager']['id']);
						$this->data['JobAlertAgent'] = $this->data['TutorSearchManager'];
						unset($this->data['TutorSearchManager']);
						
						if($this->JobAlertAgent->save($this->data))
						{
							$this->Session->setFlash('<div class="success-info">'.__('Record has been saved successfully.',true).'</div>');
						}
					}else{
					
						if($this->JobSearchManager->save($this->data))
						{
							$this->Session->setFlash('<div class="success-info">'.__('Record has been saved successfully.',true).'</div>');
						}
					}
				}
				
				
				if(isset($this->data['TutorSearchManager']['base_type']) && ($this->data['TutorSearchManager']['base_type']=='a' || $this->data['TutorSearchManager']['base_type'] == 'b') ){
					$this->data['JobAlertAgent'] = $this->data['TutorSearchManager'];
					
					if(isset($agent_type) &&  $agent_type=='s')
					{
						unset($this->data['JobAlertAgent']['id']);
						$this->data['TutorSearchManager'] = $this->data['JobAlertAgent'];
						unset($this->data['JobAlertAgent']);
						
						if($this->JobSearchManager->save($this->data['TutorSearchManager']))
						{
							$this->Session->setFlash('<div class="success-info">'.__('Record has been saved successfully.',true).'</div>');
						}
					}else{
						unset($this->data['TutorSearchManager']);
						if($this->JobAlertAgent->save($this->data['JobAlertAgent']))
						{
							$this->Session->setFlash('<div class="success-info">'.__('Record has been saved successfully.',true).'</div>');
						}
					}
				}
				
				if(isset($agent_type) && $agent_type == 'a')
				$this->redirect('/job_alert_agents/listAgentAlert');
				else
				$this->redirect('/job_search_managers/listSearchAgent');
			}
			
		}
		
		//$sessionCriteria = $this->Session->read("critForSearchAgent");
		//$this->data['TutorSearchManager'] =	$sessionCriteria['EmployerJob'];
		if((int)$agent_id>0 && $agent_id!="")
		{
			if(isset($agent_type) && $agent_type=='s')
			{
				$this->data = $this->TutorSearchManager->findById($agent_id);
				
			} elseif(isset($agent_type) && $agent_type=='a') {
				
				$this->data = $this->JobAlertAgent->findById($agent_id);
				$this->data['TutorSearchManager'] = $this->data['JobAlertAgent'];
				unset($this->data['JobAlertAgent']);
			}
			$this->set('agent_id',$agent_id);
		}
  	}

		/*
        * @Method      :listSearchAgent
        * @Description :for list search agent
        * @access      :registered User Group
        * @param       : 
        * @return      :null
        */
        function listSearchAgent(){
			$this->layout='seeker';
			
			$seekerInfo = $this->Auth->user();
			$this->set('seekerInfo',$seekerInfo);
			
			$id = $this->Auth->user("id");
			
			$conditions = "TutorSearchManager.job_seeker_id = '".$this->Auth->user('id')."'";

			$pagingUrl="/job_search_managers/listSearchAgent/";
 			$url="/job_search_managers/listSearchAgent/";
 			$element = 'list_search_agent';
 			$fields ='TutorSearchManager.id,TutorSearchManager.agent_name,TutorSearchManager.status,DATE_FORMAT(TutorSearchManager.created,"%m/%d/%Y") as created, DATE_FORMAT(JobSearchManager.modified,"%m/%d/%Y") as modified';
 	
 			$searchAgentList = $this->commonPaging('TutorSearchManager',$pagingUrl,$conditions,'0','agent_list','ajax','loaderID',$url,"TutorSearchManager.id DESC",null,null,$fields);
			
			$this->set('searchAgentList', $searchAgentList);
			if($this->RequestHandler->isAjax()){
				$this->viewPath = 'elements'.DS.'search_job';
				$this->render($element,'ajax');
			}//if
			
			$cond_for_active = "TutorSearchManager.job_seeker_id = '".$this->Auth->user('id')."' AND  TutorSearchManager.status = '1'";

			$activeAgent = $this->TutorSearchManager->find('first',array('conditions'=>array($cond_for_active)));
			$this->set('activeAgent', $activeAgent);
			
			
			#_____________Left Menu Starts____________________#
			
			//get blocked companies count
			$this->set('countBlocked',$this->getBlockedCompanies("count"));
			
			//Get seekers resume from app controller
			$resumeInfo = $this->getSeekerResumes();
			$resumecount = count($resumeInfo);
			$this->set("resumecount",$resumecount);
			$this->set("resumeInfo",$resumeInfo);
			
			//Get references from app controller
			$references = $this->getSeekerReferences("count");
			$this->set('countReferences', $references);
				
			//Get recommendations from app controller
			$recommendations = $this->getSeekerRecommendations("count");
			$this->set('countRecommendations', $recommendations);
		
			//Get cover letters from app controller
			$coverLetters = $this->getSeekerCoverLetters("count");
			$this->set('letterCount',$coverLetters);
			
			//Get count Employer who viewed me from app controller
			$countEmployerWhoViewedMe = $this->getCountEmployerWhoViewedMe();
			$this->set('countEmployerWhoViewedMe', $countEmployerWhoViewedMe);
			
			//Get saved job count
			$savedJobCount = $this->getSavedJobCount();
			$this->set("savedJobCount",$savedJobCount);
			
			//Get My Apply History from app controller
			$applyJobsList = $this->getMyApplyHistory("count");
			$this->set('countApplyJobsList', $applyJobsList);
			
			//Get Job Search Agents from app controller
			$searchAgents = $this->getJobSearchAgents("count");
			$this->set("countSearchAgents",$searchAgents);
		
			//Get Job Search Agents from app controller
			$alertAgents = $this->getJobAlertAgents("count");
			$this->set("countAlertAgents",$alertAgents);
			
			///Get Blocked Company//////////////////
  		$numBlockCompanies=$this->getBlockedCompanies("count");
      $this->set("numBlockCompanies",$numBlockCompanies);
		
			#_____________Left Menu Ends____________________#
			
			
		}
		
		
		/*
        * @Method      :updateAgentStatus
        * @Description :for update agent agent
        * @access      :registered User Group
        * @param       :
        * @return      :null
        */
        function updateAgentStatus($agent_id=null,$status=null){
			$this->layout='ajax';
			//$admin=$this->Auth->user();
			
			$seekerInfo = $this->Auth->user();
			$this->set('seekerInfo',$seekerInfo);
			$id = $this->Auth->user("id");
			
			$this->set('agent_id',$agent_id);
			$this->set('agent_status',$status);

			$status = 0;
			$newstatus = 1;
			
			$this->TutorSearchManager->updateAll(array('TutorSearchManager.status' => "'".$status."'"), array('TutorSearchManager.job_seeker_id'=>$this->Auth->user("id")));
			
			$this->TutorSearchManager->updateAll(array('TutorSearchManager.status' => "'".$newstatus."'"), array('TutorSearchManager.id'=>$agent_id));
			
			$conditions = "TutorSearchManager.job_seeker_id = '".$this->Auth->user('id')."'";
			
			$pagingUrl="/job_search_managers/listSearchAgent/";
 			$url="/job_search_managers/listSearchAgent/";
 			$element = 'list_search_agent';
 			$fields ='TutorSearchManager.id,TutorSearchManager.agent_name,TutorSearchManager.status,TutorSearchManager.created, TutorSearchManager.modified';
			
 			$searchAgentList = $this->commonPaging('TutorSearchManager',$pagingUrl,$conditions,'0','agent_list','ajax','loaderID',$url,'TutorSearchManager.id DESC',null,null,$fields);
			
			$this->set('searchAgentList', $searchAgentList);
			if($this->RequestHandler->isAjax()){
				$this->viewPath = 'elements'.DS.'search_job';
				$this->render($element,'ajax');
			}//if
			
			$cond_for_active = "TutorSearchManager.job_seeker_id = '".$this->Auth->user('id')."' AND  TutorSearchManager.status = '1'";
			
			$activeAgent = $this->TutorSearchManager->find('first',array('conditions'=>array($cond_for_active)));
			$this->set('activeAgent', $activeAgent);
			
		}
		
		
		
		/*
		*************************************************************************
		*Function Name		 :	deleteAgent
		*Functionality		 :	delete agent
		*************************************************************************
		*/
		function deleteAgent($id=null,$agent_type=null)
		{
			$seeker_id = $this->Auth->user("id");
			
			if(isset($id) OR isset($agent_type))
			{	
				if($this->TutorSearchManager->delete($id))
				{
					$this->Session->setFlash('<div class="success-info">Agent deleted successfully</div>');
				}
			}
			$this->redirect('/job_search_managers/listSearchAgent/'.$agent_type);
		}
		
		/*
		*************************************************************************
		*Function Name		 :	myApplyHistory
		*Functionality		 :	list my apply history
		*************************************************************************
		*/
		function myApplyHistory()
		{
			$this->layout='seeker';
			$seekerInfo = $this->Auth->user();
			$this->set('seekerInfo',$seekerInfo);
			
			$id = $this->Auth->user("id");
			
			$element = "my_apply_history_list";
			
			//Get my apply history form App Controller 
			$applyJobsList = $this->getMyApplyHistory();
			
			$this->set('applyJobsList', $applyJobsList);
			$this->set('countApplyJobsList', count($applyJobsList));
			if($this->RequestHandler->isAjax()){
				$this->viewPath = 'elements'.DS.'job_seeker';
				$this->render($element,'ajax');
			}//if
			
			#_____________Left Menu Starts____________________#
			
			//get blocked companies count
			$this->set('countBlocked',$this->getBlockedCompanies("count"));
			
			//Get seekers resume from app controller
			$resumeInfo = $this->getSeekerResumes();
			$resumecount = count($resumeInfo);
			$this->set("resumecount",$resumecount);
			$this->set("resumeInfo",$resumeInfo);
			
			//Get references from app controller
			$references = $this->getSeekerReferences("count");
			$this->set('countReferences', $references);
			
			//Get count Employer who viewed me from app controller
			$countEmployerWhoViewedMe = $this->getCountEmployerWhoViewedMe();
			$this->set('countEmployerWhoViewedMe', $countEmployerWhoViewedMe);
			
			//Get recommendations from app controller
			$recommendations = $this->getSeekerRecommendations("count");
			$this->set('countRecommendations', $recommendations);
		
			//Get cover letters from app controller
			$coverLetters = $this->getSeekerCoverLetters("count");
			$this->set('letterCount',$coverLetters);
			
			//Get saved job count
			$savedJobCount = $this->getSavedJobCount();
			$this->set("savedJobCount",$savedJobCount);
			
			//Get My Apply History from app controller
			$applyJobsList = $this->getMyApplyHistory("count");
			$this->set('countApplyJobsList', $applyJobsList);
			
			//Get Job Search Agents from app controller
			$searchAgents = $this->getJobSearchAgents("count");
			$this->set("countSearchAgents",$searchAgents);
		
			//Get Job Search Agents from app controller
			$alertAgents = $this->getJobAlertAgents("count");
			$this->set("countAlertAgents",$alertAgents);
			
			//Get Count Block Companies from app controller
			$numBlockCompanies=$this->getBlockedCompanies("count");
			$this->set("numBlockCompanies",$numBlockCompanies);
		
			#_____________Left Menu Ends____________________#
		}
		
		/*
        * @Method      :selectSearchAgent
        * @Description :for selecting serach agent resume row 
        * @access      :registered User Group
        * @param       :agent id
        * @return      :null
        */
		function selectSearchAgent($id,$action=null)
		{
			$seekerInfo = $this->Auth->user();
			$this->set('seekerInfo',$seekerInfo);
			$seeker_id = $this->Auth->user("id");
			
			$agent_id = $id;
			$this->set('agent_id',$agent_id);

			$status = 0;
			$newstatus = 1;
			
			$this->TutorSearchManager->updateAll(array('TutorSearchManager.status' => "'".$status."'"), array('TutorSearchManager.job_seeker_id'=>$this->Auth->user("id")));
			
			$this->TutorSearchManager->updateAll(array('TutorSearchManager.status' => "'".$newstatus."'"), array('TutorSearchManager.id'=>$agent_id));
			
			$this->layout="ajax";
			$this->set("id",$id);
			if($action!=null && $action=='delete')
			{
				$this->layout="seeker";
				App::Import("Model","TutorSearchManager");
				$TutorSearchManager= new TutorSearchManager();
				$TutorSearchManager->delete($id);
				$this->Session->setFlash('<div class="success-info">Search Agent Deleted Successfully!</div>');
				$this->redirect('/job_search_managers/listSearchAgent');
				//echo "<tr><td colspan='5'>Record Deleted Sucessfully!</td></tr>";
				die;
			}
		}//end function
}//ec
?>