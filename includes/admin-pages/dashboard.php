<?php
/**
 * Dashboard Page
 * 
 * FIXES:
 * - Issue #1: Show saved agent immediately from PHP, then update from AJAX
 * - Issue #3: Better aligned Agent Settings UI with CSS grid
 */

if (!defined('ABSPATH')) exit;

function talktopc_render_embed_card() {
    $slug = (string) get_option('talktopc_embed_slug', '');
    $src = talktopc_embed_script_url($slug);
    ?>
    <div class="card">
        <h2><span class="icon">📄</span> Partner script</h2>
        <p style="color: #646970; margin-bottom: 15px; font-size: 13px;">For registered partners only. Use an existing specific script.</p>
        <table class="form-table" style="margin-top: 0;">
            <tr>
                <th scope="row"><label for="talktopc_embed_slug">Script name</label></th>
                <td>
                    <input type="text" id="talktopc_embed_slug" class="regular-text" value="<?php echo esc_attr($slug); ?>" autocomplete="off" spellcheck="false" <?php disabled($src !== ''); ?>>
                    <p class="description" id="talktopc_embed_preview"><?php echo $src !== '' ? esc_html($src) : 'Nothing is added until you save a name.'; ?></p>
                </td>
            </tr>
        </table>
        <div style="margin-top: 10px;">
            <button type="button" class="button button-primary" id="talktopc_save_embed" <?php echo $src !== '' ? 'style="display:none;"' : ''; ?>>Save script name</button>
            <button type="button" class="button" id="talktopc_edit_embed" <?php echo $src === '' ? 'style="display:none;"' : ''; ?>>Edit</button>
            <button type="button" class="button" id="talktopc_cancel_embed" style="display:none;">Cancel</button>
            <button type="button" class="button button-link-delete" id="talktopc_remove_embed" <?php echo $src === '' ? 'style="display:none;"' : ''; ?>>Remove script</button>
            <span id="talktopc_embed_save_status" style="margin-left: 10px;"></span>
        </div>
    </div>
    <script>
    (function() {
        var input = document.getElementById('talktopc_embed_slug');
        var preview = document.getElementById('talktopc_embed_preview');
        var saveBtn = document.getElementById('talktopc_save_embed');
        var editBtn = document.getElementById('talktopc_edit_embed');
        var cancelBtn = document.getElementById('talktopc_cancel_embed');
        var removeBtn = document.getElementById('talktopc_remove_embed');
        var status = document.getElementById('talktopc_embed_save_status');
        if (!input || !preview || !saveBtn || !editBtn || !cancelBtn || !removeBtn) return;

        var saved = input.value;

        function wordFrom(value) {
            value = value.trim();
            if (/\.js$/i.test(value)) value = value.slice(0, -3);
            return value;
        }

        function refreshPreview() {
            var word = wordFrom(input.value);
            if (word === '') {
                preview.textContent = 'Nothing is added until you save a name.';
                return;
            }
            if (!/^[A-Za-z]+(?:-[A-Za-z]+){0,8}$/.test(word) || word.length > 64) {
                preview.textContent = 'English letters and dashes only.';
                return;
            }
            preview.textContent = 'https://cdn.talktopc.com/' + word + '.js';
        }

        function showStatus(text, ok) {
            status.textContent = text;
            status.style.color = ok ? '#00a32a' : '#d63638';
            setTimeout(function() { status.textContent = ''; }, 4000);
        }

        function showLocked() {
            input.value = saved;
            input.disabled = saved !== '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            editBtn.style.display = '';
            removeBtn.style.display = '';
            refreshPreview();
        }

        function showEmpty() {
            saved = '';
            input.value = '';
            input.disabled = false;
            saveBtn.style.display = '';
            cancelBtn.style.display = 'none';
            editBtn.style.display = 'none';
            removeBtn.style.display = 'none';
            refreshPreview();
        }

        function showEditing() {
            input.disabled = false;
            input.focus();
            saveBtn.style.display = '';
            cancelBtn.style.display = '';
            editBtn.style.display = 'none';
            removeBtn.style.display = '';
        }

        function postSlug(value) {
            status.textContent = 'Saving...';
            status.style.color = '#646970';
            var formData = new FormData();
            formData.append('action', 'talktopc_save_embed_slug');
            formData.append('embed_slug', value);
            formData.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('talktopc_save_embed_slug')); ?>');
            return fetch(ajaxurl, { method: 'POST', body: formData }).then(function(r) { return r.json(); });
        }

        input.addEventListener('input', refreshPreview);

        editBtn.addEventListener('click', showEditing);

        cancelBtn.addEventListener('click', function() {
            showLocked();
        });

        saveBtn.addEventListener('click', function() {
            var word = wordFrom(input.value);
            if (word !== '' && (!/^[A-Za-z]+(?:-[A-Za-z]+){0,8}$/.test(word) || word.length > 64)) {
                showStatus('English letters and dashes only.', false);
                return;
            }
            postSlug(input.value.trim()).then(function(data) {
                if (!data.success) {
                    showStatus(data.data || 'Could not save.', false);
                    return;
                }
                saved = data.data.slug || '';
                if (saved) {
                    showLocked();
                    showStatus('Saved.', true);
                } else {
                    showEmpty();
                    showStatus('Removed.', true);
                }
            }).catch(function() {
                showStatus('Network error', false);
            });
        });

        removeBtn.addEventListener('click', function() {
            if (!window.confirm('Remove this script from the site?')) return;
            postSlug('').then(function(data) {
                if (!data.success) {
                    showStatus(data.data || 'Could not remove.', false);
                    return;
                }
                showEmpty();
                showStatus('Removed.', true);
            }).catch(function() {
                showStatus('Network error', false);
            });
        });
    })();
    </script>
    <?php
}

