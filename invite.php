<?php
define('_VALID', true);
require 'include/config.php';
require 'include/function_global.php';
require 'include/function_smarty.php';
require 'classes/filter.class.php';
require 'classes/validation.class.php';
require 'classes/email.class.php';

$message    = "Hey buddy! " .$config['site_name']. " is a site for sharing and hosting porn videos and it's awesome. You should definitely come and join it!";
$invite     = array('name' => '', 'message' => $message);
$emails     = array('0' => '', '2' => '', '2' => '', '3' => '', '4' => '');
if ( isset($_POST['submit_invite']) ) {
    $filter         = new VFilter();
    $valid          = new VValidation();
    $emails         = array();
    $emails[]       = $filter->get('friend_1');
    $emails[]       = $filter->get('friend_2');
    $emails[]       = $filter->get('friend_3');
    $emails[]       = $filter->get('friend_4');
    $emails[]       = $filter->get('friend_5');
    $name           = $filter->get('name');
    $message        = $filter->get('message');
    $code           = $filter->get('verification');
    
    if ( $name == '' ) {
        $errors[]       = $lang['invite.name_empty'];
    } elseif ( mb_strlen($name) >= 100 ) {
        $errors[]       = $lang['invite.name_invalid'];
    } else {
        $invite['name'] = $name;
    }
    
    if ( $_SESSION['captcha_code'] != strtoupper($code) ) {
        $errors[]       = $lang['global.verif_invalid'];
    }
    
    if ( $message == '' ) {
        $errors[]       = $lang['global.message_empty'];
    } elseif ( strlen($message) > 999 ) {
        $errors[]       = translate($lang['global.message_length'], '999');
    } else {
        $invite['message']  = $message;
    }
    
    if ( !$emails ) {
        $errors[]       = $lang['invite.emails_empty'];
    }
    
    if ( !$errors ) {
        $valid  = new VValidation();
        $index  = 0;
        foreach ( $emails as $email ) {
            if ( !$valid->email($email) ) {
                $emails[$index] = '';
            }
            ++$index;
        }
        
        if ( !$emails ) {
            $errors[]       = $lang['invite.emails_invalid'];
        }
        
        if ( !$errors ) {
            $sql                = "SELECT email_subject, email_path FROM emailinfo
                                   WHERE email_id = 'invite_friends_email' LIMIT 1";
            $rs                 = $conn->execute($sql);
            $email_subject      = str_replace('{$sender_name}', $name, $rs->fields['email_subject']);
            $email_path         = $rs->fields['email_path'];
            $smarty->assign('message', $message);
            $smarty->assign('sender_name', $name);
            $body               = $smarty->fetch($config['BASE_DIR'].'/templates/'.$email_path);
            $mail               = new VMail();
            $mail->set();
            $mail->Subject      = $email_subject;
            $mail->AltBody      = $body;
            $mail->Body         = nl2br($body);
            foreach ($emails as $email ) {
                $mail->AddAddress($email);
            }
            $mail->Send();
            $messages[]         = $lang['invite.sent'];
        }
    }
}

$smarty->assign('errors',$errors);
$smarty->assign('messages',$messages);
$smarty->assign('menu', 'community');
$smarty->assign('self_title', $seo['invite_title']);
$smarty->assign('self_description', $seo['invite_desc']);
$smarty->assign('self_keywords', $seo['invite_keywords']);
$smarty->assign('invite', $invite);
$smarty->assign('emails', $emails);
$smarty->display('header.tpl');
$smarty->display('errors.tpl');
$smarty->display('messages.tpl');
$smarty->display('invite.tpl');
$smarty->display('footer.tpl');
$smarty->gzip_encode();
?>
