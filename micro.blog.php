<?php
defined( 'ABSPATH' ) or die( 'You know better.' );
/**
 * Plugin Name:       Micro.blog
 * Description:       Generates a custom feed “microblog” for posts. Adds a checkbox to the Publish meta box in order to add a post to the custom feed.
 * Version:           0.1.0
 * Author:            Caspar Hübinger
 * Author URI:        https://glueckpress.com/
 * Plugin URI:        https://github.com/glueckpress/micro.blog
 * GitHub Plugin URI: https://github.com/glueckpress/micro.blog
 * License:           GNU General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Based upon [DEWP Planet Feed](https://github.com/deworg/microblog-feed).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

/**
 * Set marker for activation.
 * @since 0.1.0
 */
 register_activation_hook(
 	__FILE__,
 	array( 'MicroBlog', 'activation' )
 );

/**
 * Flush rewrite rules.
 * @since 0.1.0
 */
register_deactivation_hook(
	__FILE__,
	array( 'MicroBlog', 'deactivation' )
);

class MicroBlog {

	/**
	 * Allowed post types.
	 * @since 0.1.0
	 * @var array  Default: post
	 */
	public static $post_types;

	/**
	 * Required capability.
	 * @since 0.1.0
	 * @var string  Default: publish_posts
	 */
	public static $capability;

	/**
	 * Plugin activation state.
	 * @var bool|string FALSE|activating|activated
	 */
	public static $maybe_activation;

	/**
	 * Populate default values and initiate.
	 */
	public function __construct() {


		// @todo L10n!!

		/**
		* Filterable post types.
		* @since 0.1.0
		*/
		self::$post_types = apply_filters(
			'microblog_feed__post_types',
			array( 'post' )
		);

		/**
		* Filterable capability to enable checkbox.
		* @since 0.1.0
		*/
		self::$capability = apply_filters(
			'microblog_feed__capability',
			'publish_posts'
		);

		self::$maybe_activation = get_option( 'microblog_feed__activated', false );

		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Set marker for activation.
	 * @since  0.1
	 * @return void
	 */
	public static function activation() {
		update_option( 'microblog_feed__activated', 'activating' );
	}

	/**
	 * Flush rewrite rules and delete option on deactivation.
	 * @since  0.1
	 * @return void
	 */
	public static function deactivation() {
		flush_rewrite_rules();
		delete_option( 'microblog_feed__activated', 'deactivated' );
	}

	/**
	 * Initialize plugin.
	 * @since  0.1
	 * @return void
	 */
	public static function init() {

		// Add custom feed.
		add_feed( 'microblog', array( __CLASS__, 'feed_template' ) );

		if ( 'activating' === self::$maybe_activation ) {

			// Not recommended, but it’s only once during activation.
			flush_rewrite_rules();
			update_option( 'microblog_feed__activated', 'activated' );
		}

		// Publish post actions.
		add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'add_checkbox' ), 9 );
		add_action( 'save_post', array( __CLASS__, 'save_checkbox' ) );

		// Enqueue admin scripts and styles.
		add_action(
			'admin_enqueue_scripts',
			array( __CLASS__, 'admin_enqueue_scripts' )
		);

		// Get feed content.
		add_action( 'pre_get_posts', array( __CLASS__, 'feed_content' ) );
	}

	/**
	 * Load feed template.
	 * @since  0.1
	 * @return void
	 */
	public static function feed_template() {

		load_template( trailingslashit( dirname( __FILE__ ) ) . 'templates/microblog-feed-rss2.php' );
	}

	/**
	 * Add checkbox to Publish Post meta box.
	 * @since  0.1
	 * @return void
	 */
	public static function add_checkbox() {

		global $post;

		// Bail if post type is not allowed.
		if ( ! in_array( $post->post_type, self::$post_types ) ) {
			return false;
		}

		// Check user capability. Not bailing, though, on purpose.
		$maybe_enabled = current_user_can( self::$capability );

		// This actually defines whether post will be listed in our feed.
		$value = get_post_meta( $post->ID, '_microblog_post_to_feed', true );

		ob_start();
		include trailingslashit( dirname( __FILE__ ) ) . 'inc/pub-section.php';
		$pub_section = ob_get_clean();

		echo $pub_section;
	}

	/**
	 * Register and enqueue admin scripts and styles.
	 * @since  0.1
	 * @param  string $hook Current admin page
	 * @return return       Current admin page
	 */
	public static function admin_enqueue_scripts( $hook ) {

		if ( 'post.php' !== $hook && 'post-new.php' !== $hook )
			return;

		$file_data  = get_file_data( __FILE__, array( 'v' => 'Version' ) );
		$assets_url = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/';

		// CSS
		wp_register_style(
			'microblog-post', $assets_url . 'css/post.css',
			array( 'wp-admin' ),
			$file_data['v']
		);
		wp_enqueue_style( 'microblog-post' );

		return $hook;
	}

	/**
	 * Save option value to post meta.
	 * @since  0.1
	 * @param  integer $post_id ID of current post
	 * @return integer          ID of current post
	 */
	public static function save_checkbox( $post_id ) {

		if ( empty( $post_id ) || empty( $_POST['post_ID'] ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( absint( $_POST['post_ID'] ) !== $post_id ) {
			return $post_id;
		}
		if ( ! in_array( $_POST['post_type'], self::$post_types ) ) {
			return $post_id;
		}
		if ( ! current_user_can( self::$capability ) ) {
			return $post_id;
		}
		if ( empty( $_POST['microblog__post-to-feed'] ) ) {
			delete_post_meta( $post_id, '_microblog_post_to_feed' );
		} else {
			add_post_meta( $post_id, '_microblog_post_to_feed', 1, true );
		}

		return $post_id;
	}

	/**
	 * Set feed content.
	 * @since  0.1
	 * @param  object $query WP_Query object
	 * @return object        Altered WP_Query object
	 */
	public static function feed_content( $query ) {

		// Bail if $posts_query is not an object or of incorrect class.
		if ( ! is_object( $query ) || ( 'WP_Query' !== get_class( $query ) ) ) {
			return;
		}
		// Bail if filters are suppressed on this query.
		if ( $query->get( 'suppress_filters' ) ) {
			return;
		}
		// Bail if this is not our microblog feed.
		if ( ! $query->is_feed( 'microblog' ) ) {
			return;
		}
		$query->set( 'post_type', self::$post_types );
		$query->set( 'meta_key', '_microblog_post_to_feed' );

		return $query;
	}
}

// Hello!
new MicroBlog();
