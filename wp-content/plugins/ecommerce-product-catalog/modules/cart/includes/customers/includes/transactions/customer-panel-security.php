<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Blocks digital customers to enter the default WordPress Admin
 *
 * Created by Norbert Dreszer.
 * Date: 11-Mar-15
 * Time: 13:22
 * Package: customer-panel-security.php
 */
class ic_customer_panel_security {

	function __construct() {
		add_action( 'after_setup_theme', array( __CLASS__, 'remove_admin_bar' ) );
		add_action( 'admin_init', array( __CLASS__, 'stop_access_profile' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_demo_menus' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect_admin' ) );
		add_filter( 'login_url', array( __CLASS__, 'login_url' ) );
		add_action( 'wp_login_failed', array( __CLASS__, 'login_fail' ) );
		add_action( 'authenticate', array( __CLASS__, 'check_password' ), 1, 3 );
		add_filter( 'customer_login_actions', array( __CLASS__, 'login_errors' ) );
		add_action( 'init', array( __CLASS__, 'set_session_ref' ), 1 );
	}

	/**
	 * Hide Admin bar for digital customers
	 */
	static function remove_admin_bar() {
		if ( is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			show_admin_bar( false );
		}
	}

	static function stop_access_profile() {
		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			wp_die( 'You are not permitted to change profile details.' );
		}
	}

	static function remove_demo_menus() {
		if ( is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			remove_menu_page( 'profile.php' );
			remove_menu_page( 'upload.php' );
			remove_submenu_page( 'users.php', 'profile.php' );
		}
	}

	static function redirect_admin() {
		if ( is_ic_digital_customer() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! current_user_can( 'administrator' ) ) {
			$url = ic_customer_panel_panel_url();
			if ( ! empty( $url ) ) {
				wp_redirect( $url );
				exit;
			}
		}
	}

	static function login_url( $url ) {
		$panel_url = ic_customer_panel_panel_url();
		if ( ! empty( $panel_url ) ) {
			return $panel_url;
		}

		return $url;
	}

	static function login_fail( $username ) {
		if ( empty( $_SESSION['referrer'] ) ) {
			return;
		}
		$referrer = $_SESSION['referrer'];
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) && ! current_user_can( 'administrator' ) ) {
			$url = ic_customer_panel_panel_url();
			if ( ! empty( $url ) ) {
				$url = add_query_arg( 'login', 'failed', $url );
				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Redirects to customer panel when username or password is empty
	 *
	 * @param type $login
	 * @param string $username
	 * @param string $password
	 */
	static function check_password( $login, $username, $password ) {
		if ( empty( $_SESSION['referrer'] ) ) {
			return;
		}
		$referrer = $_SESSION['referrer'];
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
			if ( $username == "" || $password == "" ) {
				$url = ic_customer_panel_panel_url();
				if ( ! empty( $url ) ) {
					$url = add_query_arg( 'login', 'empty', $url );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	/**
	 * Adds customer login error messages
	 *
	 * @param string $actions
	 *
	 * @return string
	 */
	static function login_errors( $actions ) {
		if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) {
			$redirect = ic_customer_panel_panel_url();
			$actions  .= implecode_warning( sprintf( __( 'The password you entered is incorrect, please try again or %sreset password%s.' ), '<a href="' . wp_lostpassword_url( $redirect ) . '">', '</a>' ), 0 );
		} else if ( isset( $_GET['login'] ) && $_GET['login'] == 'empty' ) {
			$actions .= implecode_warning( __( 'Please enter both username and password.' ), 0 );
		}

		return $actions;
	}

	/**
	 * Define session referrer
	 *
	 */
	static function set_session_ref() {
		if ( is_admin() && ( function_exists( 'is_ic_ajax' ) && ! is_ic_ajax() ) ) {
			return;
		}
		$ic_session = get_product_catalog_session();
		if ( isset( $ic_session['next_referrer'] ) ) {
			// Get existing referrer
			$ic_session['referrer'] = $ic_session['next_referrer'];
		} else if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			// Use given referrer
			$ic_session['referrer'] = $_SERVER['HTTP_REFERER'];
		} else {
			$ic_session['referrer'] = '';
		}

// Save current page as next page's referrer
		$ic_session['next_referrer'] = ic_current_page_url();
		set_product_catalog_session( $ic_session );
	}

}

$ic_customer_panel_security = new ic_customer_panel_security;
