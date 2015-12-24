<?php
/**
 * Plugin Name: Custom Post Type Archives in Nav Menus
 * Plugin URI: https://aarondcampbell.com/wordpress-plugin/custom-post-type-archives-in-nav-menus/
 * Description: Adds an archive checkbox to the nav menu meta box for Custom Post Types that support archives
 * Author: Aaron D. Campbell
 * Author URI: https://aarondcampbell.com/
 * Version: 0.1.0
 */

class cptArchiveNavMenu {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.4', '>=' ) ) {

			 if ( current_user_can( 'activate_plugins' ) ) {
				  add_action( 'admin_init', array( $this, 'deactivate' ) );
				  add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			 }

		} else {
			 add_action( 'admin_head-nav-menus.php', array( $this, 'add_filters' ) );
		}
	}

	public function add_filters() {
		$post_type_args = array(
			'show_in_nav_menus' => true
		);

		$post_types = get_post_types( $post_type_args, 'object' );

		foreach ( $post_types as $post_type ) {
			if ( $post_type->has_archive ) {
				add_filter( 'nav_menu_items_' . $post_type->name, array( $this, 'add_archive_checkbox' ), null, 3 );
			}
		}
	}

	public function add_archive_checkbox( $posts, $args, $post_type ) {
		global $_nav_menu_placeholder, $wp_rewrite;
		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;

		//dump( $post_type, '$post_type', 'htmlcomment' );

		$archive_slug = $post_type['args']->has_archive === true ? $post_type['args']->rewrite['slug'] : $post_type['args']->has_archive;
		if ( $post_type['args']->rewrite['with_front'] )
			$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
		else
			$archive_slug = $wp_rewrite->root . $archive_slug;

		array_unshift( $posts, (object) array(
			'ID' => 0,
			'object_id' => $_nav_menu_placeholder,
			'post_content' => '',
			'post_excerpt' => '',
			'post_title' => $post_type['args']->labels->all_items,
			'post_type' => 'nav_menu_item',
			'type' => 'custom',
			'url' => site_url( $archive_slug ),
		) );

		return $posts;
	}

	public function deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	public function admin_notice() {
		 echo '<div class="updated"><p><strong>Custom Post Type Archives in Nav Menus</strong> was added to WordPress core in 4.4; the plug-in has been <strong>deactivated</strong>.</p></div>';
		 if ( isset( $_GET['activate'] ) ) {
			  unset( $_GET['activate'] );
		 }
	}

}

$cptArchiveNavMenu = new cptArchiveNavMenu();
