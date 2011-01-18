<?php

class NSContactDialog {
	
	const URL = NSCD_DIR_URL;
	
	public static $textdomain = 'contact-dialog';
	
	private static $instance;
	
	private $donate_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JSL4JTA4KMZLG';
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private function __construct () {
		
		//Action hooks
		add_action('init', array($this, 'init'));
		
		if (isset($_GET['nscontactdialog'])) {
		
			add_action('parse_request', array($this, 'handle_ajax_request'));
			
		}
		
		//Filter hooks
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_meta_links'), 10, 2);
		
		//Shortcode
		add_shortcode('ns-contact-dialog', array($this, 'shortcode'));
		
		$nscontactdialogadmin = NSContactDialogAdmin::get_instance();
		
		self::load_textdomain();

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
	 * @since 0.3
	 * @author jameslafferty
	 */
	public function add_plugin_meta_links ($links, $file) {
		
		if (plugin_basename(realpath(dirname(__FILE__) . '/../contact-dialog.php')) == $file) {
			
			$links[] = '<a href="' . $this->donate_link . '">' . __('Donate') . '</a>';
			
		}
		
		return $links;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function handle_ajax_request () {
		
		if (isset($_GET['nscontactdialog']) && wp_verify_nonce($_GET['nscontactdialog'], basename(__FILE__))) {
			
			if (isset($_GET['nscdaction'])) {
			
				switch ($_GET['nscdaction']) {

					case 'add-dialogs' :

						header('Content-type: application/javascript');

						$options = NSContactDialogAdmin::get_options();

						unset($options['recaptcha_api_private_key']);

						$options['nscontactdialog'] = wp_create_nonce(basename(__FILE__));

						$options['nscdaction'] = 'send-message';

						$options['working_message'] = __('We\'re working on sending your message', 'contact-dialog');

						$options['invalid_message'] = __('Sorry, but we didn\'t get that. Please check that you\'ve filled out the whole form and try again.', 'contact-dialog');

						$options['contact_form'] = NSContactDialogAdmin::get_contact_form();

						echo 'jQuery(function($){$(document).nsContactDialog(' . json_encode($options) . ');})';

						exit(0);

						break;

					case 'send-message' :

						header('Content-type: application/json');

						$result = self::send_email($options['contact_email_address'], $_GET['name'], $_GET['email'], $_GET['subject'], $_GET['message-body'], $_GET['recaptcha_challenge_field'], $_GET['recaptcha_response_field']);

						echo json_encode($result);

						exit(0);

						break;

					default :

						break;

				}
				
			}
			
		}
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function init () {
		
		if (! is_admin()) {
			
			wp_enqueue_style('jquery-ui-custom-style', self::URL . 'css/jquery.ui.custom.css');

			wp_enqueue_style('jquery-ns-contactdialog', self::URL . 'css/jquery.ns-contactdialog.css');
			
			wp_enqueue_script('jquery-ui-custom', self::URL . 'js/jquery-ui-1.8.7.custom.min.js', array('jquery'), false);

			wp_enqueue_script('jquery-validate', self::URL . 'js/jquery.validate.min.js', array('jquery'), false);

			wp_enqueue_script('jquery-nscontact-dialog', self::URL . 'js/jquery.ns-contactdialog.js', array('jquery-validate', 'jquery-ui-custom'), false);

			wp_enqueue_script('jquery-nscontact-jsonp', get_bloginfo('wpurl') . '?nscontactdialog=' . wp_create_nonce(basename(__FILE__)) . '&nscdaction=add-dialogs', array('jquery-nscontact-dialog'), true);
			
		}
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function shortcode () {}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private static function send_email ($target_email, $name = null, $email_address = null, $subject = null, $message = null, $recaptcha_challenge_field = null, $recaptcha_response_field = null) {
		
		$response['response'] = 'error'; //Assume the worst.
		
		if (empty($name) || empty($email_address) || empty($subject) || empty($message) || empty($recaptcha_challenge_field) || empty($recaptcha_response_field)) {
			
			$response['message'] = __('You missed a required field. Please fill out the form completely and try again.', 'contact-dialog');
			
			$response['type'] = 'general';
			
			return $response;
			
		} elseif (!is_email($email_address)) {
			
			$response['message'] = __('That doesn\'t look like a valid email address. Perhaps you made a typo?', 'contact-dialog');
			
			$response['type'] = 'email';
			
			return $response;
			
		} else {
			
			$recaptcha_check = self::validate_recaptcha($recaptcha_challenge_field, $recaptcha_response_field);
			
			if (false == $recaptcha_check) {
				
				$response['message'] = __('Sorry, but you missed the Recaptcha. Please try again.', 'contact-dialog');
				
				$response['type'] = 'recaptcha';
				
				return $response;
				
			} else {
				
				$headers = "From: $name <{$email_address}>" . "\r\n\\";
				
				$message = stripslashes($message);
					
				$result = array(wp_mail($target_email, stripslashes($subject), $message, $headers));
				
				if (true == $result) {
					
					$response['response'] = 'success';
					
				}
				
				$response['message'] = __('Thank you for getting in touch with us through our online form.', 'contact-dialog');
				
				return $response;
				
			}
			
		}
		
	}
	
	/**
	 * @since 0.2
	 * @author jameslafferty
	 */
	private static function load_textdomain () {
		
		load_plugin_textdomain(self::$textdomain, null, str_replace('lib', 'languages', dirname(plugin_basename(__FILE__))));
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private static function validate_recaptcha ($recaptcha_challenge_field, $recaptcha_response_field) {
		
		$user_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		
		$options = NSContactDialogAdmin::get_options();
		
		$ch =  curl_init('http://www.google.com/recaptcha/api/verify');
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			
			'privatekey' => $options['recaptcha_api_private_key'],
			'remoteip' => $user_ip,
			'challenge' => $recaptcha_challenge_field,
			'response' => $recaptcha_response_field
			
		));
	
		$response = explode("\n", curl_exec($ch));
		
		$valid = ('false' == $response[0]) ? false : true;
			
		return $valid;
		
	}
	
}

?>