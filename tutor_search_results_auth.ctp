<?php
     $this->Html->script('jquery', array('inline' => false));
     $this->Html->script('bootstrap.min', array('inline' => false));
     $this->Html->script('https://code.jquery.com/ui/1.10.4/jquery-ui.js', array('inline' => false));
         
     $this->Html->css('bootstrap', null, array('inline' => false));
     $this->Html->css('http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css', null, array('inline' => false));        
     $this->Html->css('mystyle', null, array('inline' => false));
     $this->Html->css('pagination_style', null, array('inline' => false));
    $this->Html->css('http://fonts.googleapis.com/css?family=Open+Sans', null, array('inline' => false));   

    if(!isset($tutors)) {
      $tutors = array();
    } 
    
if(!empty($tutors) && sizeof($tutors) > 0) {
    if(!empty($this->params['url']['kwd']) && $this->params['url']['kwd'] === "Distance") {
            $i=0;
            foreach($tutors as $key => $value) {
                //$d= $i;
                if(!empty($zip)) {
                   $d = h($this->ZipCode->get_distance_between_zipcodes($zip, 
                                                          $tutors[$i]['TutorProfile']['zip_code'], 
                                                          $distance));
                   } else {
                    $d = h($this->ZipCode->get_distance_between_zipcodes($this->Session->read('cur_zip_code'), 
                                                          $tutors[$i]['TutorProfile']['zip_code'], 
                                                          $distance));
                   }
                
                $inserted = array('dis' => $d);
                array_splice( $tutors[$i]['Tutor'], 16, 0, $inserted);
                $i++; 
             }
              //debug($tutors); die();
              usort($tutors, 'sortByOrder');
             // debug($tutors); die();
         
             /** usort($tutors, array($this, function ($a, $b) {
                    return $a['Tutor']['0'] - $b['Tutor']['0'];
              }));
              **/
        }
   }
   function sortByOrder($a, $b) {
      return h($a['Tutor']['0'] - $b['Tutor']['0']);
   } 
?>
<div class="aboutus">

    <div class="container">
    <br />
<br />
    <div class="row searchall">
  <div class="col-md-3 leftsearch">
  <!--
  <button type="button" class="btn btn-primary mybtn" style="">Save As Search Helper</button><br /><br />
 -->
 <?php
	echo $this->Form->create($model, array('action' => 'edit_search_agent', 'type' => 'get'));
  
 if(!empty($update_agent) && !empty($agent_id) && !empty($agent_name) && !empty($id)) {
  // debug($agent_name); die();
   
   echo $this->Form->input('ZipSearch.agent_id', array('type' => 'hidden', 'value' => $agent_id, 'required' => false, 'class' => 'form-control subject_tag', 'div' => false)); 
   echo $this->Form->input('ZipSearch.agent_name', array('type' => 'hidden', 'value' => $agent_name, 'required' => false, 'class' => 'form-control subject_tag', 'div' => false)); 
   echo $this->Form->input('ZipSearch.id', array('type' => 'hidden', 'value' => $id, 'required' => false, 'class' => 'form-control subject_tag', 'div' => false)); 
   
   echo $this->Form->submit(__d('users', 'Update Search Agent'), array('class' => 'btn btn-success'));
   
  } else {
          echo $this->Form->submit(__d('users', 'Save Search Agent'), array('class' => 'btn btn-primary mybtn'));
 
 } 
 echo $this->Form->end();
 ?>
  <!--  
   <a href="/students/search_agent"  class="btn btn-warning" style="">Save Results As A One-Click Search Agent</a>
  -->  	
