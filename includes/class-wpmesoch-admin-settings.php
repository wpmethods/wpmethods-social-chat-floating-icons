<?php
/**
 * Plugin settings screen and Settings API integration.
 *
 * @package WPMESOCH
 */

namespace WPMESOCH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin administration.
 */
class Wpmesoch_Admin_Settings {

	/**
	 * Register WordPress hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wpmesoch_add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'wpmesoch_register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wpmesoch_enqueue_admin_scripts' ) );
	}

	/**
	 * Register the settings menu.
	 */
	public function wpmesoch_add_menu_page() {
		add_menu_page(
			__( 'Chat Floating Icons', 'wpmethods-social-chat-floating-icons' ),
			__( 'Chat Floating Icons', 'wpmethods-social-chat-floating-icons' ),
			'manage_options',
			'wpmethods-social-chat-floating-icons-settings',
			array( $this, 'wpmesoch_settings_page' ),
			'dashicons-format-chat'
		);
	}

	/**
	 * Load assets on the plugin settings screen.
	 *
	 * @param string $hook Current admin page.
	 */
	public function wpmesoch_enqueue_admin_scripts( $hook ) {
		if ( 'toplevel_page_wpmethods-social-chat-floating-icons-settings' !== $hook ) {
			return;
		}

		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'wpmethods-social-chat-floating-icons-admin', WPMESOCH_WPMETHODS_URL . 'assets/css/admin' . $min_suffix . '.css', array(), WPMESOCH_PLUGIN_VERSION );

		// Enqueue Admin JS.
		wp_enqueue_script( 'wpmethods-social-chat-floating-icons-admin', WPMESOCH_WPMETHODS_URL . 'assets/js/admin' . $min_suffix . '.js', array( 'jquery', 'wp-color-picker', 'wpmethods-social-chat-floating-icons-sortable' ), WPMESOCH_PLUGIN_VERSION, true );

		// Now localize the script (AFTER enqueue).

		wp_localize_script(
			'wpmethods-social-chat-floating-icons-admin',
			'wpmesoch_data',
			array(
				'channelLabel' => __( 'Contact channel', 'wpmethods-social-chat-floating-icons' ),
				'unsaved'      => __( 'You have unsaved changes.', 'wpmethods-social-chat-floating-icons' ),
				'enabled'      => __( 'Enabled', 'wpmethods-social-chat-floating-icons' ),
				'disabled'     => __( 'Disabled', 'wpmethods-social-chat-floating-icons' ),
				/* translators: %d: Number of contact channels. */
				'channelCount' => __( '%d contact channels', 'wpmethods-social-chat-floating-icons' ),
			)
		);

