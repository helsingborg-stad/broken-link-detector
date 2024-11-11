<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_6718e7ca78c94',
    'title' => __('Local Domain Settings', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_6718e7d0b54f7',
            'label' => __('Local domains', 'api-event-manager'),
            'name' => 'broken_links_local_domains',
            'aria-label' => '',
            'type' => 'repeater',
            'instructions' => __('Add domains in this list, that should not be checked.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'acfe_repeater_stylised_button' => 1,
            'layout' => 'table',
            'pagination' => 0,
            'min' => 0,
            'max' => 0,
            'collapsed' => '',
            'button_label' => __('Lägg till rad', 'api-event-manager'),
            'rows_per_page' => 20,
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_6718e860b54f9',
                    'label' => __('Domain', 'api-event-manager'),
                    'name' => 'domain',
                    'aria-label' => '',
                    'type' => 'url',
                    'instructions' => __('eg. https://domain.com or https://subdomain.domain.com', 'api-event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'parent_repeater' => 'field_6718e7d0b54f7',
                ),
            ),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'broken-links-settings',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'show_in_rest' => 0,
    'acfe_display_title' => '',
    'acfe_autosync' => array(
        0 => 'json',
    ),
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));
}