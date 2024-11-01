<?php
/*
Plugin Name: TT Guest Post Submit
Plugin URI: http://www.technologiestodayweb.com.au/guide-to-use-tt-guest-post-submit-plugin/
Description: Enables your visitors to submit posts and images from anywhere on your site.
Tags: add post, content submission, guest blog, guest blogging, guest posting, wordpress guest post, post, submit post, submit, guest, guest post, admin, anonymous post, guest author, guest author plugin, post from front end, visitor post, captcha, secured post submit, user submitted post
Author: Rashed Latif
Author URI: http://www.technologiestodayweb.com.au/rashed-latif
Donate link: http://www.technologiestodayweb.com.au/donate
Requires at least: 3.0.1
Tested up to: 4.7.4
Version: 4.0
Stable tag: 4.0
License: GPL v2
*/
include dirname( __FILE__ ).'/ttgps-functions.php';
class TT_GuestPostSubmit{
    
    public function __construct(){
        wp_enqueue_style('ttgps-style', plugins_url('ttgps-style.css',__FILE__));

	/*
	wp_enqueue_script('tinymce_min', includes_url('js/tinymce/tinymce.min.js',__FILE__));
	wp_enqueue_script('tiny_mce', plugins_url('tiny_mce.js',__FILE__));
	*/
	if (is_admin()){
	    add_action( 'admin_menu', array($this, 'ttgps_add_settings_menu') );
            add_action( 'admin_init', array($this, 'ttgps_init_settings') );
	}
	$this->options = get_option( 'ttgps_options' );
        $this->enable_shortcode();
	add_action( 'template_redirect', array($this, 'ttgps_template_redirection')  );

    }
    
    public function ttgps_template_redirection( $template ) {	
	if ( !empty( $_POST['ttgps_form_submitted'] ) ) {	    
	    $this->ttgps_process_submit_form();
	} else {
	    return $template;
	}		
    }
    
    public function ttgps_add_settings_menu() {
	//add_options_page( __('TT Guest Post Submit Options', 'ttgps_text_domain'), __('TT Guest Post Submit', 'ttgps_text_domain'), 'administrator', __FILE__, array($this, 'ttgps_display_menu_page') );
	add_options_page( 'TT Guest Post Submit Options', 'TT Guest Post Submit', 'administrator', __FILE__, array($this, 'ttgps_display_menu_page') );
    }
    
    public function ttgps_display_menu_page(){

	?>
	<div id="tt-general" class="wrap">
            <h2><?php _e('TT Guest Post Submit Options','ttgps_text_domain'); ?></h2>
            <div id="short-code">Shortcode for this plugin: [tt-submit-post]</div>
            <form name="ttgps_options_form_settings_api" method="post" action="options.php">
		<?php settings_fields( 'ttgps_settings' ); ?>
		<?php do_settings_sections( 'ttgps_settings_section' ); ?> 
		<input type="submit" value="Submit" class="button-primary" id="ttgps" />
            </form>
	</div>
	<?php
    }
    
