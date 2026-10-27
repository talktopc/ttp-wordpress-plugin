<?php
/**
 * AJAX Handlers
 * 
 * All WordPress AJAX endpoints for the plugin:
 * - talktopc_fetch_agents      - Get user's agents from TalkToPC API
 * - talktopc_fetch_voices      - Get available voices from TalkToPC API
 * - talktopc_fetch_credits     - Get user's remaining credits from TalkToPC API
 * - talktopc_create_agent      - Create new agent (with optional AI prompt)
 * - talktopc_update_agent      - Update agent settings
 * - talktopc_generate_prompt   - Generate system prompt from site content
 * - talktopc_save_agent_selection - Save selected agent to options
 * - talktopc_get_signed_url    - Get signed URL for widget (public)
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// FETCH AGENTS
// =============================================================================
add_action('wp_ajax_talktopc_fetch_agents', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $response = wp_remote_get(TALKTOPC_API_URL . '/api/public/wordpress/agents', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
        return;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        wp_send_json_error(['message' => $body['error'] ?? 'API request failed']);
        return;
    }
    
    wp_send_json_success(json_decode(wp_remote_retrieve_body($response), true));
});

// =============================================================================
// FETCH VOICES
// =============================================================================
add_action('wp_ajax_talktopc_fetch_voices', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $response = wp_remote_get(TALKTOPC_API_URL . '/api/public/wordpress/voices', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
        return;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        wp_send_json_error(['message' => $body['error'] ?? 'API request failed']);
        return;
    }
    
    wp_send_json_success(json_decode(wp_remote_retrieve_body($response), true));
});

// =============================================================================
// FETCH CREDITS - Get user's remaining voice minutes
// =============================================================================
add_action('wp_ajax_talktopc_fetch_credits', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    // Call the TalkToPC API to get credits
    $response = wp_remote_get(TALKTOPC_API_URL . '/api/public/wordpress/credits', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        wp_send_json_error(['message' => 'Credits API not available']);
    }
    
    // Pass through the full response from backend
    // Expected fields: credits, remainingBrowserMinutes, totalCredits, usedCredits, etc.
    wp_send_json_success($body);
});

// =============================================================================
// GET SETUP STATUS - Check if agent creation is in progress
// =============================================================================
add_action('wp_ajax_talktopc_get_setup_status', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $is_creating = (bool) get_transient('talktopc_agent_creating');
    $agent_id = get_option('talktopc_agent_id', '');
    $agent_name = get_option('talktopc_agent_name', '');
    
    wp_send_json_success([
        'creating' => $is_creating,
        'agent_id' => $agent_id,
        'agent_name' => $agent_name,
        'ready' => !$is_creating && !empty($agent_id)
    ]);
});

// =============================================================================
// CREATE AGENT
// =============================================================================
add_action('wp_ajax_talktopc_create_agent', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $agent_name = isset($_POST['agent_name']) ? sanitize_text_field(wp_unslash($_POST['agent_name'])) : '';
    if (empty($agent_name)) wp_send_json_error(['message' => 'Agent name required']);
    
    // Check if we should auto-generate prompt from site content
    $auto_generate = isset($_POST['auto_generate_prompt']) && $_POST['auto_generate_prompt'] === 'true';
    
    // Build agent configuration
    $agent_data = [
        'name' => $agent_name,
        'site_url' => home_url(),
        'site_name' => get_bloginfo('name')
    ];
    
    // Optional configuration fields (used for manual creation)
    if (!empty($_POST['first_message'])) {
        $agent_data['first_message'] = sanitize_text_field(wp_unslash($_POST['first_message']));
    }
    if (!empty($_POST['system_prompt'])) {
        $agent_data['system_prompt'] = sanitize_textarea_field(wp_unslash($_POST['system_prompt']));
    }
    if (!empty($_POST['voice_id'])) {
        $agent_data['voice_id'] = sanitize_text_field(wp_unslash($_POST['voice_id']));
    }
    if (isset($_POST['voice_speed']) && $_POST['voice_speed'] !== '') {
        $agent_data['voice_speed'] = max(0.5, min(2.0, floatval($_POST['voice_speed'])));
    }
    if (!empty($_POST['language'])) {
        $agent_data['language'] = sanitize_text_field(wp_unslash($_POST['language']));
    }
    if (isset($_POST['temperature']) && $_POST['temperature'] !== '') {
        $agent_data['temperature'] = max(0.0, min(2.0, floatval($_POST['temperature'])));
    }
    if (isset($_POST['max_tokens']) && $_POST['max_tokens'] !== '') {
        $agent_data['max_tokens'] = intval($_POST['max_tokens']);
    }
    if (isset($_POST['max_call_duration']) && $_POST['max_call_duration'] !== '') {
        $agent_data['max_call_duration'] = intval($_POST['max_call_duration']);
    }
    
    // If auto-generate, collect site content for AI prompt generation
    if ($auto_generate) {
        $agent_data['site_content'] = talktopc_collect_site_content();
        
        // Use detected content language (not WordPress admin locale)
        $detected_lang = $agent_data['site_content']['site']['language'];
        $lang_code = explode('_', $detected_lang)[0];
        $agent_data['language'] = $lang_code;
    }
    
    // Prepare request - use gzip if site_content is included
    $json_body = json_encode($agent_data, JSON_UNESCAPED_UNICODE);
    $headers = ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'];
    $body = $json_body;
    $timeout = 30;
    
    if ($auto_generate && !empty($agent_data['site_content'])) {
        // Gzip compress for large payloads
        $body = gzencode($json_body, 9);
        $headers['Content-Encoding'] = 'gzip';
        $timeout = 120; // Longer timeout for AI generation
    }
    
    $response = wp_remote_post(TALKTOPC_API_URL . '/api/public/wordpress/agents', [
        'headers' => $headers,
        'body' => $body,
        'timeout' => $timeout
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    
    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        wp_send_json_error(['message' => $response_body['error'] ?? 'Failed to create agent']);
    }
    
    wp_send_json_success($response_body);
});

// =============================================================================
// UPDATE AGENT
// =============================================================================
add_action('wp_ajax_talktopc_update_agent', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $agent_id = isset($_POST['agent_id']) ? sanitize_text_field(wp_unslash($_POST['agent_id'])) : '';
    if (empty($agent_id)) wp_send_json_error(['message' => 'Agent ID required']);
    
    // Build update data - use camelCase keys to match backend expectations
    $update_data = [];
    
    if (isset($_POST['system_prompt']) && $_POST['system_prompt'] !== '') {
        $update_data['systemPrompt'] = sanitize_textarea_field(wp_unslash($_POST['system_prompt']));
    }
    if (isset($_POST['first_message']) && $_POST['first_message'] !== '') {
        $update_data['firstMessage'] = sanitize_text_field(wp_unslash($_POST['first_message']));
    }
    if (isset($_POST['voice_id']) && $_POST['voice_id'] !== '') {
        $update_data['voiceId'] = sanitize_text_field(wp_unslash($_POST['voice_id']));
    }
    if (isset($_POST['voice_speed']) && $_POST['voice_speed'] !== '') {
        $update_data['voiceSpeed'] = max(0.5, min(2.0, floatval($_POST['voice_speed'])));
    }
    if (isset($_POST['language']) && $_POST['language'] !== '') {
        $update_data['agentLanguage'] = sanitize_text_field(wp_unslash($_POST['language']));
    }
    if (isset($_POST['temperature']) && $_POST['temperature'] !== '') {
        $update_data['temperature'] = max(0.0, min(2.0, floatval($_POST['temperature'])));
    }
    if (isset($_POST['max_tokens']) && $_POST['max_tokens'] !== '') {
        $update_data['maxTokens'] = intval($_POST['max_tokens']);
    }
    if (isset($_POST['max_call_duration']) && $_POST['max_call_duration'] !== '') {
        $update_data['maxCallDuration'] = intval($_POST['max_call_duration']);
    }
    
    // Call Recording - always include, default to false if not set
    // Note: JavaScript always sends '1' or '0' as string, so we check for both
    $record_call = false;
    if (isset($_POST['record_call'])) {
        $record_call_value = sanitize_text_field(wp_unslash($_POST['record_call']));
        $record_call = ($record_call_value === '1' || $record_call_value === 1 || $record_call_value === true || $record_call_value === 'true');
    }
    // Always include recordCall in the update, even if false
    $update_data['recordCall'] = $record_call;
    
    // Build internalToolIds array
    $internal_tool_ids = [];
    
    // Add leave_message if enabled
    if (isset($_POST['enable_leave_message'])) {
        $enable_leave_message = sanitize_text_field(wp_unslash($_POST['enable_leave_message']));
        if ($enable_leave_message === '1' || $enable_leave_message === 1 || $enable_leave_message === true || $enable_leave_message === 'true') {
            $internal_tool_ids[] = 'leave_message';
        }
    }
    
    // Add visual tools if enabled
    if (isset($_POST['enable_visual_tools'])) {
        $enable_visual_tools = sanitize_text_field(wp_unslash($_POST['enable_visual_tools']));
        if ($enable_visual_tools === '1' || $enable_visual_tools === 1 || $enable_visual_tools === true || $enable_visual_tools === 'true') {
            if (isset($_POST['visual_tools_selection'])) {
                $visual_tools_selection_raw = sanitize_textarea_field(wp_unslash($_POST['visual_tools_selection']));
                $selected_tools = json_decode($visual_tools_selection_raw, true);
                if (is_array($selected_tools) && !empty($selected_tools)) {
                    // Sanitize each tool ID
                    $selected_tools = array_map('sanitize_text_field', $selected_tools);
                    $internal_tool_ids = array_merge($internal_tool_ids, $selected_tools);
                }
            }
        }
    }
    
    // Only include internalToolIds if there are tools to include
    if (!empty($internal_tool_ids)) {
        $update_data['internalToolIds'] = array_values(array_unique($internal_tool_ids));
    }
    
    // If no data to update, just return success
    if (empty($update_data)) {
        wp_send_json_success(['message' => 'No changes to save']);
    }
    
    $json_body = json_encode($update_data);
    
    $response = wp_remote_request(TALKTOPC_API_URL . '/api/public/wordpress/agents/' . $agent_id, [
        'method' => 'PUT',
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'body' => $json_body,
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        wp_send_json_error(['message' => isset($body['message']) ? $body['message'] : 'Failed to update agent', 'status' => $status_code]);
    }
    
    wp_send_json_success(['message' => 'Agent updated successfully', 'agent' => $body]);
});

// =============================================================================
// GENERATE PROMPT FROM SITE CONTENT
// =============================================================================
add_action('wp_ajax_talktopc_generate_prompt', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $api_key = get_option('talktopc_api_key');
    $site_content = talktopc_collect_site_content();
    
    $stats = [
        'pages' => count($site_content['pages']),
        'posts' => count($site_content['posts']),
        'products' => count($site_content['products']),
        'menu_items' => count($site_content['menus'])
    ];
    
    // Try backend AI generation if connected
    $ai_prompt = null;
    if (!empty($api_key)) {
        $json_payload = json_encode($site_content, JSON_UNESCAPED_UNICODE);
        $compressed_payload = gzencode($json_payload, 9);
        
        $response = wp_remote_post(TALKTOPC_API_URL . '/api/public/wordpress/generate-prompt', [
            'headers' => [
                'X-API-Key' => $api_key,
                'Content-Type' => 'application/json',
                'Content-Encoding' => 'gzip'
            ],
            'body' => $compressed_payload,
            'timeout' => 120
        ]);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($body['prompt'])) {
                $ai_prompt = $body['prompt'];
            }
        }
    }
    
    // Use AI prompt if available, otherwise generate locally
    if ($ai_prompt) {
        wp_send_json_success([
            'prompt' => $ai_prompt,
            'stats' => $stats,
            'source' => 'ai'
        ]);
        return;
    }
    
    // Fallback: Generate prompt locally
    $prompt = talktopc_generate_local_prompt($site_content);
    
    wp_send_json_success([
        'prompt' => $prompt,
        'stats' => $stats,
        'source' => 'local'
    ]);
});

// =============================================================================
// SAVE AGENT SELECTION
// =============================================================================
add_action('wp_ajax_talktopc_save_agent_selection', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $agent_id = isset($_POST['agent_id']) ? sanitize_text_field(wp_unslash($_POST['agent_id'])) : '';
    $agent_name = isset($_POST['agent_name']) ? sanitize_text_field(wp_unslash($_POST['agent_name'])) : '';
    
    if (empty($agent_id)) {
        wp_send_json_error(['message' => 'Agent ID is required']);
    }
    
    // Save the agent selection
    update_option('talktopc_agent_id', $agent_id);
    update_option('talktopc_agent_name', $agent_name);
    
    wp_send_json_success(['message' => 'Agent saved successfully', 'agent_id' => $agent_id]);
});

// =============================================================================
// SAVE AGENT SETTINGS LOCAL (WordPress options cache for fast UI)
// =============================================================================
add_action('wp_ajax_talktopc_save_agent_settings_local', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    // Save to WordPress options for fast UI loading
    if (isset($_POST['system_prompt'])) {
        update_option('talktopc_override_prompt', sanitize_textarea_field(wp_unslash($_POST['system_prompt'])));
    }
    if (isset($_POST['first_message'])) {
        update_option('talktopc_override_first_message', sanitize_text_field(wp_unslash($_POST['first_message'])));
    }
    if (isset($_POST['voice_id'])) {
        update_option('talktopc_override_voice', sanitize_text_field(wp_unslash($_POST['voice_id'])));
    }
    if (isset($_POST['voice_speed'])) {
        update_option('talktopc_override_voice_speed', sanitize_text_field(wp_unslash($_POST['voice_speed'])));
    }
    if (isset($_POST['language'])) {
        update_option('talktopc_override_language', sanitize_text_field(wp_unslash($_POST['language'])));
    }
    if (isset($_POST['temperature'])) {
        update_option('talktopc_override_temperature', sanitize_text_field(wp_unslash($_POST['temperature'])));
    }
    if (isset($_POST['max_tokens'])) {
        update_option('talktopc_override_max_tokens', sanitize_text_field(wp_unslash($_POST['max_tokens'])));
    }
    if (isset($_POST['max_call_duration'])) {
        update_option('talktopc_override_max_call_duration', sanitize_text_field(wp_unslash($_POST['max_call_duration'])));
    }
    
    // Call Recording & Tools - always save, default to '0' if not set
    $record_call_value = isset($_POST['record_call']) ? sanitize_text_field(wp_unslash($_POST['record_call'])) : '0';
    update_option('talktopc_record_call', talktopc_sanitize_checkbox($record_call_value));
    if (isset($_POST['enable_leave_message'])) {
        $enable_leave_message = sanitize_text_field(wp_unslash($_POST['enable_leave_message']));
        update_option('talktopc_enable_leave_message', talktopc_sanitize_checkbox($enable_leave_message));
    }
    if (isset($_POST['enable_visual_tools'])) {
        $enable_visual_tools = sanitize_text_field(wp_unslash($_POST['enable_visual_tools']));
        update_option('talktopc_enable_visual_tools', talktopc_sanitize_checkbox($enable_visual_tools));
    }
    if (isset($_POST['visual_tools_selection'])) {
        $visual_tools_selection_raw = sanitize_textarea_field(wp_unslash($_POST['visual_tools_selection']));
        $selection = json_decode($visual_tools_selection_raw, true);
        if (is_array($selection)) {
            // Sanitize each tool ID in the selection
            $selection = array_map('sanitize_text_field', $selection);
            update_option('talktopc_visual_tools_selection', wp_json_encode($selection));
        }
    }
    
    wp_send_json_success(['message' => 'Settings cached locally']);
});

// =============================================================================
// DISMISS FEATURE DISCOVERY BANNER
// =============================================================================
/**
 * Dismiss feature discovery banner
 */
