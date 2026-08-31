/**
 * Widget Customization2 - Based on SDK widget-customization.html
 * Matches widget-customization.html from SDK
 */

(function($) {
    'use strict';
    
    const defaultIconUrl = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.defaultIconUrl)
      ? talktopcWidgetSettings2.defaultIconUrl
      : '';
    
    // Initialize widgetConfig from WordPress settings or use defaults
    // Deep merge WordPress settings with defaults to ensure all properties exist
    const defaultConfig = {
      agentName: 'Sasha',
      button: {
        size: 'medium',
        shape: 'circle',
        backgroundColor: '#FFFFFF',
        hoverColor: '#D3D3D3',
        shadow: true,
        shadowColor: 'rgba(0,0,0,0.15)'
      },
      icon: {
        type: 'custom',
        customImage: defaultIconUrl,
        size: 'medium',
        backgroundColor: '#FFFFFF'
      },
      panel: {
        width: 360,
        height: 550,
        borderRadius: 20,
        backgroundColor: '#16161e',
        border: '1px solid rgba(255,255,255,0.08)'
      },
      direction: 'ltr',
      position: {
        vertical: 'bottom',
        horizontal: 'right',
        offset: { x: 20, y: 20 }
      },
      header: {
        title: 'Chat Assistant',
        backgroundColor: '#7C3AED',
        textColor: '#FFFFFF',
        showCloseButton: true,
        onlineIndicatorText: 'Online'
      },
      messages: {
        userBackgroundColor: '#E5E7EB',
        agentBackgroundColor: '#F3F4F6',
        textColor: '#1F2937',
        fontSize: '14px',
        borderRadius: 16
      },
      text: {
        sendButtonText: '→',
        sendButtonColor: '#7C3AED',
        sendButtonHoverColor: '#6D28D9',
        inputPlaceholder: 'Type your message...',
        inputFocusColor: '#7C3AED'
      },
      voice: {
        micButtonColor: '#7C3AED',
        micButtonActiveColor: '#EF4444',
        avatarBackgroundColor: '#667eea',
        avatarType: 'icon',
        avatarIcon: '🤖',
        avatarImageUrl: '',
        startCallButtonText: 'Start Voice Call',
        startCallButtonColor: '#667eea',
        startCallButtonTextColor: '#FFFFFF',
        statusTitleColor: '#1e293b',
        statusSubtitleColor: '#64748b',
        waveformType: 'waveform',
        waveformIcon: '🎤',
        waveformImageUrl: '',
        pillGradient: '',
        pillTextColor: '#ffffff',
        pillDotColor: '#4ade80',
        avatarGradient1: '#6d56f5',
        avatarGradient2: '#a78bfa',
        onlineDotColor: '#22c55e',
        heroGradient1: '#2a2550',
        heroGradient2: '#1a1a2e',
        agentNameColor: '#f0eff8',
        agentRoleColor: 'rgba(255,255,255,0.35)',
        headlineColor: '#ffffff',
        sublineColor: 'rgba(255,255,255,0.45)',
        primaryBtnGradient1: '#6d56f5',
        primaryBtnGradient2: '#9d8df8',
        sendMessageText: 'Send a Message',
        secondaryBtnBg: 'rgba(255,255,255,0.05)',
        secondaryBtnBorder: 'rgba(255,255,255,0.09)',
        secondaryBtnTextColor: 'rgba(255,255,255,0.6)',
        agentRole: 'AI Voice Assistant',
        headline: 'Hi there 👋',
        subline: 'Ask me anything — I respond instantly<br>in voice or text.',
        waveformBarColor: '#7C3AED',
        speakerButtonColor: '#FFFFFF',
        endCallButtonColor: '#ef4444',
        liveIndicatorTextColor: '#10b981',
        liveIndicatorDotColor: '#10b981',
        liveTranscriptTextColor: '#64748b',
        liveTranscriptFontSize: '14px',
      },
      landing: {
        logo: '🤖',
        logoType: 'icon',
        logoIcon: '🤖',
        logoImageUrl: '',
        logoBackgroundColor: '#7C3AED',
        title: 'Welcome to AI Assistant',
        subtitle: 'Choose how you\'d like to interact',
        voiceCardTitle: 'Voice Call',
        textCardTitle: 'Text Chat',
        titleColor: '#1e293b',
        subtitleColor: '#64748b',
        modeCardBackgroundColor: '#FFFFFF',
        backgroundColor: 'linear-gradient(180deg, #ffffff 0%, rgba(168,85,247,0.03) 100%)',
        modeCardBorderColor: 'rgba(0,0,0,0.06)',
        modeCardTitleColor: '#1e1b4b',
        modeCardIconBackgroundColor: '#7C3AED'
      },
      position: {
        vertical: 'bottom',
        horizontal: 'right',
        offset: { x: 20, y: 20 }
      }
    };
    
    // Merge WordPress settings with defaults
    const wpSettings = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.settings) ? talktopcWidgetSettings2.settings : {};
    let widgetConfig = JSON.parse(JSON.stringify(defaultConfig)); // Deep copy defaults
    
    // Deep merge function
    function deepMerge(target, source) {
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                if (!target[key]) target[key] = {};
                deepMerge(target[key], source[key]);
            } else if (source[key] !== undefined && source[key] !== null && source[key] !== '') {
                target[key] = source[key];
            }
        }
        return target;
    }
    
    // Merge WordPress settings into defaults
    if (wpSettings && Object.keys(wpSettings).length > 0) {
        widgetConfig = deepMerge(widgetConfig, wpSettings);
    }

    function isLightBg(hex) {
        if (!hex || hex.charAt(0) !== '#') return false;
        const r = parseInt(hex.substr(1, 2), 16);
        const g = parseInt(hex.substr(3, 2), 16);
        const b = parseInt(hex.substr(5, 2), 16);
        return (r * 299 + g * 587 + b * 114) / 1000 > 160;
    }

    const themePresets = {
        default: {
            direction: 'ltr', agentName: 'Sasha',
            header: { title: 'Chat Assistant', onlineIndicatorText: 'Online' },
            panel: { backgroundColor: '#16161e', border: '1px solid rgba(255,255,255,0.08)' },
            voice: {
                pillGradient: '', pillTextColor: '#ffffff', pillDotColor: '#4ade80',
                avatarGradient1: '#6d56f5', avatarGradient2: '#a78bfa', onlineDotColor: '#22c55e',
                heroGradient1: '#2a2550', heroGradient2: '#1a1a2e',
                agentNameColor: '#f0eff8', agentRoleColor: 'rgba(255,255,255,0.35)',
                headlineColor: '#ffffff', sublineColor: 'rgba(255,255,255,0.45)',
                primaryBtnGradient1: '#6d56f5', primaryBtnGradient2: '#9d8df8',
                startCallButtonTextColor: '#FFFFFF', startCallButtonText: 'Start Voice Call', sendMessageText: 'Send a Message',
                secondaryBtnBg: 'rgba(255,255,255,0.05)', secondaryBtnBorder: 'rgba(255,255,255,0.09)', secondaryBtnTextColor: 'rgba(255,255,255,0.6)',
                agentRole: 'AI Voice Assistant', headline: 'Hi there 👋', subline: 'Ask me anything — I respond instantly<br>in voice or text.',
                waveformBarColor: '#7C3AED', micButtonColor: '#7C3AED', speakerButtonColor: '#FFFFFF', endCallButtonColor: '#ef4444',
            },
        },
        light: {
            direction: 'ltr', agentName: 'Sasha',
            header: { title: 'Chat Assistant', onlineIndicatorText: 'Online' },
            panel: { backgroundColor: '#ffffff', border: '1px solid rgba(0,0,0,0.06)' },
            voice: {
                pillGradient: 'linear-gradient(135deg, #7c3aed, #6d28d9)', pillTextColor: '#ffffff', pillDotColor: '#4ade80',
                avatarGradient1: '#7c3aed', avatarGradient2: '#a78bfa', onlineDotColor: '#22c55e',
                heroGradient1: '#ede9fe', heroGradient2: '#f5f3ff',
                agentNameColor: '#1e1b4b', agentRoleColor: '#7c7c8a',
                headlineColor: '#1e1b4b', sublineColor: '#6b7280',
                primaryBtnGradient1: '#7c3aed', primaryBtnGradient2: '#a78bfa',
                startCallButtonTextColor: '#ffffff', startCallButtonText: 'Start Voice Call', sendMessageText: 'Send a Message',
                secondaryBtnBg: '#f5f3ff', secondaryBtnBorder: 'rgba(124,58,237,0.15)', secondaryBtnTextColor: '#6d28d9',
                agentRole: 'AI Voice Assistant', headline: 'Hi there 👋', subline: 'Ask me anything — I respond instantly<br>in voice or text.',
                waveformBarColor: '#8b5cf6', micButtonColor: '#f5f3ff', speakerButtonColor: '#f5f3ff', endCallButtonColor: '#ef4444',
            },
        },
        sunset: {
            direction: 'ltr', agentName: 'Sasha',
            header: { title: 'Chat Assistant', onlineIndicatorText: 'Online' },
            panel: { backgroundColor: '#140a1e', border: '1px solid rgba(249,115,22,0.12)' },
            voice: {
                pillGradient: 'linear-gradient(135deg, #9f1239, #7c2d12, #581c87)', pillTextColor: '#ffffff', pillDotColor: '#fb923c',
                avatarGradient1: '#f97316', avatarGradient2: '#ec4899', onlineDotColor: '#f59e0b',
                heroGradient1: '#4a1942', heroGradient2: '#1a0a2e',
                agentNameColor: '#fde8d0', agentRoleColor: 'rgba(255,200,160,0.45)',
                headlineColor: '#fff1e6', sublineColor: 'rgba(255,200,160,0.55)',
                primaryBtnGradient1: '#f97316', primaryBtnGradient2: '#ec4899',
                startCallButtonTextColor: '#ffffff', startCallButtonText: 'Start Voice Call', sendMessageText: 'Send a Message',
                secondaryBtnBg: 'rgba(249,115,22,0.08)', secondaryBtnBorder: 'rgba(249,115,22,0.18)', secondaryBtnTextColor: 'rgba(255,180,130,0.7)',
                agentRole: 'AI Voice Assistant', headline: 'Hi there 👋', subline: 'Ask me anything — I respond instantly<br>in voice or text.',
                waveformBarColor: '#f97316', micButtonColor: 'rgba(255,255,255,0.9)', speakerButtonColor: 'rgba(255,255,255,0.9)', endCallButtonColor: '#e11d48',
            },
        },
        hebrew: {
            direction: 'rtl', agentName: 'שרה',
            header: { title: 'עוזרת חכמה', onlineIndicatorText: 'מחוברת' },
            panel: { backgroundColor: '#0f172a', border: '1px solid rgba(59,130,246,0.12)' },
            voice: {
                pillGradient: 'linear-gradient(135deg, #1e3a5f, #1e40af, #0f172a)', pillTextColor: '#ffffff', pillDotColor: '#34d399',
                avatarGradient1: '#3b82f6', avatarGradient2: '#1d4ed8', onlineDotColor: '#34d399',
                heroGradient1: '#1a2744', heroGradient2: '#0f172a',
                agentNameColor: '#e0e7ff', agentRoleColor: 'rgba(191,219,254,0.4)',
                headlineColor: '#f0f4ff', sublineColor: 'rgba(191,219,254,0.5)',
                primaryBtnGradient1: '#3b82f6', primaryBtnGradient2: '#1d4ed8',
                startCallButtonTextColor: '#ffffff', startCallButtonText: 'התחל שיחה קולית', sendMessageText: 'שלח הודעה',
                secondaryBtnBg: 'rgba(59,130,246,0.08)', secondaryBtnBorder: 'rgba(59,130,246,0.18)', secondaryBtnTextColor: 'rgba(147,197,253,0.7)',
                agentRole: 'עוזרת קולית חכמה', headline: 'היי, מה שלומך? 👋', subline: 'שאל/י אותי הכל — אני עונה<br>מיידית בקול או בטקסט.',
                waveformBarColor: '#3b82f6', micButtonColor: 'rgba(255,255,255,0.9)', speakerButtonColor: 'rgba(255,255,255,0.9)', endCallButtonColor: '#ef4444',
            },
        },
        sasha: {
            direction: 'rtl', agentName: 'סשה',
            header: { title: 'S-Law | ייעוץ משפטי', onlineIndicatorText: 'מקוונת' },
            panel: { backgroundColor: '#f8f5ef', border: '1px solid rgba(196,162,101,0.18)' },
            voice: {
                pillGradient: 'linear-gradient(135deg, #c4a265, #9e7e4f)', pillTextColor: '#ffffff', pillDotColor: '#22c55e',
                avatarGradient1: '#c4a265', avatarGradient2: '#9e7e4f', onlineDotColor: '#22c55e',
                heroGradient1: '#f8f5ef', heroGradient2: '#ebe6de',
                agentNameColor: '#1e293b', agentRoleColor: '#78716c',
                headlineColor: '#1e293b', sublineColor: '#6b7280',
                primaryBtnGradient1: '#c4a265', primaryBtnGradient2: '#9e7e4f',
                startCallButtonTextColor: '#ffffff', startCallButtonText: 'התחל שיחה קולית', sendMessageText: 'שלח הודעה',
                secondaryBtnBg: 'rgba(196,162,101,0.08)', secondaryBtnBorder: 'rgba(196,162,101,0.2)', secondaryBtnTextColor: '#9e7e4f',
                agentRole: 'עורכת דין | S-Law', headline: 'שלום, אני סשה 👋', subline: 'אני כאן לעזור בכל שאלה משפטית —<br>דיני עבודה ומשפט מסחרי.',
                waveformBarColor: '#c4a265', micButtonColor: '#1e293b', speakerButtonColor: '#1e293b', endCallButtonColor: '#ef4444',
            },
        },
    };

    function applyTheme(themeName) {
        const theme = themePresets[themeName];
        if (!theme) return;
        if (theme.direction !== undefined) widgetConfig.direction = theme.direction;
        if (theme.agentName !== undefined) widgetConfig.agentName = theme.agentName;
        if (theme.header) Object.assign(widgetConfig.header, theme.header);
        if (theme.panel) Object.assign(widgetConfig.panel, theme.panel);
        if (theme.voice) Object.assign(widgetConfig.voice, theme.voice);
        $('.theme-btn').css('border-color', 'transparent');
        $(`.theme-btn[data-theme="${themeName}"]`).css('border-color', '#667eea');
        renderPanelContent();
        applyPillStyles();
        updateConfigCode();
    }

    let selectedElement = null;
    let currentView = 'voiceIdle'; // 'voiceIdle', 'voice', 'landing', 'text'
    let panelOpen = true;
    let clickTimeout = null;
    let lastClickTarget = null;
    let historyExpanded = false;
    
    // Track which properties have been modified by the user
    // Only modified properties will be included in the config code output
    const modifiedProperties = {};
    
    // Helper function to mark a property path as modified
    // Example: markPropertyModified('header.backgroundColor') or markPropertyModified('button', 'size')
    function markPropertyModified(...path) {
        let current = modifiedProperties;
        for (let i = 0; i < path.length - 1; i++) {
            if (!current[path[i]]) {
                current[path[i]] = {};
            }
            current = current[path[i]];
        }
        current[path[path.length - 1]] = true;
    }
    
    // Helper function to check if a property path has been modified
    function isPropertyModified(...path) {
        let current = modifiedProperties;
        for (const key of path) {
            if (!current || !current[key]) {
                return false;
            }
            current = current[key];
        }
        return true;
    }
    
    // Helper function to get a nested property value from an object
    function getNestedProperty(obj, ...path) {
        let current = obj;
        for (const key of path) {
            if (current === undefined || current === null) {
                return undefined;
            }
            current = current[key];
        }
        return current;
    }
    
    // Helper function to set a nested property value in an object
    function setNestedProperty(obj, value, ...path) {
        let current = obj;
        for (let i = 0; i < path.length - 1; i++) {
            if (!current[path[i]]) {
                current[path[i]] = {};
            }
            current = current[path[i]];
        }
        current[path[path.length - 1]] = value;
    }
    
    // Helper function to build config object with only modified properties
    function buildMinimalConfig() {
        const agentId = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.agentId) 
            ? talktopcWidgetSettings2.agentId 
            : 'your_agent_id';
        const appId = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.appId) 
            ? talktopcWidgetSettings2.appId 
            : 'your_app_id';
        
        const config = {
            agentId: agentId,
            appId: appId
        };
        
        // Helper to check if an object has any modified nested properties
        function hasModifiedChildren(obj, path = []) {
            if (!obj || typeof obj !== 'object') return false;
            for (const key in obj) {
                const currentPath = [...path, key];
                if (isPropertyModified(...currentPath)) {
                    return true;
                }
                // Recursively check nested objects
                if (obj[key] && typeof obj[key] === 'object' && !Array.isArray(obj[key])) {
                    if (hasModifiedChildren(obj[key], currentPath)) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        // Helper to recursively add modified properties
        function addModifiedProperties(source, target, path = []) {
            for (const key in source) {
                const currentPath = [...path, key];
                const isModified = isPropertyModified(...currentPath);
                
                // Also check if any nested property is modified (for parent objects)
                const value = source[key];
                const hasNestedModifications = value && typeof value === 'object' && !Array.isArray(value) && value.constructor === Object
                    ? hasModifiedChildren(value, currentPath)
                    : false;
                
                if (isModified || hasNestedModifications) {
                    if (value && typeof value === 'object' && !Array.isArray(value) && value.constructor === Object) {
                        // Create nested object and add only modified nested properties
                        if (!target[key]) {
                            target[key] = {};
                        }
                        addModifiedProperties(value, target[key], currentPath);
                    } else {
                        // Primitive value or array, add it
                        target[key] = value;
                    }
                }
            }
        }
        
        // Add modified properties from widgetConfig
        addModifiedProperties(widgetConfig, config);
        
        return config;
    }

    function initMockWidget() {
      const mockPanel = document.getElementById('mockPanel');
      
      if (!mockPanel) {
        console.error('TalkToPC Widget Customization: mockPanel not found');
        return;
      }
      
      renderPanelContent();
      applyPillStyles();
      showCustomizationControls('default');
    }

    function applyPillStyles() {
        const v = widgetConfig.voice;
        const dir = widgetConfig.direction || 'ltr';
        const pillGradient = v.pillGradient || 'linear-gradient(135deg, #581c87, #312e81, #1e1b4b)';
        const pillTextColor = v.pillTextColor || '#ffffff';
        const pillDotColor = v.pillDotColor || '#4ade80';
        
        $('#mockPillLauncher').css({
            'background': pillGradient,
            'color': pillTextColor,
            'direction': dir
        });
        $('#pillTitle').css('color', pillTextColor).text(widgetConfig.header.title || 'Chat Assistant');
        $('#pillDot').css('background', pillDotColor);
        $('#pillStatus').text(widgetConfig.header.onlineIndicatorText || 'Online');
        $('#pillIconCircle').css('background', widgetConfig.icon.backgroundColor || '#ffffff');
    }

    function togglePanel() {
        const panel = $('#mockPanel');
        if (panelOpen) {
            panel.show();
          renderPanelContent();
        } else {
            panel.hide();
        }
    }

    function updateViewButtons() {
        $('.view-btn').css({ 'background': '#e5e7eb', 'color': '#6b7280' });
        $(`.view-btn[data-view="${currentView}"]`).css({ 'background': '#667eea', 'color': '#fff' });
    }

    function applyButtonStyles(button) {
      const config = widgetConfig.button;
      button.style.width = getSizeValue(config.size) + 'px';
      button.style.height = getSizeValue(config.size) + 'px';
      button.style.backgroundColor = config.backgroundColor;
      button.style.borderRadius = config.shape === 'circle' ? '50%' : config.shape === 'rounded' ? '12px' : '0';
      
      if (config.shadow) {
        button.style.boxShadow = `0 4px 12px ${config.shadowColor}`;
      } else {
        button.style.boxShadow = 'none';
      }

      // Apply icon
      const iconConfig = widgetConfig.icon;
      button.innerHTML = ''; // Clear previous content
      
      if (iconConfig.type === 'custom' && iconConfig.customImage) {
        const img = document.createElement('img');
        img.src = iconConfig.customImage;
        img.alt = 'Chat Assistant';
        const iconSize = Math.floor(getSizeValue(config.size) * 0.6);
        img.style.width = iconSize + 'px';
        img.style.height = iconSize + 'px';
        img.style.objectFit = 'contain';
        button.appendChild(img);
      } else if (iconConfig.type === 'emoji') {
        button.textContent = iconConfig.emoji;
      } else if (iconConfig.type === 'text') {
        button.textContent = iconConfig.text;
      } else if (iconConfig.type === 'microphone') {
        // Default microphone SVG
        const iconSize = Math.floor(getSizeValue(config.size) * 0.5);
        button.innerHTML = `<svg viewBox="0 0 24 24" style="width: ${iconSize}px; height: ${iconSize}px; fill: #7C3AED;">
          <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
          <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
        </svg>`;
      } else {
        // Fallback to custom image if type is custom but no image specified
        const img = document.createElement('img');
        img.src = defaultIconUrl || '';
        img.alt = 'Chat Assistant';
        const iconSize = Math.floor(getSizeValue(config.size) * 0.6);
        img.style.width = iconSize + 'px';
        img.style.height = iconSize + 'px';
        img.style.objectFit = 'contain';
        button.appendChild(img);
      }

      // Make button highlightable
      button.dataset.elementType = 'button';
      
      // Remove old event listeners by replacing the button
      const oldButton = button;
      const newButton = oldButton.cloneNode(true);
      oldButton.parentNode.replaceChild(newButton, oldButton);
      newButton.id = 'mockButton';
      
      newButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        handleElementClick('button', newButton, e);
      });
    }

    function getSizeValue(size) {
      const sizes = { small: 48, medium: 60, large: 72, xl: 84 };
      return sizes[size] || 60;
    }

    function renderPanelContent() {
      const panel = $('#mockPanel');
      panel.css({
          'background': widgetConfig.panel.backgroundColor || '#16161e',
          'border-radius': (widgetConfig.panel.borderRadius || 20) + 'px',
          'border': widgetConfig.panel.border || '1px solid rgba(255,255,255,0.08)',
      });
      panel.empty();
      switch (currentView) {
          case 'voiceIdle': renderVoiceIdleScreen(panel); break;
          case 'voice': renderVoiceActiveCall(panel); break;
          case 'landing': renderLandingScreen(panel); break;
          case 'text': renderTextInterface(panel); break;
          default: renderVoiceIdleScreen(panel); break;
      }
    }

    function bindPanelClicks(panel) {
        panel.find('[data-element-type]').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const elementType = $(this).data('element-type');
            handleMockElementClick(e, elementType);
        });
    }

    function handleMockElementClick(e, elementType) {
        if (clickTimeout && lastClickTarget === elementType) {
            clearTimeout(clickTimeout);
            clickTimeout = null;
            lastClickTarget = null;
            if (elementType === 'button' || elementType === 'closeButton') { panelOpen = !panelOpen; togglePanel(); }
            else if (elementType === 'heroPrimaryBtn') { currentView = 'voice'; renderPanelContent(); updateViewButtons(); }
            else if (elementType === 'heroSecondaryBtn') { currentView = 'text'; renderPanelContent(); updateViewButtons(); }
            else if (elementType === 'endCallButton') { currentView = 'voiceIdle'; renderPanelContent(); updateViewButtons(); }
            else if (elementType === 'modeCard') {
                const mode = $(e.currentTarget).data('mode');
                if (mode === 'voice') { currentView = 'voiceIdle'; } else { currentView = 'text'; }
                renderPanelContent(); updateViewButtons();
            }
            return;
        }
        lastClickTarget = elementType;
        clickTimeout = setTimeout(function() {
            clickTimeout = null;
            lastClickTarget = null;
            showControlsForElement(elementType);
        }, 300);
    }

    function showControlsForElement(elementType) {
        document.querySelectorAll('.element-highlight').forEach(el => el.classList.remove('element-highlight'));
        showCustomizationControls(elementType);
    }

    function renderVoiceIdleScreen(panel) {
        const v = widgetConfig.voice;
        const dir = widgetConfig.direction || 'ltr';
        const agentName = widgetConfig.agentName || 'Sasha';
        const panelBg = widgetConfig.panel.backgroundColor || '#16161e';
        const light = isLightBg(panelBg);
        const footerBorderColor = light ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.06)';
        const footerTextColor = light ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.35)';
        const footerLinkColor = light ? (v.primaryBtnGradient1 || '#7C3AED') : '#a78bfa';
        const closeColor = light ? '#6b7280' : 'rgba(255,255,255,0.5)';

        panel.html(`
            <div style="display:flex;flex-direction:column;direction:${dir}">
                <div data-element-type="heroBackground" style="background:linear-gradient(160deg, ${v.heroGradient1 || '#2a2550'} 0%, ${v.heroGradient2 || '#1a1a2e'} 100%);padding:18px 20px 16px;cursor:pointer;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="position:relative;">
                                <div data-element-type="heroAvatar" style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg, ${v.avatarGradient1 || '#6d56f5'}, ${v.avatarGradient2 || '#a78bfa'});display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:700;cursor:pointer;">${agentName.charAt(0).toUpperCase()}</div>
                                <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;background:${v.onlineDotColor || '#22c55e'};border:2px solid ${v.heroGradient1 || '#2a2550'};"></div>
        </div>
                            <div>
                                <div data-element-type="heroAgentName" style="font-size:14px;font-weight:600;color:${v.agentNameColor || '#f0eff8'};cursor:pointer;">${agentName}</div>
                                <div data-element-type="heroAgentRole" style="font-size:11px;color:${v.agentRoleColor || 'rgba(255,255,255,0.35)'};cursor:pointer;">${v.agentRole || 'AI Voice Assistant'}</div>
              </div>
            </div>
                        <button data-element-type="closeButton" style="background:none;border:none;cursor:pointer;color:${closeColor};padding:4px;display:flex;align-items:center;justify-content:center;">
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 2L2 10M2 2l8 8"/></svg>
                        </button>
                </div>
                    <div data-element-type="heroHeadline" style="font-size:22px;font-weight:700;color:${v.headlineColor || '#ffffff'};margin-bottom:6px;cursor:pointer;">${v.headline || 'Hi there 👋'}</div>
                    <div data-element-type="heroSubline" style="font-size:13px;color:${v.sublineColor || 'rgba(255,255,255,0.45)'};line-height:1.5;cursor:pointer;">${v.subline || 'Ask me anything — I respond instantly<br>in voice or text.'}</div>
              </div>
                <div style="padding:14px 20px 10px;background:${panelBg};">
                    <button data-element-type="heroPrimaryBtn" style="width:100%;padding:13px 18px;border-radius:12px;border:none;background:linear-gradient(135deg, ${v.primaryBtnGradient1 || '#6d56f5'}, ${v.primaryBtnGradient2 || '#9d8df8'});color:${v.startCallButtonTextColor || '#ffffff'};font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="${v.startCallButtonTextColor || '#ffffff'}" stroke-width="2"><path d="M12 2a3 3 0 0 1 3 3v7a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3M8 22h8"/></svg>
                        ${v.startCallButtonText || 'Start Voice Call'}
                    </button>
                    <button data-element-type="heroSecondaryBtn" style="width:100%;padding:12px 18px;border-radius:12px;background:${v.secondaryBtnBg || 'rgba(255,255,255,0.05)'};border:1px solid ${v.secondaryBtnBorder || 'rgba(255,255,255,0.09)'};color:${v.secondaryBtnTextColor || 'rgba(255,255,255,0.6)'};font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="${v.secondaryBtnTextColor || 'rgba(255,255,255,0.6)'}" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        ${v.sendMessageText || 'Send a Message'}
                    </button>
                </div>
                <div style="padding:6px 18px 10px;display:flex;justify-content:center;border-top:1px solid ${footerBorderColor};background:${panelBg};flex-shrink:0;">
                    <span style="font-size:10px;color:${footerTextColor};">Powered by <span style="color:${footerLinkColor};font-weight:700;">TalkToPC</span></span>
              </div>
            </div>
        `);
        bindPanelClicks(panel);
    }

    function renderLandingScreen(panel) {
        const l = widgetConfig.landing;
        const dir = widgetConfig.direction || 'ltr';
        const iconBgColor = l.modeCardIconBackgroundColor || '#7C3AED';
        const logoBg = l.logoBackgroundColor || '#7C3AED';
        const landBg = l.backgroundColor || 'linear-gradient(180deg, #ffffff 0%, rgba(168,85,247,0.03) 100%)';

        panel.html(`
            <div style="display:flex;flex-direction:column;flex:1;min-height:0;direction:${dir};">
                <div style="flex:1;background:${landBg};border-radius:18px 18px 0 0;padding:30px 24px 20px;display:flex;flex-direction:column;align-items:center;">
                    <div data-element-type="landingLogo" style="width:64px;height:64px;border-radius:14px;background:${logoBg};display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:36px;cursor:pointer;box-shadow:0 8px 28px rgba(102,126,234,0.35);">${l.logo || '🤖'}</div>
                    <div data-element-type="landingTitle" style="font-size:16px;font-weight:700;color:${l.titleColor || '#1e1b4b'};margin-bottom:4px;text-align:center;cursor:pointer;">${l.title || 'Welcome to AI Assistant'}</div>
                    <div data-element-type="landingTitle" style="font-size:12px;color:#64748b;text-align:center;margin-bottom:20px;cursor:pointer;">${l.subtitle || "Choose how you'd like to interact"}</div>
                    <div style="display:flex;gap:10px;width:100%;margin-top:auto;">
                        <div data-element-type="modeCard" data-mode="voice" style="flex:1;aspect-ratio:1;background:${l.modeCardBackgroundColor || '#fff'};border:1px solid ${l.modeCardBorderColor || 'rgba(0,0,0,0.06)'};border-radius:12px;padding:14px;cursor:pointer;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <div style="width:44px;height:44px;border-radius:10px;background:${iconBgColor};display:flex;align-items:center;justify-content:center;margin-bottom:8px;color:#fff;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
        </div>
                            <div style="font-size:13px;color:${l.modeCardTitleColor || '#1e1b4b'};">${l.voiceCardTitle || 'Voice Call'}</div>
            </div>
                        <div data-element-type="modeCard" data-mode="text" style="flex:1;aspect-ratio:1;background:${l.modeCardBackgroundColor || '#fff'};border:1px solid ${l.modeCardBorderColor || 'rgba(0,0,0,0.06)'};border-radius:12px;padding:14px;cursor:pointer;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <div style="width:44px;height:44px;border-radius:10px;background:${iconBgColor};display:flex;align-items:center;justify-content:center;margin-bottom:8px;color:#fff;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 2.98.97 4.29L1 23l6.71-1.97C9.02 21.64 10.46 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
            </div>
                            <div style="font-size:13px;color:${l.modeCardTitleColor || '#1e1b4b'};">${l.textCardTitle || 'Text Chat'}</div>
            </div>
          </div>
          </div>
                <div style="padding:6px 18px 10px;display:flex;justify-content:center;border-top:1px solid rgba(0,0,0,0.08);background:transparent;flex-shrink:0;">
                    <span style="font-size:10px;color:rgba(0,0,0,0.4);">Powered by <span style="color:${iconBgColor};font-weight:700;">TalkToPC</span></span>
        </div>
            </div>
        `);
        bindPanelClicks(panel);
    }

    function renderTextInterface(panel) {
        const panelBg = widgetConfig.panel.backgroundColor || '#16161e';
        const light = isLightBg(panelBg);
        const dir = widgetConfig.direction || 'ltr';
        const inputBg = light ? '#f3f4f6' : 'rgba(255,255,255,0.06)';
        const inputBorder = light ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)';
        const inputColor = light ? '#111827' : '#fff';
        const m = widgetConfig.messages;
        const t = widgetConfig.text;
        const footerBorderColor = light ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.06)';
        const footerTextColor = light ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.35)';
        const footerLinkColor = light ? (widgetConfig.voice.primaryBtnGradient1 || '#7C3AED') : '#a78bfa';

        panel.html(`
            <div style="display:flex;flex-direction:column;flex:1;min-height:0;background:${panelBg};direction:${dir};">
                <div style="flex:1;padding:16px;overflow-y:auto;">
                    <div data-element-type="userMessage" style="max-width:75%;padding:10px 14px;border-radius:${m.borderRadius || 16}px;background:${m.userBackgroundColor || '#E5E7EB'};color:${m.textColor || '#1F2937'};font-size:${m.fontSize || '14px'};margin-bottom:10px;margin-left:auto;cursor:pointer;">Hello!</div>
                    <div data-element-type="agentMessage" style="max-width:75%;padding:10px 14px;border-radius:${m.borderRadius || 16}px;background:${m.agentBackgroundColor || (light ? '#f3f4f6' : 'rgba(255,255,255,0.08)')};color:${m.textColor || (light ? '#1F2937' : 'rgba(255,255,255,0.85)')};font-size:${m.fontSize || '14px'};margin-bottom:10px;cursor:pointer;">Hi! How can I help?</div>
            </div>
                <div style="padding:8px 12px;border-top:1px solid ${light ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.06)'};">
                    <div style="display:flex;gap:8px;">
                        <input data-element-type="input" type="text" readonly placeholder="${t.inputPlaceholder || 'Type your message...'}" style="flex:1;padding:10px 14px;border-radius:20px;background:${inputBg};border:1px solid ${inputBorder};color:${inputColor};font-size:13px;outline:none;cursor:pointer;">
                        <button data-element-type="sendButton" style="width:36px;height:36px;border-radius:50%;border:none;background:${t.sendButtonColor || '#7C3AED'};color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
          </div>
          </div>
                <div style="padding:6px 18px 10px;display:flex;justify-content:center;border-top:1px solid ${footerBorderColor};background:${panelBg};flex-shrink:0;">
                    <span style="font-size:10px;color:${footerTextColor};">Powered by <span style="color:${footerLinkColor};font-weight:700;">TalkToPC</span></span>
        </div>
            </div>
        `);
        bindPanelClicks(panel);
    }

    function renderVoiceActiveCall(panel) {
        const v = widgetConfig.voice;
        const dir = widgetConfig.direction || 'ltr';
        const panelBg = widgetConfig.panel.backgroundColor || '#16161e';
        const light = isLightBg(panelBg);
        const callBg1 = v.heroGradient1 || '#2a2550';
        const callBg2 = v.heroGradient2 || '#1a1a2e';
        const waveColor = v.waveformBarColor || v.primaryBtnGradient1 || '#7C3AED';
        const timerTextColor = v.headlineColor || '#fff';
        const sectionBorder = light ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.08)';
        const convHeaderColor = light ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.4)';
        const inputBg = light ? '#f3f4f6' : 'rgba(255,255,255,0.06)';
        const inputBorder = light ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)';
        const inputColor = light ? '#111827' : '#fff';
        const liveColor = v.liveIndicatorTextColor || '#10b981';
        const liveDotColor = v.liveIndicatorDotColor || '#10b981';
        const transcriptColor = v.liveTranscriptTextColor || '#64748b';
        const footerBorderColor = light ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.06)';
        const footerTextColor = light ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.35)';
        const footerLinkColor = light ? (v.primaryBtnGradient1 || '#7C3AED') : '#a78bfa';
        const agentName = widgetConfig.agentName || 'Sasha';

        const bars = [10,16,22,28,32,36,38,40,38,36,32,28,22,16,10].map((h) =>
            `<div style="width:3px;border-radius:2px;background:${waveColor};height:${h}px;"></div>`
        ).join('');

        panel.html(`
            <div style="display:flex;flex-direction:column;flex:1;min-height:0;direction:${dir};background:linear-gradient(160deg, ${callBg1} 0%, ${callBg2} 100%);color:${timerTextColor};">
                <div style="padding:16px 20px;border-bottom:1px solid ${sectionBorder};text-align:center;">
                    <div data-element-type="timer" style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,0.06);font-size:12px;font-family:'JetBrains Mono',monospace;cursor:pointer;margin-bottom:12px;">
                        <div style="width:6px;height:6px;border-radius:50%;background:#ef4444;"></div><span>00:11</span>
              </div>
                    <div data-element-type="waveform" style="display:flex;align-items:center;justify-content:center;gap:2px;height:48px;margin-bottom:10px;cursor:pointer;">${bars}</div>
                    <div style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:#10b981;font-weight:500;margin-bottom:12px;">
                        <div style="width:6px;height:6px;border-radius:50%;background:#10b981;"></div>Listening...
              </div>
                    <div style="display:flex;justify-content:center;gap:10px;">
                        <button data-element-type="micButton" style="width:44px;height:44px;border-radius:50%;background:${v.micButtonColor || '#7C3AED'};border:none;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/></svg>
                </button>
                        <button data-element-type="endCallButton" style="width:48px;height:48px;border-radius:50%;background:${v.endCallButtonColor || '#ef4444'};border:none;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(239,68,68,0.4);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" transform="rotate(135 12 12)"/></svg>
                </button>
                        <button data-element-type="speakerButton" style="width:44px;height:44px;border-radius:50%;background:${v.speakerButtonColor || '#FFFFFF'};border:none;cursor:pointer;color:#333;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg>
                </button>
              </div>
            </div>
                <div style="flex:1;display:flex;flex-direction:column;background:${panelBg};min-height:0;">
                    <div style="padding:10px 16px 6px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:10px;font-weight:600;color:${convHeaderColor};letter-spacing:0.5px;">CONVERSATION</span>
          </div>
                    <div data-element-type="liveTranscript" style="padding:0 16px 10px;cursor:pointer;flex:1;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                            <div style="width:6px;height:6px;border-radius:50%;background:${liveDotColor};"></div>
                            <span style="font-size:10px;font-weight:700;color:${liveColor};">LIVE</span>
              </div>
                        <div style="font-size:${v.liveTranscriptFontSize || '14px'};color:${transcriptColor};line-height:1.6;">Hello, I'm ${agentName}, How can I help you today?</div>
            </div>
                    <div style="padding:8px 12px;border-top:1px solid ${sectionBorder};background:${panelBg};">
                        <div style="display:flex;gap:8px;">
                            <input type="text" readonly placeholder="Type your message..." style="flex:1;padding:8px 12px;border-radius:20px;background:${inputBg};border:1px solid ${inputBorder};color:${inputColor};font-size:13px;outline:none;">
                            <button style="width:32px;height:32px;border-radius:50%;border:none;background:${waveColor};color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
            </div>
          </div>
                    <div style="padding:6px 18px 10px;display:flex;justify-content:center;border-top:1px solid ${footerBorderColor};background:${panelBg};flex-shrink:0;">
                        <span style="font-size:10px;color:${footerTextColor};">Powered by <span style="color:${footerLinkColor};font-weight:700;">TalkToPC</span></span>
          </div>
        </div>
            </div>
        `);
        bindPanelClicks(panel);
    }
    
    function setupElementListeners(panel) {
      // Get all elements with data-element-type, sorted by depth (deepest first)
      const elements = Array.from(panel.querySelectorAll('[data-element-type]'));
      // Sort by depth - deeper elements first so we handle children before parents
      elements.sort((a, b) => {
        const depthA = (a.parentElement.closest('[data-element-type]') ? 1 : 0) + (a.querySelectorAll('[data-element-type]').length);
        const depthB = (b.parentElement.closest('[data-element-type]') ? 1 : 0) + (b.querySelectorAll('[data-element-type]').length);
        return depthB - depthA;
      });
      
      elements.forEach(el => {
        // Skip if this element is inside another element with data-element-type (child elements)
        const parentWithType = el.parentElement.closest('[data-element-type]');
        if (parentWithType && parentWithType !== panel) {
          // This is a child element, make sure it stops propagation
          const newEl = el.cloneNode(true);
          if (el.id) newEl.id = el.id;
          el.parentNode.replaceChild(newEl, el);
          
          newEl.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            handleElementClick(newEl.dataset.elementType, newEl, e);
          });
        } else {
          // This is a top-level element
          const newEl = el.cloneNode(true);
          if (el.id) newEl.id = el.id;
          el.parentNode.replaceChild(newEl, el);
          
          newEl.addEventListener('click', (e) => {
            // Check if click was on a child element with data-element-type
            const clickedElement = e.target.closest('[data-element-type]');
            if (clickedElement && clickedElement !== newEl) {
              // Click was on a child element, don't handle it here
              return;
            }
            e.preventDefault();
            e.stopPropagation();
            handleElementClick(newEl.dataset.elementType, newEl, e);
          });
        }
      });
    }

    function switchView(view) {
      currentView = view;
      renderPanelContent();
    }

    // Make switchView and selectElement available globally
    window.switchView = switchView;
    window.selectElement = selectElement;

    function selectElement(elementType, element, event) {
      // Prevent default action
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      // Remove previous selection
      document.querySelectorAll('.element-highlight').forEach(el => {
        el.classList.remove('element-highlight');
      });
      
      // Highlight selected element
      element.classList.add('element-highlight');
      selectedElement = { type: elementType, element };
      
      // Show customization controls
      showCustomizationControls(elementType);
    }
    
    let lastClickTime = 0;
    let lastClickElement = null;
    let lastClickType = null;
    
    function handleElementClick(elementType, element, event) {
      const currentTime = Date.now();
      const timeSinceLastClick = currentTime - lastClickTime;
      
      // Check if this is a double click (within 400ms and same element type)
      if (timeSinceLastClick < 400 && lastClickElement && elementType === lastClickType && 
          lastClickElement.dataset.elementType === element.dataset.elementType) {
        // Double click detected - perform normal interaction
        lastClickTime = 0;
        lastClickElement = null;
        lastClickType = null;
        performNormalInteraction(elementType, element, event);
        return;
      }
      
      // Single click - wait to see if there's a second click
      lastClickTime = currentTime;
      lastClickElement = element;
      lastClickType = elementType;
      
      setTimeout(() => {
        // If no second click happened, treat as single click
        if (lastClickTime === currentTime && lastClickElement === element) {
          lastClickTime = 0;
          lastClickElement = null;
          lastClickType = null;
          selectElement(elementType, element, event);
        }
      }, 400);
    }
    
    function performNormalInteraction(elementType, element, event) {
      if (elementType === 'closeButton' || elementType === 'endCallButton' || elementType === 'backButton') {
        // End call or close should go back to landing screen
        switchView('landing');
      } else if (elementType === 'modeCard') {
        const mode = element.getAttribute('data-mode');
        if (mode === 'voice') {
          switchView('voice');
        } else if (mode === 'text') {
          switchView('text');
        }
      } else if (elementType === 'button') {
        // Toggle panel
        panelOpen = !panelOpen;
        const panel = document.getElementById('mockPanel');
        panel.classList.toggle('open');
        if (panelOpen) {
          renderPanelContent();
        }
      } else if (elementType === 'conversationToggle') {
        // Toggle conversation history
        toggleConversationHistory();
      }
      // For other elements, allow normal behavior
    }
    
    function updateHistoryView() {
      const collapsed = document.getElementById('collapsedTranscript');
      const expanded = document.getElementById('expandedHistory');
      const toggleText = document.getElementById('historyToggleText');
      const toggleIcon = document.getElementById('historyToggleIcon');
      const expandedSection = document.getElementById('mockVoiceSectionExpanded');
      const compactSection = document.getElementById('mockVoiceSectionCompact');
      
      console.log('updateHistoryView called, historyExpanded:', historyExpanded);
      console.log('Elements found:', { collapsed: !!collapsed, expanded: !!expanded, toggleText: !!toggleText, toggleIcon: !!toggleIcon, expandedSection: !!expandedSection, compactSection: !!compactSection });
      
      if (!collapsed || !expanded || !toggleText || !toggleIcon) {
        // Elements not found yet, try again after a short delay
        console.log('Elements not found, retrying...');
        setTimeout(updateHistoryView, 100);
        return;
      }
      
      if (historyExpanded) {
        console.log('Expanding history...');
        // Hide collapsed transcript
        collapsed.style.display = 'none';
        // Show expanded history
        expanded.style.display = 'flex';
        expanded.classList.add('expanded');
        // Switch to compact single-row layout
        if (expandedSection) expandedSection.style.display = 'none';
        if (compactSection) compactSection.style.display = 'block';
        // Update toggle text and icon
        toggleText.textContent = 'Hide history';
        const path = toggleIcon.querySelector('path');
        if (path) {
          path.setAttribute('d', 'M18 15l-6-6-6 6');
        } else {
          toggleIcon.innerHTML = '<path d="M18 15l-6-6-6 6" stroke="currentColor" stroke-width="2" fill="none"/>';
        }
      } else {
        console.log('Collapsing history...');
        // Show collapsed transcript
        collapsed.style.display = 'block';
        // Hide expanded history
        expanded.style.display = 'none';
        expanded.classList.remove('expanded');
        // Switch to multi-row expanded layout
        if (expandedSection) expandedSection.style.display = 'block';
        if (compactSection) compactSection.style.display = 'none';
        // Update toggle text and icon
        toggleText.textContent = 'Show history';
        const path = toggleIcon.querySelector('path');
        if (path) {
          path.setAttribute('d', 'M6 9l6 6 6-6');
        } else {
          toggleIcon.innerHTML = '<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none"/>';
        }
      }
    }
    
    function toggleConversationHistory() {
      historyExpanded = !historyExpanded;
      console.log('toggleConversationHistory called, new state:', historyExpanded);
      updateHistoryView();
    }

    function showCustomizationControls(elementType) {
      const controlsDiv = document.getElementById('customizationControls');
      let controlsHTML = '';

      switch(elementType) {
        case 'button':
          controlsHTML = `
            <div class="customization-group">
              <h3>Floating Button</h3>
              <div class="control-item">
                <label>Size</label>
                <select id="btnSize">
                  <option value="small">Small</option>
                  <option value="medium" selected>Medium</option>
                  <option value="large">Large</option>
                  <option value="xl">Extra Large</option>
                </select>
              </div>
              <div class="control-item">
                <label>Shape</label>
                <select id="btnShape">
                  <option value="circle" selected>Circle</option>
                  <option value="rounded">Rounded</option>
                  <option value="square">Square</option>
                </select>
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="btnBgColor" value="${widgetConfig.button.backgroundColor}">
              </div>
              <div class="control-item">
                <label>Hover Color</label>
                <input type="color" id="btnHoverColor" value="${widgetConfig.button.hoverColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Icon</h3>
              <div class="control-item">
                <label>Type</label>
                <select id="iconType">
                  <option value="custom" ${widgetConfig.icon.type === 'custom' ? 'selected' : ''}>Custom Image</option>
                  <option value="microphone" ${widgetConfig.icon.type === 'microphone' ? 'selected' : ''}>Microphone</option>
                  <option value="emoji" ${widgetConfig.icon.type === 'emoji' ? 'selected' : ''}>Emoji</option>
                  <option value="text" ${widgetConfig.icon.type === 'text' ? 'selected' : ''}>Text</option>
                </select>
              </div>
              <div class="control-item" id="iconCustomImageControl" style="display: ${widgetConfig.icon.type === 'custom' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="iconCustomImage" value="${widgetConfig.icon.customImage || defaultIconUrl}" placeholder="${defaultIconUrl}">
              </div>
              <div class="control-item" id="iconEmojiControl" style="display: ${widgetConfig.icon.type === 'emoji' ? 'block' : 'none'};">
                <label>Emoji</label>
                <input type="text" id="iconEmoji" value="${widgetConfig.icon.emoji || '🎤'}" placeholder="🎤">
              </div>
              <div class="control-item" id="iconTextControl" style="display: ${widgetConfig.icon.type === 'text' ? 'block' : 'none'};">
                <label>Text</label>
                <input type="text" id="iconText" value="${widgetConfig.icon.text || 'AI'}" placeholder="AI">
              </div>
            </div>
          `;
          break;
        case 'header':
          controlsHTML = `
            <div class="customization-group">
              <h3>Header</h3>
              <div class="control-item">
                <label>Title Text</label>
                <input type="text" id="headerTitle" value="${widgetConfig.header.title}" placeholder="Chat Assistant">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="headerBgColor" value="${widgetConfig.header.backgroundColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="headerTextColor" value="${widgetConfig.header.textColor}">
              </div>
              <div class="control-item">
                <label>
                  <input type="checkbox" id="headerShowClose" ${widgetConfig.header.showCloseButton ? 'checked' : ''}>
                  Show Close Button
                </label>
              </div>
            </div>
          `;
          break;
        case 'panel':
          controlsHTML = `
            <div class="customization-group">
              <h3>Panel</h3>
              <div class="control-item">
                <label>Width (px)</label>
                <input type="number" id="panelWidth" value="${widgetConfig.panel.width}">
              </div>
              <div class="control-item">
                <label>Height (px)</label>
                <input type="number" id="panelHeight" value="${widgetConfig.panel.height}">
              </div>
              <div class="control-item">
                <label>Border Radius (px)</label>
                <input type="number" id="panelRadius" value="${widgetConfig.panel.borderRadius}">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="panelBgColor" value="${widgetConfig.panel.backgroundColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Widget Position</h3>
              <div class="control-item">
                <label>Vertical Position</label>
                <select id="positionVertical">
                  <option value="bottom" ${widgetConfig.position.vertical === 'bottom' ? 'selected' : ''}>Bottom</option>
                  <option value="top" ${widgetConfig.position.vertical === 'top' ? 'selected' : ''}>Top</option>
                </select>
              </div>
              <div class="control-item">
                <label>Horizontal Position</label>
                <select id="positionHorizontal">
                  <option value="right" ${widgetConfig.position.horizontal === 'right' ? 'selected' : ''}>Right</option>
                  <option value="left" ${widgetConfig.position.horizontal === 'left' ? 'selected' : ''}>Left</option>
                </select>
              </div>
              <div class="control-item">
                <label>Offset X (px)</label>
                <input type="number" id="positionOffsetX" value="${widgetConfig.position.offset.x}">
              </div>
              <div class="control-item">
                <label>Offset Y (px)</label>
                <input type="number" id="positionOffsetY" value="${widgetConfig.position.offset.y}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Position of the floating button and widget on the page.
              </p>
            </div>
            <div class="customization-group">
              <h3>Text Direction</h3>
              <div class="control-item">
                <label>Direction</label>
                <select id="direction">
                  <option value="ltr" ${widgetConfig.direction === 'ltr' ? 'selected' : ''}>Left to Right (LTR)</option>
                  <option value="rtl" ${widgetConfig.direction === 'rtl' ? 'selected' : ''}>Right to Left (RTL)</option>
                </select>
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Text direction for the widget. Use RTL for languages like Arabic or Hebrew.
              </p>
            </div>
          `;
          break;
        case 'position':
          controlsHTML = `
            <div class="customization-group">
              <h3>Widget Position</h3>
              <div class="control-item">
                <label>Vertical Position</label>
                <select id="positionVertical">
                  <option value="bottom" ${widgetConfig.position.vertical === 'bottom' ? 'selected' : ''}>Bottom</option>
                  <option value="top" ${widgetConfig.position.vertical === 'top' ? 'selected' : ''}>Top</option>
                </select>
              </div>
              <div class="control-item">
                <label>Horizontal Position</label>
                <select id="positionHorizontal">
                  <option value="right" ${widgetConfig.position.horizontal === 'right' ? 'selected' : ''}>Right</option>
                  <option value="left" ${widgetConfig.position.horizontal === 'left' ? 'selected' : ''}>Left</option>
                </select>
              </div>
              <div class="control-item">
                <label>Offset X (px)</label>
                <input type="number" id="positionOffsetX" value="${widgetConfig.position.offset.x}">
              </div>
              <div class="control-item">
                <label>Offset Y (px)</label>
                <input type="number" id="positionOffsetY" value="${widgetConfig.position.offset.y}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Position of the floating button and widget on the page.
              </p>
            </div>
          `;
          break;
        case 'direction':
          controlsHTML = `
            <div class="customization-group">
              <h3>Text Direction</h3>
              <div class="control-item">
                <label>Direction</label>
                <select id="direction">
                  <option value="ltr" ${widgetConfig.direction === 'ltr' ? 'selected' : ''}>Left to Right (LTR)</option>
                  <option value="rtl" ${widgetConfig.direction === 'rtl' ? 'selected' : ''}>Right to Left (RTL)</option>
                </select>
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Text direction for the widget. Use RTL for languages like Arabic or Hebrew.
              </p>
            </div>
          `;
          break;
        case 'userMessage':
        case 'agentMessage':
          controlsHTML = `
            <div class="customization-group">
              <h3>Messages</h3>
              <div class="control-item">
                <label>User Message Background</label>
                <input type="color" id="msgUserBg" value="${widgetConfig.messages.userBackgroundColor}">
              </div>
              <div class="control-item">
                <label>Agent Message Background</label>
                <input type="color" id="msgAgentBg" value="${widgetConfig.messages.agentBackgroundColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="msgTextColor" value="${widgetConfig.messages.textColor}">
              </div>
              <div class="control-item">
                <label>Font Size</label>
                <input type="text" id="msgFontSize" value="${widgetConfig.messages.fontSize}" placeholder="14px">
              </div>
              <div class="control-item">
                <label>Border Radius (px)</label>
                <input type="number" id="msgRadius" value="${widgetConfig.messages.borderRadius}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Note: Message text content is controlled by your agent, not the widget configuration.
              </p>
            </div>
          `;
          break;
        case 'sendButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Send Button</h3>
              <div class="control-item">
                <label>Button Text/Icon</label>
                <input type="text" id="sendButtonText" value="→" placeholder="→ or Send">
              </div>
              <div class="control-item">
                <label>Color</label>
                <input type="color" id="sendBtnColor" value="${widgetConfig.text.sendButtonColor}">
              </div>
              <div class="control-item">
                <label>Hover Color</label>
                <input type="color" id="sendBtnHover" value="${widgetConfig.text.sendButtonHoverColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Input Field</h3>
              <div class="control-item">
                <label>Placeholder Text</label>
                <input type="text" id="inputPlaceholder" value="${widgetConfig.text.inputPlaceholder}" placeholder="Type your message...">
              </div>
              <div class="control-item">
                <label>Focus Color</label>
                <input type="color" id="inputFocusColor" value="${widgetConfig.text.inputFocusColor}">
              </div>
            </div>
          `;
          break;
        case 'startCallButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Start Call Button</h3>
              <div class="control-item">
                <label>Button Text</label>
                <input type="text" id="startCallBtnText" value="Start Call" placeholder="Start Call">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="startCallBtnColor" value="${widgetConfig.voice.startCallButtonColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="startCallBtnTextColor" value="${widgetConfig.voice.startCallButtonTextColor}">
              </div>
            </div>
          `;
          break;
        case 'micButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Microphone Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="micBtnColor" value="${widgetConfig.voice.micButtonColor}">
              </div>
              <div class="control-item">
                <label>Active/Muted Color</label>
                <input type="color" id="micBtnActive" value="${widgetConfig.voice.micButtonActiveColor}">
              </div>
            </div>
          `;
          break;
        case 'endCallButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>End Call Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="endCallBtnColor" value="${widgetConfig.voice.endCallButtonColor || '#ef4444'}">
              </div>
            </div>
          `;
          break;
        case 'speakerButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Speaker Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="speakerBtnColor" value="${widgetConfig.voice.speakerButtonColor || '#FFFFFF'}">
              </div>
            </div>
          `;
          break;
        case 'timer':
          controlsHTML = `
            <div class="customization-group">
              <h3>Call Timer</h3>
              <div class="control-item">
                <label>Timer Dot Color</label>
                <input type="color" id="timerDotColor" value="#ef4444">
              </div>
              <div class="control-item">
                <label>Timer Text Color</label>
                <input type="color" id="timerTextColor" value="#64748b">
              </div>
            </div>
          `;
          break;
        case 'waveform':
          controlsHTML = `
            <div class="customization-group">
              <h3>Waveform Visualizer</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="waveformType">
                  <option value="waveform" ${widgetConfig.voice.waveformType === 'waveform' ? 'selected' : ''}>Waveform</option>
                  <option value="icon" ${widgetConfig.voice.waveformType === 'icon' ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.voice.waveformType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="waveformColorControl">
                <label>Waveform Color</label>
                <input type="color" id="waveformColor" value="${widgetConfig.voice.waveformBarColor || widgetConfig.voice.micButtonColor}">
              </div>
              <div class="control-item" id="waveformIconControl" style="display: ${widgetConfig.voice.waveformType === 'icon' ? 'block' : 'none'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="waveformIcon" value="${widgetConfig.voice.waveformIcon || '🎤'}" placeholder="🎤">
              </div>
              <div class="control-item" id="waveformImageControl" style="display: ${widgetConfig.voice.waveformType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="waveformImageUrl" value="${widgetConfig.voice.waveformImageUrl || ''}" placeholder="https://example.com/image.png">
              </div>
            </div>
          `;
          break;
        case 'statusTitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Status Text</h3>
              <div class="control-item">
                <label>Status Text</label>
                <input type="text" id="statusText" value="Listening..." placeholder="Listening...">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="statusTitleColor" value="${widgetConfig.voice.statusTitleColor}">
              </div>
              <div class="control-item">
                <label>Status Dot Color</label>
                <input type="color" id="statusDotColor" value="#10b981">
              </div>
            </div>
          `;
          break;
        case 'liveTranscript':
        case 'liveTranscriptText':
          controlsHTML = `
            <div class="customization-group">
              <h3>Live Transcript</h3>
              <div class="control-item">
                <label>Transcript Text Color</label>
                <input type="text" id="liveTranscriptColor" value="${widgetConfig.voice.liveTranscriptTextColor || '#64748b'}" placeholder="#64748b">
              </div>
              <div class="control-item">
                <label>Font Size</label>
                <input type="text" id="liveTranscriptFontSize" value="${widgetConfig.voice.liveTranscriptFontSize || '14px'}" placeholder="14px">
              </div>
            </div>
          `;
          break;
        case 'voiceInput':
        case 'voiceSendButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Voice Text Input</h3>
              <div class="control-item">
                <label>Placeholder Text</label>
                <input type="text" id="voiceInputPlaceholder" value="${widgetConfig.text.inputPlaceholder}" placeholder="Type your message...">
              </div>
              <div class="control-item">
                <label>Send Button Color</label>
                <input type="color" id="voiceSendBtnColor" value="${widgetConfig.text.sendButtonColor}">
              </div>
            </div>
          `;
          break;
        case 'voiceAvatar':
          controlsHTML = `
            <div class="customization-group">
              <h3>Voice Avatar</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="avatarBg" value="${widgetConfig.voice.avatarBackgroundColor}">
              </div>
            </div>
          `;
          break;
        case 'conversationHistory':
          controlsHTML = `
            <div class="customization-group">
              <h3>Conversation History</h3>
              <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                This is the expanded conversation history view showing all messages.
              </p>
            </div>
          `;
          break;
        case 'agentAvatar':
          controlsHTML = `
            <div class="customization-group">
              <h3>Agent Avatar (Next to Messages)</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="agentAvatarType">
                  <option value="icon" ${widgetConfig.voice.avatarType === 'icon' || !widgetConfig.voice.avatarType ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.voice.avatarType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="agentAvatarIconControl" style="display: ${widgetConfig.voice.avatarType === 'image' ? 'none' : 'block'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="agentAvatarIcon" value="${widgetConfig.voice.avatarIcon || '🤖'}" placeholder="🤖">
              </div>
              <div class="control-item" id="agentAvatarImageControl" style="display: ${widgetConfig.voice.avatarType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="agentAvatarImageUrl" value="${widgetConfig.voice.avatarImageUrl || ''}" placeholder="https://example.com/avatar.png">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="agentAvatarBg" value="${widgetConfig.voice.avatarBackgroundColor}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                This icon appears next to agent messages in the conversation history.
              </p>
            </div>
          `;
          break;
        case 'liveIndicator':
          controlsHTML = `
            <div class="customization-group">
              <h3>Live Indicator</h3>
              <div class="control-item">
                <label>Live Dot Color</label>
                <input type="color" id="liveDotColor" value="#10b981">
              </div>
              <div class="control-item">
                <label>Live Text Color</label>
                <input type="color" id="liveTextColor" value="#10b981">
              </div>
            </div>
          `;
          break;
        case 'landingLogoBg':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Logo Background</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="text" id="landingLogoBgColor" class="wp-color-picker" value="${widgetConfig.landing.logoBackgroundColor || '#7C3AED'}" data-default-color="#7C3AED">
              </div>
            </div>
          `;
          break;
        case 'landingLogo':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Logo</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="logoType">
                  <option value="icon" ${widgetConfig.landing.logoType === 'icon' || !widgetConfig.landing.logoType ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.landing.logoType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="logoIconControl" style="display: ${widgetConfig.landing.logoType === 'image' ? 'none' : 'block'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="landingLogoIcon" value="${widgetConfig.landing.logoIcon || widgetConfig.landing.logo || '🤖'}" placeholder="🤖">
              </div>
              <div class="control-item" id="logoImageControl" style="display: ${widgetConfig.landing.logoType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="landingLogoImageUrl" value="${widgetConfig.landing.logoImageUrl || ''}" placeholder="https://example.com/logo.png">
              </div>
            </div>
          `;
          break;
        case 'landingTitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Title</h3>
              <div class="control-item">
                <label>Title Text</label>
                <input type="text" id="landingTitleText" value="${widgetConfig.landing.title || 'Welcome to AI Assistant'}" placeholder="Welcome to AI Assistant">
              </div>
              <div class="control-item">
                <label>Title Color</label>
                <input type="color" id="landingTitleColor" value="${widgetConfig.landing.titleColor}">
              </div>
            </div>
          `;
          break;
        case 'landingSubtitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Subtitle</h3>
              <div class="control-item">
                <label>Subtitle Text</label>
                <input type="text" id="landingSubtitleText" value="${widgetConfig.landing.subtitle || 'Choose how you\'d like to interact'}" placeholder="Choose how you'd like to interact">
              </div>
              <div class="control-item">
                <label>Subtitle Color</label>
                <input type="color" id="landingSubtitleColor" value="${widgetConfig.landing.subtitleColor}">
              </div>
            </div>
          `;
          break;
        case 'modeCard':
          controlsHTML = `
            <div class="customization-group">
              <h3>Mode Cards (Voice/Text Buttons)</h3>
              <div class="control-item">
                <label>Voice Card Title</label>
                <input type="text" id="voiceCardTitle" value="${widgetConfig.landing.voiceCardTitle || 'Voice Call'}" placeholder="Voice Call">
              </div>
              <div class="control-item">
                <label>Text Card Title</label>
                <input type="text" id="textCardTitle" value="${widgetConfig.landing.textCardTitle || 'Text Chat'}" placeholder="Text Chat">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="modeCardBg" value="${widgetConfig.landing.modeCardBackgroundColor}">
              </div>
            </div>
          `;
          break;
        case 'heroBackground':
          controlsHTML = `
            <div class="customization-group">
              <h3>Hero Background</h3>
              <div class="control-item">
                <label>Gradient Start Color</label>
                <input type="color" id="heroGradient1" value="${widgetConfig.voice.heroGradient1 || '#2a2550'}">
              </div>
              <div class="control-item">
                <label>Gradient End Color</label>
                <input type="color" id="heroGradient2" value="${widgetConfig.voice.heroGradient2 || '#1a1a2e'}">
              </div>
            </div>
          `;
          break;
        case 'heroAvatar':
          controlsHTML = `
            <div class="customization-group">
              <h3>Hero Avatar</h3>
              <div class="control-item">
                <label>Gradient Color 1</label>
                <input type="color" id="avatarGradient1" value="${widgetConfig.voice.avatarGradient1 || '#6d56f5'}">
              </div>
              <div class="control-item">
                <label>Gradient Color 2</label>
                <input type="color" id="avatarGradient2" value="${widgetConfig.voice.avatarGradient2 || '#a78bfa'}">
              </div>
              <div class="control-item">
                <label>Agent Name</label>
                <input type="text" id="agentNameInput" value="${widgetConfig.agentName || 'Sasha'}" placeholder="Sasha">
              </div>
            </div>
          `;
          break;
        case 'heroAgentName':
          controlsHTML = `
            <div class="customization-group">
              <h3>Agent Name</h3>
              <div class="control-item">
                <label>Name</label>
                <input type="text" id="agentNameInput" value="${widgetConfig.agentName || 'Sasha'}" placeholder="Sasha">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="agentNameColor" value="${widgetConfig.voice.agentNameColor || '#f0eff8'}">
              </div>
            </div>
          `;
          break;
        case 'heroAgentRole':
          controlsHTML = `
            <div class="customization-group">
              <h3>Agent Role</h3>
              <div class="control-item">
                <label>Role Text</label>
                <input type="text" id="agentRoleInput" value="${widgetConfig.voice.agentRole || 'AI Voice Assistant'}" placeholder="AI Voice Assistant">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="text" id="agentRoleColor" value="${widgetConfig.voice.agentRoleColor || 'rgba(255,255,255,0.35)'}" placeholder="rgba(255,255,255,0.35)">
              </div>
            </div>
          `;
          break;
        case 'heroHeadline':
          controlsHTML = `
            <div class="customization-group">
              <h3>Headline</h3>
              <div class="control-item">
                <label>Headline Text</label>
                <input type="text" id="headlineInput" value="${(widgetConfig.voice.headline || 'Hi there 👋').replace(/"/g, '&quot;')}" placeholder="Hi there 👋">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="headlineColor" value="${widgetConfig.voice.headlineColor || '#ffffff'}">
              </div>
            </div>
          `;
          break;
        case 'heroSubline':
          controlsHTML = `
            <div class="customization-group">
              <h3>Subline</h3>
              <div class="control-item">
                <label>Subline Text (HTML allowed)</label>
                <textarea id="sublineInput" rows="2" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;">${widgetConfig.voice.subline || 'Ask me anything — I respond instantly<br>in voice or text.'}</textarea>
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="text" id="sublineColor" value="${widgetConfig.voice.sublineColor || 'rgba(255,255,255,0.45)'}" placeholder="rgba(255,255,255,0.45)">
              </div>
            </div>
          `;
          break;
        case 'heroPrimaryBtn':
          controlsHTML = `
            <div class="customization-group">
              <h3>Primary Button (Start Call)</h3>
              <div class="control-item">
                <label>Button Text</label>
                <input type="text" id="startCallBtnText" value="${widgetConfig.voice.startCallButtonText || 'Start Voice Call'}" placeholder="Start Voice Call">
              </div>
              <div class="control-item">
                <label>Gradient Color 1</label>
                <input type="color" id="primaryBtnGradient1" value="${widgetConfig.voice.primaryBtnGradient1 || '#6d56f5'}">
              </div>
              <div class="control-item">
                <label>Gradient Color 2</label>
                <input type="color" id="primaryBtnGradient2" value="${widgetConfig.voice.primaryBtnGradient2 || '#9d8df8'}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="startCallBtnTextColor" value="${widgetConfig.voice.startCallButtonTextColor || '#FFFFFF'}">
              </div>
            </div>
          `;
          break;
        case 'heroSecondaryBtn':
          controlsHTML = `
            <div class="customization-group">
              <h3>Secondary Button (Send Message)</h3>
              <div class="control-item">
                <label>Button Text</label>
                <input type="text" id="sendMessageTextInput" value="${widgetConfig.voice.sendMessageText || 'Send a Message'}" placeholder="Send a Message">
              </div>
              <div class="control-item">
                <label>Background</label>
                <input type="text" id="secondaryBtnBg" value="${widgetConfig.voice.secondaryBtnBg || 'rgba(255,255,255,0.05)'}" placeholder="rgba(255,255,255,0.05)">
              </div>
              <div class="control-item">
                <label>Border Color</label>
                <input type="text" id="secondaryBtnBorder" value="${widgetConfig.voice.secondaryBtnBorder || 'rgba(255,255,255,0.09)'}" placeholder="rgba(255,255,255,0.09)">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="text" id="secondaryBtnTextColor" value="${widgetConfig.voice.secondaryBtnTextColor || 'rgba(255,255,255,0.6)'}" placeholder="rgba(255,255,255,0.6)">
              </div>
            </div>
          `;
          break;
        default:
          controlsHTML = `
            <div class="customization-group">
              <h3>Select an Element</h3>
              <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                Click on any widget element in the preview to start customizing.
              </p>
              <p style="color: #6b7280; font-size: 13px; margin-top: 12px;">
                💡 <strong>Tip:</strong> Click the ⚙️ settings icon on the widget panel to configure panel size, position, and text direction.
              </p>
            </div>
          `;
      }

      controlsDiv.innerHTML = controlsHTML;
      
      // Scroll controls to top so user can see them
      controlsDiv.scrollTop = 0;
      
      // Attach event listeners based on element type
      attachControlListeners(elementType);
      
      // Update code output
      updateConfigCode();
      
      // Ensure controls are visible by scrolling the panel if needed
      setTimeout(() => {
        const firstControl = controlsDiv.querySelector('.control-item');
        if (firstControl) {
          firstControl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }, 100);
    }

    function attachControlListeners(elementType) {
      // Get controls div reference (it was already updated in showCustomizationControls)
      const controlsDiv = document.getElementById('customizationControls');
      if (!controlsDiv) return;
      
      // Button controls
      if (elementType === 'button') {
        document.getElementById('btnSize')?.addEventListener('change', (e) => {
          widgetConfig.button.size = e.target.value;
          markPropertyModified('button', 'size');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('btnShape')?.addEventListener('change', (e) => {
          widgetConfig.button.shape = e.target.value;
          markPropertyModified('button', 'shape');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('btnBgColor')?.addEventListener('input', (e) => {
          widgetConfig.button.backgroundColor = e.target.value;
          markPropertyModified('button', 'backgroundColor');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconType')?.addEventListener('change', (e) => {
          widgetConfig.icon.type = e.target.value;
          markPropertyModified('icon', 'type');
          // Show/hide relevant controls
          const customImageControl = document.getElementById('iconCustomImageControl');
          const emojiControl = document.getElementById('iconEmojiControl');
          const textControl = document.getElementById('iconTextControl');
          if (customImageControl) customImageControl.style.display = e.target.value === 'custom' ? 'block' : 'none';
          if (emojiControl) emojiControl.style.display = e.target.value === 'emoji' ? 'block' : 'none';
          if (textControl) textControl.style.display = e.target.value === 'text' ? 'block' : 'none';
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconCustomImage')?.addEventListener('input', (e) => {
          widgetConfig.icon.customImage = e.target.value;
          markPropertyModified('icon', 'customImage');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconEmoji')?.addEventListener('input', (e) => {
          widgetConfig.icon.emoji = e.target.value;
          markPropertyModified('icon', 'emoji');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconText')?.addEventListener('input', (e) => {
          widgetConfig.icon.text = e.target.value;
          markPropertyModified('icon', 'text');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
      }

      // Header controls
      if (elementType === 'header') {
        document.getElementById('headerTitle')?.addEventListener('input', (e) => {
          widgetConfig.header.title = e.target.value;
          markPropertyModified('header', 'title');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('headerBgColor')?.addEventListener('input', (e) => {
          // Update header backgroundColor in widgetConfig
          widgetConfig.header.backgroundColor = e.target.value;
          markPropertyModified('header', 'backgroundColor');
          // Update visual preview
          renderPanelContent();
          // Update config code display to show the change
          updateConfigCode();
          // Debug: Log to verify the change
          console.log('Header backgroundColor updated to:', widgetConfig.header.backgroundColor);
        });
        document.getElementById('headerTextColor')?.addEventListener('input', (e) => {
          widgetConfig.header.textColor = e.target.value;
          markPropertyModified('header', 'textColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('headerShowClose')?.addEventListener('change', (e) => {
          widgetConfig.header.showCloseButton = e.target.checked;
          markPropertyModified('header', 'showCloseButton');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Panel controls
      if (elementType === 'panel') {
        ['Width', 'Height', 'Radius'].forEach(prop => {
          const id = `panel${prop}`;
          document.getElementById(id)?.addEventListener('input', (e) => {
            const key = prop.toLowerCase();
            widgetConfig.panel[key] = parseInt(e.target.value);
            markPropertyModified('panel', key);
            renderPanelContent();
            updateConfigCode();
          });
        });
        document.getElementById('panelBgColor')?.addEventListener('input', (e) => {
          widgetConfig.panel.backgroundColor = e.target.value;
          markPropertyModified('panel', 'backgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        // Position controls (included in panel settings)
        document.getElementById('positionVertical')?.addEventListener('change', (e) => {
          widgetConfig.position.vertical = e.target.value;
          markPropertyModified('position', 'vertical');
          updateConfigCode();
        });
        document.getElementById('positionHorizontal')?.addEventListener('change', (e) => {
          widgetConfig.position.horizontal = e.target.value;
          markPropertyModified('position', 'horizontal');
          updateConfigCode();
        });
        document.getElementById('positionOffsetX')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.x = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'x');
          updateConfigCode();
        });
        document.getElementById('positionOffsetY')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.y = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'y');
          updateConfigCode();
        });
        // Direction controls (included in panel settings)
        document.getElementById('direction')?.addEventListener('change', (e) => {
          widgetConfig.direction = e.target.value;
          markPropertyModified('direction');
          updateConfigCode();
        });
      }

      // Position controls
      if (elementType === 'position') {
        document.getElementById('positionVertical')?.addEventListener('change', (e) => {
          widgetConfig.position.vertical = e.target.value;
          markPropertyModified('position', 'vertical');
          updateConfigCode();
        });
        document.getElementById('positionHorizontal')?.addEventListener('change', (e) => {
          widgetConfig.position.horizontal = e.target.value;
          markPropertyModified('position', 'horizontal');
          updateConfigCode();
        });
        document.getElementById('positionOffsetX')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.x = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'x');
          updateConfigCode();
        });
        document.getElementById('positionOffsetY')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.y = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'y');
          updateConfigCode();
        });
      }

      // Direction controls
      if (elementType === 'direction') {
        document.getElementById('direction')?.addEventListener('change', (e) => {
          widgetConfig.direction = e.target.value;
          markPropertyModified('direction');
          updateConfigCode();
        });
      }

      // Message controls
      if (elementType === 'userMessage' || elementType === 'agentMessage') {
        document.getElementById('msgUserBg')?.addEventListener('input', (e) => {
          widgetConfig.messages.userBackgroundColor = e.target.value;
          markPropertyModified('messages', 'userBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgAgentBg')?.addEventListener('input', (e) => {
          widgetConfig.messages.agentBackgroundColor = e.target.value;
          markPropertyModified('messages', 'agentBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgTextColor')?.addEventListener('input', (e) => {
          widgetConfig.messages.textColor = e.target.value;
          markPropertyModified('messages', 'textColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgRadius')?.addEventListener('input', (e) => {
          widgetConfig.messages.borderRadius = parseInt(e.target.value);
          markPropertyModified('messages', 'borderRadius');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgFontSize')?.addEventListener('input', (e) => {
          widgetConfig.messages.fontSize = e.target.value;
          markPropertyModified('messages', 'fontSize');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Send button and input controls
      if (elementType === 'sendButton') {
        document.getElementById('sendButtonText')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonText = e.target.value;
          markPropertyModified('text', 'sendButtonText');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('sendBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonColor = e.target.value;
          markPropertyModified('text', 'sendButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('sendBtnHover')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonHoverColor = e.target.value;
          markPropertyModified('text', 'sendButtonHoverColor');
          updateConfigCode();
        });
        document.getElementById('inputPlaceholder')?.addEventListener('input', (e) => {
          widgetConfig.text.inputPlaceholder = e.target.value;
          markPropertyModified('text', 'inputPlaceholder');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('inputFocusColor')?.addEventListener('input', (e) => {
          widgetConfig.text.inputFocusColor = e.target.value;
          markPropertyModified('text', 'inputFocusColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Start call button controls
      if (elementType === 'startCallButton') {
        document.getElementById('startCallBtnText')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonText = e.target.value;
          markPropertyModified('voice', 'startCallButtonText');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('startCallBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonColor = e.target.value;
          markPropertyModified('voice', 'startCallButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('startCallBtnTextColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonTextColor = e.target.value;
          markPropertyModified('voice', 'startCallButtonTextColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'micButton') {
        document.getElementById('micBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.micButtonColor = e.target.value;
          markPropertyModified('voice', 'micButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('micBtnActive')?.addEventListener('input', (e) => {
          widgetConfig.voice.micButtonActiveColor = e.target.value;
          markPropertyModified('voice', 'micButtonActiveColor');
          updateConfigCode();
        });
      }

      // Voice avatar controls (for voice interface)
      if (elementType === 'voiceAvatar') {
        document.getElementById('avatarBg')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarBackgroundColor = e.target.value;
          markPropertyModified('voice', 'avatarBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Agent avatar controls (for conversation history)
      if (elementType === 'agentAvatar') {
        document.getElementById('agentAvatarType')?.addEventListener('change', (e) => {
          widgetConfig.voice.avatarType = e.target.value;
          markPropertyModified('voice', 'avatarType');
          // Show/hide relevant controls
          const iconControl = document.getElementById('agentAvatarIconControl');
          const imageControl = document.getElementById('agentAvatarImageControl');
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarIcon')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarIcon = e.target.value;
          markPropertyModified('voice', 'avatarIcon');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarImageUrl = e.target.value;
          markPropertyModified('voice', 'avatarImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarBg')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarBackgroundColor = e.target.value;
          markPropertyModified('voice', 'avatarBackgroundColor');
          const avatars = document.querySelectorAll('.mock-history-avatar');
          avatars.forEach(avatar => {
            avatar.style.background = e.target.value;
          });
          updateConfigCode();
        });
      }

      // Status text controls
      if (elementType === 'statusTitle' || elementType === 'statusSubtitle') {
        document.getElementById('statusText')?.addEventListener('input', (e) => {
          // Update status text in both regular and compact views
          const statusEls = document.querySelectorAll('.mock-voice-status span, .mock-compact-status span');
          statusEls.forEach(el => el.textContent = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusTitleColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.statusTitleColor = e.target.value;
          markPropertyModified('voice', 'statusTitleColor');
          const statusEls = document.querySelectorAll('.mock-voice-status, .mock-compact-status');
          statusEls.forEach(el => el.style.color = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-status-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusSubtitleColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.statusSubtitleColor = e.target.value;
          markPropertyModified('voice', 'statusSubtitleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Live indicator controls
      if (elementType === 'liveIndicator') {
        document.getElementById('liveDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-live-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('liveTextColor')?.addEventListener('input', (e) => {
          const indicators = document.querySelectorAll('.mock-live-indicator');
          indicators.forEach(ind => ind.style.color = e.target.value);
          updateConfigCode();
        });
      }

      // Timer controls
      if (elementType === 'timer') {
        document.getElementById('timerDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-timer-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('timerTextColor')?.addEventListener('input', (e) => {
          const timers = document.querySelectorAll('.mock-voice-timer, .mock-compact-timer');
          timers.forEach(timer => timer.style.color = e.target.value);
          updateConfigCode();
        });
      }

      if (elementType === 'endCallButton') {
        document.getElementById('endCallBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.endCallButtonColor = e.target.value;
          markPropertyModified('voice', 'endCallButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'speakerButton') {
        document.getElementById('speakerBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.speakerButtonColor = e.target.value;
          markPropertyModified('voice', 'speakerButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Waveform controls
      if (elementType === 'waveform') {
        document.getElementById('waveformType')?.addEventListener('change', (e) => {
          widgetConfig.voice.waveformType = e.target.value;
          markPropertyModified('voice', 'waveformType');
          // Show/hide relevant controls
          const colorControl = document.getElementById('waveformColorControl');
          const iconControl = document.getElementById('waveformIconControl');
          const imageControl = document.getElementById('waveformImageControl');
          if (colorControl) colorControl.style.display = e.target.value === 'waveform' ? 'block' : 'none';
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('waveformColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.waveformBarColor = e.target.value;
          markPropertyModified('voice', 'waveformBarColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('waveformIcon')?.addEventListener('input', (e) => {
          widgetConfig.voice.waveformIcon = e.target.value;
          markPropertyModified('voice', 'waveformIcon');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('waveformImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.voice.waveformImageUrl = e.target.value;
          markPropertyModified('voice', 'waveformImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'liveTranscript' || elementType === 'liveTranscriptText') {
        document.getElementById('liveTranscriptColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.liveTranscriptTextColor = e.target.value;
          markPropertyModified('voice', 'liveTranscriptTextColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('liveTranscriptFontSize')?.addEventListener('input', (e) => {
          widgetConfig.voice.liveTranscriptFontSize = e.target.value;
          markPropertyModified('voice', 'liveTranscriptFontSize');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Voice input controls
      if (elementType === 'voiceInput' || elementType === 'voiceSendButton') {
        document.getElementById('voiceInputPlaceholder')?.addEventListener('input', (e) => {
          widgetConfig.text.inputPlaceholder = e.target.value;
          markPropertyModified('text', 'inputPlaceholder');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('voiceSendBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonColor = e.target.value;
          markPropertyModified('text', 'sendButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Landing screen controls
      if (elementType === 'landingLogoBg') {
        // Initialize color picker
        const bgColorPicker = $('#landingLogoBgColor');
        if (bgColorPicker.length) {
          bgColorPicker.wpColorPicker({
            change: function(event, ui) {
              widgetConfig.landing.logoBackgroundColor = ui.color.toString();
              markPropertyModified('landing', 'logoBackgroundColor');
              renderPanelContent();
              updateConfigCode();
            }
          });
        }
      }
      if (elementType === 'landingLogo') {
        document.getElementById('logoType')?.addEventListener('change', (e) => {
          widgetConfig.landing.logoType = e.target.value;
          markPropertyModified('landing', 'logoType');
          // Show/hide relevant controls
          const iconControl = document.getElementById('logoIconControl');
          const imageControl = document.getElementById('logoImageControl');
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingLogoIcon')?.addEventListener('input', (e) => {
          widgetConfig.landing.logoIcon = e.target.value;
          widgetConfig.landing.logo = e.target.value; // Keep backward compatibility
          markPropertyModified('landing', 'logoIcon');
          markPropertyModified('landing', 'logo');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingLogoImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.landing.logoImageUrl = e.target.value;
          markPropertyModified('landing', 'logoImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'landingTitle') {
        document.getElementById('landingTitleText')?.addEventListener('input', (e) => {
          widgetConfig.landing.title = e.target.value;
          markPropertyModified('landing', 'title');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingTitleColor')?.addEventListener('input', (e) => {
          widgetConfig.landing.titleColor = e.target.value;
          markPropertyModified('landing', 'titleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'landingSubtitle') {
        document.getElementById('landingSubtitleText')?.addEventListener('input', (e) => {
          widgetConfig.landing.subtitle = e.target.value;
          markPropertyModified('landing', 'subtitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingSubtitleColor')?.addEventListener('input', (e) => {
          widgetConfig.landing.subtitleColor = e.target.value;
          markPropertyModified('landing', 'subtitleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'modeCard') {
        document.getElementById('voiceCardTitle')?.addEventListener('input', (e) => {
          widgetConfig.landing.voiceCardTitle = e.target.value;
          markPropertyModified('landing', 'voiceCardTitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('textCardTitle')?.addEventListener('input', (e) => {
          widgetConfig.landing.textCardTitle = e.target.value;
          markPropertyModified('landing', 'textCardTitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('modeCardBg')?.addEventListener('input', (e) => {
          widgetConfig.landing.modeCardBackgroundColor = e.target.value;
          markPropertyModified('landing', 'modeCardBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'heroBackground') {
        document.getElementById('heroGradient1')?.addEventListener('input', (e) => {
          widgetConfig.voice.heroGradient1 = e.target.value;
          markPropertyModified('voice', 'heroGradient1');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('heroGradient2')?.addEventListener('input', (e) => {
          widgetConfig.voice.heroGradient2 = e.target.value;
          markPropertyModified('voice', 'heroGradient2');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroAvatar') {
        document.getElementById('avatarGradient1')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarGradient1 = e.target.value;
          markPropertyModified('voice', 'avatarGradient1');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('avatarGradient2')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarGradient2 = e.target.value;
          markPropertyModified('voice', 'avatarGradient2');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('agentNameInput')?.addEventListener('input', (e) => {
          widgetConfig.agentName = e.target.value;
          markPropertyModified('agentName');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroAgentName') {
        document.getElementById('agentNameInput')?.addEventListener('input', (e) => {
          widgetConfig.agentName = e.target.value;
          markPropertyModified('agentName');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('agentNameColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.agentNameColor = e.target.value;
          markPropertyModified('voice', 'agentNameColor');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroAgentRole') {
        document.getElementById('agentRoleInput')?.addEventListener('input', (e) => {
          widgetConfig.voice.agentRole = e.target.value;
          markPropertyModified('voice', 'agentRole');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('agentRoleColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.agentRoleColor = e.target.value;
          markPropertyModified('voice', 'agentRoleColor');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroHeadline') {
        document.getElementById('headlineInput')?.addEventListener('input', (e) => {
          widgetConfig.voice.headline = e.target.value;
          markPropertyModified('voice', 'headline');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('headlineColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.headlineColor = e.target.value;
          markPropertyModified('voice', 'headlineColor');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroSubline') {
        document.getElementById('sublineInput')?.addEventListener('input', (e) => {
          widgetConfig.voice.subline = e.target.value;
          markPropertyModified('voice', 'subline');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('sublineColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.sublineColor = e.target.value;
          markPropertyModified('voice', 'sublineColor');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroPrimaryBtn') {
        document.getElementById('startCallBtnText')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonText = e.target.value;
          markPropertyModified('voice', 'startCallButtonText');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('primaryBtnGradient1')?.addEventListener('input', (e) => {
          widgetConfig.voice.primaryBtnGradient1 = e.target.value;
          markPropertyModified('voice', 'primaryBtnGradient1');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('primaryBtnGradient2')?.addEventListener('input', (e) => {
          widgetConfig.voice.primaryBtnGradient2 = e.target.value;
          markPropertyModified('voice', 'primaryBtnGradient2');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('startCallBtnTextColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonTextColor = e.target.value;
          markPropertyModified('voice', 'startCallButtonTextColor');
          renderPanelContent(); updateConfigCode();
        });
      }

      if (elementType === 'heroSecondaryBtn') {
        document.getElementById('sendMessageTextInput')?.addEventListener('input', (e) => {
          widgetConfig.voice.sendMessageText = e.target.value;
          markPropertyModified('voice', 'sendMessageText');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('secondaryBtnBg')?.addEventListener('input', (e) => {
          widgetConfig.voice.secondaryBtnBg = e.target.value;
          markPropertyModified('voice', 'secondaryBtnBg');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('secondaryBtnBorder')?.addEventListener('input', (e) => {
          widgetConfig.voice.secondaryBtnBorder = e.target.value;
          markPropertyModified('voice', 'secondaryBtnBorder');
          renderPanelContent(); updateConfigCode();
        });
        document.getElementById('secondaryBtnTextColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.secondaryBtnTextColor = e.target.value;
          markPropertyModified('voice', 'secondaryBtnTextColor');
          renderPanelContent(); updateConfigCode();
        });
      }
    }

    function updateConfigCode() {
      const codeOutput = document.getElementById('configCode');
      
      // Build minimal config with only agentId, appId, and modified properties
      const displayConfig = buildMinimalConfig();
      
      // Format as proper JavaScript object with comments
      let configStr = JSON.stringify(displayConfig, null, 2);
      
      // Add helpful comments for agentId and appId
      const agentId = displayConfig.agentId || 'your_agent_id';
      const appId = displayConfig.appId || 'your_app_id';
      configStr = configStr
        .replace(new RegExp(`"agentId": "${agentId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"`, 'g'), 
                 `"agentId": "${agentId}"  // Required: Your agent ID`)
        .replace(new RegExp(`"appId": "${appId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"`, 'g'), 
                 `"appId": "${appId}"  // Required: Your app ID`);
      
      codeOutput.textContent = `const widget = new TTPChatWidget(${configStr});`;
    }

    function resetToDefaults() {
      Object.keys(modifiedProperties).forEach(key => delete modifiedProperties[key]);
      widgetConfig = JSON.parse(JSON.stringify(defaultConfig));
      currentView = 'voiceIdle';
      updateViewButtons();
      initMockWidget();
      updateConfigCode();
      selectedElement = null;
      document.querySelectorAll('.element-highlight').forEach(el => {
        el.classList.remove('element-highlight');
      });
      $('.theme-btn').css('border-color', 'transparent');
      $('.theme-btn[data-theme="default"]').css('border-color', '#667eea');
      showCustomizationControls('default');
    }

    // No edit mode toggle needed - single click = edit, double click = interact

    $(document).ready(function() {
        $('#resetBtn').on('click', resetToDefaults);

        $(document).on('click', '.view-btn', function() {
            currentView = $(this).data('view');
            updateViewButtons();
            renderPanelContent();
        });

        $(document).on('click', '.theme-btn', function() {
            const themeName = $(this).data('theme');
            applyTheme(themeName);
        });

        $(document).on('click', '#mockPillLauncher', function(e) {
            handleMockElementClick(e, 'button');
        });
    });

    // Add a way to select panel via a small indicator
    function addPanelSelector() {
      const panel = document.getElementById('mockPanel');
      if (!panel) return;
      if (!panel.querySelector('.panel-selector')) {
        const selector = document.createElement('div');
        selector.className = 'panel-selector';
        selector.style.cssText = 'position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; background: rgba(102, 126, 234, 0.2); border: 2px dashed #667eea; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; z-index: 1001;';
        selector.textContent = '⚙️';
        selector.title = 'Click to customize panel';
        selector.addEventListener('click', (e) => {
          e.stopPropagation();
          selectElement('panel', panel);
        });
        panel.appendChild(selector);
      }
    }

    $(document).ready(function() {
        console.log('TalkToPC Widget Customization: DOM ready, initializing...');
        
        const mockPanel = document.getElementById('mockPanel');
        const saveBtn = document.getElementById('saveCustomizationBtn');
        
        if (!mockPanel) {
            console.error('TalkToPC Widget Customization: mockPanel not found!');
            return;
        }
        
        initMockWidget();
        updateConfigCode();
        
        if (saveBtn) {
            $('#saveCustomizationBtn').on('click', function() {
                saveSettings();
            });
        }
        
        console.log('TalkToPC Widget Customization: Initialization complete');
    });
    
    /**
     * Save settings to WordPress via AJAX
     */
    function saveSettings() {
        const $saveBtn = $('#saveCustomizationBtn');
        const $saveStatus = $('#saveStatus');
        
        $saveBtn.prop('disabled', true).text('Saving...');
        $saveStatus.hide();
        
        // Build settings object from widgetConfig
        const settings = {};
        
        // Button settings
        settings.talktopc_button_size = widgetConfig.button.size;
        settings.talktopc_button_shape = widgetConfig.button.shape;
        settings.talktopc_button_bg_color = widgetConfig.button.backgroundColor;
        settings.talktopc_button_hover_color = widgetConfig.button.hoverColor;
        settings.talktopc_button_shadow = widgetConfig.button.shadow ? '1' : '0';
        settings.talktopc_button_shadow_color = widgetConfig.button.shadowColor;
        
        // Icon settings
        settings.talktopc_icon_type = widgetConfig.icon.type;
        settings.talktopc_icon_custom_image = widgetConfig.icon.customImage || '';
        settings.talktopc_icon_emoji = widgetConfig.icon.emoji || '';
        settings.talktopc_icon_text = widgetConfig.icon.text || '';
        
        // Panel settings
        settings.talktopc_panel_width = widgetConfig.panel.width;
        settings.talktopc_panel_height = widgetConfig.panel.height;
        settings.talktopc_panel_border_radius = widgetConfig.panel.borderRadius;
        settings.talktopc_panel_bg_color = widgetConfig.panel.backgroundColor;
        settings.talktopc_panel_border = widgetConfig.panel.border;
        
        // Position settings
        settings.talktopc_position = widgetConfig.position.vertical + '-' + widgetConfig.position.horizontal;
        settings.talktopc_position_offset_x = widgetConfig.position.offset.x;
        settings.talktopc_position_offset_y = widgetConfig.position.offset.y;
        
        // Direction
        settings.talktopc_direction = widgetConfig.direction;
        
        // Header settings
        settings.talktopc_header_title = widgetConfig.header.title;
        settings.talktopc_header_bg_color = widgetConfig.header.backgroundColor;
        settings.talktopc_header_text_color = widgetConfig.header.textColor;
        settings.talktopc_header_show_close = widgetConfig.header.showCloseButton ? '1' : '0';
        
        // Messages settings
        settings.talktopc_msg_user_bg = widgetConfig.messages.userBackgroundColor;
        settings.talktopc_msg_agent_bg = widgetConfig.messages.agentBackgroundColor;
        settings.talktopc_msg_text_color = widgetConfig.messages.textColor;
        settings.talktopc_msg_font_size = widgetConfig.messages.fontSize;
        settings.talktopc_msg_border_radius = widgetConfig.messages.borderRadius;
        
        // Text settings
        settings.talktopc_text_send_btn_text = widgetConfig.text.sendButtonText;
        settings.talktopc_text_send_btn_color = widgetConfig.text.sendButtonColor;
        settings.talktopc_text_send_btn_hover_color = widgetConfig.text.sendButtonHoverColor;
        settings.talktopc_text_input_placeholder = widgetConfig.text.inputPlaceholder;
        settings.talktopc_text_input_focus_color = widgetConfig.text.inputFocusColor;
        
        // Agent name
        settings.talktopc_agent_name = widgetConfig.agentName || '';

        // Voice settings (legacy)
        settings.talktopc_voice_mic_color = widgetConfig.voice.micButtonColor;
        settings.talktopc_voice_mic_active_color = widgetConfig.voice.micButtonActiveColor;
        settings.talktopc_voice_avatar_color = widgetConfig.voice.avatarBackgroundColor;
        settings.talktopc_voice_start_btn_text = widgetConfig.voice.startCallButtonText;
        settings.talktopc_voice_start_btn_color = widgetConfig.voice.startCallButtonColor;
        settings.talktopc_voice_start_btn_text_color = widgetConfig.voice.startCallButtonTextColor;
        settings.talktopc_voice_status_title_color = widgetConfig.voice.statusTitleColor;
        settings.talktopc_voice_status_subtitle_color = widgetConfig.voice.statusSubtitleColor;
        settings.talktopc_voice_live_dot_color = widgetConfig.voice.liveDotColor;
        settings.talktopc_voice_live_text_color = widgetConfig.voice.liveTextColor;

        // Voice settings (new)
        settings.talktopc_voice_pill_gradient = widgetConfig.voice.pillGradient || '';
        settings.talktopc_voice_pill_text_color = widgetConfig.voice.pillTextColor || '';
        settings.talktopc_voice_pill_dot_color = widgetConfig.voice.pillDotColor || '';
        settings.talktopc_voice_avatar_gradient1 = widgetConfig.voice.avatarGradient1 || '';
        settings.talktopc_voice_avatar_gradient2 = widgetConfig.voice.avatarGradient2 || '';
        settings.talktopc_voice_online_dot_color = widgetConfig.voice.onlineDotColor || '';
        settings.talktopc_voice_hero_gradient1 = widgetConfig.voice.heroGradient1 || '';
        settings.talktopc_voice_hero_gradient2 = widgetConfig.voice.heroGradient2 || '';
        settings.talktopc_voice_agent_name_color = widgetConfig.voice.agentNameColor || '';
        settings.talktopc_voice_agent_role_color = widgetConfig.voice.agentRoleColor || '';
        settings.talktopc_voice_headline_color = widgetConfig.voice.headlineColor || '';
        settings.talktopc_voice_subline_color = widgetConfig.voice.sublineColor || '';
        settings.talktopc_voice_primary_btn_gradient1 = widgetConfig.voice.primaryBtnGradient1 || '';
        settings.talktopc_voice_primary_btn_gradient2 = widgetConfig.voice.primaryBtnGradient2 || '';
        settings.talktopc_voice_send_message_text = widgetConfig.voice.sendMessageText || '';
        settings.talktopc_voice_secondary_btn_bg = widgetConfig.voice.secondaryBtnBg || '';
        settings.talktopc_voice_secondary_btn_border = widgetConfig.voice.secondaryBtnBorder || '';
        settings.talktopc_voice_secondary_btn_text_color = widgetConfig.voice.secondaryBtnTextColor || '';
        settings.talktopc_voice_agent_role = widgetConfig.voice.agentRole || '';
        settings.talktopc_voice_headline = widgetConfig.voice.headline || '';
        settings.talktopc_voice_subline = widgetConfig.voice.subline || '';
        settings.talktopc_voice_waveform_bar_color = widgetConfig.voice.waveformBarColor || '';
        settings.talktopc_voice_speaker_color = widgetConfig.voice.speakerButtonColor || '';
        settings.talktopc_voice_end_call_color = widgetConfig.voice.endCallButtonColor || '';
        settings.talktopc_voice_live_indicator_text_color = widgetConfig.voice.liveIndicatorTextColor || '';
        settings.talktopc_voice_live_indicator_dot_color = widgetConfig.voice.liveIndicatorDotColor || '';
        settings.talktopc_voice_live_transcript_text_color = widgetConfig.voice.liveTranscriptTextColor || '';
        settings.talktopc_voice_live_transcript_font_size = widgetConfig.voice.liveTranscriptFontSize || '';
        
        // Landing settings
        settings.talktopc_landing_logo = widgetConfig.landing.logo;
        settings.talktopc_landing_logo_bg_color = widgetConfig.landing.logoBackgroundColor || '#7C3AED';
        settings.talktopc_landing_title = widgetConfig.landing.title;
        settings.talktopc_landing_title_color = widgetConfig.landing.titleColor;
        settings.talktopc_landing_subtitle_color = widgetConfig.landing.subtitleColor;
        settings.talktopc_landing_voice_title = widgetConfig.landing.voiceCardTitle;
        settings.talktopc_landing_text_title = widgetConfig.landing.textCardTitle;
        // CRITICAL: Always save modeCardBackgroundColor explicitly, even if it's the default
        // This prevents SDK from falling back to header.backgroundColor
        settings.talktopc_landing_card_bg_color = widgetConfig.landing.modeCardBackgroundColor || '#FFFFFF';
        
        // Add nonce
        settings.nonce = talktopcWidgetSettings2.nonce;
        settings.action = talktopcWidgetSettings2.saveAction;
        
        $.ajax({
            url: talktopcWidgetSettings2.ajaxUrl,
            type: 'POST',
            data: settings,
            success: function(response) {
                $saveBtn.prop('disabled', false).text('Save Changes');
                if (response.success) {
                    $saveStatus.removeClass('error').addClass('success').text('Settings saved successfully!').show();
                    setTimeout(function() {
                        $saveStatus.fadeOut();
                    }, 3000);
                } else {
                    $saveStatus.removeClass('success').addClass('error').text('Error saving settings: ' + (response.data || 'Unknown error')).show();
                }
            },
            error: function(xhr, status, error) {
                $saveBtn.prop('disabled', false).text('Save Changes');
                $saveStatus.removeClass('success').addClass('error').text('Error saving settings: ' + error).show();
            }
        });
    }
    
})(jQuery);