function talktopc_render_dashboard_page() {
    $is_connected = !empty(get_option('talktopc_api_key'));
    $user_email = get_option('talktopc_user_email', '');
    $current_agent_id = get_option('talktopc_agent_id', '');
    $current_agent_name = get_option('talktopc_agent_name', '');
    
    // Build OAuth URLs
    $connect_url = admin_url('admin-post.php?action=talktopc_connect');
    $connect_url = wp_nonce_url($connect_url, 'talktopc_connect_action');
    $disconnect_url = wp_nonce_url(admin_url('admin.php?page=talktopc&action=disconnect'), 'talktopc_disconnect');
    
    // Enqueue WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Styles and scripts are now enqueued via admin_enqueue_scripts hook
    // No need to call render functions here
    ?>
    <div class="wrap talktopc-admin-wrap">
        <div class="wp-header">
            <h1>Dashboard</h1>
            <span class="version">v<?php echo esc_html(TALKTOPC_VERSION); ?></span>
        </div>
        
        <?php settings_errors(); ?>
        
        <?php 
        // Check if agent setup is needed (from OAuth callback)
        $needs_agent_setup = false;
        if ($is_connected) {
            $needs_agent_setup = get_transient('talktopc_needs_agent_setup');
            if ($needs_agent_setup) {
                delete_transient('talktopc_needs_agent_setup');
            }
        }
        ?>
        
        <?php if ($is_connected): ?>
            <!-- Credits Box - Dynamic states based on credit amount -->
            <div class="credits-box" id="talktopcCreditsBox">
                <div class="credits-left">
                    <div class="credits-icon" id="talktopcCreditsIcon">💳</div>
                    <div class="credits-info">
                        <h3 id="talktopcCreditsTitle">Available Credits</h3>
                        <div class="credits-amount">
                            <span class="amount" id="talktopcCreditsAmount">
                                <span class="spinner"></span> Loading...
                            </span>
                            <span class="unit" id="talktopcCreditsUnit"></span>
                        </div>
                        <div class="credits-label" id="talktopcCreditsLabel">Checking your account...</div>
                        <div class="credits-warning" id="talktopcCreditsWarning"></div>
                    </div>
                </div>
                <div class="credits-right">
                    <a href="https://talktopc.com/upgrade" target="_blank" class="button" id="talktopcCreditsButton">Buy More →</a>
                    <div class="credits-hint" id="talktopcCreditsHint"></div>
                </div>
            </div>
            
            <!-- Connection Status -->
            <div class="card">
                <h2><span class="icon">🔗</span> Account</h2>
                <div class="status-connected">
                    <span class="status-dot"></span>
                    <span>Connected</span>
                    <?php if ($user_email): ?><span class="status-email">• <?php echo esc_html($user_email); ?></span><?php endif; ?>
                    <a href="<?php echo esc_url($disconnect_url); ?>" class="button-link-delete" style="margin-left: auto;">Disconnect</a>
                </div>
            </div>
            
            <?php talktopc_render_embed_card(); ?>
            
            <!-- Default Agent -->
            <div class="card">
                <h2><span class="icon">🤖</span> Your AI Agent</h2>
                
                <div id="agentSelectorArea">
                <div class="agent-selector-big">
                    <label>Select Agent</label>
                    <select id="defaultAgentSelect" name="talktopc_agent_id">
                        <option value="">-- Select Agent --</option>
                        <?php 
                        // FIX #1: Show saved agent immediately from PHP
                        if ($current_agent_id && $current_agent_id !== 'none'): 
                        ?>
                            <option value="<?php echo esc_attr($current_agent_id); ?>" selected>
                                <?php echo esc_html($current_agent_name ?: 'Loading...'); ?>
                            </option>
                        <?php elseif ($current_agent_id === 'none'): ?>
                            <option value="none" selected>🚫 Widget Disabled</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($current_agent_id && $current_agent_id !== 'none'): ?>
                        <span class="agent-loading-indicator" id="agentLoadingIndicator">
                            <span class="spinner"></span> Syncing agents...
                        </span>
                    <?php endif; ?>
                    <p class="description">
                        This agent appears on all pages of your website.
                        <a href="<?php echo esc_url(admin_url('admin.php?page=talktopc-page-rules')); ?>">Need different agents on different pages? →</a>
                    </p>
                </div>
                
                <!-- Agent Settings (collapsible) -->
                <div class="agent-settings <?php echo ($current_agent_id && $current_agent_id !== 'none') ? '' : 'collapsed'; ?>" id="agentSettings">
                    <div class="agent-settings-header">
                        <div class="header-left" onclick="toggleAgentSettings()">
                            <h3><span>⚙️</span> Agent Settings</h3>
                            <span class="arrow">▼</span>
                        </div>
                        <div class="header-right">
                            <button type="button" class="button button-primary button-large-edit" id="editAgentSettingsBtn">
                                <span class="edit-icon">✏️</span>
                                <span class="edit-text-btn">Edit Settings</span>
                            </button>
                            <button type="button" class="button button-small" id="cancelEditBtn" style="display: none;">✖️ Cancel</button>
                        </div>
                    </div>
                    
                    <!-- View Mode Notice Banner -->
                    <div class="view-mode-notice" id="viewModeNotice">
                        <div class="notice-content">
                            <span class="notice-icon">🔒</span>
                            <div class="notice-text">
                                <strong>View Mode</strong>
                                <span>Click "Edit Settings" above to modify these settings</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Mode Banner -->
                    <div class="edit-mode-banner" id="editModeBanner" style="display: none;">
                        <span class="edit-indicator">✏️</span>
                        <span class="edit-text">You are editing agent settings</span>
                        <span class="edit-hint">Make your changes below, then click Save</span>
                    </div>
                    
                    <div class="agent-settings-body" data-tooltip="Click 'Edit Settings' above to modify these settings">
                        <!-- Settings saved via AJAX to both WordPress (cache) and TalkToPC backend (DB) -->
                        <div id="agentSettingsForm" class="agent-settings-form">
                            
                            <!-- FIX #3: Better aligned form layout -->
                            <div class="agent-form-grid">
                                <div class="form-row full-width">
                                    <label for="talktopc_override_prompt">System Prompt</label>
                                    <div class="field">
                                        <textarea id="talktopc_override_prompt" rows="5" disabled><?php echo esc_textarea(get_option('talktopc_override_prompt', '')); ?></textarea>
                                        <p class="field-action edit-only" style="display: none;">
                                            <button type="button" class="button button-small" id="talktopcGeneratePrompt">🔄 Generate from Site Content</button>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="talktopc_override_first_message">First Message</label>
                                    <div class="field">
                                        <input type="text" id="talktopc_override_first_message" value="<?php echo esc_attr(get_option('talktopc_override_first_message', '')); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="talktopc_override_voice">Voice</label>
                                    <div class="field">
                                        <select id="talktopc_override_voice" disabled>
                                            <option value="">-- Select Voice --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="section-title">Advanced</h3>
                            
                            <!-- FIX #3: CSS Grid for aligned inline fields -->
                            <div class="advanced-settings-grid">
                                <div class="grid-field">
                                    <label for="talktopc_override_voice_speed">Speed</label>
                                    <input type="number" id="talktopc_override_voice_speed" value="<?php echo esc_attr(get_option('talktopc_override_voice_speed', '1.0')); ?>" step="0.1" min="0.5" max="2.0" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_language">Language</label>
                                    <select id="talktopc_override_language" disabled>
                                        <option value="">-- Select Language --</option>
                                    </select>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_temperature">Temperature</label>
                                    <input type="number" id="talktopc_override_temperature" value="<?php echo esc_attr(get_option('talktopc_override_temperature', '0.7')); ?>" step="0.1" min="0" max="2" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_max_tokens">Max Tokens</label>
                                    <input type="number" id="talktopc_override_max_tokens" value="<?php echo esc_attr(get_option('talktopc_override_max_tokens', '1000')); ?>" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_max_call_duration">Max Duration</label>
                                    <div class="input-with-suffix">
                                        <input type="number" id="talktopc_override_max_call_duration" value="<?php echo esc_attr(get_option('talktopc_override_max_call_duration', '300')); ?>" disabled>
                                        <span class="suffix">sec</span>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="section-title">Call Recording & Tools</h3>
                            
                            <!-- Call Recording Card -->
                            <div class="tool-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0;">
                                    <input type="checkbox" id="talktopc_record_call" value="1" <?php checked(get_option('talktopc_record_call', false), true); ?> disabled style="margin-top: 2px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 20px;">🎙️</span>
                                            <strong style="font-size: 14px; color: #1d2327;">Call Recording</strong>
                                        </div>
                                        <p style="margin: 0; color: #646970; font-size: 13px; line-height: 1.5;">
                                            Record conversations for review and training. Recorded calls appear in the Conversations menu.
                                            <a href="<?php echo esc_url('https://talktopc.com/agents/conversations' . ($user_email ? '?email=' . urlencode($user_email) : '')); ?>" target="_blank" style="margin-left: 4px; text-decoration: none;">View Conversations →</a>
                                        </p>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Leave Message Tool Card -->
                            <div class="tool-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0;">
                                    <input type="checkbox" id="talktopc_enable_leave_message" value="1" <?php checked(get_option('talktopc_enable_leave_message', true), true); ?> disabled style="margin-top: 2px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 20px;">💬</span>
                                            <strong style="font-size: 14px; color: #1d2327;">Leave Message</strong>
                                        </div>
                                        <p style="margin: 0; color: #646970; font-size: 13px; line-height: 1.5;">
                                            Allow customers to leave messages instead of using contact forms. Messages will be sent to <strong><?php echo esc_html($user_email ?: 'your email'); ?></strong>.
                                        </p>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Visual Tools Card -->
                            <div class="tool-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0;">
                                    <input type="checkbox" id="talktopc_enable_visual_tools" value="1" <?php checked(get_option('talktopc_enable_visual_tools', true), true); ?> disabled style="margin-top: 2px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="font-size: 20px;">👁️</span>
                                            <strong style="font-size: 14px; color: #1d2327;">Visual Tools</strong>
                                        </div>
                                        <p style="margin: 0 0 12px 0; color: #646970; font-size: 13px; line-height: 1.5;">
                                            Enable visual assistance tools for website navigation and interaction.
                                        </p>
                                        
                                        <!-- Visual Tools Grid -->
                                        <div id="visualToolsList" class="visual-tools-list" style="margin-top: 12px; padding: 12px; background: #f9f9f9; border-radius: 6px; <?php echo get_option('talktopc_enable_visual_tools', true) ? '' : 'display: none;'; ?>">
                                            <?php
                                            $visual_tools = [
                                                'capture_screen' => ['name' => 'Capture Screen', 'icon' => '📸'],
                                                'highlight_element' => ['name' => 'Highlight Element', 'icon' => '✨'],
                                                'click_element' => ['name' => 'Click Element', 'icon' => '👆'],
                                                'fill_field' => ['name' => 'Fill Field', 'icon' => '✍️'],
                                                'scroll_to_element' => ['name' => 'Scroll to Element', 'icon' => '📜'],
                                                'navigate_to' => ['name' => 'Navigate To', 'icon' => '🧭'],
                                                'read_page' => ['name' => 'Read Page', 'icon' => '📖']
                                            ];
                                            $selected_tools = json_decode(get_option('talktopc_visual_tools_selection', '["capture_screen","highlight_element","click_element","fill_field","scroll_to_element","navigate_to","read_page"]'), true);
                                            if (!is_array($selected_tools)) {
                                                $selected_tools = array_keys($visual_tools);
                                            }
                                            ?>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                                <?php foreach ($visual_tools as $tool_id => $tool_data): ?>
                                                    <label class="visual-tool-item" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; transition: all 0.2s; font-weight: normal; font-size: 13px;">
                                                        <input type="checkbox" class="visual-tool-checkbox" data-tool-id="<?php echo esc_attr($tool_id); ?>" value="1" <?php checked(in_array($tool_id, $selected_tools), true); ?> disabled style="margin: 0;">
                                                        <span style="font-size: 16px;"><?php echo esc_html($tool_data['icon']); ?></span>
                                                        <span style="flex: 1;"><?php echo esc_html($tool_data['name']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <p style="margin: 12px 0 0 0; color: #646970; font-size: 12px; text-align: center;">
                                                <a href="<?php echo esc_url('https://talktopc.com/agents/create' . ($current_agent_id ? '?agentId=' . urlencode($current_agent_id) : '')); ?>" target="_blank" style="text-decoration: none;">View all tools in the TalkToPC app →</a>
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Save Area - Always visible when editing -->
                            <div class="save-area edit-only" id="agentSaveArea" style="display: none;">
                                <div class="save-area-content">
                                    <div class="save-buttons">
                                        <button type="button" class="button button-primary button-large" id="saveAgentSettingsBtn">💾 Save Agent Settings</button>
                                        <button type="button" class="button button-large" id="cancelEditBtn2">✖️ Cancel</button>
                                    </div>
                                    <span class="save-status" id="agentSaveStatus"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                
                <!-- Create New Agent -->
                <div class="create-agent-section">
                    <h3>➕ Create New Agent</h3>
                    <p>Need another agent? Create one here or in the TalkToPC app.</p>
                    <div class="create-agent-inline">
                        <input type="text" id="newAgentName" placeholder="Agent name (e.g., Hebrew Support)">
                        <button type="button" class="button button-primary" id="createAgentBtn">Create Agent</button>
                    </div>
                </div>
            </div>
            
            <!-- E-commerce Settings -->
            <div class="card">
                <h2><span class="icon">🛒</span> E-commerce</h2>
                <p style="color: #646970; margin-bottom: 15px; font-size: 13px;">Connect your Shopify store to enable product-aware conversations.</p>
                
                <table class="form-table" style="margin-top: 0;">
                    <tr>
                        <th scope="row">Enable E-commerce</th>
                        <td>
                            <label>
                                <input type="checkbox" id="talktopc_ecommerce_enabled" name="talktopc_ecommerce_enabled" value="1" <?php checked(get_option('talktopc_ecommerce_enabled', '0'), '1'); ?>>
                                Activate ecommerce flavor for the widget
                            </label>
                            <p class="description">When enabled, the widget will be initialized with product and store awareness.</p>
                        </td>
                    </tr>
                    <tr id="talktopc_shopify_domain_row" style="<?php echo get_option('talktopc_ecommerce_enabled', '0') === '1' ? '' : 'display: none;'; ?>">
                        <th scope="row"><label for="talktopc_shopify_domain">Shopify Domain</label></th>
                        <td>
                            <input type="text" id="talktopc_shopify_domain" name="talktopc_shopify_domain" value="<?php echo esc_attr(get_option('talktopc_shopify_domain', '')); ?>" class="regular-text" placeholder="mystore.myshopify.com">
                            <p class="description">Your Shopify store's <code>.myshopify.com</code> domain. Found in Shopify Admin &gt; Settings &gt; Domains.</p>
                        </td>
                    </tr>
                </table>
                
                <div id="talktopc_ecommerce_save_area" style="margin-top: 10px;">
                    <button type="button" class="button button-primary" id="talktopc_save_ecommerce">Save E-commerce Settings</button>
                    <span id="talktopc_ecommerce_save_status" style="margin-left: 10px;"></span>
                </div>
            </div>

            <script>
            (function() {
                var toggle = document.getElementById('talktopc_ecommerce_enabled');
                var domainRow = document.getElementById('talktopc_shopify_domain_row');
                if (toggle && domainRow) {
                    toggle.addEventListener('change', function() {
                        domainRow.style.display = this.checked ? '' : 'none';
                    });
                }

                var saveBtn = document.getElementById('talktopc_save_ecommerce');
                if (saveBtn) {
                    saveBtn.addEventListener('click', function() {
                        var status = document.getElementById('talktopc_ecommerce_save_status');
                        var enabled = document.getElementById('talktopc_ecommerce_enabled').checked ? '1' : '0';
                        var domain = document.getElementById('talktopc_shopify_domain').value.trim();

                        status.textContent = 'Saving...';
                        status.style.color = '#646970';

                        var formData = new FormData();
                        formData.append('action', 'talktopc_save_ecommerce');
                        formData.append('ecommerce_enabled', enabled);
                        formData.append('shopify_domain', domain);
                        formData.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('talktopc_save_ecommerce')); ?>');

                        fetch(ajaxurl, { method: 'POST', body: formData })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.success) {
                                    status.textContent = 'Saved!';
                                    status.style.color = '#00a32a';
                                } else {
                                    status.textContent = 'Error: ' + (data.data || 'Unknown error');
                                    status.style.color = '#d63638';
                                }
                                setTimeout(function() { status.textContent = ''; }, 3000);
                            })
                            .catch(function() {
                                status.textContent = 'Network error';
                                status.style.color = '#d63638';
                            });
                    });
                }
            })();
            </script>
            
            <!-- Quick Links -->
            <div class="card">
                <h2><span class="icon">🚀</span> TalkToPC App</h2>
                <p style="color: #646970; margin-bottom: 15px; font-size: 13px;">Advanced features available in the TalkToPC web app:</p>
                <div class="quick-links">
                    <a href="https://talktopc.com/agents" target="_blank" class="quick-link">
                        <span class="icon">📊</span>
                        <span class="text">Analytics Dashboard</span>
                    </a>
                    <a href="https://talktopc.com/agents/conversations" target="_blank" class="quick-link">
                        <span class="icon">🎙️</span>
                        <span class="text">Call Recording & Transcripts</span>
                    </a>
                    <a href="https://talktopc.com/agents/tools" target="_blank" class="quick-link">
                        <span class="icon">🔧</span>
                        <span class="text">Tools & Integrations</span>
                    </a>
                    <a href="https://talktopc.com/agents/usage" target="_blank" class="quick-link">
                        <span class="icon">📈</span>
                        <span class="text">Usage Tracking</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><span class="icon">🔗</span> Account Connection</h2>
                <p>Connect your TalkToPC account to get started.</p>
                <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero"><?php echo esc_html__('Connect to TalkToPC', 'talktopc'); ?></a>
            </div>
            <?php talktopc_render_embed_card(); ?>
        <?php endif; ?>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    // Scripts and styles are now enqueued via admin_enqueue_scripts hook
    // No need to call render functions here
}

