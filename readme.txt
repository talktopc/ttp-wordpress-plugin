=== TalkToPC Voice Widget ===
Contributors: yinon11
Tags: talktopc, voice, ai agent, voice assistant, chatbot
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.9.114
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Voice AI agent for WordPress. 40+ languages, real-time conversations, 2-minute setup. Let visitors talk to your site—no typing needed.

== Description ==

**Try before you install!**
Live Demo: https://talktopc.com/demos/agent_1900570ad

---

TalkToPC Voice Widget adds an AI-powered voice assistant to your WordPress website. Visitors can have natural voice conversations with your AI agent in over 40 languages — no coding or typing required.

**🚀 Quick Setup (Under 2 Minutes):**

1. Install the plugin
2. Click "Connect to TalkToPC" 
3. Done! Your AI assistant is auto-created with content from your website

The plugin automatically scans your pages, posts, and products to create a personalized AI assistant that knows your business inside and out.

**✨ Features:**

* **One-Click Setup** - OAuth connection with automatic AI agent creation.
* **40+ Languages Supported** - Full support for English, Hebrew, Spanish, French, Arabic, and dozens more.
* **AI-Powered Configuration** - System prompt auto-generated from your site content.
* **Voice & Text Modes** - Visitors can speak naturally or type.
* **Fully Customizable** - Match your brand with custom colors, icons, and positions.
* **WooCommerce Support** - AI knows your products, prices, and availability.
* **Mobile Ready** - Optimized for all devices with microphone access.
* **Low Latency** - High-speed response for natural, flowing conversations.

**🎯 Use Cases:**

* Customer support automation
* Instant FAQ handling
* Product recommendations and sales
* Appointment scheduling
* Lead qualification

**🔒 Security:**

* Secure OAuth 2.0 connection
* No passwords stored
* One-time secrets with automatic expiration
* All data transmitted over encrypted HTTPS

== Installation ==

= Automatic Installation =

1. Go to Plugins → Add New in your WordPress admin
2. Search for "TalkToPC Voice Widget"
3. Click Install Now, then Activate
4. Go to TalkToPC in the admin menu
5. Click "Connect to TalkToPC" and authorize
6. Your AI assistant is ready!

== Frequently Asked Questions ==

= How do I connect my account? =

Click "Connect to TalkToPC" in the plugin settings. You'll be redirected to TalkToPC to authorize the connection. After authorization, you'll be sent back to WordPress automatically.

= Does the AI know about my website? =

Yes! When you first connect, the plugin scans your pages, posts, and products to create a customized knowledge base. You can regenerate the prompt anytime from the settings.

= What languages are supported? =

The AI supports over 40 languages including English, Hebrew, Spanish, French, German, Arabic, Chinese, Japanese, and more. The plugin automatically detects your site's language for the best experience.

= Is this free? =

The WordPress plugin is free to install. TalkToPC offers a free trial and various pricing plans for the AI voice service. Visit [talktopc.com/pricing](https://talktopc.com/upgrade) for details.

= Does it work with WooCommerce? =

Absolutely! The plugin automatically includes your product catalog, prices, and descriptions in the AI's memory.

== Changelog ==

= 1.9.114 =
* Added: Partner script on the dashboard for a registered partner. English letters and dashes only. The site loads https://cdn.talktopc.com/<name>.js outside iframes, even with no TalkToPC account connected.
* Added: A saved script name stays locked until Edit. Remove clears it from the site.

= 1.9.113 =
* Fixed: Customization preview — browser chrome and page layout (flex); clearer mock site frame.
* Fixed: Plugin Check — default pill/widget icon is a bundled SVG (no remote image offloading in admin).
* Added: assets/images/default-widget-icon.svg and talktopc_plugin_default_icon_url() for defaults.
* Improved: upload_all.sh uses rsync ignore-times so local edits sync reliably with no-times.

= 1.9.112 =
* Improved: Agent auto-setup now searches by name ("{site_name} Assistant") instead of picking the first agent.
* Improved: Widget initializes directly with appId + agentId — removed signed URL round-trip for faster load.
* Changed: Widget uses TTPChatWidget with flavor configuration instead of separate TTPEcommerceWidget class.
* Added: E-commerce settings section in dashboard.

= 1.9.111 =
* Fixed: Customization page - removed toggle button, improved floating button spacing and z-index layering.
* Improved: Added instruction message "Single click to customize • Double click to interact" next to Preview title.
* Fixed: Security improvements - added proper sanitization for visual_tools_selection input using sanitize_textarea_field().
* Fixed: Plugin check compliance - added .distignore file to exclude development files from distribution package.
* Fixed: All $_POST inputs now properly unslashed and sanitized before processing.
* Improved: Debug code now properly guarded with WP_DEBUG and WP_DEBUG_LOG checks.

= 1.9.108 =
* Removed: Old Customization page (widget-customization.php) - replaced with improved Customization2.
* Improved: Customization2 page now shows only modified properties in config code (starts with agentId/appId only).
* Fixed: WordPress coding standards compliance - added phpcs ignore comments for debug code and migration utilities.
* Fixed: Database query warnings in migration.php with proper phpcs annotations.
* Cleaned: Removed temporary/debug files (deploy scripts, test files) from plugin package.
* Updated: Menu now shows "Customization" (using Customization2 functionality).

= 1.9.97 =
* Added: Live widget customization page with real-time preview.
* Added: Database migration utility for upgrading from old table structure.
* Fixed: Security improvements - SQL injection prevention, output escaping, input sanitization.
* Fixed: Plugin check compliance - resolved all critical errors and warnings.
* Improved: Error logging now respects WP_DEBUG setting.
* Updated: Reduced plugin tags to meet WordPress.org requirements.

= 1.9.79 =
* Updated: Documentation and short description for better search visibility.
* Added: Explicit support for 40+ languages in documentation.
* Fixed: Version consistency for WordPress.org submission.

= 1.9.78 =
* Fixed: Replaced heredoc syntax with output buffering for WordPress.org compliance.
* Updated: All admin JavaScript files now use ob_start()/ob_get_clean() pattern.
* Technical: dashboard.js.php, common.js.php, and page-rules.js.php refactored.

= 1.0.0 =
* Initial release.

== External Services ==

This plugin relies on the TalkToPC service (https://talktopc.com) to provide AI voice conversations.

**What data is sent:**
* Site content (pages, posts, products) during initial setup for AI configuration.
* Voice audio when visitors use the voice widget.
* API credentials for authentication.

**External domains used:**
* https://cdn.talktopc.com - Widget JavaScript files.
* https://speech.talktopc.com - Voice processing.
* https://backend.talktopc.com - API requests.
**Service policies:**
* Terms of Service: https://talktopc.com/terms-of-service
* Privacy Policy: https://talktopc.com/privacy-agreement
