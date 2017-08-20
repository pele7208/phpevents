<?php
require 'PHPMailerAutoload.php';
include 'DB.php';

$db = new DB();
$tblName = 'users';
if(isset($_POST['action_type']) && !empty($_POST['action_type'])){
    if($_POST['action_type'] == 'data'){
        $conditions['where'] = array('id'=>$_POST['id']);
        $conditions['return_type'] = 'single';
        $user = $db->getRows($tblName,$conditions);
        echo json_encode($user);
    }elseif($_POST['action_type'] == 'view'){
        $users = $db->getRows($tblName,array('order_by'=>'id DESC'));
        if(!empty($users)){
            $count = 0;
            foreach($users as $user): $count++;
                echo '<tr>';
                echo '<td>#'.$count.'</td>';
                echo '<td>'.$user['firstname'].'</td>';
                echo '<td>'.$user['lastname'].'</td>';
                echo '<td>'.$user['email'].'</td>';
                echo '<td>'.$user['company'].'</td>';
                echo '<td>'.$user['phone'].'</td>';
                if ($user['referral'] == 'News Website') {
                    echo '<td>'.$user['website'].'</td>';
                } else {
                    echo '<td>'.$user['referral'].'</td>';
                }
                echo '<td>'.$user['location'].'</td>';
                echo '<td><a href="javascript:void(0);" class="glyphicon glyphicon-edit" onclick="editUser(\''.$user['id'].'\')"></a><a href="javascript:void(0);" class="glyphicon glyphicon-trash" onclick="return confirm(\'Are you sure to delete data?\')?userAction(\'delete\',\''.$user['id'].'\'):false;"></a></td>';
                echo '</tr>';
            endforeach;
        }else{
            echo '<tr><td colspan="5">No user(s) found......</td></tr>';
        }
    }elseif($_POST['action_type'] == 'add'){
        session_start();
        if($_POST["captcha"]==$_SESSION["captcha_code"]){
            $userData = array(
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'email' => $_POST['email'],
                'company' => $_POST['company'],
                'phone' => $_POST['phone'],
                'referral' => $_POST['referral'],
                'website' => $_POST['website'],
                'location' => $_POST['location']
            );
            $insert = $db->insert($tblName,$userData);
            if($insert) {
                $mail = new PHPMailer;

                //$mail->SMTPDebug = 3;                               // Enable verbose debug output

                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = SMTPHOST;  // Specify  SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = MAILUSER;                 // SMTP username
                $mail->Password = MAILPASSWORD;;                           // SMTP password
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = SMTPPORT;                                    // TCP port to connect to

                $mail->setFrom(MAILFROMEMAIL, MAILFROMNAME);
                $mail->addAddress($userData['email'], $userData['firstname'].' '.$userData['lastname']);     // Add a recipient
                //$mail->addAddress('ellen@example.com');               // Name is optional
                //$mail->addReplyTo('info@example.com', 'Information');
                //$mail->addCC('cc@example.com');
                //$mail->addBCC('bcc@example.com');

                //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                //$mail->isHTML(true);                                  // Set email format to HTML

                $mail->Subject = MAILSUBJECT;
                $mail->Body    = MAILBODY;
                $mail->AltBody = MAILBODY;

                if(!$mail->send()) {
                    //echo 'ok';
                    //echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    //echo 'err';
                }                
            }
            echo $insert?'ok':'err';            
        } else {
            echo "incorrectcaptcha";
        }        

    }elseif($_POST['action_type'] == 'edit'){
        if(!empty($_POST['id'])){
            $userData = array(
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'email' => $_POST['email'],
                'company' => $_POST['company'],
                'phone' => $_POST['phone'],
                'referral' => $_POST['referral'],
                'website' => $_POST['website'],
                'location' => $_POST['location']
            );
            $condition = array('id' => $_POST['id']);
            $update = $db->update($tblName,$userData,$condition);
            echo $update?'ok':'err';
        }
    }elseif($_POST['action_type'] == 'delete'){
        if(!empty($_POST['id'])){
            $condition = array('id' => $_POST['id']);
            $delete = $db->delete($tblName,$condition);
            echo $delete?'ok':'err';
        }
    }elseif($_POST['action_type'] == 'checkemail'){
        if(!empty($_POST['email'])){
            $conditions['where'] = array('email'=>$_POST['email']);
            $conditions['return_type'] = 'single';
            $user = $db->getRows($tblName,$conditions);
            echo $user?'ok':'err';
        }
    }
    exit;
}
?>