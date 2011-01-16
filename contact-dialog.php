<?php
/*
Plugin Name: Contact Dialog
Plugin URI: https://github.com/kalchas
Description: An AJAX-driven contact form in a modal dialog box.
Author: James Lafferty
Version: 0.2.1
Author URI: https://github.com/kalchas
License: GPL2
*/

/*  Copyright 2010  James Lafferty  (email : james@nearlysensical.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Set up the autoloader.
 */

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/lib/'));

spl_autoload_extensions('.class.php');

if (! function_exists('buffered_autoloader')) {
	
	function buffered_autoloader ($c) {

		try {
		
			spl_autoload($c);
			
		} catch (Exception $e) {
			
			$message = $e->getMessage();
			
			return $message;
			
		}
		

	}
	
}spl_autoload_register('buffered_autoloader');

/**
 * Define some constants that are useful.
 */

define('NSCD_DIR_PATH', plugin_dir_path(__FILE__));
define('NSCD_DIR_URL', plugin_dir_url(__FILE__));

/**
 * Get the plugin object. All the bookkeeping and other setup stuff happens here.
 */

$nscontactdialog = NSContactDialog::get_instance();

?>