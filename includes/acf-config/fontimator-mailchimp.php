<?php
acf_add_options_sub_page(array(
	'page_title'  => __('MailChimp Settings', 'fontimator'),
	'menu_title'  => _x('MailChimp', 'Fontimator submenu item in admin', 'fontimator'),
	'parent_slug' => 'fontimator-config',
	'menu_slug' => 'fontimator-mailchimp',
));

acf_add_local_field_group(array(
	'key' => 'group_5e8afe690b6bd',
	'title' => 'MailChimp Settings',
	'fields' => array(
		array(
			'key' => 'ftm_main_list',
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
			'key' => 'ftm_gender_merge_field',
			'label' => 'Gender Merge Field',
			'name' => 'ftm_gender_merge_field',
			'type' => 'select',
			'instructions' => 'The merge field to read/write gender from, on the main list',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
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
			'key' => 'ftm_subscribe_groups',
			'label' => 'Website Group',
			'name' => 'ftm_subscribe_groups',
			'type' => 'select',
			'instructions' => 'If selected, subscribe all users from this website to this group',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
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
			'key' => 'ftm_subscription_sync_group',
			'label' => 'Fonts Subscription Group',
			'name' => 'ftm_subscription_sync_group',
			'type' => 'select',
			'instructions' => 'The group to sync memberships to, on the main list',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
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
			'key' => 'sync_retroactively_all_subscriptions',
			'label' => 'Retroactive Sync',
			'name' => 'sync_retroactively_all_subscriptions',
			'type' => 'message',
			'instructions' => 'Will remove the tag from all users without the subscription and add it to the ones with it.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_subscription_sync_group',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '<a class="button-secondary" onclick="return confirm(\'Are you sure? This will override the selected group!\')" href="admin.php?page=fontimator-config&sync_retroactively_all_subscriptions=1">Sync all subscriptions to the selected list</a><br /><small>Log file will be saved in <code>/var/www/output/</code></small>',
			'new_lines' => '',
			'esc_html' => 0,
		),
		array(
			'key' => 'ftm_academic_group',
			'label' => 'Academic Group',
			'name' => 'ftm_academic_group',
			'type' => 'select',
			'instructions' => 'The group category that contains academic license students. It should only contain sub-interests of <b>graduation years</b>.<b>Any non-numeric sub-category will be treated as cancelled.</b>',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
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
			'key' => 'ftm_freefonts_group',
			'label' => 'Free Fonts Group',
			'name' => 'ftm_freefonts_group',
			'type' => 'select',
			'instructions' => 'The group category that users get subscribed to once they download a free font.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
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
			'key' => 'ftm_interest_groups',
			'label' => 'Interests Groups',
			'name' => 'ftm_interest_groups',
			'type' => 'repeater',
			'instructions' => 'Each row represents a group the user can subscribe or unsubscribe to in their dashboard.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'ftm_main_list',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => 'ftm_interest_group',
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Another group',
			'sub_fields' => array(
				array(
					'key' => 'ftm_interest_group',
					'label' => 'Group',
					'name' => 'ftm_interest_group',
					'type' => 'select',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '20',
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
					'key' => 'ftm_interest_icon',
					'label' => 'Icon',
					'name' => 'ftm_interest_icon',
					'type' => 'text',
					'instructions' => 'Iconimonim Icon',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '10',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => 1,
				),
				array(
					'key' => 'ftm_interest_label',
					'label' => 'Label',
					'name' => 'ftm_interest_label',
					'type' => 'text',
					'instructions' => 'The text to be shown in the myaccount page next to the checkbox',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '20',
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
					'key' => 'ftm_interest_description',
					'label' => 'Description',
					'name' => 'ftm_interest_description',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '20',
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
					'key' => 'ftm_interest_frequency',
					'label' => 'Frequency',
					'name' => 'ftm_interest_frequency',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '20',
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
				'value' => 'fontimator-mailchimp',
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
