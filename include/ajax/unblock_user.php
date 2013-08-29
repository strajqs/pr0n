<?php
defined('_VALID') or die('Restricted Access!');

require $config['BASE_DIR']. '/classes/filter.class.php';
require $config['BASE_DIR']. '/include/compat/json.php';
require $config['BASE_DIR']. '/include/adodb/adodb.inc.php';
require $config['BASE_DIR']. '/include/dbconn.php';

$response = array('status' => 0, 'msg' => $lang['ajax.unblock_user_success']);
if ( isset($_POST['user_id']) ) {
    if ( isset($_SESSION['uid']) ) {
        $filter     = new VFilter();
        $uid        = intval($_SESSION['uid']);
        $user_id    = $filter->get('user_id', 'INTEGER');
        if ( $uid == $user_id ) {
            $response['msg']   = $lang['ajax.block_user_self'];
        } else {
            $sql        = "DELETE FROM users_blocks WHERE UID = " .$uid. " AND BID = " .$user_id. " LIMIT 1";
            $conn->execute($sql);
            $response['status'] = 1;
        }
    } else {
        $response['msg'] = $lang['ajax.block_user_login'];
    }
}

echo json_encode($response);
die();
?>