add_action('wp_ajax_talktopc_dismiss_feature_banner', 'talktopc_dismiss_feature_banner');
function talktopc_dismiss_feature_banner() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }
    
    update_option('talktopc_feature_banner_dismissed', true);
    wp_send_json_success();
}

// =============================================================================
// HANDLE REVIEW REQUEST ACTIONS
// =============================================================================
/**
 * Handle review request actions
 */
add_action('wp_ajax_talktopc_review_action', 'talktopc_handle_review_action');

function talktopc_handle_review_action() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $type = sanitize_text_field(wp_unslash($_POST['type'] ?? ''));
    
    if ($type === 'done') {
        update_option('talktopc_review_done', true);
    } elseif ($type === 'later') {
        // Reset connected time to ask again in 30 days
        update_option('talktopc_connected_time', time() - (7 * DAY_IN_SECONDS) + (30 * DAY_IN_SECONDS));
    }
    
    wp_send_json_success();
}

// =============================================================================
// GET SIGNED URL (Public - also for non-logged-in users)
// =============================================================================
function talktopc_get_signed_url() {
    // Security:
    // - Nonce verified via frontend widget nonce
    // - Read-only: does NOT store or update credentials
    // - Required for frontend widget operation
    
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'talktopc_widget_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }
    
    $api_key = get_option('talktopc_api_key');
    $app_id = get_option('talktopc_app_id');
    
    // FIX: Get agent for current page (check page rules first)
    $agent_config = talktopc_get_agent_for_current_page_ajax();
    $agent_id = $agent_config['agent_id'];
    
    if (empty($api_key) || empty($agent_id)) wp_send_json_error(['message' => 'Widget not configured']);
    
    // If widget is disabled for this page, return error
    if ($agent_config['is_disabled']) {
        wp_send_json_error(['message' => 'Widget disabled for this page']);
    }
    
    // Check credits before allowing widget - if 0 credits, widget should not work
    $credits_response = wp_remote_get(TALKTOPC_API_URL . '/api/public/wordpress/credits', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 10
    ]);
    
    if (!is_wp_error($credits_response)) {
        $status_code = wp_remote_retrieve_response_code($credits_response);
        if ($status_code === 200) {
            $credits_body = json_decode(wp_remote_retrieve_body($credits_response), true);
            
            // Use remainingBrowserMinutes if available, fallback to credits
            $minutes = $credits_body['remainingBrowserMinutes'] ?? $credits_body['credits'] ?? $credits_body['balance'] ?? 0;
            
            // Parse if string
            if (is_string($minutes)) {
                $minutes = intval(str_replace(',', '', $minutes));
            }
            
            if ($minutes <= 0) {
                wp_send_json_error([
                    'message' => 'No credits available',
                    'code' => 'NO_CREDITS',
                    'credits' => 0
                ]);
            }
        }
    }
    
    $response = wp_remote_post(TALKTOPC_API_URL . '/api/public/agents/signed-url', [
        'headers' => ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode(['agentId' => $agent_id, 'appId' => $app_id, 'allowOverride' => false, 'expirationMs' => 3600000]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        wp_send_json_error(['message' => isset($body['message']) ? $body['message'] : 'Failed to get signed URL', 'code' => $status_code]);
    }
    
    wp_send_json_success(['signedUrl' => $body['signedLink']]);
}
// Frontend widget endpoint.
// Must support unauthenticated visitors (wp_ajax_nopriv_)
// because site visitors are not logged in.
add_action('wp_ajax_talktopc_get_signed_url', 'talktopc_get_signed_url');
add_action('wp_ajax_nopriv_talktopc_get_signed_url', 'talktopc_get_signed_url');

/**
 * Get agent for current page via AJAX (uses referer URL)
 * This is needed because AJAX requests don't have WordPress conditionals
 */
function talktopc_get_agent_for_current_page_ajax() {
    $rules = json_decode(get_option('talktopc_page_rules', '[]'), true);
    $default_agent_id = get_option('talktopc_agent_id', '');
    $default_agent_name = get_option('talktopc_agent_name', '');
    
    // Get the page URL from referer
    $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
    
    if (!empty($referer) && !empty($rules)) {
        // Try to get page ID from URL
        $page_id = url_to_postid($referer);
        
        if ($page_id) {
            $post = get_post($page_id);
            $post_type = $post ? $post->post_type : '';
            
            foreach ($rules as $rule) {
                $rule_type = $rule['type'] ?? '';
                $target_id = $rule['target_id'] ?? '';
                
                // Convert target_id to appropriate type
                if (in_array($rule_type, ['page', 'post', 'category', 'product_cat'])) {
                    $target_id = intval($target_id);
                }
                
                $matches = false;
                
                switch ($rule_type) {
                    case 'page':
                        $matches = ($post_type === 'page' && $page_id === $target_id);
                        break;
                    case 'post':
                        $matches = ($post_type === 'post' && $page_id === $target_id);
                        break;
                    case 'post_type':
                        $matches = ($post_type === $target_id);
                        break;
                    case 'category':
                        $matches = ($post_type === 'post' && has_category($target_id, $page_id));
                        break;
                    case 'product_cat':
                        $matches = ($post_type === 'product' && has_term($target_id, 'product_cat', $page_id));
                        break;
                }
                
                if ($matches) {
                    return [
                        'agent_id' => $rule['agent_id'],
                        'agent_name' => $rule['agent_name'] ?? '',
                        'is_disabled' => ($rule['agent_id'] === 'none')
                    ];
                }
            }
        }
    }
    
    return [
        'agent_id' => $default_agent_id,
        'agent_name' => $default_agent_name,
        'is_disabled' => ($default_agent_id === 'none' || empty($default_agent_id))
    ];
}

// =============================================================================
// GET PAGES LIST FOR MODAL
// =============================================================================
add_action('wp_ajax_talktopc_get_pages_list', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $pages = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC']);
    $pages_list = array_map(function($p) {
        return ['id' => $p->ID, 'name' => $p->post_title, 'type' => 'page', 'icon' => '📄'];
    }, $pages);
    
    $post_types = [
        ['id' => 'post', 'name' => 'All Blog Posts', 'type' => 'post_type', 'icon' => '📝', 'badge' => 'Post Type']
    ];
    if (class_exists('WooCommerce')) {
        $post_types[] = ['id' => 'product', 'name' => 'All Products', 'type' => 'post_type', 'icon' => '🛍️', 'badge' => 'WooCommerce'];
    }
    
    $categories = get_categories(['hide_empty' => false]);
    $cats_list = array_map(function($c) {
        return ['id' => $c->term_id, 'name' => $c->name, 'type' => 'category', 'icon' => '📁'];
    }, $categories);
    
    if (class_exists('WooCommerce')) {
        $product_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        foreach ($product_cats as $pc) {
            $cats_list[] = ['id' => $pc->term_id, 'name' => $pc->name, 'type' => 'product_cat', 'icon' => '🛍️', 'badge' => 'Product Cat'];
        }
    }
    
    wp_send_json_success(['pages' => $pages_list, 'post_types' => $post_types, 'categories' => $cats_list]);
});

