<?php
/**
 * Plugin Name: CGC Cookie Institute
 * Plugin URI: http://cgcookie.com
 * Description: Lesson Quizzes, integrated with Gravity Forms and Gravity Forms Quiz plugins.
 * Author: Brian DiChiara
 * Author URI: http://briandichiara.com
 * Version: 0.0.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'CGCCI_VERSION',		'0.0.1' );
define( 'CGCCI_PATH',			plugin_dir_path( __FILE__ ) );
define( 'CGCCI_DIR',			plugin_dir_url( __FILE__ ) );
define( 'CGCCI_PREFIX',			'_cgcci_' );
define( 'CGCCI_QUIZ_CPT',		'cgc_quiz' );

require_once( CGCCI_PATH . 'includes/functions.php' );
require_once( CGCCI_PATH . 'classes/cgc-quiz.class.php' );
require_once( CGCCI_PATH . 'classes/cgc-user-results.class.php' );
require_once( CGCCI_PATH . 'classes/cgc-ci-admin.class.php' );
require_once( CGCCI_PATH . 'classes/cgc-cookie-institute.class.php' );

global $cgcci_admin;
$cgcci_admin = new CGC_CI_Admin();
$cgcci_admin->initialize();

global $cgcci_plugin;
$cgcci_plugin = new CGC_Cookie_Institute();
$cgcci_plugin->initialize();