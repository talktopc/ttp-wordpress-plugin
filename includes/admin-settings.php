<?php
/**
 * Admin Settings Registration & Sanitizers
 * 
 * Handles:
 * - WordPress settings registration
 * - Custom sanitizer functions
 * - Admin menu creation
 * - Backend sync hooks (sync settings to TalkToPC API)
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// ADMIN MENU
// =============================================================================
add_action('admin_menu', function() {
    add_menu_page('TalkToPC Voice Widget', 'TalkToPC', 'manage_options', 'talktopc', 'talktopc_render_dashboard_page', 'dashicons-microphone', 30);
    add_submenu_page('talktopc', 'Dashboard', 'Dashboard', 'manage_options', 'talktopc', 'talktopc_render_dashboard_page');
    add_submenu_page('talktopc', 'Page Rules', 'Page Rules', 'manage_options', 'talktopc-page-rules', 'talktopc_render_page_rules_page');
    
    // Add Customization page (previously Customization2) - check if function exists before registering
    if (function_exists('talktopc_render_customization2_page')) {
        add_submenu_page('talktopc', 'Customization', 'Customization', 'manage_options', 'talktopc-customization2', 'talktopc_render_customization2_page');
    } else {
        // Debug: Log error if function doesn't exist
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('TalkToPC: talktopc_render_customization2_page() function not found. Make sure customization2.php is included.');
        }
    }
});

// =============================================================================
// REGISTER SETTINGS
// =============================================================================
add_action('admin_init', function() {
    
    // =========================================================================
    // CONNECTION (set via OAuth, not editable)
    // =========================================================================
    register_setting('talktopc_connection', 'talktopc_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_connection', 'talktopc_app_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_connection', 'talktopc_user_email', ['sanitize_callback' => 'sanitize_email']);

    // Partner embed. Own settings group so saving it cannot wipe other options.
    register_setting('talktopc_embed', 'talktopc_embed_slug', [
        'sanitize_callback' => 'talktopc_sanitize_embed_slug',
        'default' => ''
    ]);
    
    // =========================================================================
    // AGENT SELECTION
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_agent_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_agent_name', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // PAGE RULES (JSON array stored as option)
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_page_rules', [
        'sanitize_callback' => 'talktopc_sanitize_page_rules',
        'default' => '[]'
    ]);
    
    // =========================================================================
    // AGENT OVERRIDES
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_override_prompt', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('talktopc_settings', 'talktopc_override_first_message', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_override_voice', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_override_voice_speed', [
        'type' => 'number',
        'sanitize_callback' => 'talktopc_sanitize_voice_speed',
        'default' => 1.0
    ]);
    register_setting('talktopc_settings', 'talktopc_override_language', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_override_temperature', [
        'type' => 'number',
        'sanitize_callback' => 'talktopc_sanitize_temperature',
        'default' => 0.7
    ]);
    register_setting('talktopc_settings', 'talktopc_override_max_tokens', ['sanitize_callback' => 'absint']);
    register_setting('talktopc_settings', 'talktopc_override_max_call_duration', ['sanitize_callback' => 'absint']);
    
    // =========================================================================
    // CALL RECORDING & TOOLS
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_record_call', [
        'sanitize_callback' => 'talktopc_sanitize_checkbox',
        'default' => false
    ]);
    register_setting('talktopc_settings', 'talktopc_enable_leave_message', [
        'sanitize_callback' => 'talktopc_sanitize_checkbox',
        'default' => true
    ]);
    register_setting('talktopc_settings', 'talktopc_enable_visual_tools', [
        'sanitize_callback' => 'talktopc_sanitize_checkbox',
        'default' => true
    ]);
    register_setting('talktopc_settings', 'talktopc_visual_tools_selection', [
        'sanitize_callback' => 'talktopc_sanitize_visual_tools_selection',
        'default' => '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]'
    ]);
    
    // =========================================================================
    // ECOMMERCE
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_ecommerce_enabled', [
        'sanitize_callback' => 'talktopc_sanitize_checkbox',
        'default' => '0'
    ]);
    register_setting('talktopc_settings', 'talktopc_shopify_domain', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    
    // =========================================================================
    // BEHAVIOR
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_mode', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'unified']);
    register_setting('talktopc_settings', 'talktopc_direction', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'ltr']);
    register_setting('talktopc_settings', 'talktopc_auto_open', ['sanitize_callback' => 'rest_sanitize_boolean']);
    register_setting('talktopc_settings', 'talktopc_welcome_message', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // BUTTON
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_position', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'bottom-right']);
    register_setting('talktopc_settings', 'talktopc_button_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('talktopc_settings', 'talktopc_button_shape', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'circle']);
    register_setting('talktopc_settings', 'talktopc_button_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_button_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_button_shadow', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('talktopc_settings', 'talktopc_button_shadow_color', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_position_offset_x', ['sanitize_callback' => 'absint', 'default' => 20]);
    register_setting('talktopc_settings', 'talktopc_position_offset_y', ['sanitize_callback' => 'absint', 'default' => 20]);
    
    // =========================================================================
    // ICON
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_icon_type', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'custom']);
    register_setting('talktopc_settings', 'talktopc_icon_custom_image', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('talktopc_settings', 'talktopc_icon_emoji', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_icon_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_icon_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('talktopc_settings', 'talktopc_icon_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // PANEL
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_panel_width', ['sanitize_callback' => 'absint', 'default' => 350]);
    register_setting('talktopc_settings', 'talktopc_panel_height', ['sanitize_callback' => 'absint', 'default' => 550]);
    register_setting('talktopc_settings', 'talktopc_panel_border_radius', ['sanitize_callback' => 'absint', 'default' => 12]);
    register_setting('talktopc_settings', 'talktopc_panel_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_panel_border', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1px solid #E5E7EB']);
    register_setting('talktopc_settings', 'talktopc_panel_backdrop_filter', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // HEADER
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_header_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_header_show_title', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('talktopc_settings', 'talktopc_header_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_header_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_header_show_close', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    
    // =========================================================================
    // FOOTER (TTP Branding)
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_footer_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_footer_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_footer_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // MESSAGES
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_msg_user_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_msg_agent_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_msg_system_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_msg_error_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_msg_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_msg_font_size', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_msg_border_radius', ['sanitize_callback' => 'absint']);
    
    // =========================================================================
    // LANDING SCREEN
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_landing_bg_color', ['sanitize_callback' => 'sanitize_text_field']); // Can be gradient
    register_setting('talktopc_settings', 'talktopc_landing_logo', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_landing_logo_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_landing_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_landing_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_landing_card_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_landing_voice_icon', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_landing_text_icon', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_landing_voice_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_landing_text_title', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // VOICE INTERFACE
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_voice_mic_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_mic_active_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_avatar_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_status_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_status_subtitle_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_start_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_voice_start_subtitle', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_voice_start_btn_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_voice_start_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_start_btn_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_live_dot_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_voice_live_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // TEXT INTERFACE
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_text_send_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_text_send_btn_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_text_send_btn_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_text_send_btn_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('talktopc_settings', 'talktopc_text_input_placeholder', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_text_input_focus_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // TOOLTIPS
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_tooltip_new_chat', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_tooltip_back', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_tooltip_close', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_tooltip_mute', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_tooltip_speaker', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_tooltip_end_call', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // ANIMATION
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_anim_enable_hover', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('talktopc_settings', 'talktopc_anim_enable_pulse', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('talktopc_settings', 'talktopc_anim_enable_slide', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('talktopc_settings', 'talktopc_anim_duration', [
        'type' => 'number',
        'sanitize_callback' => 'talktopc_sanitize_anim_duration',
        'default' => 0.3
    ]);
    
    // =========================================================================
    // ACCESSIBILITY
    // =========================================================================
    register_setting('talktopc_settings', 'talktopc_a11y_aria_label', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_a11y_aria_description', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('talktopc_settings', 'talktopc_a11y_keyboard_nav', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
});

// =============================================================================
// CUSTOM SANITIZERS
// =============================================================================

/**
 * Sanitize float value with range validation
 * 
 * WordPress Plugin Review: Ensures values are within allowed min/max bounds
 * 
 * @param mixed $value The value to sanitize
 * @param float $min Minimum allowed value
 * @param float $max Maximum allowed value
 * @param float $default Default value if invalid
 * @return float Sanitized and clamped value
 */