    public function ttgps_init_settings(){
	
        register_setting( 'ttgps_settings', 'ttgps_options');
		
	add_settings_section( 'ttgps_general_settings_section', __('General Settings', 'ttgps_text_domain'), array($this, 'ttgps_general_setting_section_callback'), 'ttgps_settings_section' );
	add_settings_section( 'ttgps_imageupload_settings_section', __('Image Upload Settings', 'ttgps_text_domain'), array($this, 'ttgps_imageupload_setting_section_callback'), 'ttgps_settings_section' );
        add_settings_section( 'ttgps_google_settings_section', __('Google reCAPTCHA Settings', 'ttgps_text_domain'), array($this, 'ttgps_google_setting_section_callback'), 'ttgps_settings_section' );
        add_settings_section( 'ttgps_field_selection_section', __('Field Selection', 'ttgps_text_domain'), array($this, 'ttgps_field_selection_section_callback'), 'ttgps_settings_section' );
        
	/*GENERAL SETTINGS*/
        add_settings_field( 'ttgps_chk_notifyfield', __('Send Notification via Email', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_general_settings_section', array('name' => 'ttgps_chk_notifyfield' ));
        add_settings_field( 'ttgps_txt_contact_email', __('Email for Notification', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_contact_email', 'txt_type' => 'email', 'place_holder' =>'Email Address For Sending Notification', 'size'=>50  ) );
        add_settings_field( 'ttgps_txt_confirmation_msg', __('Post Submit Confirmation Message', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_confirmation_msg', 'txt_type' => 'text', 'place_holder' =>'Type Message To Show When Post Submit Successfull', 'size'=>50  ) );
        add_settings_field( 'ttgps_txt_failure_msg', __('Post Submit Failure Message', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_failure_msg', 'txt_type' => 'text', 'place_holder' =>'Type Message To Show When Post Submit Fails', 'size'=>50  ) );
  /*nf*/add_settings_field( 'ttgps_chk_redirecttopost', __('Allow Redirect to Submitted Post', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_general_settings_section', array('name' => 'ttgps_chk_redirecttopost', 'note'=>'Only works when user is logged in or Publish Status is set to "Publish" from plugin option', 'disabled'=>'disabled' ));
        add_settings_field( 'ttgps_txt_redirect', __('Redirect To', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_redirect', 'txt_type' => 'text', 'place_holder' =>'URL to Redirect After Post Submit', 'size'=>50  ) );
        add_settings_field( 'ttgps_drp_status', __('Publish Status', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_status', 'drp_type' => 'post_status' ) );
	add_settings_field( 'ttgps_drp_account', __('Guest Account', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_account', 'drp_type' => 'guest_account' ) );
  /*nf*/add_settings_field( 'ttgps_drp_editortype', __('Content Editor Type', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_account', 'drp_type' => 'editor_type' ) );
  /*nf*/add_settings_field( 'ttgps_txt_minlength', __('Minimum Length of the post', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_minlength', 'txt_type' => 'number', 'place_holder' =>'Full Version Only', 'disabled' => 'disabled'  ) );  
        add_settings_field( 'ttgps_txt_maxlength', __('Maximum Length of the post', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txt_maxlength', 'txt_type' => 'number', 'place_holder' =>'Number of characters'  ) );
  /*nf*/add_settings_field( 'ttgps_chk_comment', __('Enable Comment ', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_general_settings_section', array('name' => 'ttgps_chk_comment', 'disabled' => 'disabled' ));
        add_settings_field( 'ttgps_chk_filter', __('Enable Filter For Post Content', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_general_settings_section', array('name' => 'ttgps_chk_filter' ));
  /*nf*/add_settings_field( 'ttgps_chk_filter_title', __('Enable Filter For Post Title', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_general_settings_section', array('name' => 'ttgps_chk_filter_title', 'disabled' => 'disabled' ));
        add_settings_field( 'ttgps_txta_filter', __('Add Filtered Words', 'ttgps_text_domain'), array($this,'ttgps_display_text_area'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_txta_filter', 'txt_type' => 'area', 'place_holder' =>'Add Filtered Words'  ) ); 
  /*nf*/add_settings_field( 'ttgps_drp_allowedcategories', __('Select Categories To Display ', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_allowedcategories', 'drp_type' => 'allowed_categories', 'multiple'=>'multiple' ) );
  /*nf*/add_settings_field( 'ttgps_drp_defaultategory', __('Select Default Category ', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_defaultategory', 'drp_type' => 'default_category') );
  /*nf*/add_settings_field( 'ttgps_drp_captchaselect', __('Select Captcha Type ', 'ttgps_text_domain'), array($this,'ttgps_display_dropdown'), 'ttgps_settings_section', 'ttgps_general_settings_section', array( 'name' => 'ttgps_drp_captchaselect', 'drp_type' => 'select_captcha') );
  
        /*IMAGE UPLOAD*/
  
  /*nf*/add_settings_field( 'ttgps_txt_filesize', __('Maximum File Size', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_imageupload_settings_section', array( 'name' => 'ttgps_txt_filesize', 'txt_type' => 'number', 'place_holder' =>'Full Version Only', 'disabled' => 'disabled'  ) );        
  /*nf*/add_settings_field( 'ttgps_txt_filetype', __('Allowed File Type', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_imageupload_settings_section', array( 'name' => 'ttgps_txt_filetype', 'txt_type' => 'text', 'place_holder' =>'Full Version Only ', 'size'=>50, 'disabled' => 'disabled'  ) );        
  /*nf*/add_settings_field( 'ttgps_txt_numberofimages', __('Maximum Number of Images To Upload', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_imageupload_settings_section', array( 'name' => 'ttgps_txt_numberofimages', 'txt_type' => 'number', 'place_holder' =>'Full Version Only', 'disabled'=>'disabled'  ) );        
  /*nf*/add_settings_field( 'ttgps_txt_imageheight', __('Maximum Resolution For Image', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_imageupload_settings_section', array( 'name' => 'ttgps_txt_imageheight', 'txt_type' => 'number', 'place_holder' =>'Height In Pixel', 'second_field'=>true, 'disabled'=>'disabled' ) );
  
        /*GOOGLE RECAPTCHA*/
        add_settings_field( 'ttgps_txt_google_sitekey', __('Site Key', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_google_settings_section', array( 'name' => 'ttgps_txt_google_sitekey', 'txt_type' => 'text', 'place_holder' =>'Enter Site Key For Your Website', 'size'=>50, 'disabled'=>'disabled'  ) );
	add_settings_field( 'ttgps_txt_google_secretkey', __('Secret Key', 'ttgps_text_domain'), array($this,'ttgps_display_text_field'), 'ttgps_settings_section', 'ttgps_google_settings_section', array( 'name' => 'ttgps_txt_google_secretkey', 'txt_type' => 'text', 'place_holder' =>'Enter Secret Key For Your Website ', 'size'=>50, 'disabled'=>'disabled'  ) );
        
        /*FIELD SELECTION*/
        add_settings_field( 'ttgps_chk_titlefield', __('Add Title Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_titlefield', 'req' => true));
	add_settings_field( 'ttgps_chk_contentfield', __('Add Post Content Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_contentfield', 'req' => true));
        add_settings_field( 'ttgps_chk_categoryfield', __('Add Category Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_categoryfield', 'req' => true));
	add_settings_field( 'ttgps_chk_tagsfield', __('Add Tags Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_tagsfield', 'req' => true));
	add_settings_field( 'ttgps_chk_namefield', __('Add Author\'s Name Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_namefield', 'req' => true));
        /*nf*/add_settings_field( 'ttgps_chk_phonefield', __('Add Author\'s Contact Number Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_phonefield', 'req' => true, 'size'=>35));
        add_settings_field( 'ttgps_chk_emailfield', __('Add Authors\'s Email Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_emailfield', 'req' => true));
	add_settings_field( 'ttgps_chk_websitefield', __('Add Website Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_websitefield', 'req' => true));
        add_settings_field( 'ttgps_chk_captchafield', __('Add Captcha Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_captchafield', 'req' => true));
        add_settings_field( 'ttgps_chk_uploadafield', __('Add Upload Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_uploadfield', 'req' => true));
	/**/add_settings_field( 'ttgps_lbl_litemsg', __('', 'ttgps_text_domain'), array($this,'ttgps_display_label'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_lbl_litemsg'));
        add_settings_field( 'ttgps_chk_featuredimagefield', __('Add Featured Image Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_featuredimagefield', 'req' => true, 'size'=>35, 'disabled'=>'disabled'));
        add_settings_field( 'ttgps_chk_uploadafield1', __('Add Additinal File Upload Field', 'ttgps_text_domain'), array($this,'ttgps_display_check_box'), 'ttgps_settings_section', 'ttgps_field_selection_section', array('name' => 'ttgps_chk_uploadfield1', 'req' => true, 'size'=>35, 'disabled'=>'disabled'));
    }
    
    public function ttgps_general_setting_section_callback() {
        echo "<p class='fullv-msg'>Inactive options are available in full version.";
        echo "<a href='http://technologiestodayweb.com.au/product/tt-guest-post-submit-plugin/'>Get Full Version 40% Off</a></p>"; /*NEED TO UPDATE*/
	echo "<p>".__("General configuration section", 'ttgps_text_domain')."</p>";
        
    }
    
    public function ttgps_imageupload_setting_section_callback() {
        echo "<p class='fullv-msg'>Inactive options are available in full version.";
        echo "<a href='http://technologiestodayweb.com.au/product/tt-guest-post-submit-plugin/'>Get Full Version 40% Off</a></p>"; /*NEED TO UPDATE*/
	echo "<p class='section-msg'>".__("Image Upload settings can be changed here. If you dont want to set any restrictions for the options below you can just leave them empty.", 'ttgps_text_domain')."</p>"; 
    }
    
    public function ttgps_google_setting_section_callback() {
        echo "<p class='fullv-msg'>Inactive options are available in full version.";
        echo "<a href='http://technologiestodayweb.com.au/product/tt-guest-post-submit-plugin/'>Get Full Version 40% Off</a></p>"; /*NEED TO UPDATE*/
	echo "<p class='section-msg'>".__("Step 1: In order to use Google reCAPTCHA you need to register your website first. To do that click <a href='https://www.google.com/recaptcha/'>here</a>", 'ttgps_text_domain')."<br>"; 
	echo __("Step 2: Click on 'Get reCAPTCHA' button")."<br>";
	echo __("Step 3: Login to your Google account and submit the form")."<br>";
	echo __("Step 4: Once submit, Google will provide you two informations. #Site key and #Secret key. Enter those keys in the following fields")."";
    }
	
    public function ttgps_field_selection_section_callback() {
        echo "<p class='fullv-msg'>Inactive options are available in full version.";
        echo "<a href='http://technologiestodayweb.com.au/product/tt-guest-post-submit-plugin/'>Get Full Version 40% Off</a></p>"; /*NEED TO UPDATE*/
        echo "<p>".__("Select fields which you want to be appear on post submit form", 'ttgps_text_domain')."</p>";
    }
    
    public function ttgps_display_text_field( $data = array() ) {
	extract( $data );
	//$options = get_option( 'ttgps_options' ); 
	?>
        <input type="<?php echo $txt_type ?>" name="ttgps_options[<?php echo $name; ?>]" placeholder="<?php echo $place_holder; ?>" <?php if($txt_type=="number"){echo ' min="0"';} ?> size="<?php echo $size; ?>" <?php echo " ".$disabled; ?> value="<?php echo esc_html( $this->options[$name] ); ?>"/>
	<?php
        if($second_field){
            ?>
            <label>&nbsp;X&nbsp;</label><input type="number" placeholder="Width In Pixel" min="0" name="ttgps_options[<?php echo 'ttgps_txt_imagewidth'; ?>]" disabled value="<?php echo esc_html( $this->options['ttgps_txt_imagewidth'] ); ?>"/>
            <?php
        }else{
            //echo "<br />";
        }
    }

    public function ttgps_display_label( $data = array() ){
        extract($data);
        ?>
        <label id="lite-msg" name="ttgps_options[<?php echo $name; ?>]">Following two options are available in full version only replacing "Ädd Upload Field"</label>
        <?php
    }
    
    public function ttgps_display_text_area( $data = array() ){
	extract( $data );
	?>
	<textarea rows="5" cols="45" maxlength="5000" name="ttgps_options[<?php echo $name; ?>]" placeholder="<?php echo $place_holder; ?>" ><?php echo esc_html( $this->options[$name]);?></textarea>
	<?php
    }
    

    public function ttgps_display_check_box( $data = array() ) {
	extract ( $data );

	$required_item = $name . "_req";
        $field_title = $name . "_title";
        $field_order = $name . "_order";

	?>
	<input type="checkbox" <?php echo $disabled;  ?> name="ttgps_options[<?php echo $name; ?>]" <?php if (isset($this->options[$name])) echo ' checked="checked" '; ?>/>
	<?php
	
	if (isset($req) && $req==true){
	?>
        <label id="required-label"><?php _e('Required', 'ttgps_text_domain'); ?></label> <input type="checkbox" name="ttgps_options[<?php echo $required_item; ?>]" <?php echo $disabled; ?> <?php if (isset( $this->options[$required_item] )) echo ' checked="checked" '; ?>/>
            <label id="order-label"><?php _e('Order', 'ttgps_text_domain'); ?></label><input id="num-ord" type="number" disabled min="0" name="ttgps_options[<?php echo $field_order; ?>]" value="<?php echo esc_html( $this->options[$field_order] ); ?>"/>
            <label id="title-label"><?php _e('Title', 'ttgps_text_domain'); ?></label><input type="text" disabled name="ttgps_options[<?php echo $field_title; ?>]" placeholder="<?php echo "Custom Title For This Field - (Full Version Only)"; ?>" size="50" value="<?php echo esc_html( $this->options[$field_title] ); ?>"/>
            <label id="optional-label"><?php _e('(Optional)', 'ttgps_text_domain'); ?><br />
        <?php
        }
    }
//OK
    public function ttgps_display_dropdown( $data = array() ) {
	extract($data);
        $disflag = false;
	if ($drp_type == 'post_status'){
	    $drp_array = array('Publish', 'Pending', 'Draft');	
	}else if($drp_type == 'guest_account'){
	    $drp_array = get_users('role=author');     
	}else if($drp_type == 'editor_type'){
	    $drp_array = array('Simple', 'Rich Text(Full) - Full Version Only', 'Rich Text(Tiny) - Full Version Only'); 
            $disflag = true;
	}else if($drp_type == 'select_captcha'){
	    $drp_array = array('Easy Captcha', 'Google reCAPTCHA');
            $disflag = true;
        }else if($drp_type == 'allowed_categories' || $drp_type == 'default_category'){
             $args = array(
                            'orderby' => 'name',
                            'order' => 'ASC'
                            );
             $drp_array = get_categories($args);
             $disflag = true;
             $lite = true;
        }
	?>
        <select name="ttgps_options[<?php echo $name; ?>]" <?php if(isset($multiple)){echo $multiple;} ?> <?php if($lite){echo " disabled='disabled'";}?> >
            <?php
            if($drp_type == 'allowed_categories' || $drp_type == 'default_category'){
            ?>
                <option value="">Available in Full Version</option>
            <?php    
            }

            $counter = 1;
	    foreach($drp_array as $drp_item){ 
		if($drp_type == 'guest_account'){?>
		    <!--<option value="<?php //echo $drp_item->user_login; ?>" <?php //echo selected( $this->options['ttgps_drp_account'], $drp_item->user_login ); ?> > <?php //echo $drp_item->display_name; ?></option>-->
                    <option value="<?php echo $drp_item->user_login; ?>" <?php echo selected( $this->options[$name], $drp_item->user_login ); ?> > <?php echo $drp_item->display_name; ?></option>
          <?php }else if($drp_type == 'allowed_categories' || $drp_type == 'default_category'){ ?>
                    <option value="<?php echo $drp_item->cat_ID; ?>"><?php echo $drp_item->name; ?></option>	
	  <?php }else{     
                    if($disflag && $counter>1){$disabled = 'disabled';}else{$disabled = '';}
                    ?>
                    <option value="<?php echo $drp_item; ?>"  <?php echo $disabled . selected( $this->options['ttgps_drp_status'], $drp_item ); ?> ><?php echo $drp_item; ?></option>	
	    <?php }
                ++$counter;
            } ?>
	</select>
	<?php
    }
    
    public function enable_shortcode(){
        add_shortcode('tt-submit-post', array($this, 'ttgps_guest_submit_post_shortcode') );
    }
   //OK2 
    public function ttgps_guest_submit_post_shortcode($atts){
	
	/*$user = get_user_by('login', $this->options['ttgps_drp_account']);
	extract(shortcode_atts(array(
                            'author' => $user->ID,
                            'redirect_url' => $this->options['ttgps_txt_redirect'], //get_permalink(),
                            ), $atts )
                );
        if (is_user_logged_in()){
            $author = get_current_user_id();    
        }else{
	    
	    $user = get_user_by('login', $this->options['ttgps_drp_account']);
	    $author = $user->ID;
	}*/
	
	/*$to_mail = "";
	if(empty($this->options['ttgps_txt_contact_email'])){
	    $to_mail = get_option('admin_email');
	}else{
	    $to_mail = $this->options['ttgps_txt_contact_email'];
	}*/
	
	$template_str = "";

	//Display confirmation message to users who submit a book review
	if ( isset ( $_GET['submission_success'] ) && $_GET['submission_success'] ) {
	    
	    $template_str = '<div class="message-box">' . 
				$this->options['ttgps_txt_confirmation_msg'] .
			    '</div>';
	}

	//Post variable to indicate user-submitted items
	$template_str .= '<input type="hidden" name="ttgps_form_submitted" value="1" />';
        $template_str .= '<form id="ttgps-form" action="" method="post" enctype="multipart/form-data">
			    <div id="wrapping" class="clearfix">
                                <section id="aligned">';
                                            
				    if(isset($this->options['ttgps_chk_titlefield']) && $this->options['ttgps_chk_titlefield'] == "on"){
					$template_str .= '<input  type="text" class="txtinput" id="title" name="title" title= "'.__("Please Enter a Post Title","ttgps_text_domain").'" x-moz-errormessage="'.__("Please Enter a Post Title","ttgps_text_domain").'" size="72"';
					$template_str .= (isset($this->options['ttgps_chk_titlefield_req']) && $this->options['ttgps_chk_titlefield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Post Title Here", "ttgps_text_domain").'">';// . wp_nonce_field();
				    }
                                    if(isset($this->options['ttgps_chk_contentfield']) && $this->options['ttgps_chk_contentfield'] == "on"){   
				        $template_str .= '<textarea class="txtblock" name="content" title="'.__("Please Enter Contents", "ttgps_text_domain").'" x-moz-errormessage="'.__("Please Enter Contents", "ttgps_text_domain").'" rows="15" cols="72" maxlength="'.$this->options['ttgps_txt_maxlength'].'"';
					$template_str .= (isset($this->options['ttgps_chk_contentfield_req']) && $this->options['ttgps_chk_contentfield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Write Your Post Contents", "ttgps_text_domain").'"></textarea>';
					
				    }//OK3
				    if(isset($this->options['ttgps_chk_categoryfield']) && $this->options['ttgps_chk_categoryfield'] == "on"){   
					$args = array(
						'orderby' => 'name',
						'order' => 'ASC'
						);
					$categories = get_categories($args);
					$template_str .= '<select name="catdrp" class="postform" id="catdrp" ';
					$template_str .= (isset($this->options['ttgps_chk_categoryfield_req']) && $this->options['ttgps_chk_categoryfield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= '> <option value="">'.__("Select a Category", "ttgps_text_domain").'</option>';
					foreach($categories as $category) { 
					    $template_str .= '<option value="' . $category->cat_ID . '">'.$category->name.'</option>';
					}
					$template_str .= '</select>';
				    }//OK4
				    if(isset($this->options['ttgps_chk_tagsfield']) && $this->options['ttgps_chk_tagsfield'] == "on"){
				        $template_str .= '<input type="text" class="txtinput" id="tags" name="tags" size="72"';
					$template_str .= (isset($this->options['ttgps_chk_tagsfield_req']) && $this->options['ttgps_chk_tagsfield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Comma Separated Tags", "ttgps_text_domain").'">';
				    }
				    if(isset($this->options['ttgps_chk_namefield']) && $this->options['ttgps_chk_namefield'] == "on"){
					$template_str .= '<input type="text" class="txtinput" title="'.__("Please Enter Author Name", "ttgps_text_domain").'" x-moz-errormessage="'.__("Please Enter Author Name", "ttgps_text_domain").'"  id="author"  name="author" size="72"';
					$template_str .= (isset($this->options['ttgps_chk_namefield_req']) && $this->options['ttgps_chk_namefield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Your Name Here", "ttgps_text_domain").'">';       
				    }
                                    if(isset($this->options['ttgps_chk_phonefield']) && $this->options['ttgps_chk_phonefield'] == "on"){
                                                    $template_str .= '<input type="text" class="txtinput" title="'.__("Please Enter Author\'s Contact Number", "ttgps_text_domain").'" x-moz-errormessage="'.__("Please Enter Author\'s Contact Number", "ttgps_text_domain").'"  id="phone"  name="phone" size="72"';
                                                    $template_str .= (isset($this->options['ttgps_chk_phonefield_req']) && $this->options['ttgps_chk_phonefield_req']=="on") ? ' required="required" ' : ' ';
                                                    $template_str .= 'placeholder="'.__((isset($this->options['ttgps_chk_phonefield_title'])&&$this->options['ttgps_chk_phonefield_title']!='')? $this->options['ttgps_chk_phonefield_title']:"Your Contact Number Here", "ttgps_text_domain").'">';       
                                                    ++$field_counter;
                                    }
                                    
				    if(isset($this->options['ttgps_chk_emailfield']) && $this->options['ttgps_chk_emailfield'] == "on"){
					$template_str .= '<input type="email" class="txtinput" title="'.__("Please Enter a Valid Email Address", "ttgps_text_domain").'" x-moz-errormessage="'.__("Please Enter a Valid Email Address", "ttgps_text_domain").'" id="email" name="email" size="72"';
					$template_str .= (isset($this->options['ttgps_chk_emailfield_req']) && $this->options['ttgps_chk_emailfield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Your Email Address Here", "ttgps_text_domain").'">';
				    }
				    if( isset($this->options['ttgps_chk_websitefield']) && $this->options['ttgps_chk_websitefield'] == "on"){
				        $template_str .= '<input type="text" class="txtinput" id="site" name="site" size="72"';
					$template_str .= (isset($this->options['ttgps_chk_websitefield_req']) && $this->options['ttgps_chk_websitefield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= 'placeholder="'.__("Your Website Address Here", "ttgps_text_domain").'">';
				    }
				    if(isset($this->options['ttgps_chk_uploadfield']) && $this->options['ttgps_chk_uploadfield'] == "on"){
					$template_str .= '<p id="fi-title">'. __("Upload Featured Image and Additional Images","ttgps_text_domain") . '</p>
							    <div class="featured-img">
								<input name="featured-img[]" type="file" id="featured-img"';
								$template_str .= (isset($this->options['ttgps_chk_uploadfield_req']) && $this->options['ttgps_chk_uploadfield_req']=="on") ? ' required="required" ' : ' ';
								$template_str .= ' multiple="multiple"><br>
							    </div>';
				    }//OK5
				    if(isset($this->options['ttgps_chk_captchafield']) && $this->options['ttgps_chk_captchafield'] == "on"){
					$template_str .= '<img src="' . plugins_url( 'EasyCaptcha/easycaptcha.php', __FILE__ ) . '" id="captcha-code" />' .
							 '<input type="text" class="" placeholder="'.__("Captcha Code Here", "ttgps_text_domain").'" x-moz-errormessage="Please Enter Correct Captcha Code" id="code" name="ttgps_captcha" size="16"';
					$template_str .= (isset($this->options['ttgps_chk_captchafield_req']) && $this->options['ttgps_chk_captchafield_req']=="on") ? ' required="required" ' : ' ';
					$template_str .= ' />';
				    }            
				        $template_str .= /*'<input type="hidden" value="'. $author .'" name="authorid">
							  <input type="hidden" value="'. $redirect_url .'" name="redirect_url">
							  <input type="hidden" value="'. $this->options["ttgps_drp_status"] .'" name="post_status">
							  
							  <input type="hidden" value="';
							  $template_str .= isset($this->options["ttgps_chk_notifyfield"])?$this->options["ttgps_chk_notifyfield"]:"";
							  $template_str .= '" name="notify_flag">
							  
							  <input type="hidden" value="'. $this->check_and_set_value("ttgps_chk_captchafield") .'" name="capf">
							  
							  <input type="hidden" value="'. $this->check_and_set_value("ttgps_chk_captchafield_req") .'" name="capr">
							  
							  <input type="hidden" value="'. $this->check_and_set_value("ttgps_chk_filter") .'" name="enable_filter">

							  <input type="hidden" value="'. $this->check_and_set_value("ttgps_txta_filter") .'" name="filter_items">
							  
							  
							  <input type="hidden" value="'. $to_mail .'" name="to_email">*/
							  '<input type="hidden" name="ttgps_form_submitted" value="1" />' . 
                                '</section>
                                <section id="buttons">
                                        <input type="reset" name="reset" id="resetbtn" class="resetbtn" value="'.__("Reset", "ttgps_text_domain").'">
                                        <input type="submit" name="submit" id="submitbtn" class="submitbtn" tabindex="7" value="'.__("Submit Post", "ttgps_text_domain").'">
                                        <br style="clear:both;">
                                </section>
                            </div>
                        </form>';
        return $template_str; 
    }
    
    public function check_and_set_value($val){
	if(isset($this->options[$val])){
	    return $this->options[$val];
	}else{
	    return "";
	}
	
    }
    
    public function ttgps_process_submit_form(){
	submit_post_function();
    } // End of function

} // End of Class


add_action( 'init', 'ttgps_plugin_init' );
function ttgps_plugin_init() {
	$ttgpsObj = new TT_GuestPostSubmit();
	load_plugin_textdomain( 'ttgps_text_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

?>