<?php echo $this->Form->create($model, array('type' => 'get')); ?>
<br />
  	<div class="highlight_grey">
    	<strong>Find Great Tutors/SMEs</strong> <br />
		<!--<input type="text" class="form-control" placeholder="Subject" /> -->
        <?php 
              echo $this->Form->input('ZipSearch.subject', array('placeholder' => 'Subject', 'required' => false, 'class' => 'form-control subject_tag', 'div' => false));                            
        ?>
        <br />
        <div class="row">
        <div class="col-md-5">
        Zip Code <br />
        
	<!--	<input type="text" class="form-control padding5"  name="" onkeyup="checkInput(this)" placeholder="Zip Code"><br />
     -->
         <?php 
            $zip = $this->Session->read('cur_zip_code'); 
             if(!empty($zip)) {
              echo $this->Form->input('ZipSearch.zip_code', array('onkeyup' => 'checkInput(this)', 'label' => false, 'value' => $zip, 'required' => false, 'class' => 'form-control padding5', 'div' => false ));                            
          } else {
             echo $this->Form->input('ZipSearch.zip_code', array('onkeyup' => 'checkInput(this)', 'label' => false, 'placeholder' => 'Zip Code', 'required' => false, 'class' => 'form-control padding5', 'div' => false ));             
          }
              echo $this->Form->input('ZipSearch.is_advanced', array('type' => 'hidden', 'value' => 0, 'div' => false)); 
              echo $this->Form->input('ZipSearch.cur_page', array('type' => 'hidden', 'value' => 1, 'div' => false)); 
        
         ?>
        </div>
        
     <div class="col-md-7 padding_left_no"> 
        Distance<br />
       <!-- <select class="form-control">
        <option>1 mile</option>
	    <option>5 miles</option>
	    <option>10 miles</option>
	    <option>15 miles</option>
	    <option>20 miles</option>
	    <option>25 miles</option>
	    <option>30 miles</option>
	    <option>35 miles</option>
	    <option>40 miles</option>
	    <option>45 miles</option>
        <option>50 miles</option>
        </select><br />
     -->
        
     <?php echo $this->Form->input('ZipSearch.distance', array('label' => false, 'options' => Configure::read('distances'), 'class' => 'form-control', 'div' => false));?>
                               
      <br />
		<!--<a href="#" id="showadvance">Advance Search +</a>-->
     </div> 
  <?php echo $this->Form->create($model, array('type' => 'get')); ?>
        <div class="advance" id="toggleText" style="display:none">
        	<!--<input type="text" class="form-control" placeholder="Subject 2" /> -->
             <?php 
              //echo $this->Form->input('ZipSearch.subject_2', array('placeholder' => 'Subject 2', 'required' => false, 'class' => 'form-control subject_tag', 'div' => false                                                     
                         //      ));     
           ?>
            <br />
           <!-- <input type="text" class="form-control" placeholder="Subject 3" /> -->
             <?php 
            //  echo $this->Form->input('ZipSearch.subject_3', array('placeholder' => 'Subject 3', 'required' => false, 'class' => 'form-control subject_tag', 'div' => false                                                      
                           //    ));                            
           ?>
            <br />
            <span><strong>Hourly Rate</strong></span><br />
            <div id="slider-range_rate"></div>
            <br />
			<div class=" table-responsive">
            <table width="70%">
              <tr><td scope="col">$</td>
              
                <td scope="col">
                   <?php 
                      echo $this->Form->input('ZipSearch.amount_min_rate', array('label' => false, 'id' => 'amount_min_rate', 'required' => false, 'readonly' => 'readonly', 'class' => 'form-control padding5', 'style'=> 'font-weight:bold', 'div' => false                                                      
                               ));                            
                   ?>
                  <!-- 
                   <input class="form-control padding5" type="text" id="amount_min_rate" disabled="true" value="" style=" font-weight:bold;"> 
                  -->
                </td>
                
                <td scope="col">&nbsp;&nbsp;<b>to</b></td>
                <td scope="col">&nbsp;&nbsp;</td>
                <td scope="col"> $</td>
                
                <td scope="col">
                   <?php 
                      echo $this->Form->input('ZipSearch.amount_max_rate', array('label' => false, 'id' => 'amount_max_rate', 'required' => false, 'readonly' => 'readonly', 'class' => 'form-control padding5', 'style'=> 'font-weight:bold', 'div' => false                                                      
                               ));                            
                   ?>
                   <!-- <input class="form-control padding5" type="text" id="amount_max_rate" disabled="true" value="" style=" font-weight:bold;"> -->
                </td>
                
              </tr>
            </table>
              
            </div>
            <br />
			
            <span><strong>Age</strong></span><br />
            <div id="slider-range_age"></div>
            
            <div class=" table-responsive">
            <table width="55%">
              <tr>
                <td scope="col">
                 <?php 
                      echo $this->Form->input('ZipSearch.min_age', array('label' => false, 'id' => 'min_age', 'required' => false, 'readonly' => 'readonly', 'class' => 'form-control padding5', 'style'=> 'font-weight:bold', 'div' => false,                                                      
                               ));                            
                   ?>
                <!-- 
                   <input class="form-control padding5" type="text" id="min_age" disabled="true" style=" font-weight:bold;">
                -->
                </td>
                <td scope="col">&nbsp;&nbsp;</td>
                <td scope="col"><b>to</b></td>
                <td scope="col">&nbsp;&nbsp;</td>
                <td scope="col">
                <?php 
                      echo $this->Form->input('ZipSearch.max_age', array('label' => false, 'id' => 'max_age', 'required' => false, 'readonly' => 'readonly', 'class' => 'form-control padding5', 'style'=> 'font-weight:bold', 'div' => false,                                                      
                               ));                            
                   ?>
                   
                  <!-- 
                   <input class="form-control padding5" type="text" id="max_age" disabled="true" style=" font-weight:bold;"> 
                   -->
                </td>
                <td scope="col">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
              </tr>
            </table>
			
			<table width="60%">
              <tr>
                <td scope="col" width="40%"><strong>Gender</strong></td>
                </tr>
                <tr>
                <td scope="col">
              
                   <?php echo $this->Form->input('ZipSearch.gender', array('label' => false, 'empty' => array('No Preference'), 'options' => Configure::read('gender'), 'class' => 'form-control', 'div' => false));?>
                       
                   </td>
              </tr>
            </table>
          
            <!-- <div class="checkbox b_chk">
                <label> 
                   <input type="checkbox" class="mycheckbox"/><strong>&nbsp;Background check on file</strong>
               -->
               <?php

               echo $this->Form->input('ZipSearch.bg_checked', array('type' => 'checkbox', 'hiddenField' => false, 'class' => 'mycheckbox', 'label' => __d('students', '<strong>&nbsp;Background Checked</strong>'),
                 'div' => array('class' => 'checkbox b_chk' )                                                           

                 ));
                 ?>
               <!-- 
                </label>
              </div> -->
              
            </div>       	
        </div>
        
        <div class="col-md-5">
        <!-- <button type="button" class="btn btn-primary mybtn" style="">Search</button>-->
         <?php echo $this->Form->submit('Search', array('class' => 'btn btn-primary mybtn', 'div' => false)); ?>                    
        </div>
        
        <div class="col-md-7 padding_left_no">
                <a id="displayText" class="btn btn-link" href="javascript:toggle();">Advance Search +</a>

        </div>
         <?php echo $this->Form->end(); ?>
        </div>
        
    </div>
    <div class="highlight">
        <div class="watchlistbox">
        <strong>My Tutor Watch List</strong><br /><br />
    
        <?php
                      for($i=0;$i<0;$i++)
                      {
        ?>
        
     
        <div class="row small_profile" id="removewatch">
            <div class="col-md-5 padding_right_no"><img src="/img/no_img.jpg" width="70px"><br />
                <div class="stars">
                    <i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i>
                </div>
                125 Reviews</div>
            <div class="col-md-7 padding_left_no" >John. D  <br />
    BS, Mathematics<br />
    University Of Maryland<br /><br />
    
    <a href="/students/tutor_details_profile_auth" >View Me</a> &nbsp; <a href="javascript:void(0);"   onclick='$("#removewatch").slideUp("normal", function() { $(this).remove(); } );preventDefault();'>Remove</a>
    
    
    </div>
            
        </div> 
    
        <!--end small profile-->
        <?php
        }
        ?>
      
        </div>
        <!--<a href="#" class=" pull-right"> More >>> </a>-->
        <div class="clearfix"></div>
    </div> <!--end highlight box-->
    <!--<div class="highlight_grey">-->
    <div class="highlight">
    <strong>My Tutor Search Agents</strong> &nbsp;&nbsp;&nbsp;
<?php 
   if(sizeof($search_agents) > 0) {
     echo $this->Html->link(
         'View All',
         'tutor_search_tools',
          array('class' => 'button', 'target' => '_blank')
    ); }
