<!DOCTYPE html>
<html lang="en">
<head>
<title>Event Registration</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<script type="text/javascript" src="https://maps.google.com/maps/api/js?key=AIzaSyBFs5n0bO3Nm9IR3udTvP6JLszkQjgD5pI"></script>

<style type="text/css">
table tr th, table tr td{font-size: 1.2rem;}
.row{ margin:20px 20px 20px 20px;width: 100%;}
.glyphicon{font-size: 20px;}
.glyphicon-plus{float: right;}
a.glyphicon{text-decoration: none;}
a.glyphicon-trash{margin-left: 10px;}
.none{display: none;}
.info{font-size:.8em;color: #FF6600;letter-spacing:2px;padding-left:5px;}
</style>
<script type="text/javascript"> 


// Fetch user location
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else { 
        
    }
}

// Convert lat. and Long. to a state
function showPosition(position) {
    
    var geocoder = new google.maps.Geocoder();

    // use google goelocation API to fetch state component
    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    geocoder.geocode({       
            latLng: latlng     
            }, 
            function(responses) 
            {     
                var results=responses;
                var storableLocation;
                for (var ac = 0; ac < results[0].address_components.length; ac++) {
                    var component = results[0].address_components[ac];

                    switch(component.types[0]) {
                        case 'locality':
                            city = component.long_name;
                            break;
                        case 'administrative_area_level_1':
                            state = component.short_name;
                            break;
                        case 'country':
                            country = component.long_name;
                            registered_country_iso_code = component.short_name;
                            break;
                    }
                };

                document.getElementById("location").value=state;
                  
            }
);    

}

var states = new Object();
function generateStates() {
    // generate a validation table for state abbreviation

    <?php
        include 'DB.php';
        $db = new DB();
        $states = $db->getRows('states',array('order_by'=>'id ASC'));
        if(!empty($states)): $count = 0; foreach($states as $state): $count++;
    ?>
    states["<?php echo $state['abbr']; ?>"] = "<?php echo $state['abbr']; ?>";
    <?php endforeach; endif; ?>
}

