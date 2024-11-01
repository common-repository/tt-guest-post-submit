<?php

    function submit_post_function(){
        $optionArray = get_option( 'ttgps_options' );
        
        //echo "<pre>";
        //print_r($optionArray);;
        //echo "</pre>";
        
        $captchaFlag = !empty($optionArray['ttgps_chk_captchafield']) ? $optionArray['ttgps_chk_captchafield'] : '' ;//NewCode
        //$captchaType = !empty($optionArray['ttgps_drp_captchaselect']) ? esc_attr($optionArray['ttgps_drp_captchaselect']) : '';//NewCode
        //$secretKey = !empty($optionArray['ttgps_txt_google_secretkey']) ? esc_attr($optionArray['ttgps_txt_google_secretkey']) : '';//NerCode
        $enableFilter = !empty($optionArray['ttgps_chk_filter']) ? esc_attr($optionArray['ttgps_chk_filter']) : '';//NewCode 
        //$enableFilterTitle = !empty($optionArray['ttgps_chk_filter_title']) ? esc_attr($optionArray['ttgps_chk_filter_title']) : '';//NewCOde
        //$minLength = intval($optionArray['ttgps_txt_minlength']);//NewCode
        //$maxLength = intval($optionArray['ttgps_txt_maxlength']);//NewCode
        //$postRedirectFlag = !empty($optionArray['ttgps_chk_redirecttopost']) ? esc_attr($optionArray['ttgps_chk_redirecttopost']) : ''; // NewCode
        $notifyFlag = !empty($optionArray['ttgps_chk_notifyfield']) ? esc_attr($optionArray['ttgps_chk_notifyfield']) : ""; //NewCode
        $enableComment = !empty($optionArray['ttgps_chk_comment']) ? $optionArray['ttgps_chk_comment'] : "";
        $poststatus = !empty($optionArray['ttgps_drp_status']) ? $optionArray['ttgps_drp_status'] : "Pending";//NewCode
           
        $to_email = "";
        if(empty($optionArray['ttgps_txt_contact_email'])){
            $to_email = get_option('admin_email');
        }else{
            $to_email = $optionArray['ttgps_txt_contact_email'];
        }
        //echo $to_email;
        
        $content_str = strip_tags($_POST["content"]);
        
        
        //if ((isset($_POST['capf']) && $_POST['capf']== "on") && (isset($_POST['capr']) && $_POST['capr'] == "on")){    
         if ($captchaFlag == "on"){   
            $valid = false;
                if ( isset( $_COOKIE['Captcha'] ) ) {
                    list( $hash, $time ) = explode( '.', $_COOKIE['Captcha'] );
                    
                    // The code under the md5 first section needs to match the code
                    // entered in easycaptcha.php
                    if ( md5( 'HDBHAYYEJKPWIKJHDD' . $_REQUEST['ttgps_captcha'] . $_SERVER['REMOTE_ADDR'] . $time ) != $hash ) {
                            $abortmessage = __('Captcha code is wrong. Go back and try to get it right or reload to get a new captcha code.', 'ttgps_text_domain');
                            wp_die( $abortmessage );
                            exit;
                    }elseif (( time() - 5 * 60 ) > $time ){
                            $abortmessage = __('Captcha timed out. Please go back, reload the page and submit again.', 'ttgps_text_domain');
                            wp_die( $abortmessage );
                            exit;
                    }else{
                            // Set flag to accept and store user input
                            $valid = true;
                    }
                } else {
                    $abortmessage = __('No captcha cookie given. Make sure cookies are enabled.', 'ttgps_text_domain');
                    wp_die( $abortmessage );
                    exit;
                } // End of if (isset($_COOKIE['Captcha']))
        }
        else{
	    
            $valid = true;
        }
	//OK1
	//Checking Filtered Key words//
	//if(isset($_POST['enable_filter']) && $_POST['enable_filter']=="on"){
	if($enableFilter == "on"){  
	    $filter_array = explode(',', $optionArray['ttgps_txta_filter']);
	    $filtered_words_found = array_filter($filter_array, 'filtered_word_check');
	    if(count($filtered_words_found)>0){
		$abortmessage = __('Following Filtered Messeged are found in your Post. Please go back and Edit your Post before submit');
		$abortmessage .= "<br><br> <strong>";
		$abortmessage .= __('Filtered Words List: ');
		$abortmessage .=  implode(', ', $filtered_words_found ) . "</strong>";
		wp_die($abortmessage);
	    }
	}
        //OK2
	//====================================//
	if ( $valid ) {
	    
	    $title = isset($_POST["title"]) ? esc_attr($_POST["title"]) : "";
            $content = isset($_POST["content"]) ? wp_kses_post($_POST["content"]) : ""; // NewCode
            $tags = isset($_POST["tags"]) ? esc_attr($_POST["tags"]) : "";
            $author = isset($_POST["author"]) ? esc_attr($_POST["author"]) : "";
            $email = isset($_POST["email"]) ? sanitize_email($_POST["email"]) : "";
            $site = isset($_POST["site"]) ? esc_url($_POST["site"]) : "";
            $phone = isset($_POST["phone"]) ? preg_replace('/[^0-9+-]/', '', $_POST["phone"]) : "";
	    //$authorid = isset($_POST["authorid"]) ? $_POST["authorid"] : "" ;
	    if (is_user_logged_in()){
                $author = get_current_user_id();  
                $authorid = get_current_user_id();  
            }else{
                $user = get_user_by('login', $optionArray['ttgps_drp_account']);
                $authorid = $user->ID;
            }
            
            $redirect_location = !empty($optionArray["ttgps_txt_redirect"]) ? esc_url($optionArray["ttgps_txt_redirect"]) : "";   
            $commentstatus = ($enableComment == "on") ? 'open' : 'closed'; 
            
            if(isset($_POST['catdrp'])){
                $category = intval($_POST['catdrp'])==-1 ? array(1) : array(intval($_POST['catdrp']));
                
            }else{
                $category = "";	
	    }
	    //$redirect_location = isset($_POST["redirect_url"]) ? $_POST["redirect_url"] : "";
            //$to_email = isset($_POST["to_email"]) ? $_POST["to_email"] : "";

            //$nonce=$_POST["_wpnonce"];
	    //$poststatus = $_POST["post_status"];
	
	    if (isset($_POST['submit'])){
		$new_post = array(
		    'post_title'    => $title,
		    'post_content'  => $content,
		    'post_category' => $category,  // Usable for custom taxonomies too
		    'tags_input'    => $tags,
		    'post_status'   => $poststatus,           // Choose: publish, preview, future, draft, etc.
		    'post_type' => 'post',  //'post',page' or use a custom post type if you want to
		    'post_author' => $authorid //Author ID
		);
		
		$pid = wp_insert_post($new_post);
		add_post_meta($pid, 'author', $author, true);
		add_post_meta($pid, 'author-email', $email, true);
		add_post_meta($pid, 'author-website', $site, true);
                add_post_meta($pid, 'author-phone', $phone, true);
		    
		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );

                if ( $_FILES ) {
                    $files = $_FILES['featured-img'];
                    foreach ($files['name'] as $key => $value) {
                        if ($files['name'][$key]) {
                            $file = array(
			    'name'     => $files['name'][$key],
                            'type'     => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error'    => $files['error'][$key],
                            'size'     => $files['size'][$key]
                            );
 
                            $_FILES = array("featured-img" => $file);
                                
                            $counter = 1;    
                            foreach ($_FILES as $file => $array) {
                                if($counter == 1){
                                    $newupload = insert_attachment($file,$pid, true);
                                }else{
                                    $newupload = insert_attachment($file,$pid, false);    
                                }
                                ++$counter;
                            }   // End of inner foreach
                        }       // End of if
                    }           // End of outer foreach
                }               // End of if($_FILES)
            }                   // End of if (isset($_POST['submit']))
	    
            //if($_POST['notify_flag']=="on"){
            
            if($notifyFlag == "on"){  
                ttgps_send_confirmation_email($to_email, $poststatus);
            }
            
	    // Redirect browser to review submission page
	    //$redirectaddress = ( empty( $_POST['_wp_http_referer'] ) ? site_url() : $_POST['_wp_http_referer'] );
	    //$redirectaddress = ( !empty( $redirect_location ) ? $redirect_location : $_POST['_wp_http_referer'] );
            $redirectaddress =  !empty( $redirect_location ) ? $redirect_location : esc_url($_POST['_wp_http_referer']);
            wp_redirect( add_query_arg( __('submission_success','ttgps_text_domain'), '1', $redirectaddress ) );
	    exit;
	} // End of if ($valid)
    }
    
    function filtered_word_check($var){
        $optionArray = get_option( 'ttgps_options' );
        $enableFilter = !empty($optionArray['ttgps_chk_filter']) ? esc_attr($optionArray['ttgps_chk_filter']) : '';//NewCode 
        
        $content_str = strip_tags($_POST["content"]);
        if($enableFilter == "on"){$strtocheck .=  $content_str;}
        
        if(strpos(" ".$strtocheck, $var)){
            return true;
        }
	//if(strpos(" ".$_POST["content"], $var)){
	//    return true;
	//}
    }
    
    function insert_attachment($file_handler, $post_id, $setthumb) {
 
        // check to make sure its a successful upload
        if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
       
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');
       
        $attach_id = media_handle_upload( $file_handler, $post_id );
       
        if ($setthumb) {update_post_meta($post_id,'_thumbnail_id',$attach_id);}
        return $attach_id;
    }
    
    function check_and_set_value($val){
	if(isset($_POST[$val])){
	    return esc_attr($_POST[$val]);
	}else{
	    return "";
	}
	
    }
    
    function ttgps_send_confirmation_email($to_email, $poststatus) {

        $headers = 'Content-type: text/html';
        $message = __('A user submitted a new post to your Wordpress site database.','ttgps_text_domain').'<br /><br />';
        $message .= __('Post Title: ','ttgps_text_domain') . check_and_set_value('title') ;
        $message .= '<br />';
        $message .= '<a href="';
        $message .= add_query_arg( array(
                                'post_status' => $poststatus,
                                'post_type' => 'post' ),
                                admin_url( 'edit.php' ) );
        $message .= '">'.__('Moderate new post', 'ttgps_text_domain').'</a>';
        $email_title = htmlspecialchars_decode( get_bloginfo(), ENT_QUOTES ) . __(" - New Post Added: ", "ttgps_text_domain") . htmlspecialchars( check_and_set_value('title') );
        // Send e-mail
        wp_mail( $to_email, $email_title, $message, $headers );
        
        
    }
?>