?>
<br /><br />
    <?php 
       if(!empty($search_agents) && sizeof($search_agents) > 0) {
        
             $i=0;
            //debug($search_agents); die();
            //if($i < 5) {
  	          foreach ($search_agents as $search_agent):
                 if(!empty($search_agent['StudentSearchAgent'])) {
                    //debug("test"); die();
                 if($i < 5) {
                    $agent_name = $search_agent['StudentSearchAgent']['agent_name'];
                   // debug($agent_name); die();
                    $agent_id = $search_agent['StudentSearchAgent']['agent_id'];
                    $id = $search_agent['StudentSearchAgent']['id'];
                    $search_query = $search_agent['StudentSearchAgent']['search_query'];
                    $subject_p = $search_agent['StudentSearchAgent']['subject'];
                    $zip_code_p = $search_agent['StudentSearchAgent']['zip_code'];
                    $distance_p = $search_agent['StudentSearchAgent']['distance'];
                    
                    $min_rate_p = $search_agent['StudentSearchAgent']['min_rate'];
                    $max_rate_p = $search_agent['StudentSearchAgent']['max_rate'];
                    
                    $min_age_p = $search_agent['StudentSearchAgent']['min_age'];
                    $max_age_p = $search_agent['StudentSearchAgent']['max_age'];
                    
                    $gender_p = $search_agent['StudentSearchAgent']['gender'];
                    $bg_checked_p = $search_agent['StudentSearchAgent']['bg_checked'];
                    //debug($bg_checked_p);
                    $kwd_p = $search_agent['StudentSearchAgent']['kwd'];
                    $is_advanced_p = $search_agent['StudentSearchAgent']['is_advanced'];
                    $cur_page_p = $search_agent['StudentSearchAgent']['cur_page'];
                    
                    
                    
                    //debug($subject);
                    //$i++;
                    //$this->Session->write($agent_id, $search_agent);
                                                 
     ?>
     
   	<div class="row mytooltip">
    	<div class="col-md-7 col-xs-6 padding_right_no"><?php echo '<b>'.$agent_name.'</b>' ?></div>
        
    	<div class="col-md-1 col-xs-2 ">
        <!--<a href="#" data-toggle="tooltip" data-placement="top" title="Run" >-->
     
      <?php  
          echo $this->Html->link(
                            $this->Html->image('/img/run.png', array('alt' => 'Run')),
                            $search_query.'&agent_name='.$agent_name.'&agent_id='.$agent_id.'&update_agent=1',
							//$search_query,
                           //"/students/tutor_search_results/?",
                            array('escape' => false)
                           );
      ?>
      
      
        </div>
   	   <div class="col-md-1 col-xs-2">
        <!--<a href="#" data-toggle="tooltip" data-placement="top" title="Edit"><img src="/img/edit.png"/></a>-->
        
        
         
           <a href="/students/search_agent/<?php echo $id?>"  data-id="<?php echo $id ?>" data-toggle="modal" data-target="#edit"><img src="/img/edit.png"/></a>
         
        <?php 
          echo $this->Form->input('HiddenSubject', array('id' => 'HiddenSubject'.$id, 'type' => 'hidden', 'label' => false, 'value' => $subject_p, 'div' => false));  
          echo $this->Form->input('HiddenZipCode', array('id' => 'HiddenZipCode'.$id, 'type' => 'hidden', 'label' => false, 'value' => $zip_code_p, 'div' => false)); 
          echo $this->Form->input('HiddenDistance', array('id' => 'HiddenDistance'.$id, 'type' => 'hidden', 'label' => false, 'value' => $distance_p, 'div' => false));
           
          echo $this->Form->input('HiddenMinRate', array('id' => 'HiddenMinRate'.$id, 'type' => 'hidden', 'label' => false, 'value' => $min_rate_p, 'div' => false)); 
          echo $this->Form->input('HiddenMaxRate', array('id' => 'HiddenMaxRate'.$id, 'type' => 'hidden', 'label' => false, 'value' => $max_rate_p, 'div' => false));             
          
          echo $this->Form->input('HiddenMinAge', array('id' => 'HiddenMinAge'.$id, 'type' => 'hidden', 'label' => false, 'value' => $min_age_p, 'div' => false)); 
          echo $this->Form->input('HiddenMaxAge', array('id' => 'HiddenMaxAge'.$id, 'type' => 'hidden', 'label' => false, 'value' => $max_age_p, 'div' => false));             
          
          echo $this->Form->input('HiddenGender', array('id' => 'HiddenGender'.$id, 'type' => 'hidden', 'label' => false, 'value' => $gender_p, 'div' => false)); 
          echo $this->Form->input('HiddenBgChecked', array('id' => 'HiddenBgChecked'.$id, 'type' => 'hidden', 'label' => false, 'value' => $bg_checked_p, 'div' => false));             
          
          echo $this->Form->input('HiddenKwd', array('id' => 'HiddenKwd'.$id, 'type' => 'hidden', 'label' => false, 'value' => $kwd_p, 'div' => false));             
          echo $this->Form->input('HiddenIsAdv', array('id' => 'HiddenIsAdv'.$id, 'type' => 'hidden', 'label' => false, 'value' => $is_advanced_p, 'div' => false));             
          echo $this->Form->input('HiddenCurPage', array('id' => 'HiddenCurPage'.$id, 'type' => 'hidden', 'label' => false, 'value' => $cur_page_p, 'div' => false));             
          
          echo $this->Form->input('HiddenAgentNameP', array('id' => 'HiddenAgentNameP'.$id, 'type' => 'hidden', 'label' => false, 'value' => $agent_name, 'div' => false));             
          echo $this->Form->input('HiddenAgentIdP', array('id' => 'HiddenAgentIdP'.$id, 'type' => 'hidden', 'label' => false, 'value' => $agent_id, 'div' => false));             
          echo $this->Form->input('HiddenIdP', array('id' => 'HiddenIdP'.$id, 'type' => 'hidden', 'label' => false, 'value' => $id, 'div' => false));             
          
          
          //$imgEdit =  $this->Js->link($this->Html->image('/img/edit.png'));
          //echo $this->Js->link("Edit Agent" , "/students/search_agent/".$id,
                 // array('update' => '#edit',
                     // 'htmlAttributes' => array(
                        //  'data-id' => $id,
                          //'data-toggle' => 'modal',
                          //'data-target' => '#edit',
                      
                      //)));
          ?>
       </div>
   	   <div class="col-md-1 col-xs-2">
           <a href="#" data-toggle="tooltip" data-placement="top" title="Delete">
        
        <!--<img src="/img/delete.png"/></a>-->
       <?php                
                    echo $this->Form->postLink(
                        'Delete',
                        array('action' => 'delete_agent', $id),
                        array('confirm' => 'Are you sure you wish to delete this Agent?')
                    );
                  ?>
        
        </div>
    </div>
    <br /> 
    <?php 
    $i++;
      }} endforeach; //die();
    }  else {
    ?>
    
    <!-- <strong>My Tutor Search Agent</strong><br /><br />-->
        <p>
        A <b>"Tutor Search Agent"</b> allows you to do a One-Click Search. It's a quick, efficient way to find tutors without typing anything into a search Box. You Only need to build it Once, save It and run It everytime you need to Search for tutors that match the Saved criteria. Start Building your Tutor Search Agents.<br /><br />
        <b> 1.Search above with Search Criteria <br />
         2. Click "Search"  <br />
         3. View Results  <br />
         4. Click "Save AS Search Helper"   <br />
         5. Give your Agent a Name and Voila!!!<br />
         6. The Name Appears here <br />
         7. Click on it to bring Save Results </b>
        </p>
        <center>
        <a href="#" class="bold_link">See How It Works</a><br />
        <!--<button type="button" class="btn btn-primary mybtn" >Login to Build Your Agent</button>-->
        <a href="/students/search_agent" class="btn btn-primary mybtn" style="">Save Current Search Results</a> 
        </center>
    <?php } ?>
    </div>
    
    
    
    <div class="highlight_grey testi">
    <p>
<img src="/img/quote1.png"/> &nbsp; finally found what I was looking foe Great Tutors, great Service whether Online or face to face, the quality and expertise is alway there. &nbsp; <img src="/img/quote2.png"/> <br />
Donald 0, Atlanta, GA
	</p>
    <br />
    <p>
<img src="/img/quote1.png"/> &nbsp; finally found what I was looking foe Great Tutors, great Service whether Online or face to face, the quality and expertise is alway there. &nbsp; <img src="/img/quote2.png"/> <br /> David A. NYC, NY
	</p>
    <br />

    <p>
<img src="/img/quote1.png"/> &nbsp; finally found what I was looking foe Great Tutors, great Service whether Online or face to face, the quality and expertise is alway there. &nbsp; <img src="/img/quote2.png"/> <br /> Detroit, Michigan
	</p><br />

    <p>
<img src="/img/quote1.png"/> &nbsp; finally found what I was looking foe Great Tutors, great Service whether Online or face to face, the quality and expertise is alway there. &nbsp; <img src="/img/quote2.png"/> <br /> Trish R, San Diego, CA
	</p>
    
    
    </div>
  </div> 
  <!--end search left section-->
  
  <div class="col-md-9 rightsearch">
  <a name="topsearch"></a>
  	<div class="row">
      <?php  
      $city = $this->Session->read('search_city'); 
       if(!empty($city)) { ?>
            <div class="col-md-4 padding_top14"><strong><?php echo h($total_post_count); ?> &nbsp;tutors in <?php echo h($city); ?> </strong></div>
      <?php } else {?>
            <div class="col-md-4 padding_top14"><strong><?php echo h($total_post_count); ?> tutors</strong></div>
      <?php } ?>
      <div class="col-md-5 padding_top14">
      <!--
        <a href="#" id="showlist" > <img src="/img/list2.png"/></a>&nbsp;
        <a href="#" id="showgrid"><img src="/img/grid.png"/></a> 
      --> 
     
<?php
    echo $this->Html->image('/img/list2.png', array('id' => 'showlist', 'alt' => 'img'));
?>
&nbsp;
<?php
    echo $this->Html->image('/img/grid.png', array('id' => 'showgrid', 'alt' => 'img'));
    
