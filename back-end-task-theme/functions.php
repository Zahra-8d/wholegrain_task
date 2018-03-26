<?php /*
wholegrain task theme functions
*/

//add custom post types 

function register_custom_post_types() {

	//recipes

	$recipes_args = array(
		'public' => true,
		'label' => 'Recipes',
		'supports' => array('title', 'editor'),
		'taxonomies' => array('category'),
		'register_meta_box_cb' => 'add_ingredients_metabox'
	);
	register_post_type('recipes', $recipes_args);
}

add_action('init', 'register_custom_post_types');

//add ingredients meta box

function add_ingredients_metabox() {
	add_meta_box(
	'ingredients',
	'Ingredients',
	'show_ingredients_metabox',
	'recipes',
	'normal',
	'high'
	);
}

add_action('add_meta_boxes', 'add_ingredients_metabox');

function show_ingredients_metabox() {
	global $post;
	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'event_fields' );
	// Get the location data if it's already been entered
	$ingredients = get_post_meta( $post->ID, 'ingredients', true );
	// Output the field
	echo '<input type="text" name="ingredients" value="' . esc_textarea( $ingredients )  . '" class="widefat">';
}

//save metabox data

function wpt_save_events_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['ingredients'] ) || ! wp_verify_nonce( $_POST['event_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $events_meta.
	$events_meta['ingredients'] = esc_textarea( $_POST['ingredients'] );
	// Cycle through the $events_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $events_meta as $key => $value ) :
		// Don't store custom data twice
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
add_action( 'save_post', 'wpt_save_events_meta', 1, 2 );