function talktopc_sanitize_float_range($value, $min, $max, $default) {
    // Check if value is numeric
    if (!is_numeric($value)) {
        return $default;
    }
    
    // Convert to float
    $value = floatval($value);
    
    // Clamp to min/max range
    if ($value < $min) {
        return $min;
    }
    if ($value > $max) {
        return $max;
    }
    
    return $value;
}

/**
 * Sanitize voice speed (0.5 - 2.0, default 1.0)
 * 
 * WordPress Plugin Review: Enforces valid range for voice speed setting
 */
function talktopc_sanitize_voice_speed($value) {
    return talktopc_sanitize_float_range($value, 0.5, 2.0, 1.0);
}

/**
 * Sanitize temperature (0.0 - 2.0, default 0.7)
 * 
 * WordPress Plugin Review: Enforces valid range for temperature setting
 */
function talktopc_sanitize_temperature($value) {
    return talktopc_sanitize_float_range($value, 0.0, 2.0, 0.7);
}

/**
 * Sanitize animation duration (0.1 - 2.0, default 0.3)
 * 
 * WordPress Plugin Review: Enforces valid range for animation duration setting
 */
function talktopc_sanitize_anim_duration($value) {
    return talktopc_sanitize_float_range($value, 0.1, 2.0, 0.3);
}