?>
      </div>
      <!--<div class="col-md-3" align="right">
      
      	<select class="form-control">
      	  <option>Ratings</option>
          <option>Lowest Price</option>
          <option>Highest Price</option>
          <option>Distance</option>
          <option>Hours</option>
        </select>           
      </div> -->
         <?php //echo $this->Form->input('ZipSearch.sortcriteria', array('onchange' => "document.location.href = '/students/tutor_search_results_auth?var=' + this.value", 'label' => false, 'empty' => array('Best Match'), 'options' => Configure::read('sortcriteria'), 'class' => 'form-control', 'div' => array('class' => 'col-md-3', 'align' => 'right')));?>
         <?php //$test = 'document.location.href ='.Router::reverse($this->request); ?> <?php //echo $test ?>
         
         <?php 
            if(!empty($sortBy)) {
              echo $this->Form->input('ZipSearch.sortcriteria', array('label' => 'Sort By:', 'style'=> 'font-weight:bold', 'empty' => array('Best Match'), 'default'=>$sortBy, 'options' => Configure::read('sortcriteria'), 'class' => 'form-control', 'div' => array('class' => 'col-md-3', 'align' => 'right')));
             } else {
                echo $this->Form->input('ZipSearch.sortcriteria', array('label' => 'Sort By:',  'empty' => array('Best Match'), 'options' => Configure::read('sortcriteria'), 'class' => 'form-control', 'div' => array('class' => 'col-md-3', 'align' => 'right')));
         } ?>
    </div> <!--end header-->
    <br />

    <div class="row highlight" style="padding:10px 5px" id="listview">
    
    <?php
             	 
             $i=0;
             $fn = "";
             $ln = "";
             if(sizeof($tutors) > 0 ) {  
                //debug($tutors); die();
  	          foreach ($tutors as $tutor):
               
                 if(isset($tutor['Tutor']))	{
                    //debug($tutor['Tutor']['first_name']); 
                    $fn = h($tutor['Tutor']['first_name']);
                    $ln = h(substr($tutor['Tutor']['last_name'],0,1));
                    $blank = "  ";
                    $name = "$fn $blank $ln.";
                    $name = h($name);
                    $hourly_rate =        h($tutor['TutorProfile']['hourly_rate']);
                    $travel_radius =      h($tutor['TutorProfile']['travel_radius']);
                    $degree =             h($tutor['TutorProfile']['degree']);
                    $school =             h($tutor['TutorProfile']['school']);
                    $title =              h($tutor['TutorProfile']['title']);
                    $city =               h($tutor['TutorProfile']['city']);
                    $state =              h($tutor['TutorProfile']['state']);
                    $tutor_zip_code =     h($tutor['TutorProfile']['zip_code']);
                    $description =        h($tutor['TutorProfile']['description']);
                    $bgCheck =            h($tutor['TutorProfile']['background_checked']);
                    
                     $a_to_b = "";
                     $user_zip_code = h($this->Session->read('cur_zip_code'));
                     $r_distance = "";
                     
                     if(!empty($radius_distance)) { //need to find out where it is set
                        $r_distance = h($radius_distance);
                     } else {
                        $r_distance = 20; //$radius_distance
                     }
                     
                      
                     if(!empty($point_a)) { //need to find out where it is set
                          $a_to_b = h($this->ZipCode->get_distance_between_zipcodes($point_a, $tutor_zip_code, $r_distance));
                     } else if(!empty($user_zip_code)) {
                          $a_to_b = h($this->ZipCode->get_distance_between_zipcodes($user_zip_code, $tutor_zip_code, $r_distance));
                     } 
                    // show subject and category
                    // $subject_name = $tutor['TutorSubject'][0]['subject_name'];
                    // $subject_category_name = $tutor['TutorSubject'][0]['subject_category_name'];
                    // end show subject and category

                    $tutor_image =        !empty($tutor['TutorImage'][0]['thumb_image']) ? $tutor['TutorImage'][0]['thumb_image'] : "/img/no_img.jpg";
               }
               $i++;                   
        ?> 
    <div class="listbox">
      <div class="col-md-2 list1">
      	<div class="row">
          <div class="col-md-3 ">
          	<img src="<?php echo $tutor_image; ?>"/><br />
           <!-- <div class="stars">
                &nbsp;&nbsp;&nbsp;<i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i>
            </div>
			&nbsp;&nbsp;&nbsp;<b>125 Reviews</b>-->
          </div>
          <div class="col-md-7 padding_right_no padding_left_no t_details">
          			 
                    <!--
                    <strong>Hourly Rate:</strong>	$<?php echo $hourly_rate ?>/hour<br />
                    <strong>Travel Radius:</strong>	<?php echo $travel_radius ?><br />
                    <strong>Subjects Offering:</strong> 10+ <br />
                    -->
          </div>
     </div>     
  </div>
      <div class="col-md-5 list2">
     
      <?php echo '<b>'.$name.'</b>'; echo '&nbsp;&nbsp;&nbsp;'.$city; echo ',&nbsp;&nbsp;' .$state; echo '&nbsp;&nbsp;<b>' .$a_to_b; echo '&nbsp;&nbsp; miles away</b>' ;?>
      
      <p>
      <br />
      <strong><?php echo $title ?></strong>
      </p>
			<p>
              <?php 
                // echo $description;
                if(strlen($description) > 240){
                  echo substr($description, 1, 230);
                  echo "&nbsp <a href='/students/tutor_details_profile_auth'>more...</a>";
                }
                else{
                  echo $description;
                }

                // show subject and category
                // echo $subject_category_name;
                // echo "<br />";
                // echo $subject_name;
                // end show subject and category
              ?>
           
            </p>
            <div class="clearfix"></div>
            <div class="row">
	
<?php
	
echo '<div class="col-md-8 watchlink" id="watch_parent'.$i.'"><a href="#"  class="addwatch" id="'.$i.'">
<i class="glyphicon glyphicon-eye-open"></i> &nbsp; Add to Watch List</a></div>';
?>
  
</div>
            
            <div class="clearfix"></div>
		</div>
       <div class="col-md-3 varified">&nbsp;&nbsp;&nbsp;&nbsp;<b>$<?php echo $hourly_rate ?>/Per Hour</b><br /><br />
         
           <div class="stars">
               4.9 &nbsp;<i class="glyphicon glyphicon-star star"></i> &nbsp;&nbsp;(190 Reviews)
                <!--<i class="glyphicon glyphicon-star star"></i>
                <i class="glyphicon glyphicon-star star"></i>
                <i class="glyphicon glyphicon-star star"></i>
                <i class="glyphicon glyphicon-star star"></i>
                -->
            </div>
                    <strong>Travel Radius:</strong>	<?php echo $travel_radius ?> miles<br /><br />
                    <strong>Background Check:&nbsp; <img src="/img/question.png"/> <br /><br /><br />

            <a href="/students/tutor_details_profile_auth"  class="btn btn-primary col-md-12" style="">View tutor's Profile</a>
    	
        </div>
        </div><!--end list box-->
        <div class="clearfix"></div>
        <!-- <br /> -->

      <?php
          endforeach;
        }   
	  ?>

<div class="row">
  <div class="col-md-7">
  <br />
