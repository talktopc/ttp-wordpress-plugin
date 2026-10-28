<?php
/**
 * Frontend Widget
 * 
 * Handles:
 * - Enqueuing the widget script from CDN
 * - Building the configuration object from all settings
 * - Injecting the initialization script
 * 
 * FIX: Page rules now properly match with type casting
 */

if (!defined('ABSPATH')) exit;

/**
 * Partner embed. Prints even when no TalkToPC API key is connected.
 * Empty slug prints nothing. The URL is always https://cdn.talktopc.com/<slug>.js.
 */
add_action('wp_head', 'talktopc_print_partner_embed', 1);

function talktopc_print_partner_embed() {
    if (is_admin()) {
        return;
    }
    $src = talktopc_embed_script_url((string) get_option('talktopc_embed_slug', ''));
    if ($src === '') {
        return;
    }
    // nowprocket is WP Rocket's mark to leave this tag alone. Browsers ignore it.
    echo "<script nowprocket>\n";
    echo "(function () {\n";
    echo "  if (window.top !== window.self) return;\n";
    echo "  var s = document.createElement('script');\n";
    echo "  s.src = " . wp_json_encode($src, JSON_UNESCAPED_SLASHES) . ";\n";
    echo "  s.async = true;\n";
    echo "  document.head.appendChild(s);\n";
    echo "})();\n";
    echo "</script>\n";
}

add_filter('script_loader_tag', 'talktopc_script_nowprocket', 10, 2);

function talktopc_script_nowprocket($tag, $handle) {
    if ($handle !== 'talktopc-agent-widget') {
        return $tag;
    }
    return preg_replace('/<script\b(?![^>]*\bnowprocket\b)/', '<script nowprocket', $tag);
}

