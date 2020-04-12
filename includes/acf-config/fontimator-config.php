<?php
acf_add_options_page(
	array(

		/* (string) The title displayed on the options page. Required. */
		'page_title' => 'Fontimator Configuration',

		/* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
		'menu_title' => __( 'The Fontimator', 'fontimator'),

		/* (string) The URL slug used to uniquely identify this options page.
		Defaults to a url friendly version of menu_title */
		'menu_slug' => 'fontimator-config',

		/* (int|string) The position in the menu order this menu should appear.
		WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
		Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
		Defaults to bottom of utility menu items */
		'position' => false,

		/* (string) The slug of another WP admin page. if set, this will become a child page. */
		// 'parent_slug' => 'options-general',

		/* (string) The icon class for this menu. Defaults to default WordPress gear.
		Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
		'icon_url' => 'dashicons-media-archive',

		/* (string) The update button text. Added in v5.3.7. */
		'update_button'     => __( 'Save Fontimator Configuration', 'fontimator' ),

		/* (string) The message shown above the form on submit. Added in v5.6.0. */
		'updated_message'   => __( 'Fontimator Configuration Saved', 'fontimator' ),

		/* Don't redirect to subpage */
		'redirect' => false,
	)
);