/**
 * FIX #3: Additional CSS for better Agent Settings alignment
 */
/**
 * Get agent settings CSS content
 * 
 * WordPress Plugin Review: Returns CSS as string for wp_add_inline_style()
 * 
 * @return string CSS content
 */
function talktopc_get_agent_settings_styles_css() {
    return '
    /* Agent loading indicator */
    .agent-loading-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 10px;
        font-size: 12px;
        color: #646970;
    }
    .agent-loading-indicator .spinner {
        width: 14px;
        height: 14px;
        border: 2px solid #ddd;
        border-top-color: #7C3AED;
        border-radius: 50%;
        animation: talktopc-spin 0.8s linear infinite;
    }
    .agent-loading-indicator.hidden {
        display: none;
    }
    
    /* Agent settings header with Edit button */
    .agent-settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f6f7f7;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .agent-settings-header .header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .agent-settings-header .header-left h3 {
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .agent-settings-header .header-right {
        display: flex;
        gap: 8px;
    }
    
    .agent-settings-header .header-right .button {
        margin: 0;
    }
    
    /* Disabled state for fields */
    #agentSettingsForm input:disabled,
    #agentSettingsForm textarea:disabled,
    #agentSettingsForm select:disabled {
        background-color: #f6f7f7;
        color: #50575e;
        cursor: not-allowed;
        opacity: 0.8;
    }
    
    /* Edit mode styling */
    #agentSettingsForm.edit-mode input:not(:disabled),
    #agentSettingsForm.edit-mode textarea:not(:disabled),
    #agentSettingsForm.edit-mode select:not(:disabled) {
        background-color: #fff;
        border-color: #8c8f94;
    }
    
    #agentSettingsForm.edit-mode input:focus,
    #agentSettingsForm.edit-mode textarea:focus,
    #agentSettingsForm.edit-mode select:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }
    
    /* ============================================
       Agent Settings Form - Clean Layout
       ============================================ */
    
    /* Form grid container */
    .agent-form-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    /* Each form row: label on left, field on right */
    .agent-form-grid .form-row {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    
    .agent-form-grid .form-row label {
        width: 130px;
        min-width: 130px;
        font-weight: 500;
        font-size: 13px;
        color: #1d2327;
        padding-top: 10px;
        text-align: right;
    }
    
    .agent-form-grid .form-row .field {
        flex: 1;
        max-width: 700px;
    }
    
    /* Textarea styling */
    .agent-form-grid .field textarea {
        width: 100%;
        min-height: 120px;
        padding: 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
    }
    
    /* Text input styling */
    .agent-form-grid .field input[type="text"] {
        width: 100%;
        max-width: 500px;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
    }
    
    /* Select styling */
    .agent-form-grid .field select {
        width: 100%;
        max-width: 300px;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .agent-form-grid .field-action {
        margin-top: 10px;
    }
    
    /* Section title */
    .agent-settings-body .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #1d2327;
        margin: 28px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    /* ============================================
       Advanced Settings - 5-column Grid
       ============================================ */
    .advanced-settings-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        max-width: 100%;
    }
    
    .advanced-settings-grid .grid-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .advanced-settings-grid .grid-field label {
        font-size: 13px;
        font-weight: 500;
        color: #1d2327;
    }
    
    .advanced-settings-grid .grid-field input,
    .advanced-settings-grid .grid-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
        background: #fff;
    }
    
    .advanced-settings-grid .grid-field input:focus,
    .advanced-settings-grid .grid-field select:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }
    
    /* Input with suffix (e.g., "sec") */
    .input-with-suffix {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .input-with-suffix input {
        flex: 1;
        min-width: 0;
    }
    .input-with-suffix .suffix {
        font-size: 13px;
        color: #646970;
        white-space: nowrap;
    }
    
    /* Save area */
    .agent-settings-body .save-area {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    /* Responsive: Stack on smaller screens */
    @media (max-width: 900px) {
        .advanced-settings-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 600px) {
        .agent-form-grid .form-row {
            flex-direction: column;
        }
        .agent-form-grid .form-row label {
            width: 100%;
            text-align: left;
            padding-top: 0;
            margin-bottom: 6px;
        }
        .advanced-settings-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    ';
}

/**
 * Enqueue agent settings styles using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_style() instead of inline <style> tags
 */
function talktopc_enqueue_agent_settings_styles($hook) {
    // Only load on dashboard page
    if ($hook !== 'toplevel_page_talktopc') {
        return;
    }
    
    // Register dummy stylesheet handle
    wp_register_style('talktopc-agent-settings', false, [], TALKTOPC_VERSION);
    wp_enqueue_style('talktopc-agent-settings');
    
    // Add inline styles
    wp_add_inline_style('talktopc-agent-settings', talktopc_get_agent_settings_styles_css());
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_agent_settings_styles');

/**
 * Render agent settings styles (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_agent_settings_styles() instead
 */
function talktopc_render_agent_settings_styles() {
    // This function is deprecated but kept for backwards compatibility
    // Styles are now enqueued via admin_enqueue_scripts hook
}