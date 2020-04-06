<?php
acf_add_local_field_group(array(
	'key' => 'group_5e8afe690b6bd',
	'title' => 'MailChimp Settings',
	'fields' => array(
		array(
			'key' => 'field_5e8afe813076a',
			'label' => 'Main List',
			'name' => 'ftm_main_list',
			'type' => 'select',
			'instructions' => 'The main subscribers list. Gender, free fonts, subscription notices and more will be connected to this list.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
			),
			'default_value' => array(
			),
			'allow_null' => 1,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5e8b00403076d',
			'label' => 'Gender Merge Field',
			'name' => 'ftm_gender_merge_field',
			'type' => 'select',
			'instructions' => 'The merge field to read/write gender from, on the main list',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5e8afe813076a',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
			),
			'default_value' => array(
			),
			'allow_null' => 1,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5e8affff3076c',
			'label' => 'Subscribed Merge Field',
			'name' => 'ftm_subscribed_merge_field',
			'type' => 'select',
			'instructions' => 'The merge field to sync subscribed status to, on the main list',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5e8afe813076a',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
			),
			'default_value' => array(
			),
			'allow_null' => 1,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5e8aff7e3076b',
			'label' => 'Academic List',
			'name' => 'ftm_academic_list',
			'type' => 'select',
			'instructions' => 'The users\' academic licenses will be read from this list.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
			),
			'default_value' => array(
			),
			'allow_null' => 1,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
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
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));
