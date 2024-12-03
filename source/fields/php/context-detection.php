<?php 


if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group(array(
    'key' => 'group_6718e9e8554ca',
    'title' => __('Context Detect', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_6718e9eed55b7',
            'label' => __('Enable detection of user context', 'api-event-manager'),
            'name' => 'broken_links_context_check_enabled',
            'aria-label' => '',
            'type' => 'true_false',
            'instructions' => __('The user detection functionality will disable links that are internal only. It also adds a tipbox to them explaining why the link is unreachable.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('Detects user context by fetching a internal resource.', 'api-event-manager'),
            'default_value' => 0,
            'ui_on_text' => __('Enabled', 'api-event-manager'),
            'ui_off_text' => __('Disabled', 'api-event-manager'),
            'ui' => 1,
        ),
        1 => array(
            'key' => 'field_6718ea46d55b8',
            'label' => __('Internal Context Detection Resource', 'api-event-manager'),
            'name' => 'broken_links_context_check_url',
            'aria-label' => '',
            'type' => 'url',
            'instructions' => __('The internal context checker require you to publish a image on a server without public access. The image should be as small as possible, if your site is running on https, this resource must also be served with https.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_6718e9eed55b7',
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
            'default_value' => '',
            'placeholder' => __('https://internal.resource.admin-network.local/image-1x1.jpg', 'api-event-manager'),
        ),
        2 => array(
            'key' => 'field_674ed2cff358e',
            'label' => __('Inform user by the following methods', 'api-event-manager'),
            'name' => 'broken_links_context_notify_by',
            'aria-label' => '',
            'type' => 'checkbox',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_6718e9eed55b7',
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
            'choices' => array(
                'tooltip' => __('Tooltip', 'api-event-manager'),
                'modal' => __('Modal', 'api-event-manager'),
            ),
            'default_value' => array(
            ),
            'return_format' => 'value',
            'allow_custom' => 0,
            'layout' => 'horizontal',
            'toggle' => 0,
            'save_custom' => 0,
            'custom_choice_button_text' => 'Lägg till nytt val',
        ),
        3 => array(
            'key' => 'field_6733096f5d072',
            'label' => __('Tooltip Text', 'api-event-manager'),
            'name' => 'broken_links_context_tooltip',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => __('The text that displays in the tooltip, whenever a link is unavailable.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_674ed2cff358e',
                        'operator' => '==',
                        'value' => 'tooltip',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => __('Link unavailable', 'api-event-manager'),
            'prepend' => '',
            'append' => '',
        ),
        4 => array(
            'key' => 'field_674ed3fcf358f',
            'label' => __('Modal Title', 'api-event-manager'),
            'name' => 'broken_links_context_modal_title',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_674ed2cff358e',
                        'operator' => '==',
                        'value' => 'modal',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        5 => array(
            'key' => 'field_674ed42ef3590',
            'label' => __('Modal Content', 'api-event-manager'),
            'name' => 'broken_links_context_modal_content',
            'aria-label' => '',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_674ed2cff358e',
                        'operator' => '==',
                        'value' => 'modal',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'acfe_textarea_code' => 0,
            'maxlength' => '',
            'rows' => '',
            'placeholder' => '',
            'new_lines' => '',
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