// =============================================================================
// SAVE PAGE RULES
// =============================================================================
add_action('wp_ajax_talktopc_save_page_rules', function() {
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    $rules = isset($_POST['rules']) ? sanitize_text_field(wp_unslash($_POST['rules'])) : '[]';
    update_option('talktopc_page_rules', $rules);
    wp_send_json_success();
});

// =============================================================================
// AUTO-SETUP AGENT (AJAX - Non-blocking)
// =============================================================================
add_action('wp_ajax_talktopc_auto_setup_agent', 'talktopc_ajax_auto_setup_agent');

// =============================================================================
// TEST ENDPOINT - Verify file is deployed (Docker-friendly)
// =============================================================================
// Administrators only. This answers "is the deployed file current?", and the
// honest answer names the server's PHP and WordPress versions -- which is
// reconnaissance in the hands of anyone else. wp_ajax_ alone only requires a
// logged-in user, so on a WooCommerce or membership site every customer could
// read it.
//
// There is deliberately no nonce here, unlike the other handlers in this file.
// This endpoint has no side effect and exists to be called with curl during a
// deploy; a nonce would break that workflow to defend against an admin's own
// browser being made to fetch a version number it can already see in wp-admin.
// The capability check is the control that matters. Any handler added here that
// *writes* anything needs the nonce as well.
add_action('wp_ajax_talktopc_test_handler', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }

    // The absolute path is not reported. It identifies the hosting account and
    // directory layout, and says nothing about whether the file is current --
    // the modification time already answers that.
    $file_mtime = filemtime(__FILE__);
    wp_send_json_success([
        'message' => 'Handler file is deployed correctly!',
        'version' => TALKTOPC_VERSION,
        'timestamp' => time(),
        'file_mtime' => $file_mtime ? $file_mtime : 0,
        'file_mtime_formatted' => $file_mtime ? gmdate('Y-m-d H:i:s', $file_mtime) : 'Unknown',
        'php_version' => PHP_VERSION,
        'wordpress_version' => get_bloginfo('version')
    ]);
});