<a href="#topsearch">Back to top</a></div>

  <!--<div class="col-md-5" align="right">-->
  
  <?php 
    // echo $this->element('pagination');
  ?>	
  <!-- </div>-->

  <!-- pagination -->
    <?php 
  if($total_post_count > $posts_per_page){
    ?>
    <div class="custom_pagination">
    <br />
      <?php
      if($cur_page > 1){
        echo "<a id='go_to_first' href='javascript:void(0)' target_page='1'><<</a>&nbsp;&nbsp;";

        $prev= $cur_page - 1;
        echo "<a id='prev' href='javascript:void(0)' target_page='{$prev}'><</a>&nbsp;&nbsp;";
      }

      for ($i=$start_page; $i <= $end_page; $i++) { 
        if($i == $cur_page){
          echo "<a href='javascript:void(0)' class='active' target_page='{$i}'>$i</a>&nbsp;&nbsp;";
        }
        else{
          echo "<a href='javascript:void(0)' target_page='{$i}'>$i</a>&nbsp;&nbsp;";
        }
      }

      if($cur_page < $total_page_count){
        $next = $cur_page + 1;
        echo "<a id='next' href='javascript:void(0)' target_page='{$next}'>></a>&nbsp;&nbsp;";
        echo "<a id='go_to_last' href='javascript:void(0)' target_page='{$total_page_count}'>>></a>";
      } 

      ?>
    </div>  
    <?php
  }
  ?>  <!-- end pagination -->

<!-- pagenation  -->
<!-- end pagenation -->

  <?php //echo $this->Form->end(); ?>



</div>
                                               
</div>


  <!--end list view==================================================-->
  
  	<div class="row highlight" style="padding:10px 5px" id="gridview">
    <div class="listbox auth">
    <?php	 
             $j=0;
             $fn = "";
             $ln = "";

             if(sizeof($tutors) > 0 ) {  
                //debug($tutors); die();
  	          foreach ($tutors as $tutor):
               
                 if(isset($tutor['Tutor']))	{
                    //debug($tutor['Tutor']['first_name']); 
                    $fn = $tutor['Tutor']['first_name'];
                    $ln = substr($tutor['Tutor']['last_name'],0,1);
                    $blank = "  ";
                    $name = "$fn $blank $ln";

                    $houly_rate = $tutor['TutorProfile']['hourly_rate'];
                    $tutor_image = !empty($tutor['TutorImage'][0]['thumb_image']) ? $tutor['TutorImage'][0]['thumb_image'] : "/img/no_img.jpg";
               }
               
                   
        ?>
    
    
      <div class="col-md-4 list1">
      	<div class="row">
          <div class="col-md-5 ">
          	<!-- <img src="/img/no_img.jpg"/><br /> -->
            <img src="<?php echo $tutor_image; ?>"/><br />
            
            <div class="stars">
                <i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i>
            </div>
            125 Reviews


          </div>
          <div class="col-md-7 padding_right_no padding_left_no t_details">
          			<b><?php echo $name ?><br />
                    BS, Mathematics<br />
                    University Of Maryland</b><br /><br />

                    <strong>Hourly Rate:</strong>	$<?php echo $houly_rate; ?>/hour<br />
                   <strong> Subjects Offering:</strong> 10+ <br />
					<strong>Background Check:</strong>	Passed<br />
                    on 11/22/2013


</div>
        </div>
        <br />

<div class="row">

    <?php
echo '<div class="col-md-8 watchlink" id="watch_parent'.$j.'"><a href="#" class="addwatch" id="'.$j.'">
<i class="glyphicon glyphicon-eye-open"></i> &nbsp; Add to Watch List</a></div>';
    	//echo '<div class="col-md-8 watchlink" id="watch_parent'.$j.'"><a href="#" class="addwatch" id="'.$j.'"><i class="glyphicon glyphicon-eye-open"></i> &nbsp; Add to Watch List</a></div>';

?>

    <div class="col-md-4">
    	<a href="/students/tutor_details_profile_auth" class="btn btn-primary  pull-right" style="">View Me</a>
    </div>
</div>



      </div>
      
      <?php
      $j++;
	  if($j % 4 == 0)
	  {
		  	echo '<div class="clearfix"></div>';
	  }
      //$j++;
      endforeach;
	}
 
 ?>
      <!--end list-->
       <!--end list-->

       <!--end list-->

       <!--end list-->

        </div><!--end list box-->

        <div class="clearfix"></div>

<div class="row">
  <div class="col-md-7">
  <br />
<a href="#topsearch">Back to top</a></div>
  <div class="col-md-5" align="right">
  	<?php // echo $this->element('pagination');?>

    <!-- pagination -->
    
    <?php 
    if($total_post_count > $posts_per_page){
      ?>
      <div class="custom_pagination">
      <br />
        <?php
        if($cur_page > 1){
          echo "<a id='go_to_first' href='javascript:void(0)' target_page='1'><<</a>&nbsp;&nbsp;";

          $prev= $cur_page - 1;
          echo "<a id='prev' href='javascript:void(0)' target_page='{$prev}'><</a>&nbsp;&nbsp;";
        }

        for ($i=$start_page; $i <= $end_page; $i++) { 
          if($i == $cur_page){
            echo "<a href='javascript:void(0)' class='active' target_page='{$i}'>$i</a>&nbsp;&nbsp;";
          }
          else{
            echo "<a href='javascript:void(0)' target_page='{$i}'>$i</a>&nbsp;&nbsp;";
          }
        }

        if($cur_page < $total_page_count){
          $next = $cur_page + 1;
          echo "<a id='next' href='javascript:void(0)' target_page='{$next}'>></a>&nbsp;&nbsp;";
          echo "<a id='go_to_last' href='javascript:void(0)' target_page='{$total_page_count}'>>></a>";
        } 

        ?>
      </div>  
      <?php
    }
    ?>
    <!-- end pagination -->
  </div>
</div>

    </div>
  </div> <!--end search right-->
</div>
     <!--end banner-->
    </div>
</div>
<div class="clearfix"></div>

 <!--end home middle--> 
   
