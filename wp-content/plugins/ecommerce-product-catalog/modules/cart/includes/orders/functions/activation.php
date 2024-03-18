<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin activation functions
 *
 * Here all plugin ativation functions are defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-order/functions
 * @author        Norbert Dreszer
 */
class ic_orders_caps {

	function __construct() {
		add_filter( 'admin_init', array( $this, 'add_caps' ), 10, 4 );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	function add_caps() {
		if ( current_user_can( 'administrator' ) && ! current_user_can( 'publish_digital_orders' ) ) {
			$role = get_role( 'administrator' );
			$role->add_cap( 'publish_digital_orders' );
			$role->add_cap( 'edit_digital_orders' );
			$role->add_cap( 'edit_others_digital_orders' );
			$role->add_cap( 'edit_private_digital_orders' );
			$role->add_cap( 'delete_digital_orders' );
			$role->add_cap( 'delete_others_digital_orders' );
			$role->add_cap( 'read_private_digital_orders' );
			$role->add_cap( 'delete_private_digital_orders' );
			$role->add_cap( 'delete_published_digital_orders' );
			$role->add_cap( 'edit_published_digital_orders' );
		}
	}

	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( empty( $args[0] ) ) {
			return $caps;
		}
		if ( 'edit_digital_order' == $cap || 'delete_digital_order' == $cap || 'read_digital_order' == $cap ) {
			$post      = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );
			$caps      = array();
		}
		if ( empty( $post ) || empty( $post_type ) ) {
			return $caps;
		}
		if ( 'edit_digital_order' == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->edit_posts;
			} else {
				$caps[] = $post_type->cap->edit_others_posts;
			}
		} elseif ( 'delete_digital_order' == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->delete_posts;
			} else {
				$caps[] = $post_type->cap->delete_others_posts;
			}
		} elseif ( 'read_digital_order' == $cap ) {
			if ( 'private' != $post->post_status ) {
				$caps[] = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$caps[] = 'read';
			} else {
				$caps[] = $post_type->cap->read_private_posts;
			}
		}

		return $caps;
	}

}

$ic_orders_caps = new ic_orders_caps;