// =============================================================================
// SAVE WIDGET CUSTOMIZATION
// =============================================================================
add_action('wp_ajax_talktopc_save_widget_customization', function() {
    // Prevent any output before JSON
    @ob_clean();
    
    
    try {
        // Check nonce
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(['message' => 'Missing security token. Please refresh the page.', 'version' => '1.9.96-fixed-docker-v2']);
            return;
        }
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handles sanitization
        if (!wp_verify_nonce(wp_unslash($_POST['nonce']), 'talktopc_customization_nonce')) {
            wp_send_json_error(['message' => 'Security check failed. Please refresh the page and try again.', 'version' => '1.9.96-fixed-docker-v2']);
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized. You do not have permission to save settings.', 'version' => '1.9.96-fixed-docker-v2']);
            return;
        }
        
        // Save all widget settings from the form
        $settings_map = [
        // Button
        'talktopc_button_size' => 'sanitize_text_field',
        'talktopc_button_shape' => 'sanitize_text_field',
        'talktopc_button_bg_color' => 'sanitize_hex_color',
        'talktopc_button_hover_color' => 'sanitize_hex_color',
        'talktopc_button_shadow' => 'talktopc_sanitize_checkbox',
        'talktopc_button_shadow_color' => 'sanitize_text_field',
        
        // Icon
        'talktopc_icon_type' => 'sanitize_text_field',
        'talktopc_icon_custom_image' => 'esc_url_raw',
        'talktopc_icon_emoji' => 'sanitize_text_field',
        'talktopc_icon_text' => 'sanitize_text_field',
        'talktopc_icon_size' => 'sanitize_text_field',
        'talktopc_icon_bg_color' => 'sanitize_hex_color',
        
        // Panel
        'talktopc_panel_width' => 'absint',
        'talktopc_panel_height' => 'absint',
        'talktopc_panel_border_radius' => 'absint',
        'talktopc_panel_bg_color' => 'sanitize_hex_color',
        'talktopc_panel_border' => 'sanitize_text_field',
        'talktopc_panel_backdrop_filter' => 'sanitize_text_field',
        
        // Position
        'talktopc_position_offset_x' => 'absint',
        'talktopc_position_offset_y' => 'absint',
        
        // Header
        'talktopc_header_title' => 'sanitize_text_field',
        'talktopc_header_bg_color' => 'sanitize_hex_color',
        'talktopc_header_text_color' => 'sanitize_hex_color',
        'talktopc_header_show_title' => 'talktopc_sanitize_checkbox',
        'talktopc_header_show_close' => 'talktopc_sanitize_checkbox',
        
        // Footer
        'talktopc_footer_bg_color' => 'sanitize_hex_color',
        'talktopc_footer_text_color' => 'sanitize_hex_color',
        'talktopc_footer_hover_color' => 'sanitize_hex_color',
        
        // Messages
        'talktopc_msg_user_bg' => 'sanitize_hex_color',
        'talktopc_msg_agent_bg' => 'sanitize_hex_color',
        'talktopc_msg_system_bg' => 'sanitize_hex_color',
        'talktopc_msg_error_bg' => 'sanitize_hex_color',
        'talktopc_msg_text_color' => 'sanitize_hex_color',
        'talktopc_msg_font_size' => 'sanitize_text_field',
        'talktopc_msg_border_radius' => 'absint',
        
        // Text
        'talktopc_text_send_btn_text' => 'sanitize_text_field',
        'talktopc_text_send_btn_color' => 'sanitize_hex_color',
        'talktopc_text_send_btn_hover_color' => 'sanitize_hex_color',
        'talktopc_text_send_btn_text_color' => 'sanitize_hex_color',
        'talktopc_text_input_placeholder' => 'sanitize_text_field',
        'talktopc_text_input_focus_color' => 'sanitize_hex_color',
        
        // Button
        'talktopc_button_shadow' => 'talktopc_sanitize_checkbox',
        'talktopc_button_shadow_color' => 'sanitize_text_field',
        
        // Landing
        'talktopc_landing_title' => 'sanitize_text_field',
        'talktopc_landing_title_color' => 'sanitize_hex_color',
        'talktopc_landing_subtitle_color' => 'sanitize_hex_color',
        'talktopc_landing_logo' => 'sanitize_text_field',
        'talktopc_landing_logo_bg_color' => 'sanitize_hex_color',
        'talktopc_landing_voice_icon' => 'sanitize_text_field',
        'talktopc_landing_voice_title' => 'sanitize_text_field',
        'talktopc_landing_text_icon' => 'sanitize_text_field',
        'talktopc_landing_text_title' => 'sanitize_text_field',
        'talktopc_landing_card_bg_color' => 'sanitize_hex_color',
        
        // Voice
        'talktopc_voice_mic_color' => 'sanitize_hex_color',
        'talktopc_voice_mic_active_color' => 'sanitize_hex_color',
        'talktopc_voice_avatar_color' => 'sanitize_hex_color',
        'talktopc_voice_status_title_color' => 'sanitize_hex_color',
        'talktopc_voice_status_subtitle_color' => 'sanitize_hex_color',
        'talktopc_voice_start_title' => 'sanitize_text_field',
        'talktopc_voice_start_subtitle' => 'sanitize_text_field',
        'talktopc_voice_start_btn_text' => 'sanitize_text_field',
        'talktopc_voice_start_btn_color' => 'sanitize_hex_color',
        'talktopc_voice_start_btn_text_color' => 'sanitize_hex_color',
        'talktopc_voice_live_dot_color' => 'sanitize_hex_color',
        'talktopc_voice_live_text_color' => 'sanitize_hex_color',
        
        // Position & Direction
        'talktopc_position' => 'sanitize_text_field',
        'talktopc_direction' => 'sanitize_text_field',
        ];
        
        $saved = 0;
        $errors = [];
        
        foreach ($settings_map as $option_name => $sanitizer) {
            if (isset($_POST[$option_name])) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Value is sanitized below based on $sanitizer
                $value = wp_unslash($_POST[$option_name]);
                
                // Handle empty strings for optional fields
                // BUT: Allow empty placeholders and text fields (they can be intentionally empty)
                if ($value === '' && strpos($option_name, '_color') === false && 
                    strpos($option_name, '_placeholder') === false && 
                    strpos($option_name, '_text') === false && 
                    strpos($option_name, '_title') === false &&
                    strpos($option_name, '_desc') === false) {
                    // Skip empty non-color fields (they're optional)
                    continue;
                }
                
                try {
                    $sanitized_value = null;
                    
                    // Handle different sanitizer types
                    if ($sanitizer === 'sanitize_hex_color') {
                        // WordPress sanitize_hex_color returns false for invalid colors
                        // Load Customizer functions if needed
                        if (!function_exists('sanitize_hex_color')) {
                            if (file_exists(ABSPATH . 'wp-includes/class-wp-customize-manager.php')) {
                                require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
                            }
                        }
                        
                        if (function_exists('sanitize_hex_color')) {
                            $sanitized_value = sanitize_hex_color($value);
                            if ($sanitized_value === false || $sanitized_value === '') {
                                // Invalid hex color - skip it
                                continue;
                            }
                        } else {
                            // Fallback: validate hex color format
                            $value = trim($value);
                            if (preg_match('/^#[a-fA-F0-9]{6}$/', $value)) {
                                $sanitized_value = $value;
                            } elseif (preg_match('/^[a-fA-F0-9]{6}$/', $value)) {
                                // Missing # prefix, add it
                                $sanitized_value = '#' . $value;
                            } else {
                                // Invalid format - skip it
                                continue;
                            }
                        }
                    } elseif ($sanitizer === 'absint') {
                        $sanitized_value = absint($value);
                    } elseif ($sanitizer === 'esc_url_raw') {
                        $sanitized_value = esc_url_raw($value);
                    } elseif ($sanitizer === 'talktopc_sanitize_checkbox') {
                        $sanitized_value = ($value === '1' || $value === 'on' || $value === true) ? '1' : '0';
                    } elseif (is_callable($sanitizer)) {
                        $sanitized_value = call_user_func($sanitizer, $value);
                    } else {
                        $sanitized_value = sanitize_text_field($value);
                    }
                
                    // Only update if value is valid
                    if ($sanitized_value !== false && $sanitized_value !== null && $sanitized_value !== '') {
                        update_option($option_name, $sanitized_value);
                        $saved++;
                    }
                } catch (Exception $e) {
                    $errors[] = $option_name . ': ' . $e->getMessage();
                } catch (Error $e) {
                    $errors[] = $option_name . ': ' . $e->getMessage();
                }
            }
        }
        
        if (!empty($errors)) {
            wp_send_json_error([
                'message' => "Saved {$saved} settings, but encountered errors: " . implode(', ', $errors),
                'version' => '1.9.96-fixed-docker-v2'
            ]);
            return;
        }
        
        $file_mtime = filemtime(__FILE__);
        wp_send_json_success([
            'message' => "Saved {$saved} settings successfully",
            'version' => '1.9.96-fixed-docker-v2', // This confirms the updated file is deployed
            'saved_count' => $saved,
            'file_mtime' => $file_mtime,
            'file_mtime_formatted' => $file_mtime > 0 ? gmdate('Y-m-d H:i:s', $file_mtime) : 'Unknown'
        ]);
    } catch (Throwable $e) {
        $file_mtime = file_exists(__FILE__) ? filemtime(__FILE__) : 0;
        
        // Try to send error response
        if (function_exists('wp_send_json_error')) {
            wp_send_json_error([
                'message' => 'Server error: ' . $e->getMessage(),
                'version' => '1.9.96-fixed-docker-v2',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'file_mtime' => $file_mtime,
                'file_mtime_formatted' => $file_mtime > 0 ? gmdate('Y-m-d H:i:s', $file_mtime) : 'Unknown'
            ]);
        } else {
            // Fallback if wp_send_json_error doesn't exist
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'data' => [
                    'message' => 'Server error: ' . $e->getMessage(),
                    'version' => '1.9.96-fixed-docker-v2',
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            exit;
        }
    }
});

