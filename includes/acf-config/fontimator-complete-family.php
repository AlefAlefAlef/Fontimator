<?php

acf_add_local_field_group(array(
	'key' => 'fontimator-complete-family',
	'title' => 'Complete Family Discounts',
	'fields' => array(
		array(
			'key' => 'complete_family_enabled',
			'label' => 'Enable Complete Family Banner',
			'name' => 'complete_family_enabled',
			'type' => 'true_false',
			'instructions' => 'Show the complete family banner in the downloads page of the my account section, where the user purchased only some of the font\'s weights.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => 'Enabled',
			'ui_off_text' => 'Disabled',
		),
		array(
			'key' => 'complete_family_discount_percent',
			'label' => 'Discount for missing weights',
			'name' => 'complete_family_discount_percent',
			'type' => 'number',
			'instructions' => 'The percentage to be taken off the missing weights',
			'required' => 1,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'complete_family_enabled',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 60,
			'placeholder' => '',
			'prepend' => '',
			'append' => '%',
			'min' => 0,
			'max' => 100,
			'step' => '',
		),
		array(
			'key' => 'complete_family_limit_fonts',
			'label' => 'Limit to these fonts:',
			'name' => 'complete_family_limit_fonts',
			'type' => 'post_object',
			'instructions' => 'Only show on the selected fonts. If empty, show on all of them.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'complete_family_enabled',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
				0 => 'product',
			),
			'taxonomy' => '',
			'allow_null' => 1,
			'multiple' => 1,
			'return_format' => 'id',
			'ui' => 1,
		),
		array(
			'key' => 'complete_family_limit_days_from_purchase',
			'label' => 'Limit to fonts purchased before:',
			'name' => 'complete_family_limit_days_from_purchase',
			'type' => 'number',
			'instructions' => 'If the font was bought more recently than the amount of days specified here, don\'t show the banner to prevent frauds.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'complete_family_enabled',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 30,
			'placeholder' => '',
			'prepend' => '',
			'append' => 'Days ago',
			'min' => 0,
			'max' => '',
			'step' => 1,
		),
		array(
			'key' => 'generate_eligible_emails_list',
			'label' => 'Get all users eligible for the discount',
			'name' => 'generate_eligible_emails_list',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'complete_family_enabled',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '<a class="button-secondary" href="admin.php?page=fontimator-config&generate_complete_family_eligible_list=1">Generate a list of all eligible users</a><br /><small>The list will be saved in <code>/var/www/output/</code></small>',
			'new_lines' => '',
			'esc_html' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'fontimator-config',
			),
		),
	),
	'menu_order' => 10,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));