<?php
acf_add_options_sub_page(array(
	'page_title'  => __('Free Downloads', 'fontimator'),
	'menu_title'  => _x('Free Fonts', 'Fontimator submenu item in admin', 'fontimator'),
	'parent_slug' => 'fontimator-config',
	'menu_slug' => 'fontimator-free-downloads',
));

acf_add_local_field_group(
	array(
		'key' => 'fontimator-free-downloads',
		'title' => 'Free Downloads',
		'fields' => array(
			array(
				'key' => 'field_5c45bb6badaba',
				'label' => 'Downloads',
				'name' => 'ftm_free_downloads',
				'type' => 'repeater',
				'instructions' => '<span dir="ltr">Example: [fontimator-free-download download="font_heshbon"]</span>',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => 'field_5c45bc04adabc',
				'min' => 0,
				'max' => 0,
				'layout' => 'block',
				'button_label' => '',
				'sub_fields' => array(
					array(
						'key' => 'field_5c45bbc9adabb',
						'label' => 'Download Unique ID',
						'name' => 'download_id',
						'type' => 'text',
						'instructions' => 'Only lowercase letters & underscores allowed.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => 'font_varela_round',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5c45bc04adabc',
						'label' => 'Download Name',
						'name' => 'download_name',
						'type' => 'text',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5c45bc18adabd',
						'label' => 'Download File URL',
						'name' => 'download_url',
						'type' => 'text',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => FTM_FONTS_URL,
						'append' => '',
						'maxlength' => '',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'fontimator-free-downloads',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	)
);
