<?php
defined('_VALID') or die('Restricted Access!');

function getUserQuery()
{
    $options    = array('username' => NULL, 'module' => NULL, 'query' => NULL);
    if ( isset($_SESSION['uid']) && isset($_SESSION['username']) ) {
        if ( $_SESSION['uid'] != '' && $_SESSION['username'] != '' ) {
            $options['username']    = $_SESSION['username'];
        }
    }
    
    $request            = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : NULL;
    $request            = ( isset($_SERVER['QUERY_STRING']) ) ? str_replace('?' .$_SERVER['QUERY_STRING'], '', $request) : $request;
    $query              = explode('/', $request);
    foreach ( $query as $key => $value ) {
        if ( $value == 'user' ) {
            $query = array_slice($query, $key+1);
        }
    }
    
    if ( isset($query['0']) && $query['0'] != '' ) {
        $module             = $query['0'];
        $modules_allowed    = array(
            'edit' => 1,
            'avatar' => 1,
            'prefs' => 1,
            'blocks' => 1,
            'delete' => 1
        );
        
        if ( isset($modules_allowed[$module]) ) {
            $options['module']      = $module;
        } else {
            $options['username']    = $module;
            $options['query']       = array_slice($query, 1);
        }
    }
    return $options;
}

function getUserModule( $query )
{
    $options = array('module' => NULL, 'query' => NULL);
    if ( isset($query['0']) && $query['0'] != '' ) {
        $module             = $query['0'];
        $modules_allowed    = array(
            'videos'        => 1,
            'favorite'      => 1,
            'wall'          => 1,
            'addblog'       => 1,
            'blog'          => 1,
            'friends'       => 1,
            'playlist'      => 1,
            'albums'        => 1,
            'subscribers'   => 1,
            'games'         => 1,
            'subscriptions' => 1
        );
        
        if ( isset($modules_allowed[$module]) ) {
            $options['module']  = $module;
            $options['query']   = array_slice($query, 1);
        }
    }
    
    return $options;
}

function get_user_prefs( $uid )
{
    global $conn;
    
    $sql    = "SELECT * FROM users_prefs WHERE UID = " .intval($uid). " LIMIT 1";
    $rs     = $conn->execute($sql);
    $prefs  = $rs->getrows();
    
    return $prefs['0'];
}

function is_friend( $uid )
{
    global $conn;
    
    $is_friend  = false;
    if ( isset($_SESSION['uid']) ) {
        $sql        = "SELECT UID FROM friends WHERE UID = " .intval($uid). " AND FID = " .intval($_SESSION['uid']). " AND status = 'Confirmed' LIMIT 1";
        $conn->execute($sql);
        if ( $conn->Affected_Rows() == 1 ) {
            $is_friend = true;
        }
    }
    
    return $is_friend;    
}

function get_user_friends( $uid, $prefs, $is_friend, $limit=4 )
{
    $friends = array();
    $show    = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql        = "SELECT f.FID, u.username, u.photo, u.gender FROM friends AS f, signup AS u
                       WHERE f.UID = " .$uid. " AND f.FID = u.UID AND f.status = 'Confirmed'
                       ORDER BY f.invite_date DESC LIMIT " .$limit;
        $rs         = $conn->execute($sql);
        $friends    = $rs->getrows();
    }
    
    return $friends;
}

function get_user_playlist( $uid, $prefs, $is_friend, $limit=4 )
{
    $playlist = array();
    $show    = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql        = "SELECT p.VID, v.title, v.duration, v.viewnumber, v.rate, v.addtime, v.type, v.thumb, v.thumbs
                       FROM playlist AS p, video AS v
                       WHERE p.UID = " .intval($uid). " AND p.VID = v.VID ORDER by v.viewtime DESC LIMIT " .$limit;
        $rs         = $conn->execute($sql);
        $playlist   = $rs->getrows();
    }
    
    return $playlist;
}

function get_user_favorites( $uid, $prefs, $is_friend, $limit=4 )
{
    $favorites = array();
    $show    = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql        = "SELECT f.VID, v.title, v.duration, v.viewnumber, v.rate, v.type, v.thumb, v.thumbs
                       FROM favourite AS f, video AS v
                       WHERE f.UID = " .intval($uid). " AND f.VID = v.VID ORDER by v.viewtime DESC LIMIT " .$limit;
        $rs         = $conn->execute($sql);
        $favorites  = $rs->getrows();
    }
    
    return $favorites;
}

function get_user_subscribers( $uid, $prefs, $is_friend, $limit=4 )
{
    $favorites = array();
    $show    = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql            = "SELECT vs.SUID, s.username, s.photo, s.gender FROM video_subscribe AS vs, signup AS s
                           WHERE vs.UID = " .$uid. " AND vs.SUID = s.UID LIMIT " .$limit;
        $rs             = $conn->execute($sql);
        $subscribers    = $rs->getrows();
    }
    
    return $subscribers;
}

function get_user_subscriptions( $uid, $prefs, $is_friend, $limit=4 )
{
    $favorites = array();
    $show    = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql            = "SELECT vs.UID, s.username, s.photo, s.gender FROM video_subscribe AS vs, signup AS s
                           WHERE SUID = " .$uid. " AND vs.UID = s.UID LIMIT " .$limit;
        $rs             = $conn->execute($sql);
        $subscriptions  = $rs->getrows();
    }
    
    return $subscriptions;
}

function get_user_videos( $uid, $type='public', $limit=4 )
{
    global $conn;
    $sql    = "SELECT VID, title, duration, viewnumber, rate, addtime, type, thumb, thumbs, hd
	           FROM video
               WHERE UID = " .$uid. " AND type = '" .$type. "' AND active = '1'
               ORDER BY viewtime DESC LIMIT " .$limit;
    $rs     = $conn->execute($sql);
    return $rs->getrows();
}

function get_user_albums( $uid, $limit=4 )
{
    global $conn;
    $sql        = "SELECT AID, name, rate, total_photos, addtime, type FROM albums
                   WHERE UID = " .$uid. " AND status = '1' ORDER BY addtime DESC LIMIT " .$limit;
    $rs         = $conn->execute($sql);
    
    return $rs->getrows();
}

function get_user_favorite_photos( $uid, $prefs, $is_friend, $limit=4 )
{
    $favorites  = array();
    $show       = false;
    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql        = "SELECT p.PID, p.caption FROM photos AS p, photo_favorites AS f
                       WHERE f.UID = " .$uid. " AND p.PID = f.PID ORDER BY p.PID DESC LIMIT " .$limit;
        $rs         = $conn->execute($sql);
        $favorites  = $rs->getrows();
    }
    
    return $favorites;
}

function get_user_favorite_games( $uid, $prefs, $is_friend, $limit=4 )
{
    $favorites  = array();
    $show       = false;

    if ( $prefs == 2 ) {
        $show = true;
    } elseif ( $prefs == 1 && $is_friend ) {
        $show = true;
    } elseif ( $prefs == 0 && isset($_SESSION['uid']) && $_SESSION['uid'] == $uid ) {
        $show = true;
    }
    
    if ( $show ) {
        global $conn;
        $sql        = "SELECT g.GID, g.title, g.rate, g.total_plays, g.addtime, g.type FROM game AS g, game_favorites AS f
                       WHERE f.UID = " .$uid. " AND g.GID = f.GID ORDER BY g.GID DESC LIMIT " .$limit;
        $rs         = $conn->execute($sql);
        $favorites  = $rs->getrows();
    }
    
    return $favorites;    
}
?>