<script>
//toggle list /griv view
$(document).ready(
    function() {
		$("#showgrid").click( function(e){
			e.preventDefault();
			$('#gridview').show();
			$('#listview').hide();
			$('#showgrid').children("img").attr('src','/img/grid2.png')
			$('#showlist').children("img").attr('src','/img/list.png')

		});
		$("#showlist").click( function(e){
			e.preventDefault();
			$('#listview').show();
			$('#gridview').hide();
			$('#showlist').children("img").attr('src','/img/list2.png')
			$('#showgrid').children("img").attr('src','/img/grid.png')

		});

    function replaceQueryParam(param, newval, search) {
      var regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?")
      var query = search.replace(regex, "$1").replace(/&$/, '')
      return (query.length > 2 ? query + "&" : "?") + param + "=" + newval
    }
		
    jQuery('.custom_pagination a').click(function(){
      var target_page = jQuery(this).attr('target_page');
      // jQuery('#ZipSearchCurPage').val(target_page);

      var str = window.location.search;
      str = replaceQueryParam('cur_page', target_page, str);

      location.href = window.location.pathname + str;

      // jQuery('#StudentTutorSearchResultsAuthForm').submit();
    });

    			//$('#gridview, #listview').toggle();
					//});
  $("#ZipSearchSortcriteria").change( function(e){
			e.preventDefault();
            var dval =  $('#ZipSearchSortcriteria').val();
            var url = '<?php echo Router::reverse($this->request)?>';  
            var paramsSize = '<?php echo sizeof($this->params['url']); ?>';
           // alert(paramsSize);
            var lkl =  '<?php echo $this->here; ?>';
           // var qstring = '<?php echo $_SERVER['QUERY_STRING']; ?>';
            //alert(qstring);
            if(paramsSize > 4) {
                 url = removeURLParameter(url, 'kwd');
                 //url = removeURLParameter(url, 'subject_2');
                // url = removeURLParameter(url, 'subject_3');
                 
                // alert(url);
                //document.location.href = lkl + '?' + qstring + '&kwd='+ dval;
                document.location.href = url + '&kwd='+ dval;   
            } else {
                 
                 document.location.href = lkl + '?kwd='+ dval;
                 oldDval = dval;
            }
			 
		});
        
  $("a[data-target=#edit]").click(function(ev) {
    //alert('test'); 
            ev.preventDefault();
            
            //window.location.reload();
            var target = $(this).attr("href");
            //alert(target);
            var gt= $(this).data('id');
           // alert(gt);
            //var subject = "";
            // var id = JSON.stringify(gt);
            <?php 
              //$ssa = $this->Session->read('0');
              //debug($ssa); 
              //$subject = $ssa['StudentSearchAgent']['subject']; 
              // $('#zipSearchSubjectP').html(remove);
              //debug($subject);  die();
            ?>
           // alert($subject);
           var urls = '/students/search_agent/'+gt;
            // load the url and show modal on success
           // $("#edit .modal-body").load(target, function() {
                //alert("here now"); 
               // var testl = '<?php echo 6; ?>';
               // alert(testl);
              // $("#edit .modal-body").oclick(function() {}).load;
               $("#edit .modal-body").load(target, function() {
                //alert($('#HiddenSubject'+gt).val());
                $("#ZipSearchSubjectP").val($('#HiddenSubject'+gt).val());
                $("#ZipSearchZipCodeP").val($('#HiddenZipCode'+gt).val());
                $("#ZipSearchDistanceP").val($('#HiddenDistance'+gt).val());
                
                $("#ZipSearchMinRateP").val($('#HiddenMinRate'+gt).val());
                $("#ZipSearchMaxRateP").val($('#HiddenMaxRate'+gt).val());
                $("#ZipSearchMinAgeP").val($('#HiddenMinAge'+gt).val());
                
                $("#ZipSearchMaxAgeP").val($('#HiddenMaxAge'+gt).val());
                $("#ZipSearchGenderP").val($('#HiddenGender'+gt).val());
				
   	           $("#ZipSearchIdP").val($('#HiddenIdP'+gt).val());
				$("#ZipSearchAgentIdP").val($('#HiddenAgentIdP'+gt).val());
				$("#ZipSearchAgentNameP").val($('#HiddenAgentNameP'+gt).val());
                
               // alert($("#ZipSearchBgCheckedP").val());
              // $("#ZipSearchBgCheckedP").val($('#HiddenBgChecked'+gt).val());
              
              // alert($('#HiddenBgChecked'+gt).val());
                if($('#HiddenBgChecked'+gt).val() == 1) {
                    //alert("tetsgg");
                    //$("#ZipSearchBgCheckedP").val('1');
                    $("#ZipSearchBgCheckedP").attr('checked', true);
                } else {
                    // $("#ZipSearchBgCheckedP").val('0');
                     $("#ZipSearchBgCheckedP").attr('checked', false);
                    // alert("test");
                    // $("#ZipSearchBgCheckedP").val('0');
                     //$("#ZipSearchBgCheckedP").removeAttr('checked');
                }
               // alert($("#ZipSearchBgCheckedP").val());
                
                $("#ZipSearchKwdP").val($('#HiddenKwd'+gt).val());
                $("#ZipSearchCurPageP").val($('#HiddenCurPage'+gt).val());
                
                var agentNameLabel = $('#HiddenAgentNameP'+gt).val();
                $("#agentNameP").text("Edit Search Agent: " +agentNameLabel);
				
				if($('#HiddenIsAdv'+gt).val() == 0) {
				   $("#ZipSearchMinRateP").remove();
				   $("#ZipSearchMaxRateP").remove();
				   
				   $("#ZipSearchMinAgeP").remove();
				   $("#ZipSearchMaxAgeP").remove();
				   
				   $("#ZipSearchGenderP").remove();
				   $("#ZipSearchBgCheckedP").remove();
				   
				   
				  //Remove the labels
				   $("#hourlyRate").remove();
				   $("#tutorAge").remove();
				   $("#tutorGender").remove();
				   $("#tutorBgChecked").remove();
				}
				
				if($('#HiddenIsAdv'+gt).val() == '1') {
                   $("#ZipSearchIsAdvP").val($('#HiddenIsAdv'+gt).val());
				} else {
				   $("#ZipSearchIsAdvP").val('0');
				}
				
				if($('#HiddenKwd'+gt).val() == "") {
				   $("#ZipSearchKwdP").remove();
				}
               
            });
            
      });

	   $('#edit').on('hidden.bs.modal', function () {
        location.reload();
       })
   
function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');   
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url= urlparts[0]+'?'+pars.join('&');
        return url;
    } else {
        return url;
    }
}
              
    set_auto_complete();

    });
    
</script>


<script>
	//slider function for age and rate
      $(function() {
		  
        $( "#slider-range_rate" ).slider({
          range: true,
          min: 10,
          max: 250,
          values: [ 10, 250 ],
          slide: function( event, ui ) {
            $( "#amount_min_rate" ).val(  ui.values[ 0 ] );
            $( "#amount_max_rate" ).val(  ui.values[ 1 ] );
          }
        });
		
		$( "#slider-range_age" ).slider({
          range: true,
          min: 18,
          max: 100,
          values: [ 18,100 ],
          slide: function( event, ui ) {
            $( "#min_age" ).val(  ui.values[ 0 ] );
            $( "#max_age" ).val(  ui.values[ 1 ] );
          }
        });
        $( "#amount_min_rate" ).val(  $( "#slider-range_rate" ).slider( "values", 0 ));
          $( "#amount_max_rate" ).val(  $( "#slider-range_rate" ).slider( "values", 1 ) );
		  
		  $( "#min_age" ).val(  $( "#slider-range_age" ).slider( "values", 0 ));
          $( "#max_age" ).val(  $( "#slider-range_age" ).slider( "values", 1 ) );
		
		//custom field value change	for rate	  	
		$('input#amount_min_rate').change(function(){
			$('#slider-range_rate').slider("values",0,$(this).val());
			$('#slider-range_rate').slider("values",1,$('input#amount_max_rate').val());
		});
		$('input#amount_max_rate').change(function(){
			$('#slider-range_rate').slider("values",0,$('input#amount_min_rate').val());
			$('#slider-range_rate').slider("values",1,$(this).val());
		});
		  
		//custom field value change	for age	  	
		$('input#min_age').change(function(){
			$('#slider-range_age').slider("values",0,$(this).val());
			$('#slider-range_age').slider("values",1,$('input#max_age').val());
		});
		$('input#max_age').change(function(){
			$('#slider-range_age').slider("values",0,$('input#min_age').val());
			$('#slider-range_age').slider("values",1,$(this).val());
		});
		  
		  
      });
  </script>
<script type="text/javascript" language="javascript"> 
function set_auto_complete(){
  if(jQuery('#category_hidden_wrapper input')){
    var availableTags = [];
    jQuery('#category_hidden_wrapper input').each(function(){

      if(jQuery(this).val()){
        availableTags.push(jQuery(this).val());
      }
    });

    $( ".subject_tag" ).autocomplete({
      source: availableTags
    });
  }
}

//show hide advance search sections
 function toggle() {
            var is_advanced = $('#ZipSearchIsAdvanced').val();
            is_advanced = is_advanced == 0 ? 1 : 0;
           // is_advanced = 1;
            
            $('#ZipSearchIsAdvanced').val(is_advanced);

						var ele = document.getElementById("toggleText");
						var text = document.getElementById("displayText");
						if(ele.style.display == "block") {
								$( "#toggleText" ).hide( "medium");
							text.innerHTML = "Advance Search +";
						}
						else {
								$( "#toggleText" ).show( "medium");
							text.innerHTML = "Advance Search -";
						}
					} 
					
