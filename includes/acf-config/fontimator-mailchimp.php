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
			'key' => 'ftm_subscription_tag_obj2',
			'label' => 'Fonts Subscription Tag',
			'name' => 'ftm_subscription_tag_obj',
			'type' => 'select',
			'instructions' => 'The tag to sync subscribed status to, on the main list',
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
			'return_format' => 'array',
			'ajax' => 0,
			'placeholder' => '',
    ),
    array(
			'key' => 'sync_retroactively_all_subscriptions',
			'label' => 'Retroactive Sync',
			'name' => 'sync_retroactively_all_subscriptions',
			'type' => 'message',
			'instructions' => 'Will remove the tag from all users without the subscription and add it to the ones with it.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_subscription_tag_obj',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '<a class="button-secondary" onclick="return confirm(\'Are you sure? This will override the selected tag\')" href="admin.php?page=fontimator-config&sync_retroactively_all_subscriptions=1">Sync all subscriptions to the selected list</a><br /><small>Log file will be saved in <code>/var/www/output/</code></small>',
			'new_lines' => '',
			'esc_html' => 0,
		),
		array(
			'key' => 'field_5e8aff7e2076b',
			'label' => 'Academic Group',
			'name' => 'ftm_academic_group',
			'type' => 'select',
			'instructions' => 'The group category that contains academic license students. It should only contain sub-interests of <b>graduation years</b>.<b>Any non-numeric sub-category will be treated as cancelled.</b>',
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
			'key' => 'field_5e8ce5531cf36',
			'label' => 'Interests Groups',
			'name' => 'ftm_interests_groups',
			'type' => 'repeater',
			'instructions' => 'Each row represents a group the user can subscribe or unsubscribe to in their dashboard.',
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
			'collapsed' => 'field_5e8ce58f1cf37',
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Another group',
			'sub_fields' => array(
				array(
					'key' => 'field_5e8ce58f1cf37',
					'label' => 'Group',
					'name' => 'ftm_interest_group',
					'type' => 'select',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '40',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
					),
					'default_value' => array(
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5e8ce5d11cf38',
					'label' => 'Label',
					'name' => 'ftm_interest_label',
					'type' => 'text',
					'instructions' => 'The text to be shown in the myaccount page next to the checkbox',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '60',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
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
