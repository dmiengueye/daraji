 jQuery(document).ready(function() {
    
  $('.mybtn').click( function(e){
        
            e.preventDefault();
 	    var clickedID = this.id;
          
	//Because of the Loop that generate id fields, all members share the same ids in the textarea.
	//This won't work when trying to submit Creds for a particular Subject
	//As a result we have to parse the id and extract what we know is unique to the subject and conctenate that with 'texarea'
	//parse the clickedID
	//remove the  'submit' substring and replace it with 'textarea'
	//use that new id to get the value of the textarea   as textarea.$subject['TutorSubject']['subject_id'].'qual'

          var divided = clickedID.split("submit");
          var firstPart =  divided[0];
          var secondPart = divided[1];


          var texAreaID = "textarea"+secondPart;
          
          var tutorSubjectId = "TutorSubjectId"+secondPart;
          
          var karp = $("#"+tutorSubjectId).val();
          
          var creds = $("#"+texAreaID).val();   

         if(creds != "") {
             $('#credentialsDefaultInfo').hide();

          }
            
          //jsonData =  {"id": karp, "creds":creds };
          $.ajax({ 
                  type:'POST',
                //async: false, 
                //cache: false,
                  url: '/tutors/credentials_submittal',            
                  data:   {"datum": creds, id:karp},
                //dataType: json,
                  contentType: "application/x-www-form-urlencoded",
                  success: function(data) {                         
                     //alert(data);
                  //$('#credentialsDefaultInfo').hide();
                  alert('Successfully updated');
                        
                      window.location.reload();                       
                      return false;
                    },
                   
                   error: function (xhRequest, ErrorText, thrownError) {
                        alert("Failed to process request correctly, please try again");
                        alert(thrownError);
                        console.log('xhRequest: ' + xhRequest + "\n");
                        console.log('ErrorText: ' + ErrorText + "\n");
                        console.log('thrownError: ' + thrownError + "\n");
                        //window.location.reload();
                   },
                  
             });
             return false;
                          
     });

    $(".credentialsBtn").click(function(e){
	e.preventDefault();
	$('.pulled-credentials').show();
	$('#credentialsDefaultInfo').show();
    //$('#credentialsDefaultInfo').prop('disabled', true);

    });
    $("#credentialsInfobtn").click(function(e){
	e.preventDefault();
	$('#credentialsUserInfo').show();
	$('#credentialsDefaultInfo').hide();
	$('#credentialInput').hide();

     });
    $("#editCredentialsUserInfo").click(function(e){
	e.preventDefault();
	$('#credentialInput').show();
    $('#credentialInput').prop('disabled', true);

    });


//Before the Onclick, need to check as soon as the page loads
    //if the last Tab still exists or was removed because user removed all Subjects from it
    //Removing all Subjects from Tab will effectively remove the Tab from the Div

var nameToCheck = "";
var tabName= <?php echo $this->Session->read('tabName'); ?>
if(tabName != "" || tabName != null) 

    nameToCheck = tabName;
}

alert(nameToCheck);
alert(tabName);
   if(nameToCheck == "") {
      nameToCheck = sessionStorage.getItem('last_tab');
   }
   var tabNameExists = false;
    
      $('ul.nav-tabs a').each(function(index) {
        if ($(this).attr('href') == nameToCheck) {
           tabNameExists = true;
        }
     });
     
    $('a[data-toggle="tab"]').click('shown.bs.tab', function(e){
       
        //save the latest tab using a cookie:
        //$.cookie('last_tab', $(e.target).attr('href'));
        //save the latest tab using a HTML5 LocalStorage or SessionStorage either is better than a cookie
        sessionStorage.setItem('last_tab', $(this).attr('href'));
    
    });

    //console.log($.cookie('last_tab'));
     console.log(sessionStorage.getItem('last_tab'));
    //activate latest tab, if it exists:
    //var lastTab = $.cookie('last_tab');

    var lastTab = sessionStorage.getItem('last_tab');
    //alert($('#'+lastTab).length);

    if (lastTab && tabNameExists ) {
        $('ul.nav-tabs').children().removeClass('active');
        $('a[href='+ lastTab +']').parents('li:first').addClass('active');
        $('div.tab-content').children().removeClass('active');
        $(lastTab).addClass('active');
    }

<?php unset($_SESSION['tabName']); ?>

});