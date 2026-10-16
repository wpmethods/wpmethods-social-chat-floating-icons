=== Wpmethods Social Chat Floating Icons ===
Contributors: wpmethods, ajharrashed
Tags: Social Chat Floating Icon, live chat, facebook messenger, whatsapp live chat
Requires at least: 5.9
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display live chat floating icons of any social media like WhatsApp, Messenger, Telegram, etc on your WordPress website.

== Description ==
Bring your website to life with interactive, floating social icon and chat icons that keep you connected with your audience — anytime, anywhere. This lightweight and customizable plugin allows you to add WhatsApp, Messenger, Telegram, Skype, and more as stylish floating icons on your site. Whether you're offering customer support or just want to stay in touch, this plugin makes it effortless for users to reach you in one click.

= Features =
✅ Floating Chat Icon Widget
Add beautifully styled floating chat icons to any page or post.

🧩 Supports Popular Platforms
Easily link to WhatsApp, Messenger, Telegram, Skype, WeChat, Viber, and more.

🎨 Fully Customizable Styles
Set your own icon color, background color (including gradients), and label text for each link.

🔧 No Coding Required
Simple and intuitive admin panel to manage all settings.

📱 Mobile Responsive Design
Icons adapt seamlessly across all screen sizes and devices.

🛒 Support WooCommerce Product
It has woocommerce features. If customers click on the link from the product page, then it will be automatically append product price, name, atrributes, variations etc.

🖼️ Font Awesome 6 Icon Support
Use any Font Awesome 6 icon class to represent your social or chat platform.

🛠️ Custom Protocol Support
Allows custom URLs like weixin://, viber://, skype: with safe sanitization.

💬 Optional Text Labels
Add and display custom text next to each icon for better accessibility.

🔄 Drag-and-Drop Sorting
Easily reorder chat buttons to prioritize your most important platforms.

🌐 Multi-language Compatible
Fully translatable for global use.

🧩 Lightweight and Fast
No bloat – only the features you need to enhance user communication.

**Completely free:** All features and unlimited contact channels are included. No license key or paid upgrade is required.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/, or install it from WordPress.
2. Activate Wpmethods Social Chat Floating Icons from the Plugins screen.
3. Open Chat Floating Icons to add contact channels and configure the widget.

== Frequently Asked Questions ==

= How do I configure the automatic welcome? =
Open Chat Floating Icons > Chat panels > Automatic welcome prompt. Set the welcome prompt title, message, delay (0 to 300 seconds), and badge number (0 hides the badge, up to 99). The default delay is four seconds. The badge is a decorative welcome count and does not represent real unread messages. The chat panel opens only when the visitor clicks the welcome prompt or launcher.

The once-per-tab-session setting stores a dismissal flag in sessionStorage after the visitor dismisses the prompt or uses the widget. No message text or personal data is stored. When storage is unavailable, dismissal applies to the current page only. Disable the session setting to allow the prompt on each page visit.


= Can I hide a contact channel without deleting it? =
Yes. Use its Enabled switch in Contact channels, then save changes. Disabled channels keep their settings and can be enabled again. Existing channels stay enabled after updating. The frontend counts only enabled channels; disabling all of them hides the widget.

= Is every feature free? =
Yes. All features and unlimited contact channels are included, without a license key or paid upgrade.

= Where can I add custom CSS? =
Open Chat Floating Icons > Appearance > Custom CSS. Enter CSS without style tags. Prefix selectors with #lcWpmethodsChatContainer to target the widget. Custom rules load after the built-in styles.

= Where are the new options? =
Open Chat Floating Icons > Appearance for icon loading, colors and placement. Open Chat panels for WhatsApp and Messenger behavior, title and welcome message. Help and URL examples are in the left sidebar. A single configured contact shows its own button; two or more contacts use the widget launcher.

= Why disable Load Font Awesome? =
Disable it when your theme or another plugin already loads compatible Font Awesome styles and fonts. The plugin then loads no frontend Font Awesome stylesheet or webfonts of its own. Admin previews still use the local icon stylesheet. If icons disappear after disabling it, enable it again or fix your theme's Font Awesome loading.

= Do visitors chat directly inside the website? =
The local panels introduce the conversation. Visitors can write a message before continuing in WhatsApp. Messenger conversations open through your m.me link. Sending and receiving replies takes place in the selected service. These panels do not provide an embedded inbox or direct API messaging.

= Which links open a chat panel? =
Use https://wa.me/NUMBER, https://api.whatsapp.com/send?phone=NUMBER or https://web.whatsapp.com/send?phone=NUMBER for WhatsApp, and https://m.me/PAGE for Messenger. Other links open directly. Disabling a service's panel restores direct link behavior.

= Does the plugin load external services automatically? =
No. The widget assets and fonts are bundled locally. No third-party chat SDK is loaded. When a visitor follows a contact link or continues into WhatsApp or Messenger, their browser opens that service. WhatsApp links may include the configured message, product information and the visitor's draft. The destination service processes the conversation under its own terms and privacy policy:
* WhatsApp: https://www.whatsapp.com/legal/ and https://www.whatsapp.com/legal/privacy-policy
* Messenger: https://www.facebook.com/terms.php and https://www.facebook.com/privacy/policy/


= How to set icons =

You, can set fontawesome icon and go to wp-admin > Chat Floating Icons > the left help sidebar. Here you will get docs

= How to set Links =

You, can set from wp-admin > Chat Floating Icons > the left help sidebar. Here you will get docs


== Screenshots ==

1. Floating Chat Icon Widget: Add beautifully styled floating chat icons to any page or post.
2. Fully Customizable Styles: Set your own icon color, background color (including gradients), and label text for each link.
3. Support WooCommerce Product: If customers click on the link from the product page, then it will be automatically append product price, name, attributes, variations etc.
4. No Coding Required: Simple and intuitive admin panel to manage all settings.
5. Supports Popular Platforms: Easily link to WhatsApp, Messenger, Telegram, Skype, WeChat, Viber, and more.
6. Font Awesome 6 Icon Support: Use any Font Awesome 6 icon class to represent your social or chat platform.
7. Customers can send you automatically product name, price, etc.



== Changelog ==
= 1.1.0 =
* Added the Load Font Awesome switch, enabled by default for existing settings.
* Added WhatsApp and Messenger panels with title and greeting settings.
* Improved responsive styling, keyboard access and reduced motion support.
* Removed the frontend jQuery dependency and unnecessary variation enumeration.
* Fixed solid button backgrounds and empty settings handling.
* Hardened settings validation, URL handling and output escaping.
* Fixed repeater field indices after removing and adding links.

= 1.0.0 =
* Plugin initial release.

== Upgrade Notice ==
= 1.1.0 =
All features and unlimited channels are now completely free. Custom CSS is available in Appearance. Clear page and asset caches to see the softer panel styling.

Settings are now grouped under Contact channels, Appearance, Chat panels and WooCommerce. One contact now appears directly. Review your frontend after clearing page and asset caches.

= 1.1.0 =
Chat and icon loading settings are available on the plugin settings screen. Review the new panels after upgrading. Disable Load Font Awesome only if your theme or another plugin supplies compatible icons.
