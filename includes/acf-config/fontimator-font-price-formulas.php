<?php
acf_add_local_field_group(
	array(
		'key' => 'fontimator-font-price-formulas',
		'title' => 'Font Price Formulas',
		'fields' => array(
			array(
				'key' => 'field_5b87d2b427ad1',
				'label' => 'Font Price Formulas',
				'name' => 'fontprice_ratios',
				'type' => 'group',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'layout' => 'row',
				'sub_fields' => $this->get_font_price_formulas_subfields(),
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
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'product',
				),
			),
		),
		'menu_order' => 5,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	)
);