add_action('wp_enqueue_scripts', function() {
    $api_key = get_option('talktopc_api_key');
    $default_agent_id = get_option('talktopc_agent_id');
    
    if (empty($api_key)) return;
    
    // Get agent for current page (check rules first)
    $agent_config = talktopc_get_agent_for_current_page();
    
    // Debug logging (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Widget - Agent Config: ' . print_r($agent_config, true));
    }
    
    // Don't render if disabled
    if ($agent_config['is_disabled'] || empty($agent_config['agent_id'])) return;
    
    $agent_id = $agent_config['agent_id'];
    
    // Enqueue widget script from CDN
    wp_enqueue_script('talktopc-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', [], TALKTOPC_VERSION, true);
    
    // Build configuration object (pass agent_id from page rules)
    $config = talktopc_build_widget_config($agent_id);
    
    // Debug: Log full config to help diagnose color issues (only in debug mode)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('=== TTP Widget Config Debug ===');
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('Header backgroundColor: ' . ($config['header']['backgroundColor'] ?? 'NOT SET'));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('Landing modeCardIconBackgroundColor: ' . ($config['landing']['modeCardIconBackgroundColor'] ?? 'NOT SET'));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('Landing modeCardBackgroundColor: ' . ($config['landing']['modeCardBackgroundColor'] ?? 'NOT SET'));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_var_export
        error_log('Saved DB option talktopc_header_bg_color: ' . var_export(get_option('talktopc_header_bg_color'), true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_var_export
        error_log('Saved DB option talktopc_landing_card_icon_bg_color: ' . var_export(get_option('talktopc_landing_card_icon_bg_color'), true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('Full header config: ' . print_r($config['header'] ?? [], true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('Full landing config: ' . print_r($config['landing'] ?? [], true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('=== End Config Debug ===');
    }
    
    // Also add console.log in the JavaScript for browser debugging
    $debug_script = '';
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $debug_script = 'console.log("TTP Widget Config:", ' . wp_json_encode($config) . ');';
        $debug_script .= 'console.log("TTP Widget Header Config:", ' . wp_json_encode($config['header'] ?? []) . ');';
        $debug_script .= 'console.log("TTP Widget Header backgroundColor:", ' . wp_json_encode($config['header']['backgroundColor'] ?? 'NOT SET') . ');';
    } else {
        // Always log header backgroundColor for debugging (even in production)
        $debug_script = 'console.log("[TTP WP Plugin] Header backgroundColor being sent:", ' . wp_json_encode($config['header']['backgroundColor'] ?? 'NOT SET') . ');';
    }
    
    $config['flavor'] = [
        'type' => 'ecommerce',
        'partnerId' => 'wordpress',
    ];
    
    // Build initialization script - direct init with appId + agentId (domain whitelisting for security)
    $script = sprintf(
        '(function(){%s var c=%s;function i(){if(typeof TTPAgentSDK!=="undefined"&&TTPAgentSDK.TTPChatWidget){new TTPAgentSDK.TTPChatWidget(c);}else{setTimeout(i,100);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",i);}else{i();}})();',
        $debug_script,
        wp_json_encode($config)
    );
    
    wp_add_inline_script('talktopc-agent-widget', $script);
});

/**
 * Build widget configuration from all settings
 */
function talktopc_build_widget_config($agent_id = null) {
    $config = [];
    
    // ==========================================================================
    // REQUIRED
    // ==========================================================================
    $config['appId'] = get_option('talktopc_app_id');
    $config['agentId'] = $agent_id !== null ? $agent_id : get_option('talktopc_agent_id');
    if ($v = get_option('talktopc_agent_name')) $config['agentName'] = $v;
    
    // ==========================================================================
    // BASIC OPTIONS (Widget UI only - NOT agent settings)
    // ==========================================================================
    if ($v = get_option('talktopc_direction')) $config['direction'] = $v;
    if ($v = get_option('talktopc_position')) $config['position'] = $v;
    
    // ==========================================================================
    // BUTTON (Floating button)
    // ==========================================================================
    // Always include button section with explicit defaults
    $button = [];
    if ($v = get_option('talktopc_button_size')) $button['size'] = $v;
    if ($v = get_option('talktopc_button_shape')) $button['shape'] = $v;
    
    // Always set backgroundColor explicitly - default to white
    $btn_bg = get_option('talktopc_button_bg_color');
    $button['backgroundColor'] = (!empty($btn_bg) && $btn_bg !== '') ? $btn_bg : '#FFFFFF';
    
    // Always set hoverColor explicitly
    $btn_hover = get_option('talktopc_button_hover_color');
    $button['hoverColor'] = (!empty($btn_hover) && $btn_hover !== '') ? $btn_hover : '#F5F5F5';
    
    $button['shadow'] = get_option('talktopc_button_shadow', '1') === '1';
    
    // Shadow color is optional
    if ($v = get_option('talktopc_button_shadow_color')) $button['shadowColor'] = $v;
    
    $config['button'] = $button;
    
    // ==========================================================================
    // ICON
    // ==========================================================================
    // Always include icon section with explicit defaults
    $icon = [];
    if ($v = get_option('talktopc_icon_type')) $icon['type'] = $v;
    if ($v = get_option('talktopc_icon_custom_image')) $icon['customImage'] = $v;
    if ($v = get_option('talktopc_icon_emoji')) $icon['emoji'] = $v;
    if ($v = get_option('talktopc_icon_text')) $icon['text'] = $v;
    if ($v = get_option('talktopc_icon_size')) $icon['size'] = $v;
    
    // Always set backgroundColor explicitly - default to white
    $icon_bg = get_option('talktopc_icon_bg_color');
    $icon['backgroundColor'] = (!empty($icon_bg) && $icon_bg !== '') ? $icon_bg : '#FFFFFF';
    
    $config['icon'] = $icon;
    
    // ==========================================================================
    // PANEL
    // ==========================================================================
    // Always include panel section with explicit defaults
    $panel = [];
    if ($v = get_option('talktopc_panel_width')) $panel['width'] = intval($v);
    if ($v = get_option('talktopc_panel_height')) $panel['height'] = intval($v);
    if ($v = get_option('talktopc_panel_border_radius')) $panel['borderRadius'] = intval($v);
    
    // Always set backgroundColor explicitly - default to white
    $panel_bg = get_option('talktopc_panel_bg_color');
    $panel['backgroundColor'] = (!empty($panel_bg) && $panel_bg !== '') ? $panel_bg : '#FFFFFF';
    
    // Border and backdrop filter are optional
    if ($v = get_option('talktopc_panel_border')) $panel['border'] = $v;
    if ($v = get_option('talktopc_panel_backdrop_filter')) $panel['backdropFilter'] = $v;
    
    $config['panel'] = $panel;
    
    // ==========================================================================
    // LANDING SCREEN (SET BEFORE HEADER to prevent SDK fallback)
    // ==========================================================================
    // CRITICAL FIX: Always include landing section BEFORE header to prevent SDK
    // from using header.backgroundColor as fallback for card elements
    // The SDK may check landing section first, so we must ensure modeCardBackgroundColor
    // is always explicitly set, even if it's the default white
    $landing = [];
    
    // Optional values (only set if configured)
    if ($v = get_option('talktopc_landing_logo')) $landing['logo'] = $v;
    // For icon type logos, SDK uses avatarBackground. For image type, it uses logoBackgroundColor.
    // We'll set both to ensure compatibility
    if ($v = get_option('talktopc_landing_logo_bg_color')) {
        $landing['logoBackgroundColor'] = $v;
        $landing['avatarBackground'] = $v; // For icon type logos
    }
    if ($v = get_option('talktopc_landing_title')) $landing['title'] = $v;
    
    // Always explicitly set title and subtitle colors to prevent SDK fallback to header colors
    $title_color = get_option('talktopc_landing_title_color');
    $landing['titleColor'] = !empty($title_color) ? $title_color : '#1e293b';
    
    $subtitle_color = get_option('talktopc_landing_subtitle_color');
    $landing['subtitleColor'] = !empty($subtitle_color) ? $subtitle_color : '#64748b';
    
    // CRITICAL: Mode card background color - ALWAYS set explicitly (never leave undefined/null)
    // This prevents the SDK from falling back to header.backgroundColor for the cards
    $card_bg = get_option('talktopc_landing_card_bg_color');
    if (!empty($card_bg) && is_string($card_bg) && trim($card_bg) !== '' && trim($card_bg) !== 'null') {
        $landing['modeCardBackgroundColor'] = trim($card_bg);
    } else {
        // Always set explicit default - SDK will use header.backgroundColor if this is missing
        $landing['modeCardBackgroundColor'] = '#FFFFFF';
    }
    
    // Optional card content
    if ($v = get_option('talktopc_landing_voice_icon')) $landing['voiceCardIcon'] = $v;
    if ($v = get_option('talktopc_landing_voice_title')) $landing['voiceCardTitle'] = $v;
    if ($v = get_option('talktopc_landing_text_icon')) $landing['textCardIcon'] = $v;
    if ($v = get_option('talktopc_landing_text_title')) $landing['textCardTitle'] = $v;
    
    // Always include landing section BEFORE header to ensure color isolation
    // The landing section will always have at least the color defaults set above
    $config['landing'] = $landing;
    
    // ==========================================================================
    // HEADER
    // ==========================================================================
    // FIX: Always explicitly set header backgroundColor to ensure it's applied correctly
    // and doesn't get mixed up with other elements
    $header = [];
    if ($v = get_option('talktopc_header_title')) $header['title'] = $v;
    $header['showTitle'] = get_option('talktopc_header_show_title', '1') === '1';
    
    // CRITICAL FIX: Always use the saved header backgroundColor value if it exists
    // WordPress sanitize_hex_color already validates the format, so trust it
    $header_bg = get_option('talktopc_header_bg_color', '');
    
    // Use the saved value if it's a non-empty string, otherwise use default
    if (!empty($header_bg) && is_string($header_bg) && trim($header_bg) !== '') {
        $header['backgroundColor'] = trim($header_bg);
    } else {
        // No value saved or empty, use default
        $header['backgroundColor'] = '#7C3AED';
    }
    
    // Always set textColor explicitly
    $header_text = get_option('talktopc_header_text_color');
    $header['textColor'] = (!empty($header_text) && $header_text !== '') ? $header_text : '#FFFFFF';
    
    $header['showCloseButton'] = get_option('talktopc_header_show_close', '1') === '1';
    
    // Always include header section to ensure colors are properly isolated
    $config['header'] = $header;
    
    // ==========================================================================
    // FOOTER (TTP Branding)
    // ==========================================================================
    // Always include footer section - always shown for free tier
    $footer = ['show' => true];
    
    // Footer colors - SDK has defaults, but we set explicitly when configured
    if ($v = get_option('talktopc_footer_bg_color')) $footer['backgroundColor'] = $v;
    if ($v = get_option('talktopc_footer_text_color')) $footer['textColor'] = $v;
    if ($v = get_option('talktopc_footer_hover_color')) $footer['hoverColor'] = $v;
    
    $config['footer'] = $footer;
    
    // ==========================================================================
    // MESSAGES
    // ==========================================================================
    // Always include messages section with explicit defaults to prevent inheritance issues
    $messages = [];
    
    // Always set message background colors explicitly
    $msg_user_bg = get_option('talktopc_msg_user_bg');
    $messages['userBackgroundColor'] = (!empty($msg_user_bg) && $msg_user_bg !== '') ? $msg_user_bg : '#E5E7EB';
    
    $msg_agent_bg = get_option('talktopc_msg_agent_bg');
    $messages['agentBackgroundColor'] = (!empty($msg_agent_bg) && $msg_agent_bg !== '') ? $msg_agent_bg : '#F3F4F6';
    
    $msg_system_bg = get_option('talktopc_msg_system_bg');
    $messages['systemBackgroundColor'] = (!empty($msg_system_bg) && $msg_system_bg !== '') ? $msg_system_bg : '#FEF3C7';
    
    $msg_error_bg = get_option('talktopc_msg_error_bg');
    $messages['errorBackgroundColor'] = (!empty($msg_error_bg) && $msg_error_bg !== '') ? $msg_error_bg : '#FEE2E2';
    
    // Always set text color explicitly
    $msg_text_color = get_option('talktopc_msg_text_color');
    $messages['textColor'] = (!empty($msg_text_color) && $msg_text_color !== '') ? $msg_text_color : '#1F2937';
    
    // Font size and border radius are optional
    if ($v = get_option('talktopc_msg_font_size')) $messages['fontSize'] = $v;
    if ($v = get_option('talktopc_msg_border_radius')) $messages['borderRadius'] = intval($v);
    
    $config['messages'] = $messages;
    
    // ==========================================================================
    // VOICE INTERFACE
    // ==========================================================================
    // Always include voice section - SDK has good defaults, but we ensure explicit values when set
    $voice = [
        // Audio output settings (required for proper playback)
        'outputContainer' => 'raw',
        'outputEncoding' => 'pcm',
        'outputSampleRate' => 44100,
        'outputChannels' => 1,
        'outputBitDepth' => 16
    ];
    
    // Microphone colors - SDK defaults to primaryColor (purple) if not set
    if ($v = get_option('talktopc_voice_mic_color')) $voice['micButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_mic_active_color')) $voice['micButtonActiveColor'] = $v;
    
    // Avatar colors - SDK defaults to #667eea if not set
    if ($v = get_option('talktopc_voice_avatar_color')) $voice['avatarBackgroundColor'] = $v;
    
    // Status colors - SDK has explicit defaults
    if ($v = get_option('talktopc_voice_status_title_color')) $voice['statusTitleColor'] = $v;
    if ($v = get_option('talktopc_voice_status_subtitle_color')) $voice['statusSubtitleColor'] = $v;
    
    // Start Call text and button
    if ($v = get_option('talktopc_voice_start_title')) $voice['startCallTitle'] = $v;
    if ($v = get_option('talktopc_voice_start_subtitle')) $voice['startCallSubtitle'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_text')) $voice['startCallButtonText'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_color')) $voice['startCallButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_text_color')) $voice['startCallButtonTextColor'] = $v;
    
    
    // Live indicator colors - SDK has explicit defaults
    if ($v = get_option('talktopc_voice_live_dot_color')) $voice['liveDotColor'] = $v;
    if ($v = get_option('talktopc_voice_live_text_color')) $voice['liveTextColor'] = $v;
    if ($v = get_option('talktopc_voice_pill_gradient')) $voice['pillGradient'] = $v;
    if ($v = get_option('talktopc_voice_pill_text_color')) $voice['pillTextColor'] = $v;
    if ($v = get_option('talktopc_voice_pill_dot_color')) $voice['pillDotColor'] = $v;
    if ($v = get_option('talktopc_voice_avatar_gradient1')) $voice['avatarGradient1'] = $v;
    if ($v = get_option('talktopc_voice_avatar_gradient2')) $voice['avatarGradient2'] = $v;
    if ($v = get_option('talktopc_voice_online_dot_color')) $voice['onlineDotColor'] = $v;
    if ($v = get_option('talktopc_voice_hero_gradient1')) $voice['heroGradient1'] = $v;
    if ($v = get_option('talktopc_voice_hero_gradient2')) $voice['heroGradient2'] = $v;
    if ($v = get_option('talktopc_voice_agent_name_color')) $voice['agentNameColor'] = $v;
    if ($v = get_option('talktopc_voice_agent_role_color')) $voice['agentRoleColor'] = $v;
    if ($v = get_option('talktopc_voice_headline_color')) $voice['headlineColor'] = $v;
    if ($v = get_option('talktopc_voice_subline_color')) $voice['sublineColor'] = $v;
    if ($v = get_option('talktopc_voice_primary_btn_gradient1')) $voice['primaryBtnGradient1'] = $v;
    if ($v = get_option('talktopc_voice_primary_btn_gradient2')) $voice['primaryBtnGradient2'] = $v;
    if ($v = get_option('talktopc_voice_secondary_btn_bg')) $voice['secondaryBtnBg'] = $v;
    if ($v = get_option('talktopc_voice_secondary_btn_border')) $voice['secondaryBtnBorder'] = $v;
    if ($v = get_option('talktopc_voice_secondary_btn_text_color')) $voice['secondaryBtnTextColor'] = $v;
    if ($v = get_option('talktopc_voice_waveform_bar_color')) $voice['waveformBarColor'] = $v;
    if ($v = get_option('talktopc_voice_send_message_text')) $voice['sendMessageText'] = $v;
    if ($v = get_option('talktopc_voice_agent_role')) $voice['agentRole'] = $v;
    if ($v = get_option('talktopc_voice_headline')) $voice['headline'] = $v;
    if ($v = get_option('talktopc_voice_subline')) $voice['subline'] = $v;
    if ($v = get_option('talktopc_voice_speaker_color')) $voice['speakerButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_live_indicator_text_color')) $voice['liveIndicatorTextColor'] = $v;
    if ($v = get_option('talktopc_voice_live_indicator_dot_color')) $voice['liveIndicatorDotColor'] = $v;
    if ($v = get_option('talktopc_voice_live_transcript_text_color')) $voice['liveTranscriptTextColor'] = $v;
    if ($v = get_option('talktopc_voice_live_transcript_font_size')) $voice['liveTranscriptFontSize'] = $v;
    
    $config['voice'] = $voice;
    
    // ==========================================================================
    // TEXT INTERFACE
    // ==========================================================================
    // Always include text section - SDK has good defaults, but we ensure explicit values when set
    $text = [];
    
    // Send Button colors - SDK defaults to purple (#7C3AED) if not set
    if ($v = get_option('talktopc_text_send_btn_color')) $text['sendButtonColor'] = $v;
    if ($v = get_option('talktopc_text_send_btn_hover_color')) $text['sendButtonHoverColor'] = $v;
    if ($v = get_option('talktopc_text_send_btn_text')) $text['sendButtonText'] = $v;
    if ($v = get_option('talktopc_text_send_btn_text_color')) $text['sendButtonTextColor'] = $v;
    
    // Input Field
    // Check if option exists (even if empty string) - placeholders can be empty
    $placeholder = get_option('talktopc_text_input_placeholder');
    if ($placeholder !== false) $text['inputPlaceholder'] = $placeholder;
    
    // Input colors - SDK defaults
    if ($v = get_option('talktopc_text_input_focus_color')) $text['inputFocusColor'] = $v;
    
    $config['text'] = $text;
    
    // ==========================================================================
    // TOOLTIPS
    // ==========================================================================
    $tooltips = [];
    if ($v = get_option('talktopc_tooltip_new_chat')) $tooltips['newChat'] = $v;
    if ($v = get_option('talktopc_tooltip_back')) $tooltips['back'] = $v;
    if ($v = get_option('talktopc_tooltip_close')) $tooltips['close'] = $v;
    if ($v = get_option('talktopc_tooltip_mute')) $tooltips['mute'] = $v;
    if ($v = get_option('talktopc_tooltip_speaker')) $tooltips['speaker'] = $v;
    if ($v = get_option('talktopc_tooltip_end_call')) $tooltips['endCall'] = $v;
    if (!empty($tooltips)) $config['tooltips'] = $tooltips;
    
    // ==========================================================================
    // ANIMATION
    // ==========================================================================
    $animation = [];
    $animation['enableHover'] = get_option('talktopc_anim_enable_hover', '1') === '1';
    $animation['enablePulse'] = get_option('talktopc_anim_enable_pulse', '1') === '1';
    $animation['enableSlide'] = get_option('talktopc_anim_enable_slide', '1') === '1';
    if ($v = get_option('talktopc_anim_duration')) $animation['duration'] = floatval($v);
    if (!empty($animation)) $config['animation'] = $animation;
    
    // ==========================================================================
    // ACCESSIBILITY
    // ==========================================================================
    $accessibility = [];
    if ($v = get_option('talktopc_a11y_aria_label')) $accessibility['ariaLabel'] = $v;
    if ($v = get_option('talktopc_a11y_aria_description')) $accessibility['ariaDescription'] = $v;
    $accessibility['keyboardNavigation'] = get_option('talktopc_a11y_keyboard_nav', '1') === '1';
    if (!empty($accessibility)) $config['accessibility'] = $accessibility;
    
    // ==========================================================================
    // BEHAVIOR
    // ==========================================================================
    $behavior = [];
    if ($v = get_option('talktopc_mode')) $behavior['mode'] = $v;
    if (get_option('talktopc_auto_open') === '1') $behavior['autoOpen'] = true;
    if ($v = get_option('talktopc_welcome_message')) {
        $behavior['showWelcomeMessage'] = true;
        $behavior['welcomeMessage'] = $v;
    }
    if (!empty($behavior)) $config['behavior'] = $behavior;
    
    // ==========================================================================
    // AGENT SETTINGS OVERRIDE
    // ==========================================================================
    // NOTE: We no longer send overrides by default. The agent should use its 
    // own settings from the TalkToPC backend. Only send overrides if explicitly
    // configured in WordPress (future feature: add a "customize agent" checkbox)
    //
    // If you need per-page overrides, use Page Rules with custom settings.
    // ==========================================================================
    
    return $config;
}

/**
 * Get agent configuration for current page based on page rules
 * 
 * FIX: Properly cast target_id to int for page/post/category matching
 */
function talktopc_get_agent_for_current_page() {
    $rules = json_decode(get_option('talktopc_page_rules', '[]'), true);
    $default_agent_id = get_option('talktopc_agent_id', '');
    $default_agent_name = get_option('talktopc_agent_name', '');
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG && !empty($rules)) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Page Rules Check - Current URL: ' . $request_uri);
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Page Rules - Rules: ' . print_r($rules, true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('TTP Page Rules - is_page(): ' . (is_page() ? 'yes' : 'no'));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('TTP Page Rules - is_single(): ' . (is_single() ? 'yes' : 'no'));
        if (is_page() || is_single()) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('TTP Page Rules - Current post ID: ' . get_the_ID());
        }
    }
    
    foreach ($rules as $rule) {
        if (talktopc_rule_matches_current_page($rule)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
                error_log('TTP Page Rules - MATCHED rule: ' . print_r($rule, true));
            }
            return [
                'agent_id' => $rule['agent_id'],
                'agent_name' => $rule['agent_name'] ?? '',
                'is_disabled' => ($rule['agent_id'] === 'none')
            ];
        }
    }
    
    return [
        'agent_id' => $default_agent_id,
        'agent_name' => $default_agent_name,
        'is_disabled' => ($default_agent_id === 'none' || empty($default_agent_id))
    ];
}