/**
 * Generic float sanitizer (for backwards compatibility)
 * 
 * Note: This function does not enforce ranges. Use specific sanitizers
 * (talktopc_sanitize_voice_speed, talktopc_sanitize_temperature, etc.)
 * for fields that require range validation.
 */
function talktopc_sanitize_float($input) {
    if ($input === '' || $input === null) return '';
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function talktopc_sanitize_page_rules($input) {
    if (empty($input)) return '[]';
    if (is_array($input)) return wp_json_encode($input);
    $decoded = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) return '[]';
    $sanitized = [];
    foreach ($decoded as $rule) {
        $sanitized[] = [
            'id' => sanitize_text_field($rule['id'] ?? uniqid('rule_')),
            'type' => sanitize_text_field($rule['type'] ?? 'page'),
            'target_id' => sanitize_text_field($rule['target_id'] ?? ''),
            'target_name' => sanitize_text_field($rule['target_name'] ?? ''),
            'agent_id' => sanitize_text_field($rule['agent_id'] ?? ''),
            'agent_name' => sanitize_text_field($rule['agent_name'] ?? '')
        ];
    }
    return wp_json_encode($sanitized);
}

/**
 * One word used as https://cdn.talktopc.com/<word>.js
 * Empty clears the embed. Anything else is rejected.
 *
 * @param mixed $value
 * @return string Sanitized slug, or '' when empty or invalid
 */
function talktopc_sanitize_embed_slug($value) {
    $slug = talktopc_normalize_embed_slug($value);
    return $slug === false ? '' : $slug;
}

/**
 * @param mixed $value
 * @return string|false Slug, '' when empty, false when the input is not one safe word
 */
function talktopc_normalize_embed_slug($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/\.js$/i', $value)) {
        $value = substr($value, 0, -3);
        $value = trim($value);
    }
    if ($value === '') {
        return '';
    }
    if (!preg_match('/^[A-Za-z]+(?:-[A-Za-z]+){0,8}$/', $value) || strlen($value) > 64) {
        return false;
    }
    return $value;
}

function talktopc_embed_script_url($slug) {
    if (!is_string($slug) || !preg_match('/^[A-Za-z]+(?:-[A-Za-z]+){0,8}$/', $slug) || strlen($slug) > 64) {
        return '';
    }
    return 'https://cdn.talktopc.com/' . $slug . '.js';
}

/**
 * Sanitize checkbox value
 *
 * WordPress Plugin Review: Converts checkbox values to '1' or '0'
 */
function talktopc_sanitize_checkbox($value) {
    return ($value === '1' || $value === 'on' || $value === true || $value === 'true') ? '1' : '0';
}

/**
 * Sanitize visual tools selection JSON array
 * 
 * WordPress Plugin Review: Validates and sanitizes JSON array of tool IDs
 */
