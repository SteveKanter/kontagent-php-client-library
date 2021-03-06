<?php
// Kontagent an_lib version KONTAGENT_VERSION_NUMBER
include_once 'kt_config.php';

// get in here only if it's a non-ajax call
if(! (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ) 
{
    if(isset($_POST['fb_sig_uninstall']))
    {
        $an->save_app_removed();
    }

    if(isset($_GET['installed']) && $_GET['installed'] == true)
        $kt_installed = true;
    else
        $kt_installed = false;


    if(isset($_REQUEST['fb_sig_canvas_user']))
    {
        // It will get here if it's an canvas app, and the app hasn't been authorized yet.
        // Also, we'll only get fb_sig_canvas_user the first time the canvas app is loaded.
        // Without this block, we can't record the r field in ntr, inr, and ucc.
        $kt_user = $_REQUEST['fb_sig_canvas_user'];
        setcookie("KT_USER", $kt_user, time()+ 86400); // one day
    }

    // grab user info
    if ( ($kt_user = $kt_facebook->get_loggedin_user()) && $an->get_fb_param('user') != 0 && !isset($_POST['fb_sig_uninstall']))
    {
        $kt_key = "KT_".$kt_facebook->api_key."_".$kt_user;
        
        if( empty($_COOKIE[$kt_key]) || (($auto_capture_user_info_at_install == true) && ($kt_installed == true) ) )
        {
            $uid = $an->get_fb_param('user');
            $kt_user_info = $kt_facebook->get_user_info($uid);
            $an->kt_capture_user_data($uid, $kt_user_info);
            setcookie($kt_key, 1, time()+1209600); //two weeks
        }
    }
    
//if( (isset($_POST['fb_sig_in_new_facebook']) && $_POST['fb_sig_in_new_facebook'] == 1) )
    {
        if(isset($_GET["kt_type"]))
        {
            $kt_is_added = $kt_facebook->fb_params['added'];

            if( !isset($kt_is_added) )
                $kt_is_added = 0;
            
            switch($_GET["kt_type"])        
            {
            case "nt":
            {
                $kt_url = $an->save_notification_click($kt_is_added); 
                $kt_facebook->redirect($kt_url);            
                break;
            }
            case "ins":
            {
                $kt_url = $an->save_invite_send();
                break;
            }
            case "in":
            {        
                $kt_url = $an->save_invite_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "nte":
            {
                $kt_url = $an->save_notification_email_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "feedpub":
            {
                $kt_url = $an->save_feedpub_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "stream":
            {
                $kt_url = $an->save_stream_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "feedstory":
            {
                $kt_url = $an->save_feedstory_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "multifeedstory":
            {
                $kt_url = $an->save_multifeedstory_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "profilebox":
            {
                $kt_url = $an->save_profilebox_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "profileinfo":
            {
                $kt_url = $an->save_profileinfo_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "ad":        
            case "partner":        
            {
                $kt_url = $an->save_undirected_comm_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "dashboardAddNews":
            {
                $kt_url = $an->save_dashboardAddNews_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "dashboardPublishActivity":
            {
                $kt_url = $an->save_dashboardPublishActivity_click($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            case "dashboardAddGlobalNews":
            {
                $kt_url = $an->save_dashboardAddGlobalNews($kt_is_added);
                $kt_facebook->redirect($kt_url);
                break;
            }
            }//switch
        }
    }


    //If the user hasn't authorized the application, fb_sig_user will not be set.
    
    if($kt_installed == true)
    {
        $an->save_app_added();    
    }

    if ( $automatic_page_request_capture == 1 )
    {
        include_once 'an_lib/page_request_capture.php';
    }

}

function set_ab_testing_page($campaign)
{
    global $kt_facebook;
    if($kt_facebook->api_client->m_an->m_ab_testing_mgr->are_page_message_coupled($campaign))
    {
        $page_msg_info = $kt_facebook->api_client->m_an->m_ab_testing_mgr->get_ab_testing_page_msg_tuple($campaign);
        $kt_facebook->api_client->m_an->m_ab_testing_mgr->cache_ab_testing_msg_page_tuple($campaign, $page_msg_info);
    }
    else
    {
        $page_info = $kt_facebook->api_client->m_an->m_ab_testing_mgr->get_ab_testing_page($campaign);
        $msg_info = $kt_facebook->api_client->m_an->m_ab_testing_mgr->get_ab_testing_message($campaign);
        $kt_facebook->api_client->m_an->m_ab_testing_mgr->cache_ab_testing_msg_and_page($campaign, $msg_info, $page_info);
    }
}

function get_page_text($campaign)
{
    global $kt_facebook;
    if($kt_facebook->api_client->m_an->m_ab_testing_mgr->are_page_message_coupled($campaign))
    {
        $page_msg_info = $kt_facebook->api_client->m_an->m_ab_testing_mgr->get_selected_page_msg_info($campaign);
        return $page_msg_info[2];
    }
    else
    {
        $page_info = $kt_facebook->api_client->m_an->m_ab_testing_mgr->get_selected_page_info($campaign);
        return $page_info[2];
    }
}

function vo_render_js()
{
    global $an;
    $s="<script type='text/javascript'>";
    $s.="var stream_vo_related_cookies = ".$an->render_vo_stream_cookie_fbjs();
    $s.="</script>";
    echo $s;
}
?>