// Set various events
$(document).ready(function(){
    document.getElementById('website').style.visibility = 'hidden';

    $('#chkagree').click(function(){
      
        if($(this).prop('checked') == false){
             $('#btnadd').prop("disabled","disabled");   
        } else {
            $('#btnadd').removeAttr('disabled');
        }
    });
    $('#referral').on('change', function(){
      // Only show the news website drop down if "News Website" is selected from source
        if($(this).prop('value') == 'News Website'){
             document.getElementById('website').style.visibility = 'visible';
             document.getElementById('website').focus();
        } else {
            document.getElementById('website').style.visibility = 'hidden';
        }
    });
    $('#referralEdit').on('change', function(){
      // Only show the news website drop down if "News Website" is selected from source
        if($(this).prop('value') == 'News Website'){
             document.getElementById('websiteEdit').style.visibility = 'visible';
             document.getElementById('websiteEdit').focus();
        } else {
            document.getElementById('websiteEdit').style.visibility = 'hidden';
        }
    });
    $('#addLink').tooltip().eq(0).tooltip('show').tooltip('disable').one('mouseout', function() {
      $(this).tooltip('enable');
    });

    setTimeout(function() {
     $('#addLink').tooltip().eq(0).tooltip('hide').tooltip('enable');
    }, 5000);    
    generateStates();
    getLocation();

});
</script>
<script>
function getUsers(){
    // make an AJAX call to fetch user information from DB table
    $.ajax({
        type: 'POST',
        url: 'userAction.php',
        data: 'action_type=view&'+$("#userForm").serialize(),
        success:function(html){
            $('#userData').html(html);
        }
    });
}
function ajaxAction(type,userData){
    // handles add, update and delete AJAX calls
    var statusArr = {add:"added",edit:"updated",delete:"deleted"};
    $.ajax({
        type: 'POST',
        url: 'userAction.php',
        data: userData,
        success:function(msg){
            if(msg == 'ok'){
                alert('User data has been '+statusArr[type]+' successfully.');
                getUsers();
                if (type == 'add') {
                    $('.form')[0].reset();  
                    $('#referral').change();  
                    $('.formData').slideUp();
                } else {
                    $('.form')[1].reset();
                    $('.formEditData').slideUp();
                }
                
                
            }else if(msg == 'incorrectcaptcha'){
                alert('Incorrect Captcha Entered, please try again.');
                document.getElementById('captcha').focus();
            }else{
                alert('Some problem occurred, please try again.'+msg);
            }
        }
    });    
}
function userAction(type,id){
    // validates form data and calls ajaxAction with appropriate function request
    id = (typeof id == "undefined")?'':id;
    var userData = '';
    var valid;  
        
    if (type == 'add') {
        valid = validateContact();
        userData = $("#addForm").find('.form').serialize()+'&action_type='+type+'&id='+id;
    }else if (type == 'edit'){
        valid = validateEditContact();
        userData = $("#editForm").find('.form').serialize()+'&action_type='+type;
    }else{
        valid = true;
        userData = 'action_type='+type+'&id='+id;
    }

    if(valid) {  

        if(type == 'add') {
            var userData;
            userData = $("#email").serialize()+'&action_type=checkemail';
            $.ajax({
                type: 'POST',
                url: 'userAction.php',
                data: userData,
                success:function(msg){
                    if(msg == 'ok'){
                        $("#email-info").html("(email in use)");
                        $("#email").css('background-color','#FFFFDF');
                        valid = false;
                    } else {
                        userData = $("#addForm").find('.form').serialize()+'&action_type='+type+'&id='+id;
                        ajaxAction(type,userData);
                    }
                }
            });

        }else if (type == 'edit'){
            var userData;
            userData = $("#emailEdit").serialize()+'&action_type=checkemail';
            $.ajax({
                type: 'POST',
                url: 'userAction.php',
                data: userData,
                success:function(msg){
                    if((msg == 'ok') && ($("#emailEdit").val() != $("#currentemailEdit").val())) {
                        $("#emailEdit-info").html("(email in use)")
                        $("#emailEdit").css('background-color','#FFFFDF');
                        valid = false;
                    } else {
                        userData = $("#editForm").find('.form').serialize()+'&action_type='+type;
                        ajaxAction(type,userData);
                    }
                }
            });
        }else{
            userData = 'action_type='+type+'&id='+id;
            ajaxAction(type,userData);
        }

    }
}
function editUser(id){
    // populates edit form with user data using AJAX call
    $.ajax({
        type: 'POST',
        dataType:'JSON',
        url: 'userAction.php',
        data: 'action_type=data&id='+id,
        success:function(data){
            $('#idEdit').val(data.id);
            $('#firstnameEdit').val(data.firstname);
            $('#lastnameEdit').val(data.lastname);
            $('#emailEdit').val(data.email);
            $('#currentemailEdit').val(data.email);
            $('#companyEdit').val(data.company);
            $('#phoneEdit').val(data.phone);
            $('#referralEdit').val(data.referral);
            $('#websiteEdit').val(data.website);
            $('#referralEdit').change();
            $('#locationEdit').val(data.location);
            $('#editForm').slideDown();
        }
    });
}

function validateContact() {
    // validates form data
    var valid = true;   
    $(".info").html('');
    
    if(!$("#firstname").val()) {
        $("#firstname-info").html("(required)");
        $("#firstname").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#lastname").val()) {
        $("#lastname-info").html("(required)");
        $("#lastname").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#email").val()) {
        $("#email-info").html("(required)");
        $("#email").css('background-color','#FFFFDF');
        valid = false;
    }    
    if(!$("#email").val().match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/)) {
        $("#email-info").html("(invalid)");
        $("#email").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#referral").val()) {
        $("#referral-info").html("(required)");
        $("#referral").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#chkagree").prop('checked')) {
        $("#chkagree-info").html("(required)");
        $("#chkagree").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#captcha").val()) {
        $("#captcha-info").html("(required)");
        $("#captcha").css('background-color','#FFFFDF');
        valid = false;
    }
    if($("#location").val()) {
        if(!states.hasOwnProperty($("#location").val())){
            $("#location-info").html("(invalid state abbreviation)");
            $("#location").css('background-color','#FFFFDF');
            valid = false;            
        }
    }    
    
    return valid;
}