</script>


<script>
	       					//add div to watch list
	       					$(document).ready( function() {

                     $(".addwatch").click( function(e){
	       							e.preventDefault();
	       							var clickedID = this.id;
	       						//	$('#note_confirm').dialog({
	       							 // resizable: false,
	       							 // height:140,
	       							  //width:340,
	       							  //modal: false,
	       							 // buttons: {
	       							//	"Yes": function() {
	       									$('#note').modal({
	       									//show: 'false'
	       								});
	       								$('#note #addWatchmodal').click(function() {
	       								   // alert("Here");
	       									$('#note').modal('hide');
	       									$('#watch_parent'+clickedID).html("");
                                            
	       								$('#watch_parent'+clickedID).html('<a  class="watch_yes"><i class="glyphicon glyphicon-asterisk "></i>  On Your Watch List</a> <br/><a href="" data-toggle="modal" data-target="#noteEdit" ><b>Edit Note</b>&nbsp;<i class="glyphicon glyphicon-edit"></i></a>');
	       								$('.watchlistbox').append('<div class="row small_profile" id="removewatch"><div class="col-md-5 padding_right_no"><img src="/img/no_img.jpg" width="70px"><br><div class="stars"><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i></div>125 Reviews</div><div class="col-md-7 padding_left_no">John D <span class="pull-right"><b><a href="#" class="mytooltip"  data-toggle="tooltip" data-placement="top" title="Your note is placed here"><a href="" data-toggle="modal" data-target="#noteEdit" >Edit Note</a></b></span><br>BS, Mathematics<br>University Of Maryland<br><br><a href="/students/tutor_details_profile_auth" >View Me</a> &nbsp; <a href="javascript:void(0);"   onclick=\'$("#removewatch").slideUp("normal", function() { $(this).remove(); } );preventDefault();\'>Remove</a></div></div>');
	       								 
                                             return;
                                            // alert("Here");
                                           });
	       								$('#note #closeModal').click(function() {
	       									$('#note').modal('hide');
	       									$('#watch_parent'+clickedID).html("");
	       								  $('#watch_parent'+clickedID).html('<a  class="watch_yes"><i class="glyphicon glyphicon-asterisk "></i> &nbsp; On Your Watch List</a> <br/><a href="" data-toggle="modal" data-target="#noteEdit" ><b>Edit Note</b>&nbsp;<i class="glyphicon glyphicon-edit"></i></a>');
	       								  
                                          $('.watchlistbox').append('<div class="row small_profile" id="removewatch"><div class="col-md-5 padding_right_no"><img src="/img/no_img.jpg" width="70px"><br><div class="stars"><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i></div>125 Reviews</div><div class="col-md-7 padding_left_no">John D <span class="pull-right"><b><a href="#" class="mytooltip"  data-toggle="tooltip" data-placement="top" title="Your note is placed here"><a href="" data-toggle="modal" data-target="#noteEdit" >Edit Note</a></b></span><br>BS, Mathematics<br>University Of Maryland<br><br><a href="/students/tutor_details_profile_auth" >View Me</a> &nbsp; <a href="javascript:void(0);"   onclick=\'$("#removewatch").slideUp("normal", function() { $(this).remove(); } );preventDefault();\'>Remove</a></div></div>');
	       							
	       								  //$('.watchlistbox').append('<div class="row small_profile" id="removewatch"><div class="col-md-5 padding_right_no"><img src="/img/no_img.jpg" width="70px"><br><div class="stars"><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i><i class="glyphicon glyphicon-star star"></i></div>125 Reviews</div><div class="col-md-7 padding_left_no">John D <br>BS, Mathematics<br>University Of Maryland<br><br><a href="/students/tutor_details_profile_auth" >View Me</a> &nbsp; <a href="javascript:void(0);"   onclick=\'$("#removewatch").slideUp("normal", function() { $(this).remove(); } );preventDefault();\'>Remove</a></div></div>');
	       								  // alert("Here y");
                                           });
	       								
	       								//$('.mytooltip').tooltip();
	       								 // $( this ).dialog( "close" );
	       							//	},
	       								
	       							 // }
	       						//	});
	       							
	       						 });
	                               $(".editnote").click( function(e){
	                                   e.preventDefault();
	                                   alert('asdasd');
	       
	                               })
	       						});
	       
	       					
					</script>
<script>

    $(".editnote").click( function(e){
        e.preventDefault();
        alert();



    });

</script>


<!-- note -->
<div id="note_confirm" class="modal fade" title="Would you like to Add a Note?">
</div>

<div class="modal fade" id="note" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-body">
  <form role="form">
      <div class="form-group">
        <label >Comments (Optional)</label>
        <!--<input type="text" class="form-control"  placeholder="Write a note ">-->
        <textarea class="form-control"  placeholder="Write a note" rows="4" cols="50" maxlength="50">
         </textarea>
      </div>
         <button type="button" id="addWatchmodal" class="btn btn-primary" data-dismiss="modal">Save</button>
         <button type="button" id="closeModal" class="btn btn-danger" data-dismiss="modal">Cancel</button>
  </form>
      </div>
      
    </div>
  </div>
</div>
<!--end note-->
<!--end note-->
<!--start note edit modal-->
<div class="modal fade" id="noteEdit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-body">
  <form role="form">
      <div class="form-group">
        <label >Edit your note</label>
        <!--<input type="text" class="form-control"  placeholder="Edit your note "> -->
        <textarea class="form-control"  placeholder="Edit Your Notes " rows="4" cols="50" maxlength="50">
         </textarea>
      </div>
         <button type="button" id="addWatchmodal" class="btn btn-primary" data-dismiss="modal">Save Changes</button>
         <button type="button" id="closeModal" class="btn btn-danger" data-dismiss="modal">Cancel</button>
  </form>
      </div>
      
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <label id="agentNameP" class="control-label">Agent Name</label> 
        <p><br />1.Edit Your Agent<br />
           2. Click Search to View Results<br />
           4. From Results Screen, Click "Update Search Agent" button to update this Agent</p>
          <!--<h4>Edit Search Agent: Name</h4>-->
        </label>
      </div>