// =============================================================================
// SAVE WIDGET CUSTOMIZATION2
// =============================================================================
add_action('wp_ajax_talktopc_save_widget_customization2', function() {
    // Prevent any output before JSON
    @ob_clean();
    
    try {
        // Check nonce
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(['message' => 'Missing security token. Please refresh the page.']);
            return;
        }
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handles sanitization
        if (!wp_verify_nonce(wp_unslash($_POST['nonce']), 'talktopc_customization2_nonce')) {
            wp_send_json_error(['message' => 'Security check failed. Please refresh the page and try again.']);
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized. You do not have permission to save settings.']);
            return;
        }
        
        // Save all widget settings from the form (same settings map as Customization)
        $settings_map = [
        // Button
        'talktopc_button_size' => 'sanitize_text_field',
        'talktopc_button_shape' => 'sanitize_text_field',
        'talktopc_button_bg_color' => 'sanitize_hex_color',
        'talktopc_button_hover_color' => 'sanitize_hex_color',
        'talktopc_button_shadow' => 'talktopc_sanitize_checkbox',
        'talktopc_button_shadow_color' => 'sanitize_text_field',
        
        // Icon
        'talktopc_icon_type' => 'sanitize_text_field',
        'talktopc_icon_custom_image' => 'esc_url_raw',
        'talktopc_icon_emoji' => 'sanitize_text_field',
        'talktopc_icon_text' => 'sanitize_text_field',
        'talktopc_icon_size' => 'sanitize_text_field',
        'talktopc_icon_bg_color' => 'sanitize_hex_color',
        
        // Panel
        'talktopc_panel_width' => 'absint',
        'talktopc_panel_height' => 'absint',
        'talktopc_panel_border_radius' => 'absint',
        'talktopc_panel_bg_color' => 'sanitize_hex_color',
        'talktopc_panel_border' => 'sanitize_text_field',
        'talktopc_panel_backdrop_filter' => 'sanitize_text_field',
        
        // Position
        'talktopc_position_offset_x' => 'absint',
        'talktopc_position_offset_y' => 'absint',
        
        // Header
        'talktopc_header_title' => 'sanitize_text_field',
        'talktopc_header_bg_color' => 'sanitize_hex_color',
        'talktopc_header_text_color' => 'sanitize_hex_color',
        'talktopc_header_show_title' => 'talktopc_sanitize_checkbox',
        'talktopc_header_show_close' => 'talktopc_sanitize_checkbox',
        
        // Footer
        'talktopc_footer_bg_color' => 'sanitize_hex_color',
        'talktopc_footer_text_color' => 'sanitize_hex_color',
        'talktopc_footer_hover_color' => 'sanitize_hex_color',
        
        // Messages
        'talktopc_msg_user_bg' => 'sanitize_hex_color',
        'talktopc_msg_agent_bg' => 'sanitize_hex_color',
        'talktopc_msg_system_bg' => 'sanitize_hex_color',
        'talktopc_msg_error_bg' => 'sanitize_hex_color',
        'talktopc_msg_text_color' => 'sanitize_hex_color',
        'talktopc_msg_font_size' => 'sanitize_text_field',
        'talktopc_msg_border_radius' => 'absint',
        
        // Text
        'talktopc_text_send_btn_text' => 'sanitize_text_field',
        'talktopc_text_send_btn_color' => 'sanitize_hex_color',
        'talktopc_text_send_btn_hover_color' => 'sanitize_hex_color',
        'talktopc_text_send_btn_text_color' => 'sanitize_hex_color',
        'talktopc_text_input_placeholder' => 'sanitize_text_field',
        'talktopc_text_input_focus_color' => 'sanitize_hex_color',
        
        // Landing
        'talktopc_landing_title' => 'sanitize_text_field',
        'talktopc_landing_title_color' => 'sanitize_hex_color',
        'talktopc_landing_subtitle_color' => 'sanitize_hex_color',
        'talktopc_landing_logo' => 'sanitize_text_field',
        'talktopc_landing_logo_bg_color' => 'sanitize_hex_color',
        'talktopc_landing_voice_icon' => 'sanitize_text_field',
        'talktopc_landing_voice_title' => 'sanitize_text_field',
        'talktopc_landing_text_icon' => 'sanitize_text_field',
        'talktopc_landing_text_title' => 'sanitize_text_field',
        'talktopc_landing_card_bg_color' => 'sanitize_hex_color',
        
        // Voice
        'talktopc_voice_mic_color' => 'sanitize_hex_color',
        'talktopc_voice_mic_active_color' => 'sanitize_hex_color',
        'talktopc_voice_avatar_color' => 'sanitize_hex_color',
        'talktopc_voice_status_title_color' => 'sanitize_hex_color',
        'talktopc_voice_status_subtitle_color' => 'sanitize_hex_color',
        'talktopc_voice_start_title' => 'sanitize_text_field',
        'talktopc_voice_start_subtitle' => 'sanitize_text_field',
        'talktopc_voice_start_btn_text' => 'sanitize_text_field',
        'talktopc_voice_start_btn_color' => 'sanitize_hex_color',
        'talktopc_voice_start_btn_text_color' => 'sanitize_hex_color',
        'talktopc_voice_live_dot_color' => 'sanitize_hex_color',
        'talktopc_voice_live_text_color' => 'sanitize_hex_color',
        'talktopc_voice_pill_gradient' => 'sanitize_text_field',
        'talktopc_voice_pill_text_color' => 'sanitize_hex_color',
        'talktopc_voice_pill_dot_color' => 'sanitize_hex_color',
        'talktopc_voice_avatar_gradient1' => 'sanitize_hex_color',
        'talktopc_voice_avatar_gradient2' => 'sanitize_hex_color',
        'talktopc_voice_online_dot_color' => 'sanitize_hex_color',
        'talktopc_voice_hero_gradient1' => 'sanitize_hex_color',
        'talktopc_voice_hero_gradient2' => 'sanitize_hex_color',
        'talktopc_voice_agent_name_color' => 'sanitize_hex_color',
        'talktopc_voice_agent_role_color' => 'sanitize_text_field',
        'talktopc_voice_headline_color' => 'sanitize_hex_color',
        'talktopc_voice_subline_color' => 'sanitize_text_field',
        'talktopc_voice_primary_btn_gradient1' => 'sanitize_hex_color',
        'talktopc_voice_primary_btn_gradient2' => 'sanitize_hex_color',
        'talktopc_voice_secondary_btn_bg' => 'sanitize_text_field',
        'talktopc_voice_secondary_btn_border' => 'sanitize_text_field',
        'talktopc_voice_secondary_btn_text_color' => 'sanitize_text_field',
        'talktopc_voice_waveform_bar_color' => 'sanitize_hex_color',
        'talktopc_voice_send_message_text' => 'sanitize_text_field',
        'talktopc_voice_agent_role' => 'sanitize_text_field',
        'talktopc_voice_headline' => 'sanitize_text_field',
        'talktopc_voice_subline' => 'sanitize_text_field',
        'talktopc_voice_speaker_color' => 'sanitize_hex_color',
        'talktopc_voice_live_indicator_text_color' => 'sanitize_hex_color',
        'talktopc_voice_live_indicator_dot_color' => 'sanitize_hex_color',
        'talktopc_voice_live_transcript_text_color' => 'sanitize_hex_color',
        'talktopc_voice_live_transcript_font_size' => 'sanitize_text_field',
        'talktopc_agent_name' => 'sanitize_text_field',
        
        // Position & Direction
        'talktopc_position' => 'sanitize_text_field',
        'talktopc_direction' => 'sanitize_text_field',
        ];
        
        $saved = 0;
        $errors = [];
        
        foreach ($settings_map as $option_name => $sanitizer) {
            if (isset($_POST[$option_name])) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Value is sanitized below based on $sanitizer
                $value = wp_unslash($_POST[$option_name]);
                
                // Handle empty strings for optional fields
                if ($value === '' && strpos($option_name, '_color') === false && 
                    strpos($option_name, '_placeholder') === false && 
                    strpos($option_name, '_text') === false && 
                    strpos($option_name, '_title') === false &&
                    strpos($option_name, '_desc') === false) {
                    continue;
                }
                
                try {
                    $sanitized_value = null;
                    
                    // Handle different sanitizer types
                    if ($sanitizer === 'sanitize_hex_color') {
                        if (!function_exists('sanitize_hex_color')) {
                            if (file_exists(ABSPATH . 'wp-includes/class-wp-customize-manager.php')) {
                                require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
                            }
                        }
                        
                        if (function_exists('sanitize_hex_color')) {
                            $sanitized_value = sanitize_hex_color($value);
                            if ($sanitized_value === false || $sanitized_value === '') {
                                continue;
                            }
                        } else {
                            $value = trim($value);
                            if (preg_match('/^#[a-fA-F0-9]{6}$/', $value)) {
                                $sanitized_value = $value;
                            } elseif (preg_match('/^[a-fA-F0-9]{6}$/', $value)) {
                                $sanitized_value = '#' . $value;
                            } else {
                                continue;
                            }
                        }
                    } elseif ($sanitizer === 'absint') {
                        $sanitized_value = absint($value);
                    } elseif ($sanitizer === 'esc_url_raw') {
                        $sanitized_value = esc_url_raw($value);
                    } elseif ($sanitizer === 'talktopc_sanitize_checkbox') {
                        $sanitized_value = ($value === '1' || $value === 'on' || $value === true) ? '1' : '0';
                    } elseif (is_callable($sanitizer)) {
                        $sanitized_value = call_user_func($sanitizer, $value);
                    } else {
                        $sanitized_value = sanitize_text_field($value);
                    }
                
                    // Only update if value is valid
                    if ($sanitized_value !== false && $sanitized_value !== null && $sanitized_value !== '') {
                        update_option($option_name, $sanitized_value);
                        $saved++;
                    }
                } catch (Exception $e) {
                    $errors[] = $option_name . ': ' . $e->getMessage();
                } catch (Error $e) {
                    $errors[] = $option_name . ': ' . $e->getMessage();
                }
            }
        }
        
        if (!empty($errors)) {
            wp_send_json_error([
                'message' => "Saved {$saved} settings, but encountered errors: " . implode(', ', $errors)
            ]);
            return;
        }
        
        wp_send_json_success([
            'message' => "Saved {$saved} settings successfully",
            'saved_count' => $saved
        ]);
    } catch (Throwable $e) {
        wp_send_json_error([
            'message' => 'Server error: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ]);
    }
});