		// FontAwesome and Sortable.
		wp_enqueue_style( 'wpmesoch-fontawesome', WPMESOCH_WPMETHODS_URL . 'assets/css/all.min.css', array(), '6.7.2' );
		wp_enqueue_script( 'wpmethods-social-chat-floating-icons-sortable', WPMESOCH_WPMETHODS_URL . 'assets/js/Sortable.min.js', array( 'jquery' ), '1.15.6', true );
	}

	/**
	 * Register the validated plugin option.
	 */
	public function wpmesoch_register_settings() {
		register_setting(
			'wpmesoch_settings_group',
			'wpmesoch_settings',
			array(
				'sanitize_callback' => array( $this, 'wpmesoch_sanitize_settings' ),
			)
		);
	}

	/**
	 * Validate settings through the same schema used by the frontend.
	 *
	 * @param mixed $settings Raw settings.
	 * @return array
	 */
	public function wpmesoch_sanitize_settings( $settings ) {

		return Wpmesoch_Settings::sanitize( $settings );
	}

	/**
	 * Render a labeled settings control.
	 *
	 * @param array  $options Validated settings.
	 * @param string $key Option key.
	 * @param string $label Visible label.
	 * @param string $type Control type.
	 * @param string $description Help text.
	 * @param array  $attributes Input attributes.
	 */
	private function field( $options, $key, $label, $type = 'text', $description = '', $attributes = array() ) {
		$value = $options[ $key ] ?? '';
		$id    = 'wpmesoch-' . $key;
		?>
		<div class="wpmesoch-field <?php echo 'textarea' === $type || 'checkbox' === $type ? 'wpmesoch-field-wide' : ''; ?>">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( 'checkbox' === $type ) : ?>
				<input type="hidden" name="wpmesoch_settings[<?php echo esc_attr( $key ); ?>]" value="0" />
				<input class="wpmesoch-switch" type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="wpmesoch_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $value, '1' ); ?> aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>" />
			<?php elseif ( 'textarea' === $type ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="wpmesoch_settings[<?php echo esc_attr( $key ); ?>]" rows="3" spellcheck="<?php echo 'custom_css' === $key ? 'false' : 'true'; ?>" aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php elseif ( 'position' === $type ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="wpmesoch_settings[<?php echo esc_attr( $key ); ?>]" aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>">
					<option value="right" <?php selected( $value, 'right' ); ?>><?php esc_html_e( 'Right', 'wpmethods-social-chat-floating-icons' ); ?></option>
					<option value="left" <?php selected( $value, 'left' ); ?>><?php esc_html_e( 'Left', 'wpmethods-social-chat-floating-icons' ); ?></option>
				</select>
			<?php else : ?>
				<input type="<?php echo 'number' === $type ? 'number' : 'text'; ?>" class="<?php echo 'color' === $type ? 'color-field' : ''; ?>" id="<?php echo esc_attr( $id ); ?>" name="wpmesoch_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>"
				<?php foreach ( $attributes as $attribute => $attribute_value ) : ?>
					<?php if ( in_array( $attribute, array( 'min', 'max', 'placeholder' ), true ) ) : ?>
						<?php echo esc_attr( $attribute ); ?>="<?php echo esc_attr( $attribute_value ); ?>"
					<?php endif; ?>
				<?php endforeach; ?> />
			<?php endif; ?>
			<p class="description" id="<?php echo esc_attr( $id . '-help' ); ?>"><?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render one channel, also used for the client-side repeater template.
	 *
	 * @param int|string $index Row index or template placeholder.
	 * @param array      $link Channel values.
	 */
	private function channel( $index, $link ) {
		$fields = array(
			'url'        => __( 'Contact URL', 'wpmethods-social-chat-floating-icons' ),
			'label'      => __( 'Button label', 'wpmethods-social-chat-floating-icons' ),
			'icon'       => __( 'Font Awesome icon class', 'wpmethods-social-chat-floating-icons' ),
			'color'      => __( 'Icon color', 'wpmethods-social-chat-floating-icons' ),
			'bg_color'   => __( 'Background color', 'wpmethods-social-chat-floating-icons' ),
			's_bg_color' => __( 'Gradient color (optional)', 'wpmethods-social-chat-floating-icons' ),
		);
		?>
		<div class="wpmesoch-admin-field-group">
			<div class="wpmesoch-channel-header">
				<span class="wpmesoch-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>
				<div class="wpmesoch-admin-preview-icon"><i class="<?php echo esc_attr( $link['icon'] ?? 'fab fa-whatsapp' ); ?>" aria-hidden="true"></i></div>
				<strong class="wpmesoch-channel-name"><?php echo esc_html( ! empty( $link['label'] ) ? $link['label'] : __( 'Contact channel', 'wpmethods-social-chat-floating-icons' ) ); ?></strong>
				<div class="wpmesoch-channel-actions">
					<label class="wpmesoch-channel-visibility" for="wpmesoch-link-<?php echo esc_attr( $index ); ?>-enabled">
						<input type="hidden" name="wpmesoch_settings[wpmesoch_links][<?php echo esc_attr( $index ); ?>][enabled]" value="0" />
						<input type="checkbox" role="switch" aria-label="<?php esc_attr_e( 'Show this contact channel on the website', 'wpmethods-social-chat-floating-icons' ); ?>" class="wpmesoch-switch wpmesoch-channel-enabled" id="wpmesoch-link-<?php echo esc_attr( $index ); ?>-enabled" name="wpmesoch_settings[wpmesoch_links][<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( $link['enabled'] ?? '1', '1' ); ?> />
						<span class="wpmesoch-channel-state"><?php echo ! isset( $link['enabled'] ) || '1' === $link['enabled'] ? esc_html__( 'Enabled', 'wpmethods-social-chat-floating-icons' ) : esc_html__( 'Disabled', 'wpmethods-social-chat-floating-icons' ); ?></span>
					</label>
					<button type="button" class="button wpmesoch-move-up" aria-label="<?php esc_attr_e( 'Move channel up', 'wpmethods-social-chat-floating-icons' ); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
					<button type="button" class="button wpmesoch-move-down" aria-label="<?php esc_attr_e( 'Move channel down', 'wpmethods-social-chat-floating-icons' ); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
					<button type="button" class="wpmesoch-admin-remove-field"><span class="dashicons dashicons-trash" aria-hidden="true"></span><span><?php esc_html_e( 'Remove', 'wpmethods-social-chat-floating-icons' ); ?></span></button>
				</div>
			</div>
			<div class="wpmesoch-fields-grid">
				<?php foreach ( $fields as $key => $label ) : ?>
					<div class="wpmesoch-field <?php echo 'url' === $key ? 'wpmesoch-field-wide' : ''; ?>">
						<label for="wpmesoch-link-<?php echo esc_attr( $index . '-' . $key ); ?>"><?php echo esc_html( $label ); ?></label>
						<input type="text" id="wpmesoch-link-<?php echo esc_attr( $index . '-' . $key ); ?>" name="wpmesoch_settings[wpmesoch_links][<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $link[ $key ] ?? '' ); ?>" data-field="<?php echo esc_attr( $key ); ?>" class="<?php echo in_array( $key, array( 'color', 'bg_color', 's_bg_color' ), true ) ? 'color-field' : ''; ?>" />
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/** Render help and community links in the left sidebar. */
	private function sidebar() {
		$services = array(
			'WhatsApp'    => array( 'https://wa.me/15551234567', 'fab fa-whatsapp' ),
			'Messenger'   => array( 'https://m.me/your-page', 'fab fa-facebook-messenger' ),
			'Telegram'    => array( 'https://t.me/your-username', 'fab fa-telegram' ),
			'Phone'       => array( 'tel:+15551234567', 'fas fa-phone' ),
			'Email'       => array( 'mailto:hello@example.com', 'fas fa-envelope' ),
			'Instagram'   => array( 'https://instagram.com/your-username', 'fab fa-instagram' ),
			'Facebook'    => array( 'https://facebook.com/your-page', 'fab fa-facebook' ),
			'X (Twitter)' => array( 'https://x.com/your-username', 'fab fa-x-twitter' ),
			'LinkedIn'    => array( 'https://linkedin.com/in/your-username', 'fab fa-linkedin' ),
			'Discord'     => array( 'https://discord.com/users/your-id', 'fab fa-discord' ),
			'Snapchat'    => array( 'https://www.snapchat.com/add/your-username', 'fab fa-snapchat' ),
			'Signal'      => array( 'https://signal.me/#p/your-number', 'fas fa-comment' ),
			'Skype'       => array( 'skype:your-username?chat', 'fab fa-skype' ),
			'Viber'       => array( 'viber://chat?number=your-number', 'fab fa-viber' ),
			'WeChat'      => array( 'weixin://dl/chat?your-id', 'fab fa-weixin' ),
		);
		?>
		<aside class="wpmesoch-sidebar" aria-label="<?php esc_attr_e( 'Setup help and resources', 'wpmethods-social-chat-floating-icons' ); ?>">
			<div class="wpmesoch-sidebar-intro"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'QUICK GUIDE', 'wpmethods-social-chat-floating-icons' ); ?></span><h2><?php esc_html_e( 'A little help, right here.', 'wpmethods-social-chat-floating-icons' ); ?></h2><p><?php esc_html_e( 'Set up your contact channels and make it easy for visitors to reach you.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
			<details open><summary><?php esc_html_e( 'Getting started', 'wpmethods-social-chat-floating-icons' ); ?></summary><ol><li><?php esc_html_e( 'Add a contact URL and an icon class.', 'wpmethods-social-chat-floating-icons' ); ?></li><li><?php esc_html_e( 'Choose your colors and button position.', 'wpmethods-social-chat-floating-icons' ); ?></li><li><?php esc_html_e( 'Save changes and visit your website.', 'wpmethods-social-chat-floating-icons' ); ?></li></ol><p><?php esc_html_e( 'One enabled channel shows its own button. Two or more enabled channels appear behind the widget icon. Switch a channel off to hide it without deleting it.', 'wpmethods-social-chat-floating-icons' ); ?></p></details>
			<details><summary><?php esc_html_e( 'Contact URL & icon examples', 'wpmethods-social-chat-floating-icons' ); ?></summary><div class="wpmesoch-service-examples">
				<?php
				foreach ( $services as $name => $example ) :
					?>
					<div><strong><?php echo esc_html( $name ); ?></strong><code><?php echo esc_html( $example[0] ); ?></code><code><?php echo esc_html( $example[1] ); ?></code></div><?php endforeach; ?>
			</div></details>
			<details><summary><?php esc_html_e( 'More Font Awesome icons', 'wpmethods-social-chat-floating-icons' ); ?></summary><p><?php esc_html_e( 'Use free Font Awesome 6 classes. Examples:', 'wpmethods-social-chat-floating-icons' ); ?></p><div class="wpmesoch-icon-examples">
				<?php
				foreach ( array( 'fas fa-comment-dots', 'fas fa-home', 'fas fa-headset', 'fas fa-comments', 'fas fa-video', 'fas fa-map-marker-alt', 'fas fa-search', 'fas fa-user-circle', 'fab fa-youtube', 'fab fa-tiktok', 'fab fa-github', 'fab fa-pinterest', 'fab fa-reddit', 'fab fa-soundcloud', 'fab fa-slack', 'fab fa-yahoo', 'fab fa-flickr', 'fab fa-vimeo', 'fas fa-camera', 'fas fa-cogs', 'fas fa-phone-square-alt' ) as $icon ) :
					?>
					<code><?php echo esc_html( $icon ); ?></code><?php endforeach; ?>
			</div><a href="https://fontawesome.com/v6/search?o=r&m=free" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Browse free icons', 'wpmethods-social-chat-floating-icons' ); ?> &nearr;</a></details>
			<details><summary><?php esc_html_e( 'Chat panels & Font Awesome', 'wpmethods-social-chat-floating-icons' ); ?></summary><p><?php esc_html_e( 'WhatsApp and Messenger panels open before continuing in the messaging app. Replies are received in that app.', 'wpmethods-social-chat-floating-icons' ); ?></p><p><?php esc_html_e( 'Keep Load Font Awesome enabled unless your theme or another plugin already supplies compatible icons. This setting controls frontend loading; admin previews always load local icons.', 'wpmethods-social-chat-floating-icons' ); ?></p></details>
			<div class="wpmesoch-community"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'MADE WITH CARE', 'wpmethods-social-chat-floating-icons' ); ?></span><p><?php esc_html_e( 'Enjoying the plugin? Support the creator or explore more tutorials and tools.', 'wpmethods-social-chat-floating-icons' ); ?></p><a class="wpmesoch-coffee" href="https://buymeacoffee.com/ajharrashed" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-coffee" aria-hidden="true"></span><span><?php esc_html_e( 'Buy me a coffee', 'wpmethods-social-chat-floating-icons' ); ?></span><span class="wpmesoch-link-arrow" aria-hidden="true">&nearr;</span></a><a href="https://www.youtube.com/@TechTriggerBD" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'YouTube · TechTriggerBD', 'wpmethods-social-chat-floating-icons' ); ?> &nearr;</a><a href="https://wpmethods.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit WPMethods', 'wpmethods-social-chat-floating-icons' ); ?> &nearr;</a></div>
		</aside>
		<?php
	}

	/** Render the complete settings panel for administrators. */
	public function wpmesoch_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = Wpmesoch_Settings::get();
		$links   = $options['wpmesoch_links'];
		$tabs    = array(
			'general'     => __( 'Contact channels', 'wpmethods-social-chat-floating-icons' ),
			'style'       => __( 'Appearance', 'wpmethods-social-chat-floating-icons' ),
			'chat'        => __( 'Chat panels', 'wpmethods-social-chat-floating-icons' ),
			'woocommerce' => __( 'WooCommerce', 'wpmethods-social-chat-floating-icons' ),
		);
		?>
		<div class="wrap wpmesoch-admin">
			<header class="wpmesoch-admin-header">
				<div class="wpmesoch-brand"><span class="wpmesoch-brand-mark dashicons dashicons-format-chat" aria-hidden="true"></span><div><a href="https://wpmethods.com" target="_blank" rel="noopener noreferrer">WPMETHODS</a><span><?php esc_html_e( 'Simple tools. Better connections.', 'wpmethods-social-chat-floating-icons' ); ?></span></div></div>
				<span class="wpmesoch-version"><?php esc_html_e( '100% Free', 'wpmethods-social-chat-floating-icons' ); ?> &middot; v<?php echo esc_html( WPMESOCH_PLUGIN_VERSION ); ?></span>
				<div class="wpmesoch-header-copy"><h1><?php esc_html_e( 'Social Chat Floating Icons', 'wpmethods-social-chat-floating-icons' ); ?></h1><p><?php esc_html_e( 'A welcoming conversation starts with a single click.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
			</header>
			<div class="wpmesoch-admin-layout">
				<?php $this->sidebar(); ?>
				<main class="wpmesoch-admin-main">
					<nav class="wpmesoch-admin-tabs" aria-label="<?php esc_attr_e( 'Plugin settings', 'wpmethods-social-chat-floating-icons' ); ?>">
						<?php
						foreach ( $tabs as $key => $label ) :
							?>
							<button type="button" id="wpmesoch-tab-<?php echo esc_attr( $key ); ?>" class="wpmesoch-admin-tab-button <?php echo 'general' === $key ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>" aria-controls="tab-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button><?php endforeach; ?>
					</nav>
					<form class="wpmesoch-settings-form" method="post" action="options.php">
						<?php settings_fields( 'wpmesoch_settings_group' ); ?>
						<?php do_settings_sections( 'wpmesoch_settings_group' ); ?>
						<section class="wpmesoch-admin-tab-content active" id="tab-general" aria-labelledby="wpmesoch-tab-general">
							<div class="wpmesoch-section-heading"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'LET’S CONNECT', 'wpmethods-social-chat-floating-icons' ); ?></span><h2><?php esc_html_e( 'Your contact channels', 'wpmethods-social-chat-floating-icons' ); ?></h2><p><?php esc_html_e( 'Add the places where customers can reach you. Drag the handle or use the arrows to change their order.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
							<div class="wpmesoch-info"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><?php esc_html_e( 'One enabled channel: a direct contact button. Two or more: a widget with contact options. Disable a channel to hide it without deleting its settings.', 'wpmethods-social-chat-floating-icons' ); ?></div>
							<div id="wpmesoch-admin-repeater-fields">
							<?php
							foreach ( $links ? $links : array(
								array(
									'icon'     => 'fab fa-whatsapp',
									'color'    => '#ffffff',
									'bg_color' => '#00da62',
								),
							) as $index => $link ) {
																																	$this->channel( $index, $link ); }
							?>
							</div>
							<template id="wpmesoch-channel-template">
							<?php
							$this->channel(
								'__INDEX__',
								array(
									'icon'     => 'fab fa-whatsapp',
									'color'    => '#ffffff',
									'bg_color' => '#00da62',
								)
							);
							?>
																		</template>
							<div class="wpmesoch-add-channel"><button type="button" class="button" id="wpmesoch-admin-add-field"><?php esc_html_e( '+ Add contact channel', 'wpmethods-social-chat-floating-icons' ); ?></button><span id="wpmesoch-channel-count" aria-live="polite"></span></div>
						</section>
						<section class="wpmesoch-admin-tab-content" id="tab-style" aria-labelledby="wpmesoch-tab-style">
							<div class="wpmesoch-section-heading"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'MAKE IT YOURS', 'wpmethods-social-chat-floating-icons' ); ?></span><h2><?php esc_html_e( 'Appearance & placement', 'wpmethods-social-chat-floating-icons' ); ?></h2><p><?php esc_html_e( 'Style the launcher and choose where your contact button sits.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Launcher & icons', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'toggle_icon_class', __( 'Widget icon class', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'Used when two or more channels are saved.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field(
									$options,
									'icon_size',
									__( 'Icon size (px)', 'wpmethods-social-chat-floating-icons' ),
									'number',
									__( 'Applies to all floating icons.', 'wpmethods-social-chat-floating-icons' ),
									array(
										'min' => 12,
										'max' => 64,
									)
								);
								$this->field(
									$options,
									'height_width',
									__( 'Button size (px)', 'wpmethods-social-chat-floating-icons' ),
									'number',
									__( 'Equal width and height for a circular button.', 'wpmethods-social-chat-floating-icons' ),
									array(
										'min' => 36,
										'max' => 96,
									)
								);
								$this->field( $options, 'load_fontawesome', __( 'Load Font Awesome', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'Disable only if your theme or another plugin already loads compatible Font Awesome icons. Admin previews keep their local icons.', 'wpmethods-social-chat-floating-icons' ) );
								?>
							</div></div>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Widget colors', 'wpmethods-social-chat-floating-icons' ); ?></h3><p class="description"><?php esc_html_e( 'These colors style the multi-channel widget. Individual contact colors are set in Contact channels.', 'wpmethods-social-chat-floating-icons' ); ?></p><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'toggle_bg_color', __( 'Background color', 'wpmethods-social-chat-floating-icons' ), 'color' );
								$this->field( $options, 'toggle_gbg_color', __( 'Gradient color (optional)', 'wpmethods-social-chat-floating-icons' ), 'color' );
								$this->field( $options, 'icon_color', __( 'Icon color', 'wpmethods-social-chat-floating-icons' ), 'color' );
								$this->field( $options, 'hover_color', __( 'Hover color', 'wpmethods-social-chat-floating-icons' ), 'color' );
								$this->field( $options, 'pulse_animation_border_color', __( 'Pulse ring color', 'wpmethods-social-chat-floating-icons' ), 'color' );
								?>
							</div></div>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Position & spacing', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'position', __( 'Screen side', 'wpmethods-social-chat-floating-icons' ), 'position' );
								$this->field( $options, 'bottom_offset', __( 'Bottom offset', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'For example: 20px or 5%.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'left_offset', __( 'Left offset', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'Used when the screen side is Left.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'right_offset', __( 'Right offset', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'Used when the screen side is Right.', 'wpmethods-social-chat-floating-icons' ) );
								?>
							</div></div>
						<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Custom CSS', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
		<?php $this->field( $options, 'custom_css', __( 'Custom CSS', 'wpmethods-social-chat-floating-icons' ), 'textarea', __( 'Add CSS only, without style tags. Use #lcWpmethodsChatContainer before your selectors to target this widget. Rules are loaded after the default widget styles.', 'wpmethods-social-chat-floating-icons' ) ); ?>
</div><code>#lcWpmethodsChatContainer .wpmesoch-panel { border-radius: 24px; }</code></div>
</section>
						<section class="wpmesoch-admin-tab-content" id="tab-chat" aria-labelledby="wpmesoch-tab-chat">
							<div class="wpmesoch-section-heading"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'WELCOME YOUR VISITORS', 'wpmethods-social-chat-floating-icons' ); ?></span><h2><?php esc_html_e( 'Chat panels & messages', 'wpmethods-social-chat-floating-icons' ); ?></h2><p><?php esc_html_e( 'Add a personal welcome before visitors continue to WhatsApp or Messenger.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Channel behavior', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'whatsapp_chatbox', __( 'WhatsApp chat panel', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'Open a message composer before WhatsApp. Disable to open the contact link directly.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'messenger_chatbox', __( 'Messenger chat panel', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'Open a welcome panel before continuing to your m.me link.', 'wpmethods-social-chat-floating-icons' ) );
								?>
							</div></div>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Conversation copy', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'chat_title', __( 'Panel title', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'Leave blank to use “How can we help?”.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'chat_greeting', __( 'Welcome message', 'wpmethods-social-chat-floating-icons' ), 'textarea', __( 'Displayed inside both chat panels. Leave blank for a welcome message offering help with products, delivery and orders.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'custom_text', __( 'WhatsApp prefilled message', 'wpmethods-social-chat-floating-icons' ), 'textarea', __( 'Included along with the visitor’s message and optional product details.', 'wpmethods-social-chat-floating-icons' ) );
								?>
							</div></div>
						<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Automatic welcome prompt', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
		<?php
		$this->field( $options, 'welcome_enabled', __( 'Show automatic welcome', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'Reveal a friendly message beside the main button after a delay. This does not automatically open the chat panel.', 'wpmethods-social-chat-floating-icons' ) );
		$this->field( $options, 'welcome_title', __( 'Welcome prompt title', 'wpmethods-social-chat-floating-icons' ), 'text', __( 'The small heading above the welcome message. Leave blank to use Welcome to our store!.', 'wpmethods-social-chat-floating-icons' ) );
		$this->field( $options, 'welcome_text', __( 'Welcome prompt text', 'wpmethods-social-chat-floating-icons' ), 'textarea', __( 'Leave blank for a helpful store welcome. Clicking the prompt opens the contact options or your single contact.', 'wpmethods-social-chat-floating-icons' ) );
		$this->field(
			$options,
			'welcome_delay',
			__( 'Show after (seconds)', 'wpmethods-social-chat-floating-icons' ),
			'number',
			__( 'Default: 4 seconds. Choose 0 to 300 seconds.', 'wpmethods-social-chat-floating-icons' ),
			array(
				'min' => 0,
				'max' => 300,
			)
		);
		$this->field(
			$options,
			'welcome_badge',
			__( 'Welcome badge number', 'wpmethods-social-chat-floating-icons' ),
			'number',
			__( 'Decorative welcome count, not live unread messages. Set 0 to hide the badge; maximum 99.', 'wpmethods-social-chat-floating-icons' ),
			array(
				'min' => 0,
				'max' => 99,
			)
		);
		$this->field( $options, 'welcome_once', __( 'Once per browser tab session', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'After a visitor dismisses the prompt or uses the widget, do not show it again in this tab session. Disable to show it on each page visit.', 'wpmethods-social-chat-floating-icons' ) );
		?>
</div></div>
</section>
						<section class="wpmesoch-admin-tab-content" id="tab-woocommerce" aria-labelledby="wpmesoch-tab-woocommerce">
							<div class="wpmesoch-section-heading"><span class="wpmesoch-eyebrow"><?php esc_html_e( 'MORE CONTEXT, BETTER CONVERSATIONS', 'wpmethods-social-chat-floating-icons' ); ?></span><h2><?php esc_html_e( 'WooCommerce product details', 'wpmethods-social-chat-floating-icons' ); ?></h2><p><?php esc_html_e( 'Help customers ask about the exact product they are viewing.', 'wpmethods-social-chat-floating-icons' ); ?></p></div>
							<?php
							if ( ! function_exists( 'wc_get_product' ) ) :
								?>
								<div class="wpmesoch-info"><?php esc_html_e( 'Activate WooCommerce to use product context. You can prepare your settings below.', 'wpmethods-social-chat-floating-icons' ); ?></div><?php endif; ?>
							<div class="wpmesoch-settings-card"><h3><?php esc_html_e( 'Product context', 'wpmethods-social-chat-floating-icons' ); ?></h3><div class="wpmesoch-fields-grid">
								<?php
								$this->field( $options, 'track_woo_product', __( 'Include product details', 'wpmethods-social-chat-floating-icons' ), 'checkbox', __( 'Append the product name, price, link and selected variation to WhatsApp messages on single product pages.', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'woo_product_msg', __( 'Product introduction', 'wpmethods-social-chat-floating-icons' ), 'textarea' );
								$this->field( $options, 'woo_product_name', __( 'Product name label', 'wpmethods-social-chat-floating-icons' ) );
								$this->field( $options, 'woo_product_price', __( 'Product price label', 'wpmethods-social-chat-floating-icons' ) );
								?>
							</div></div>
						</section>
						<div class="wpmesoch-save-bar">
							<div class="wpmesoch-save-feedback">
								<?php
								$save_notices = get_settings_errors();
								foreach ( $save_notices as $save_notice ) :
									$notice_type = 'updated' === $save_notice['type'] ? 'success' : $save_notice['type'];
									$notice_type = in_array( $notice_type, array( 'success', 'error', 'warning', 'info' ), true ) ? $notice_type : 'info';
									?>
									<div class="notice inline wpmesoch-save-notice notice-<?php echo esc_attr( $notice_type ); ?>" role="<?php echo 'error' === $notice_type ? 'alert' : 'status'; ?>"><p><?php echo wp_kses_post( $save_notice['message'] ); ?></p></div>
								<?php endforeach; ?>
								<span class="wpmesoch-save-status" aria-live="polite">
								<?php
								if ( empty( $save_notices ) ) {
									esc_html_e( 'All sections are saved together.', 'wpmethods-social-chat-floating-icons' ); }
								?>
								</span>
							</div>
							<?php submit_button( __( 'Save changes', 'wpmethods-social-chat-floating-icons' ), 'primary', 'submit', false ); ?>
						</div>
					</form>
					<footer class="wpmesoch-admin-footer"><?php esc_html_e( 'Crafted by', 'wpmethods-social-chat-floating-icons' ); ?> <a href="https://wpmethods.com" target="_blank" rel="noopener noreferrer">WPMethods</a><span aria-hidden="true">·</span><?php esc_html_e( 'Helping your visitors feel connected.', 'wpmethods-social-chat-floating-icons' ); ?></footer>
				</main>
			</div>
		</div>
		<?php
	}
}