<div class="modal-body">
<!--<form class="form-horizontal" role="form">--> 
<?php echo $this->Form->create($model, array('class' => 'form-horizontal','role'=>'form', 'type' => 'get')); ?>
  <div class="form-group">
    <label for="subject" class="col-sm-2 control-label">Subject</label>
    <div class="col-sm-10">
      <?php
         //$subject_a = $this->Session->read('subject_a');
         //$ssa = $this->Session->read('ssa');
        //debug($ssa); die();
        // $subject_a = $ssa['StudentSearchAgent']['subject'];
        // debug($subject_a); die();
         if(!empty($subject_a)) {
              echo $this->Form->input('ZipSearch.subject', array('id'=>'ZipSearchSubjectP', 'value' => $subject_a, 'label'=>false, 'required' => false, 'class' => 'form-control', 'div' => false));     
          } else {    
              echo $this->Form->input('ZipSearch.subject', array('id'=>'ZipSearchSubjectP', 'placeholder' => 'Subject', 'label'=>false, 'required' => false, 'class' => 'form-control', 'div' => false));                        
          }
        ?>
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Zip Code</label>
    <div class="col-sm-10">
           <?php 
            //$ssa = $this->Session->read('ssa');
           // $zip_code_a = $ssa['StudentSearchAgent']['zip_code']; 
           if(!empty($zip_code_a)) {
              echo $this->Form->input('ZipSearch.zip_code', array('id'=>'ZipSearchZipCodeP', 'onkeyup' => 'checkInput(this)', 'label' => false, 'value' => $zip_code_a, 'required' => false, 'class' => 'form-control padding5', 'div' => false ));                            
          } else {
             echo $this->Form->input('ZipSearch.zip_code', array('id'=>'ZipSearchZipCodeP', 'onkeyup' => 'checkInput(this)', 'label' => false, 'placeholder' => 'Zip Code', 'required' => false, 'class' => 'form-control padding5', 'div' => false ));             
          }
      //$this->Session->remove('ssa');
     ?>
   
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Distance</label>
    <div class="col-sm-10">
      <?php 
       //$ssa = $this->Session->read('ssa');
      // $distance = $ssa['StudentSearchAgent']['distance'];
       //debug($distance);
      if(!empty($distance)) {
         echo $this->Form->input('ZipSearch.distance', array('id'=>'ZipSearchDistanceP', 'label' => false, 'default' => $distance, 'options' => Configure::read('distances'), 'class' => 'form-control', 'div' => false));
      } else {
         echo $this->Form->input('ZipSearch.distance', array('id'=>'ZipSearchDistanceP', 'label' => false,  'options' => Configure::read('distances'), 'class' => 'form-control', 'div' => false));
        }
      ?>
    </div>
    <br /><br />
  </div>
  
  <div id="hourlyRate" class="form-group">
      <label>
         Hourly Rate Range
      </label>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;&nbsp;&nbsp;&nbsp;</label>    
    <div class="col-sm-3">
     <!-- <input type="text" class="form-control zip"  placeholder="Min Rate" />-->
      <?php 
             echo $this->Form->input('ZipSearch.is_advanced', array('id'=>'ZipSearchIsAdvP', 'type' => 'hidden', 'value' => 0, 'div' => false)); 
              echo $this->Form->input('ZipSearch.cur_page', array('id'=>'ZipSearchCurPageP', 'type' => 'hidden', 'value' => 1, 'div' => false)); 
             echo $this->Form->input('ZipSearch.kwd', array('id'=>'ZipSearchKwdP', 'type' => 'hidden', 'div' => false)); 
             echo $this->Form->input('ZipSearch.update_agent', array('type' => 'hidden', 'value' => 1, 'div' => false)); 
			 echo $this->Form->input('ZipSearch.agent_id', array('id'=>'ZipSearchAgentIdP', 'type' => 'hidden', 'div' => false)); 
		     echo $this->Form->input('ZipSearch.id', array('id'=>'ZipSearchIdP', 'type' => 'hidden', 'div' => false)); 
			 
             echo $this->Form->input('ZipSearch.agent_name', array('id'=>'ZipSearchAgentNameP', 'type' => 'hidden', 'div' => false)); 
     
     
     //if()
      // $ssa = $this->Session->read('ssa');
       //$min_rate = $ssa['StudentSearchAgent']['min_rate'];
        if(!empty($min_rate)) {
            echo $this->Form->input('ZipSearch.amount_min_rate', array('id'=>'ZipSearchMinRateP', 'label' => false, 'value' => $min_rate, 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        } else {
            echo $this->Form->input('ZipSearch.amount_min_rate', array('id'=>'ZipSearchMinRateP', 'label' => false, 'placeholder' => 'Min Rate', 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        }
      ?>
    </div>
    <div class="col-sm-3">
      <!--<input type="text" class="form-control zip"  placeholder="Max Rate" />-->
      <?php 
      // $ssa = $this->Session->read('ssa');
       //$max_rate = $ssa['StudentSearchAgent']['max_rate'];
        if(!empty($max_rate)) {
            echo $this->Form->input('ZipSearch.amount_max_rate', array('id'=>'ZipSearchMaxRateP','label' => false, 'value' => $max_rate, 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        } else {
            echo $this->Form->input('ZipSearch.amount_max_rate', array('id'=>'ZipSearchMaxRateP','label' => false, 'placeholder' => 'Max Rate', 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        }
      ?>
    </div>
  </div>
  
  <div id="tutorAge" class="form-group">
      <label>
         Tutor Age Range
      </label>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;&nbsp;&nbsp;&nbsp;</label>    
    <div class="col-sm-3">
      <!--<input type="text" class="form-control zip"  placeholder="Min Age" />-->
      <?php 
      // $ssa = $this->Session->read('ssa');
      // $min_age = $ssa['StudentSearchAgent']['min_age'];
        if(!empty($min_age)) {
            echo $this->Form->input('ZipSearch.min_age', array('id'=>'ZipSearchMinAgeP','label' => false, 'value' => $min_age, 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        } else {
            echo $this->Form->input('ZipSearch.min_age', array('id'=>'ZipSearchMinAgeP','label' => false, 'placeholder' => 'Min Age', 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        }
      ?>
    </div>
    <div class="col-sm-3">
      <!--<input type="text" class="form-control zip"  placeholder="Max Age" />-->
      <?php 
      // $ssa = $this->Session->read('ssa');
      // $max_age = $ssa['StudentSearchAgent']['max_age'];
        if(!empty($max_age)) {
            echo $this->Form->input('ZipSearch.max_age', array('id'=>'ZipSearchMaxAgeP','label' => false, 'value' => $max_age, 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        } else {
            echo $this->Form->input('ZipSearch.max_age', array('id'=>'ZipSearchMaxAgeP','label' => false, 'placeholder' => 'Max Rate', 'required' => false, 'class' => 'form-control zip', 'div' => false ));
        }
      ?>
    </div>
  </div>
  
   <div class="form-group">
    <label id="tutorGender" class="col-sm-2 control-label">&nbsp;&nbsp;&nbsp;&nbsp; Gender</label>    
      <div class="col-sm-4">
         <?php 
          //$ssa = $this->Session->read('ssa');
          //$gender = $ssa['StudentSearchAgent']['gender'];
         if(!empty($gender)) {
           echo $this->Form->input('ZipSearch.gender', array('id'=>'ZipSearchGenderP','label' => false, 'empty' => array('No Preference'), 'options' => Configure::read('gender'), 'default' => $gender, 'class' => 'form-control', 'div' => false));
         } else {
            echo $this->Form->input('ZipSearch.gender', array('id'=>'ZipSearchGenderP','label' => false, 'empty' => array('No Preference'), 'options' => Configure::read('gender'), 'class' => 'form-control', 'div' => false));
         }
         ?>
     </div>
   </div>   
                    
  <div id="tutorBgChecked" class="form-group">
     <!-- 
     <label>
         &nbsp;&nbsp;<input type="checkbox" /> Background Checked
      </label>
  -->
      <?php 
         echo $this->Form->input('ZipSearch.bg_checked', array('id'=>'ZipSearchBgCheckedP','type' => 'checkbox', 'hiddenField' => false, 'class' => 'mycheckbox', 'label' => __d('students', '<strong>&nbsp;Background Checked</strong>'),
                 'div' => array('class' => 'checkbox b_chk' )));   
      ?>
  </div>   

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <!--<button type="submit" class="btn btn-default mybtn">Send</button>-->
      <button type="submit" class="btn btn-default mybtn col-md-12" >Search And View Results, then Save</button>
      <!--<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>-->
    </div>
  </div>
<?php echo $this->Form->end(); ?>
      </div>

    </div>
  </div>
</div>
<!-- End Modal -->
<!-- put categories -->
<div id="category_hidden_wrapper">
  <?php 
  //debug($subjects_and_categories); die();
    foreach ($subjects_and_categories as $key => $value) {
  ?>
      <input type="hidden" value="<?php echo $value; ?>" />
  <?php
    }
   ?>
</div>
<!-- end put categories -->

<!--end note edit-->
