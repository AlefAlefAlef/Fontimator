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
			'key' => 'field_5e8b00403073e',
			'label' => 'Website Group',
			'name' => 'ftm_subscribe_groups',
			'type' => 'select',
			'instructions' => 'If selected, subscribe all users from this website to this group',
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
			'multiple' => 1,
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
			'label' => 'Academic Group',
			'name' => 'ftm_academic_group',
			'type' => 'select',
			'instructions' => 'The group category that contains academic license students. It should only contain sub-interests of <b>graduation years</b>.<b>Any non-numeric sub-category will be treated as cancelled.',
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
			'key' => 'field_5e8aff7e3076f',
			'label' => 'Preferences Group',
			'name' => 'ftm_preferences_group',
			'type' => 'select',
			'instructions' => 'The group category that users can choose their association to in their account dashbord.',
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