function validateEditContact() {
    // validates form data
    var valid = true;   
    $(".info").html('');
    
    if(!$("#firstnameEdit").val()) {
        $("#firstnameEdit-info").html("(required)");
        $("#firstnameEdit").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#lastnameEdit").val()) {
        $("#lastnameEdit-info").html("(required)");
        $("#lastnameEdit").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#emailEdit").val()) {
        $("#emailEdit-info").html("(required)");
        $("#emailEdit").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#emailEdit").val().match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/)) {
        $("#emailEdit-info").html("(invalid)");
        $("#emailEdit").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#referralEdit").val()) {
        $("#referralEdit-info").html("(required)");
        $("#referralEdit").css('background-color','#FFFFDF');
        valid = false;
    }
    if($("#locationEdit").val()) {
        if(!states.hasOwnProperty($("#locationEdit").val())){
            $("#locationEdit-info").html("(invalid state abbreviation)");
            $("#locationEdit").css('background-color','#FFFFDF');
            valid = false;            
        }
    }    

    return valid;
}
function refreshCaptcha() {
    // refreshes captcha image
    $("#captcha_code").prop('src','captcha_code.php?_='+((new Date()).getTime()));
}

</script>
</head>
<body>

<div class="container">
    <div class="row">
        <h2>Event Registration Form</h2>
        <div class="panel panel-default users-content">
            <div class="panel-heading">Registered Users <a href="javascript:void(0);" class="glyphicon glyphicon-plus" id="addLink" onclick="javascript:$('#addForm').slideToggle();if(!$('#location').val()) {getLocation();}" data-original-title="Click Here To Register"></a></div>
            <div class="panel-body none formData" id="addForm" action="">
                <h2 id="actionLabel">Add User</h2>
                <form class="form" id="userForm">
                    <div class="form-group">
                        <label>First Name</label>
                        <span id="firstname-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="firstname" id="firstname" required="" />
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <span id="lastname-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="lastname" id="lastname" required="" />
                    </div>                    
                    <div class="form-group">
                        <label>Email</label>
                        <span id="email-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="email" id="email" required="" />
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" class="form-control" name="company" id="company"/>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone"/>
                    </div>
                    <div class="form-group">
                        <label>Where did you hear from us?</label>
                        <span id="referral-info" class="info">*</span><br/>
                        <select class="form-control" name="referral" id="referral" required="">
                            <option value=""></option>
                            <?php
                                //include 'DB.php';
                                $db = new DB();
                                $referrals = $db->getRows('referrals',array('order_by'=>'id ASC'));
                                if(!empty($referrals)): $count = 0; foreach($referrals as $referral): $count++;
                            ?>
                            <option value="<?php echo $referral['name']; ?>"><?php echo $referral['name']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <select class="form-control" name="website" id="website" required="">
                            <?php
                                //include 'DB.php';
                                $db = new DB();
                                $newssites = $db->getRows('newssites',array('order_by'=>'id ASC'));
                                if(!empty($newssites)): $count = 0; foreach($newssites as $newsite): $count++;
                            ?>
                            <option value="<?php echo $newsite['name']; ?>"><?php echo $newsite['name']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>                                       
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <span id="location-info" class="info"></span><br/>
                        <input type="text" class="form-control" name="location" id="location"/>
                    </div>
                    <div class="form-group">
                        <label>Captcha</label>
                        <span id="captcha-info" class="info">*</span><br/>
                        <input type="text" name="captcha" id="captcha" class="form-control"><br>
                        <img id="captcha_code" src="captcha_code.php"/>
                        <a href="javascript:void(0);" class="btn btn-success" onclick="refreshCaptcha();">Refresh Captcha</a>
                        
                    </div>                    
                    <div class="form-group">
                        <label>I agree to the Terms &amp; Conditions</label>
                        <span id="chkagree-info" class="info">*</span>
                        <input type="checkbox" id="chkagree"/>
                    </div>                    
                    <a href="javascript:void(0);" class="btn btn-warning" onclick="$('#addForm').slideUp();">Cancel</a>
                    <a href="javascript:void(0);" id="btnadd" class="btn btn-success" onclick="userAction('add')">Add User</a>
                </form>
            </div>
            <div class="panel-body none formEditData" id="editForm">
                <h2 id="actionLabel">Edit User</h2>
                <form class="form" id="userForm">
                    <div class="form-group">
                        <label>First Name</label>
                        <span id="firstnameEdit-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="firstname" id="firstnameEdit" required="" />
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <span id="lastnameEdit-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="lastname" id="lastnameEdit" required="" />
                    </div>                    
                    <div class="form-group">
                        <label>Email</label>
                        <span id="emailEdit-info" class="info">*</span><br/>
                        <input type="text" class="form-control" name="email" id="emailEdit" required="" />
                        <input type="hidden" class="form-control" name="currentemail" id="currentemailEdit"/>
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" class="form-control" name="company" id="companyEdit"/>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" id="phoneEdit"/>
                    </div>   
                    <div class="form-group">
                        <label>Where did you hear from us?</label>
                        <span id="referralEdit-info" class="info">*</span><br/>
                        <select class="form-control" name="referral" id="referralEdit" required="">
                            <option value=""></option>
                            <?php
                                //include 'DB.php';
                                $db = new DB();
                                $referrals = $db->getRows('referrals',array('order_by'=>'id ASC'));
                                if(!empty($referrals)): $count = 0; foreach($referrals as $referral): $count++;
                            ?>
                            <option value="<?php echo $referral['name']; ?>"><?php echo $referral['name']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <select class="form-control" name="website" id="websiteEdit" required="">
                            <?php
                                //include 'DB.php';
                                $db = new DB();
                                $newssites = $db->getRows('newssites',array('order_by'=>'id ASC'));
                                if(!empty($newssites)): $count = 0; foreach($newssites as $newsite): $count++;
                            ?>
                            <option value="<?php echo $newsite['name']; ?>"><?php echo $newsite['name']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>                                       
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <span id="locationEdit-info" class="info"></span><br/>
                        <input type="text" class="form-control" name="location" id="locationEdit"/>
                    </div>                    
                    <input type="hidden" class="form-control" name="id" id="idEdit"/>
                    <a href="javascript:void(0);" class="btn btn-warning" onclick="$('#editForm').slideUp();">Cancel</a>
                    <a href="javascript:void(0);" class="btn btn-success" onclick="userAction('edit')">Update User</a>
                </form>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>Referral</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="userData">
                    <?php
                        //include 'DB.php';
                        $db = new DB();
                        $users = $db->getRows('users',array('order_by'=>'id DESC'));
                        if(!empty($users)): $count = 0; foreach($users as $user): $count++;
                    ?>
                    <tr>
                        <td><?php echo '#'.$count; ?></td>
                        <td><?php echo $user['firstname']; ?></td>
                        <td><?php echo $user['lastname']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['company']; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                        <?php if ($user['referral'] == 'News Website'): ?>
                        <td><?php echo $user['website']; ?></td>
                        <?php else: ?>
                        <td><?php echo $user['referral']; ?></td>
                        <?php endif; ?>
                        <td><?php echo $user['location']; ?></td>
                        <td>
                            <a href="javascript:void(0);" class="glyphicon glyphicon-edit" onclick="editUser('<?php echo $user['id']; ?>')"></a>
                            <a href="javascript:void(0);" class="glyphicon glyphicon-trash" onclick="return confirm('Are you sure to delete data?')?userAction('delete','<?php echo $user['id']; ?>'):false;"></a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5">No user(s) found......</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>