// =============================================================================
// GET WIDGET CONFIG (For reloading after save)
// =============================================================================
add_action('wp_ajax_talktopc_get_widget_config', function() {
    check_ajax_referer('talktopc_customization_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    // Get the widget config using the same function that builds it for the customization page
    require_once plugin_dir_path(__FILE__) . 'admin-pages/customization2.php';
    
    $config = talktopc_get_all_widget_settings();
    
    wp_send_json_success($config);
});

function talktopc_ajax_auto_setup_agent() {
    // Verify nonce
    check_ajax_referer('talktopc_ajax_nonce', 'nonce');
    
    // Verify capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    // Get API key
    $api_key = get_option('talktopc_api_key');
    if (empty($api_key)) {
        wp_send_json_error(['message' => 'Not connected']);
        return;
    }
    
    // Run agent setup
    if (function_exists('talktopc_auto_setup_agent')) {
        // Set transient BEFORE calling agent setup so polling can detect it
        // This ensures the popup overlay stays visible during agent creation
        set_transient('talktopc_agent_creating', true, 180);
        
        // Call agent setup (this will also set/clear the transient internally)
        $result = talktopc_auto_setup_agent($api_key);
        
        if (isset($result['error'])) {
            // Clear transient on error
            delete_transient('talktopc_agent_creating');
            wp_send_json_error(['message' => $result['error']]);
        } else {
            // If agent was created, transient will be cleared by talktopc_auto_setup_agent()
            // If agent already existed, clear it here
            if (!($result['agent_created'] ?? false)) {
                delete_transient('talktopc_agent_creating');
            }
            wp_send_json_success([
                'agent_id' => $result['agent_id'],
                'agent_name' => $result['agent_name'],
                'created' => $result['agent_created'] ?? false
            ]);
        }
    } else {
        wp_send_json_error(['message' => 'Setup function not found']);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Detect the primary language of text content based on character ranges
 * Returns locale code (e.g., 'he_IL', 'ru_RU', 'en_US')
 */
function talktopc_detect_content_language($text) {
    if (empty($text)) {
        return get_locale();
    }
    
    // Count characters in different scripts
    $hebrew_count = preg_match_all('/[\x{0590}-\x{05FF}]/u', $text);
    $cyrillic_count = preg_match_all('/[\x{0400}-\x{04FF}]/u', $text);
    $arabic_count = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
    $greek_count = preg_match_all('/[\x{0370}-\x{03FF}]/u', $text);
    $thai_count = preg_match_all('/[\x{0E00}-\x{0E7F}]/u', $text);
    $japanese_count = preg_match_all('/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $text); // Hiragana + Katakana
    $korean_count = preg_match_all('/[\x{AC00}-\x{D7AF}\x{1100}-\x{11FF}]/u', $text); // Hangul
    $chinese_count = preg_match_all('/[\x{4E00}-\x{9FFF}]/u', $text); // CJK
    $latin_count = preg_match_all('/[a-zA-Z]/u', $text);
    
    // Find the dominant script
    $counts = [
        'he_IL' => $hebrew_count,
        'ru_RU' => $cyrillic_count,
        'ar_SA' => $arabic_count,
        'el_GR' => $greek_count,
        'th_TH' => $thai_count,
        'ja_JP' => $japanese_count,
        'ko_KR' => $korean_count,
        'zh_CN' => $chinese_count,
        'en_US' => $latin_count
    ];
    
    // Get max count
    $max_lang = 'en_US';
    $max_count = 0;
    
    foreach ($counts as $lang => $count) {
        if ($count > $max_count) {
            $max_count = $count;
            $max_lang = $lang;
        }
    }
    
    // If Latin is dominant, use WordPress locale for specific language (fr, de, es, etc.)
    if ($max_lang === 'en_US') {
        return get_locale();
    }
    
    return $max_lang;
}

/**
 * Collect site content for AI prompt generation
 */
function talktopc_collect_site_content() {
    // Collect site info (language will be detected from content below)
    $site_content = [
        'site' => [
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => home_url(),
            'language' => get_locale() // Will be overwritten with detected language
        ],
        'pages' => [],
        'posts' => [],
        'products' => [],
        'menus' => [],
        'currency' => '$'
    ];
    
    // Build sample text for language detection
    $sample_text = $site_content['site']['name'] . ' ' . $site_content['site']['description'];
    
    // Get pages
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => 50,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ]);
    
    foreach ($pages as $page) {
        $content = wp_strip_all_tags($page->post_content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (!empty($content)) {
            $site_content['pages'][] = [
                'title' => $page->post_title,
                'content' => mb_substr($content, 0, 2000)
            ];
            // Add to sample text for language detection
            $sample_text .= ' ' . $page->post_title . ' ' . mb_substr($content, 0, 500);
        }
    }
    
    // Get posts
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 10,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    foreach ($posts as $post) {
        $content = wp_strip_all_tags($post->post_content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (!empty($content)) {
            $site_content['posts'][] = [
                'title' => $post->post_title,
                'excerpt' => mb_substr($content, 0, 500)
            ];
            // Add to sample text for language detection
            $sample_text .= ' ' . $post->post_title;
        }
    }
    
    // Get WooCommerce products
    if (class_exists('WooCommerce')) {
        $site_content['currency'] = get_woocommerce_currency_symbol();
        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => 100,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        foreach ($products as $product) {
            $wc_product = wc_get_product($product->ID);
            if ($wc_product) {
                $site_content['products'][] = [
                    'name' => $product->post_title,
                    'price' => $wc_product->get_price(),
                    'description' => mb_substr(wp_strip_all_tags($product->post_content), 0, 300),
                    'in_stock' => $wc_product->is_in_stock()
                ];
                // Add to sample text for language detection
                $sample_text .= ' ' . $product->post_title;
            }
        }
    }
    
    // Get menus
    $menu_locations = get_nav_menu_locations();
    foreach ($menu_locations as $location => $menu_id) {
        if ($menu_id) {
            $menu_items = wp_get_nav_menu_items($menu_id);
            if ($menu_items) {
                foreach ($menu_items as $item) {
                    $site_content['menus'][] = $item->title;
                    // Add to sample text for language detection
                    $sample_text .= ' ' . $item->title;
                }
            }
        }
    }
    $site_content['menus'] = array_unique(array_values($site_content['menus']));
    
    // Detect actual content language from collected text
    $detected_language = talktopc_detect_content_language($sample_text);
    $site_content['site']['language'] = $detected_language;
    
    // Ensure all arrays are indexed (not associative) for proper JSON encoding
    // This prevents PHP from sending {} instead of [] when keys are non-sequential
    $site_content['pages'] = array_values($site_content['pages']);
    $site_content['posts'] = array_values($site_content['posts']);
    $site_content['products'] = array_values($site_content['products']);
    $site_content['menus'] = array_values($site_content['menus']);
    
    return $site_content;
}

/**
 * Generate a local prompt (fallback when AI generation fails)
 */
function talktopc_generate_local_prompt($site_content) {
    $site_name = $site_content['site']['name'];
    $site_description = $site_content['site']['description'];
    $currency_symbol = $site_content['currency'];
    $pages_content = $site_content['pages'];
    $posts_content = $site_content['posts'];
    $products_content = $site_content['products'];
    $menus = $site_content['menus'];
    
    $prompt = "You are a helpful voice assistant for {$site_name}";
    if (!empty($site_description)) {
        $prompt .= " - {$site_description}";
    }
    $prompt .= ".\n\n";
    
    $prompt .= "Your role is to assist visitors with questions about the website, its services, and content. Be friendly, professional, and helpful.\n\n";
    
    // Add pages context
    if (!empty($pages_content)) {
        $prompt .= "=== WEBSITE PAGES ===\n";
        foreach ($pages_content as $page) {
            $prompt .= "\n## {$page['title']}\n";
            $prompt .= "{$page['content']}\n";
        }
        $prompt .= "\n";
    }
    
    // Add products context if WooCommerce
    if (!empty($products_content)) {
        $prompt .= "=== PRODUCTS ===\n";
        foreach ($products_content as $product) {
            $prompt .= "- {$product['name']}";
            if (!empty($product['price'])) {
                $prompt .= " ({$currency_symbol}{$product['price']})";
            }
            if (!empty($product['description'])) {
                $prompt .= ": {$product['description']}";
            }
            $prompt .= "\n";
        }
        $prompt .= "\n";
    }
    
    // Add blog posts context
    if (!empty($posts_content)) {
        $prompt .= "=== RECENT BLOG POSTS ===\n";
        foreach ($posts_content as $post) {
            $prompt .= "- {$post['title']}: {$post['excerpt']}\n";
        }
        $prompt .= "\n";
    }
    
    // Add navigation context
    if (!empty($menus)) {
        $prompt .= "=== MAIN NAVIGATION ===\n";
        $prompt .= "The website has these main sections: " . implode(', ', array_slice($menus, 0, 15)) . "\n\n";
    }
    
    // Add instructions
    $prompt .= "=== INSTRUCTIONS ===\n";
    $prompt .= "- Answer questions based on the website content above\n";
    $prompt .= "- If asked about something not covered, politely say you don't have that information and suggest contacting the website directly\n";
    $prompt .= "- Keep responses concise and conversational since this is a voice interface\n";
    $prompt .= "- Direct visitors to relevant pages when appropriate\n";
    $prompt .= "- Be warm and welcoming to new visitors\n";
    
    return $prompt;
}

// =============================================================================
// SAVE ECOMMERCE SETTINGS
// =============================================================================
add_action('wp_ajax_talktopc_save_ecommerce', function() {
    check_ajax_referer('talktopc_save_ecommerce', '_wpnonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $enabled = isset($_POST['ecommerce_enabled']) && $_POST['ecommerce_enabled'] === '1' ? '1' : '0';
    $domain  = isset($_POST['shopify_domain']) ? sanitize_text_field(wp_unslash($_POST['shopify_domain'])) : '';

    update_option('talktopc_ecommerce_enabled', $enabled);
    update_option('talktopc_shopify_domain', $domain);

    wp_send_json_success(['ecommerce_enabled' => $enabled, 'shopify_domain' => $domain]);
});

// =============================================================================
// SAVE PARTNER EMBED SLUG
// One option only, so the rest of the plugin settings stay as they are.
// =============================================================================
add_action('wp_ajax_talktopc_save_embed_slug', function() {
    check_ajax_referer('talktopc_save_embed_slug', '_wpnonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $raw = isset($_POST['embed_slug']) ? wp_unslash($_POST['embed_slug']) : '';
    $slug = talktopc_normalize_embed_slug($raw);
    if ($slug === false) {
        wp_send_json_error('English letters and dashes only.');
    }

    update_option('talktopc_embed_slug', $slug);
    wp_send_json_success([
        'slug' => $slug,
        'src' => talktopc_embed_script_url($slug),
    ]);
});
