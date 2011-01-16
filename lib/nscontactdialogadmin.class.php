<?php

class NSContactDialogAdmin {
	
	private static $instance;
	
	private static $option_defaults = array (
		
		'contact_email_address' => 'james@nearlysensical.com',
		'contact_link_class' => 'nscontact-dialog',
		'recaptcha_api_private_key' => '6LffebsSAAAAANHFPu9hHr6qdVxfpYeG0V2pVZvL',
		'recaptcha_api_public_key' => '6LffebsSAAAAAMVjN-4dWWM7JX1WA2oCdFzqnVgD'
		
	);
	
	private static $option_labels;

	private static $options = array(
			
		array(
				
			'name' => 'contact_email_address',
			'type' => 'text'
				
		),
		
		array(
			
			'name' => 'contact_link_class',
			'type' => 'text'
				
		),		
		
		array(

			'name' => 'recaptcha_api_private_key',
			'type' => 'password'
				
		),
		
		array(

			'name' => 'recaptcha_api_public_key',
			'type' => 'text'
				
		)
		
	);
	
	private static $shortname = 'nscontact-dialog';
	
	private static $option_values;
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private function __construct () {

		self::$option_labels = array(
			
			'contact_email_address' => __('Send Emails To', 'contact-dialog'),
			'contact_link_class' => __('Contact Link Class', 'contact-dialog'),
			'recaptcha_api_private_key' => __('Recaptcha API Private Key', 'contact-dialog'),
			'recaptcha_api_public_key' => __('Recaptcha API Public Key', 'contact-dialog')
			
		);
		
		$current_options = get_option(self::$shortname, array());
		
		$options = wp_parse_args($current_options, self::$option_defaults);
		
		if (0 < count(array_diff_assoc($current_options, $options))) {
			
			update_option(self::$shortname, $options);
			
		}
		
		self::$option_values = $options;
		
		add_action('init', array($this, 'update_options'));
		
		add_action('admin_menu', array($this, 'admin_menu'));
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function admin_menu () {
		
		add_menu_page(__('Contact Dialog', 'contact-dialog'), __('Contact Dialog', 'contact-dialog'), 'manage_options', 'nscontactdialogadmin', array('NSContactDialogAdmin', 'admin_form'));
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function admin_form () {
		
		$admin_form_content = '<div class="wrap">';
		
		$admin_form_content .= '<h2>' . __('Contact Form Dialog', 'contact-dialog') . '</h2>';
		
		$admin_form_content .= '<p class="howto">' . __('Set the options below to select what class link to attach the mailing list dialog to, what email address to send incoming mails to, and to change the recaptcha validation fields.', 'contact-dialog') . '</p>';
		
		$admin_form_content .= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
		
		$admin_form_content .= wp_nonce_field(basename(__FILE__), 'nscontactdialog', false, false);
		
		$admin_form_content .= '<table class="form-table">';
		
		$admin_form_content .= self::format_options_fields();
		
		$admin_form_content .= '<tr>';
		
		$admin_form_content .= '<td colspan="2"><input class="button-primary" type="submit" value="' . __('Update Contact Dialog Options', 'contact-dialog') . '" /></td>';
		
		$admin_form_content .= '</tr>';
		
		$admin_form_content .= '</table>';
		
		$admin_form_content .= '</form>';
		
		$admin_form_content .= '</div>';
		
		echo $admin_form_content;
		
	}
	
	public static function get_contact_form () {
		
		$contact_form = '<form action="' . get_bloginfo('wpurl') . '" method="post">';
		
		$contact_form .= '<p><label>' . __('Your Name', 'contact-dialog') . '<br /><input class="regular-text required" name="name" type="text" /></label></p>';
		
		$contact_form .= '<p><label>' . __('Your Email Address', 'contact-dialog') . '<br /><input class="regular-text required email" name="email" type="text" /></label></p>';
		
		$contact_form .= '<p><label>' . __('Subject', 'contact-dialog') . '<br /><input class="regular-text required" name="subject" type="text" /></label></p>';
		
		$contact_form .= '<p><label>' . __('Message', 'contact-dialog') . '<br /><textarea class="message-body" name="message-body"></textarea></label></p>';
		
		$contact_form .= '<p><input class="button" type="submit" value="' . __('Send Message', 'contact-dialog') . '" /></p>';
		
		$contact_form .= '</form>';
		
		return $contact_form;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function get_instance () {
		
		if (empty(self::$instance)) {
			
			$classname = __CLASS__;
			self::$instance = new $classname;
			
		}
		
		return self::$instance;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function get_options () {
		
		return self::$option_values;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function update_options () {

		if (isset($_POST['nscontactdialog']) && wp_verify_nonce($_POST['nscontactdialog'], basename(__FILE__))) {
			
			$new_values = $_POST;
			
			unset($new_values['nscontactdialog']);
			
			$new_values = wp_parse_args($new_values, self::$option_values);
			
			update_option(self::$shortname, $new_values);
			
			self::$option_values = $new_values;
			
		}
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private static function format_an_option_field ($value) {
		
		$option_field = '<th scope="row"><label>' . self::$option_labels[$value['name']] . '</label></th><td>';
		
		switch ($value['type']) {
			
			case 'text':
			
				$option_field .= '<input class="regular-text" name="' . $value['name'] . '" type="text" value="' . self::$option_values[$value['name']] . '" />';
				
				break;
			
			case 'password':
			
				$option_field .= '<input class="regular-text" name="' . $value['name'] . '" type="password" value="' . self::$option_values[$value['name']] . '" />';
				
				break;
				
			default:
			
				break;
			
		}
		
		$option_field .= '</td>';
		
		return $option_field;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private static function format_options_fields () {
		
		$option_fields = array_map(array('NSContactDialogAdmin', 'format_an_option_field'), self::$options);
		
		return '<tr>' . implode('</tr><tr>', $option_fields) . '</tr>';
		
	}
	
}

?>