function talktopc_sanitize_visual_tools_selection($value) {
    if (empty($value)) {
        return '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]';
    }
    
    if (is_array($value)) {
        // Already an array, validate and return as JSON
        $valid_tools = ['capture_screen', 'highlight_element', 'click_element', 'fill_field', 'scroll_to_element', 'navigate_to', 'read_page'];
        $sanitized = array_filter($value, function($tool) use ($valid_tools) {
            return in_array($tool, $valid_tools, true);
        });
        return wp_json_encode(array_values($sanitized));
    }
    
    // Try to decode JSON string
    $decoded = json_decode($value, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]';
    }
    
    if (!is_array($decoded)) {
        return '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]';
    }
    
    // Validate tool IDs
    $valid_tools = ['capture_screen', 'highlight_element', 'click_element', 'fill_field', 'scroll_to_element', 'navigate_to', 'read_page'];
    $sanitized = array_filter($decoded, function($tool) use ($valid_tools) {
        return in_array($tool, $valid_tools, true);
    });
    
    return wp_json_encode(array_values($sanitized));
}

// =============================================================================
// BACKEND SYNC HOOKS
// Sync agent settings to TalkToPC backend when WordPress settings are saved
// =============================================================================
add_action('update_option_talktopc_override_prompt', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_first_message', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_voice', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_voice_speed', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_language', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_temperature', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_max_tokens', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_override_max_call_duration', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_record_call', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_enable_leave_message', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_enable_visual_tools', 'talktopc_sync_agent_to_backend', 10, 0);
add_action('update_option_talktopc_visual_tools_selection', 'talktopc_sync_agent_to_backend', 10, 0);

function talktopc_sync_agent_to_backend() {
    // Prevent multiple syncs in the same request
    static $synced = false;
    if ($synced) return;
    $synced = true;
    
    $api_key = get_option('talktopc_api_key');
    $agent_id = get_option('talktopc_agent_id');
    
    if (empty($api_key) || empty($agent_id)) {
        return;
    }
    
    // Collect all override values - use camelCase for backend
    $update_data = [];
    
    $prompt = get_option('talktopc_override_prompt');
    $first_message = get_option('talktopc_override_first_message');
    $voice = get_option('talktopc_override_voice');
    $voice_speed = get_option('talktopc_override_voice_speed');
    $language = get_option('talktopc_override_language');
    $temperature = get_option('talktopc_override_temperature');
    $max_tokens = get_option('talktopc_override_max_tokens');
    $max_call_duration = get_option('talktopc_override_max_call_duration');
    
    if (!empty($prompt)) $update_data['systemPrompt'] = $prompt;
    if (!empty($first_message)) $update_data['firstMessage'] = $first_message;
    if (!empty($voice)) $update_data['voiceId'] = $voice;
    if (!empty($voice_speed)) {
        $update_data['voiceSpeed'] = max(0.5, min(2.0, floatval($voice_speed)));
    }
    if (!empty($language)) $update_data['agentLanguage'] = $language;
    if (!empty($temperature)) {
        $update_data['temperature'] = max(0.0, min(2.0, floatval($temperature)));
    }
    if (!empty($max_tokens)) $update_data['maxTokens'] = intval($max_tokens);
    if (!empty($max_call_duration)) $update_data['maxCallDuration'] = intval($max_call_duration);
    
    // Call Recording
    $record_call = get_option('talktopc_record_call', false);
    $update_data['recordCall'] = ($record_call === '1' || $record_call === true || $record_call === 'true');
    
    // Build internalToolIds array
    $internal_tool_ids = [];
    
    // Add leave_message if enabled
    $enable_leave_message = get_option('talktopc_enable_leave_message', true);
    if ($enable_leave_message === '1' || $enable_leave_message === true || $enable_leave_message === 'true') {
        $internal_tool_ids[] = 'leave_message';
    }
    
    // Add visual tools if enabled
    $enable_visual_tools = get_option('talktopc_enable_visual_tools', true);
    if ($enable_visual_tools === '1' || $enable_visual_tools === true || $enable_visual_tools === 'true') {
        $visual_tools_selection = get_option('talktopc_visual_tools_selection', '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]');
        $selected_tools = json_decode($visual_tools_selection, true);
        if (is_array($selected_tools) && !empty($selected_tools)) {
            $internal_tool_ids = array_merge($internal_tool_ids, $selected_tools);
        }
    }
    
    // Only include internalToolIds if there are tools to include
    if (!empty($internal_tool_ids)) {
        $update_data['internalToolIds'] = array_values(array_unique($internal_tool_ids));
    }
    
    if (empty($update_data)) {
        return;
    }
    
    $response = wp_remote_request(TALKTOPC_API_URL . '/api/public/wordpress/agents/' . $agent_id, [
        'method' => 'PUT',
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode($update_data),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200) {
        } else {
        }
    }
}