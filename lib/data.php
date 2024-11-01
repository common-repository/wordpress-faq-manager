<?php
/**
 * WP FAQ Manager - Data Module
 *
 * Various queries, functions, etc.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Data {

	/**
	 * Get a random FAQ for the sidebar widget.
	 *
	 * @param  integer $count  The total number of FAQs to get.
	 *
	 * @return mixed           The array of post objects or false.
	 */
	public static function get_random_widget_faqs( $count = 1 ) {

		// Check for the transient first.
		if ( false === $items = get_transient( 'wpfaq_widget_fetch_random' )  ) {

			// Set my args.
			$args   = array(
				'post_type'     => 'question',
				'nopaging'      => true,
				'post_status'   => 'publish',
			);

			// Fetch the items.
			$items  = get_posts( $args );

			// Set an empty transient if we have none.
			if ( ! $items ) {

				// Set the transient time to an hour.
				set_transient( 'wpfaq_widget_fetch_random', '', HOUR_IN_SECONDS );

				// And return false.
				return false;
			}

			// Set the transient time to an hour.
			set_transient( 'wpfaq_widget_fetch_random', $items, DAY_IN_SECONDS );
		}

		// Shuffle the array data.
		shuffle( $items );

		// Return the requested number of items.
		return array_slice( $items, 0, absint( $count ), true );
	}

	/**
	 * Get a recent FAQ for the sidebar widget.
	 *
	 * @param  integer $count  The total number of FAQs to get.
	 *
	 * @return mixed           The array of post objects or false.
	 */
	public static function get_recent_widget_faqs( $count = 5 ) {

		// Check for the transient first.
		if ( false === $items = get_transient( 'wpfaq_widget_fetch_recent' )  ) {

			// Set my args.
			$args   = array(
				'post_type'       => 'question',
				'posts_per_page'  => absint( $count ),
				'post_status'     => 'publish',
			);

			// Fetch the items.
			$items  = get_posts( $args );

			// Set an empty transient if we have none.
			if ( ! $items ) {

				// Set the transient time to an hour.
				set_transient( 'wpfaq_widget_fetch_recent', '', HOUR_IN_SECONDS );

				// And return false.
				return false;
			}

			// Set the transient time to an hour.
			set_transient( 'wpfaq_widget_fetch_recent', $items, WEEK_IN_SECONDS );
		}

		// Return the items.
		return $items;
	}

	/**
	 * Get the FAQ list for the main shortcode.
	 *
	 * @param  integer $id      Optional single FAQ post ID.
	 * @param  integer $count   The total number of FAQs to get.
	 * @param  array   $topics  The optional FAQ topic.
	 * @param  array   $tags    The optional FAQ tag.
	 * @param  integer $paged   Pagination setup.
	 *
	 * @return array           The array of post objects or false.
	 */
	public static function get_main_shortcode_faqs( $id = 0, $count = 10, $topics = array(), $tags = array(), $paged = 1 ) {

		// If we have a single ID, do that lookup first.
		if ( ! empty( $id ) ) {

			// Confirm the post type and return false if it isn't an FAQ.
			if ( 'question' !== get_post_type( $id ) ) {
				return false;
			}

			// Get the data.
			$item   = get_post( $id );

			// Bail if the data isn't an object, or isn't published.
			if ( ! is_object( $item ) || empty( $item->post_status ) || 'publish' !== esc_attr( $item->post_status ) ) {
				return false;
			}

			// Return the data set as an array.
			return array( $item );
		}

		// Set my base primary args.
		$base   = array(
			'post_type'       => 'question',
			'post_status'     => 'publish',
			'orderby'         => 'menu_order',
			'order'           => 'ASC',
		);

		// If we are using the "all" feature, set the args with that.
		// Otherwise, use the paged and posts_per_page setup.
		if ( 'all' === $count || $count < 0 ) {
			$args   = wp_parse_args( array( 'nopaging' => true, 'paged' => $paged ), $base );
		} else {
			$args   = wp_parse_args( array( 'posts_per_page' => absint( $count ), 'paged' => $paged ), $base );
		}

		// Set a tax query array.
		$tq = array();

		// Check for topics passed.
		if ( ! empty( $topics ) ) {

			// Set the query portion.
			$tq[]   = array(
				'taxonomy' => 'faq-topic',
				'field'    => 'slug',
				'terms'    => $topics,
			);
		}

		// Check for tags passed.
		if ( ! empty( $tags ) ) {

			// Set the query portion.
			$tq[]   = array(
				'taxonomy' => 'faq-tags',
				'field'    => 'slug',
				'terms'    => $tags,
			);
		}

		// Add the args if we have them.
		if ( ! empty( $tq ) ) {

			// Set the merge relation.
			$taxq   = array_merge( array( 'relation' => 'OR' ), $tq );

			// And do the actual parsing.
			$args   = wp_parse_args( array( 'tax_query' => array( $taxq ) ), $args );
		}

		// Fetch the items.
		$items  = get_posts( $args );

		// Return the items if we have them, or false.
		return ! empty( $items ) ? $items : false;
	}

	/**
	 * Get the FAQ list for the main shortcode.
	 *
	 * @param  integer $id      Optional single FAQ post ID.
	 * @param  array   $topics  The optional FAQ topic.
	 * @param  array   $tags    The optional FAQ tag.
	 *
	 * @return array           The array of post objects or false.
	 */
	public static function get_combo_shortcode_faqs( $id = 0, $topics = array(), $tags = array() ) {

		// If we have a single ID, do that lookup first.
		if ( ! empty( $id ) ) {

			// Confirm the post type and return false if it isn't an FAQ.
			if ( 'question' !== get_post_type( $id ) ) {
				return false;
			}

			// Get the data.
			$item   = get_post( $id );

			// Bail if the data isn't an object, or isn't published.
			if ( ! is_object( $item ) || empty( $item->post_status ) || 'publish' !== esc_attr( $item->post_status ) ) {
				return false;
			}

			// Return the data set as an array.
			return array( $item );
		}

		// Set my primary args.
		$args   = array(
			'post_type'     => 'question',
			'nopaging'      => true,
			'post_status'   => 'publish',
			'orderby'       => 'menu_order',
			'order'         => 'ASC',
		);

		// Set a tax query array.
		$tq = array();

		// Check for topics passed.
		if ( ! empty( $topics ) ) {

			// Set the query portion.
			$tq[]   = array(
				'taxonomy' => 'faq-topic',
				'field'    => 'slug',
				'terms'    => $topics,
			);
		}

		// Check for tags passed.
		if ( ! empty( $tags ) ) {

			// Set the query portion.
			$tq[]   = array(
				'taxonomy' => 'faq-tags',
				'field'    => 'slug',
				'terms'    => $tags,
			);
		}

		// Add the args if we have them.
		if ( ! empty( $tq ) ) {

			// Set the merge relation.
			$taxq   = array_merge( array( 'relation' => 'OR' ), $tq );

			// And do the actual parsing.
			$args   = wp_parse_args( array( 'tax_query' => array( $taxq ) ), $args );
		}

		// Fetch the items.
		$items  = get_posts( $args );

		// Return the items if we have them, or false.
		return ! empty( $items ) ? $items : false;
	}

	/**
	 * Get the FAQ list for the main shortcode.
	 *
	 * @param  string $term  Which term we are going to pull a list from.
	 *
	 * @return array           The array of term objects or false.
	 */
	public static function get_tax_shortcode_terms( $term = '' ) {

		// Filter the available args.
		$args    = apply_filters( 'wpfaq_taxlist_shortcode_args', array( 'hide_empty' => false ), $term );

		// Fetch my terms.
		$terms   = get_terms( array( $term ), $args );

		// Return the terms if we have them, or false.
		return empty( $terms ) || is_wp_error( $terms ) ? false : $terms;
	}

	/**
	 * Get the total number of FAQs I have available to me.
	 *
	 * @param  integer $divide  Optional to divide the count by.
	 *
	 * @return int     $count   The number of FAQs
	 */
	public static function get_total_faq_count( $divide = 0 ) {

		// Check for the transient first.
		if ( false === $count = get_transient( 'wpfaq_total_faq_count' )  ) {

			// Call the global database.
			global $wpdb;

			// Set up our query.
			$query  = $wpdb->prepare("
				SELECT  ID
				FROM    $wpdb->posts
				WHERE   post_type = '%s'
				AND     post_status = '%s'
			", esc_sql( 'question' ), esc_sql( 'publish' ) );

			// Fetch the column.
			$data  = $wpdb->get_col( $query );

			// Set an empty transient if we have none.
			if ( empty( $data ) ) {

				// Set the transient time to an hour.
				set_transient( 'wpfaq_total_faq_count', 0, HOUR_IN_SECONDS );

				// And return false.
				return false;
			}

			// Do our count.
			$count = count( $data );

			// Set the transient time to an hour.
			set_transient( 'wpfaq_total_faq_count', $count, DAY_IN_SECONDS );
		}

		// If we aren't calculating anything, just return the value.
		if ( empty( $divide ) ) {
			return $count;
		}

		// If we are doing math, math it up.
		$calcd  = $count / absint( $divide );

		// Return the number, calculated up.
		return ceil( $calcd );
	}

	/**
	 * Get all the FAQs for the admin page.
	 *
	 * @return mixed           The array of post objects or false.
	 */
	public static function get_admin_faqs() {

		// Check for the transient first.
		if ( false === $items = get_transient( 'wpfaq_admin_fetch_faqs' )  ) {

			// Set my args.
			$args   = array(
				'post_type'     => 'question',
				'nopaging'      => true,
				'post_status'   => 'publish',
				'order'         => 'ASC',
				'orderby'       => 'menu_order',
			);

			// Fetch the items.
			$items  = get_posts( $args );

			// Set an empty transient if we have none.
			if ( ! $items ) {

				// Set the transient time to an hour.
				set_transient( 'wpfaq_admin_fetch_faqs', '', HOUR_IN_SECONDS );

				// And return false.
				return false;
			}

			// Set the transient time to an hour.
			set_transient( 'wpfaq_admin_fetch_faqs', $items, WEEK_IN_SECONDS );
		}

		// Return the items.
		return $items;
	}

	// End our class.
}

// Call our class.
new WPFAQ_Manager_Data();