/**
 * Check if a rule matches the current page
 * 
 * FIX: Cast target_id to int for numeric comparisons
 */
function talktopc_rule_matches_current_page($rule) {
    $type = $rule['type'] ?? '';
    $target_id = $rule['target_id'] ?? '';
    
    if (empty($target_id)) return false;
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log("TTP Rule Check - Type: {$type}, Target: {$target_id}");
    }
    
    switch ($type) {
        case 'page':
            // FIX: Cast to int for is_page() check
            $target_int = intval($target_id);
            $matches = is_page($target_int);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("TTP Rule Check - is_page({$target_int}): " . ($matches ? 'yes' : 'no'));
            }
            return $matches;
            
        case 'post':
            // FIX: Cast to int for is_single() check
            $target_int = intval($target_id);
            return is_single($target_int);
            
        case 'post_type':
            // post_type target_id is a string like 'post', 'product'
            return is_singular($target_id) || is_post_type_archive($target_id);
            
        case 'category':
            // FIX: Cast to int for category checks
            $target_int = intval($target_id);
            return is_category($target_int) || (is_single() && has_category($target_int));
            
        case 'product_cat':
            if (function_exists('is_product_category')) {
                // FIX: Cast to int for product category checks
                $target_int = intval($target_id);
                return is_product_category($target_int) || (is_singular('product') && has_term($target_int, 'product_cat'));
            }
            return false;
            
        default:
            return false;
    }
}