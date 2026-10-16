<?php
/**
 * Frontend assets and accessible chat widget.
 *
 * @package WPMESOCH
 */

namespace WPMESOCH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin frontend.
 */
class Wpmesoch_Front_End {
	/**
	 * Cached validated options.
	 *
	 * @var array|null
	 */
	private $options;

	/**
	 * Register WordPress hooks.
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'wpmesoch_render_chat_buttons' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wpmesoch_enqueue_scripts' ) );
	}

	/**
	 * Read validated options once per request.
	 *
	 * @return array
	 */
	private function settings() {
		if ( null === $this->options ) {
			$this->options                   = Wpmesoch_Settings::get();
			$this->options['wpmesoch_links'] = array_values(
				array_filter(
					$this->options['wpmesoch_links'],
					static function ( $link ) {
						return '1' === $link['enabled'];
					}
				)
			);
		}
		return $this->options;
	}

	/**
	 * Load local assets only when there are configured links.
	 */
	public function wpmesoch_enqueue_scripts() {
		$options = $this->settings();
		if ( empty( $options['wpmesoch_links'] ) ) {
			return;
		}
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$dependencies = array();
		if ( '1' === $options['load_fontawesome'] ) {
			// Registration by another plugin does not mean its font is loaded.
			wp_enqueue_style( 'wpmesoch-fontawesome', WPMESOCH_WPMETHODS_URL . 'assets/css/all.min.css', array(), '6.7.2' );
			$dependencies[] = 'wpmesoch-fontawesome';
		}
		wp_enqueue_style( 'wpmesoch-css', WPMESOCH_WPMETHODS_URL . 'assets/css/front-end' . $suffix . '.css', $dependencies, WPMESOCH_PLUGIN_VERSION );
		$background = $options['toggle_gbg_color'] ? 'linear-gradient(120deg,' . $options['toggle_bg_color'] . ',' . $options['toggle_gbg_color'] . ')' : $options['toggle_bg_color'];
		$css        = sprintf(
			'#lcWpmethodsChatContainer.wpmesoch-chat-container{--wpmesoch-size:%1$dpx;--wpmesoch-icon-size:%2$dpx;--wpmesoch-bg:%3$s;--wpmesoch-color:%4$s;--wpmesoch-hover:%5$s;--wpmesoch-pulse:%6$s;bottom:%7$s;%8$s:%9$s;%10$s:auto;align-items:%11$s;}',
			$options['height_width'],
			$options['icon_size'],
			$background,
			$options['icon_color'],
			$options['hover_color'],
			$options['pulse_animation_border_color'],
			$options['bottom_offset'],
			$options['position'],
			$options[ $options['position'] . '_offset' ],
			'left' === $options['position'] ? 'right' : 'left',
			'left' === $options['position'] ? 'flex-start' : 'flex-end'
		);
		wp_add_inline_style( 'wpmesoch-css', $css );
		if ( '' !== $options['custom_css'] ) {
			wp_add_inline_style( 'wpmesoch-css', $options['custom_css'] );
		}
		wp_enqueue_script( 'wpmesoch-js', WPMESOCH_WPMETHODS_URL . 'assets/js/front-end' . $suffix . '.js', array(), WPMESOCH_PLUGIN_VERSION, true );
	}

	/**
	 * Match services by exact URL host rather than labels or partial matches.
	 *
	 * @param string $url Contact URL.
	 * @return string
	 */
	private function service( $url ) {
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( in_array( $host, array( 'wa.me', 'api.whatsapp.com', 'web.whatsapp.com' ), true ) ) {
			return 'whatsapp';
		}
		if ( in_array( $host, array( 'm.me', 'www.m.me' ), true ) ) {
			return 'messenger';
		}
		if ( in_array( $host, array( 't.me', 'www.t.me', 'telegram.me', 'www.telegram.me', 'telegram.dog', 'www.telegram.dog' ), true ) ) {
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			// Draft messages apply to public usernames and phone contacts, not invite, bot or post links.
			$reserved = array( 'share', 'joinchat', 'addstickers', 'addemoji', 'addlist', 'addtheme', 'addstyle', 'setlanguage', 'confirmphone', 'proxy', 'socks', 'login', 'invoice', 'giftcode', 'boost', 'contact', 'auction' );
			$special  = array( 'start', 'startgroup', 'startchannel', 'startapp', 'game', 'profile', 'direct', 'attach', 'choose' );
			if ( preg_match( '~^/(?:[a-zA-Z][a-zA-Z0-9_]{4,31}|\+[0-9]{5,15})/?$~', $path ) && ! in_array( strtolower( trim( $path, '/' ) ), $reserved, true ) && ! array_intersect( $special, array_keys( $query ) ) ) {
				return 'telegram';
			}
		}
		return '';
	}

	/**
	 * Render contact links and chat panels.
	 */
	public function wpmesoch_render_chat_buttons() {
		$options = $this->settings();
		if ( empty( $options['wpmesoch_links'] ) ) {
			return;
		}
		$single   = 1 === count( $options['wpmesoch_links'] );
		$product_message = $this->wpmesoch_get_product_message();
		$message  = trim( $options['custom_text'] . "\n" . $product_message );
		$title    = $options['chat_title'] ? $options['chat_title'] : __( 'How can we help?', 'wpmethods-social-chat-floating-icons' );
		$greeting = $options['chat_greeting'] ? $options['chat_greeting'] : __( 'Hi! Need help with a product or order? Send us a message.', 'wpmethods-social-chat-floating-icons' );
		?>
		<div class="wpmesoch-chat-container <?php echo $single ? 'wpmesoch-single' : 'wpmesoch-multiple'; ?> wpmesoch-position-<?php echo esc_attr( $options['position'] ); ?>" id="lcWpmethodsChatContainer">
			<?php if ( '1' === $options['welcome_enabled'] ) : ?>
				<div class="wpmesoch-welcome" data-delay="<?php echo esc_attr( $options['welcome_delay'] ); ?>" data-once="<?php echo esc_attr( $options['welcome_once'] ); ?>" hidden>
					<button type="button" class="wpmesoch-welcome-message"><span class="wpmesoch-welcome-label"><?php echo esc_html( $options['welcome_title'] ? $options['welcome_title'] : __( 'Welcome to our store!', 'wpmethods-social-chat-floating-icons' ) ); ?></span><span><?php echo esc_html( $options['welcome_text'] ? $options['welcome_text'] : __( 'Need a hand? Ask us about products, delivery or your order. We are happy to help!', 'wpmethods-social-chat-floating-icons' ) ); ?></span></button>
					<button type="button" class="wpmesoch-welcome-dismiss" aria-label="<?php esc_attr_e( 'Dismiss welcome message', 'wpmethods-social-chat-floating-icons' ); ?>"><span class="wpmesoch-close-icon" aria-hidden="true"></span></button>
				</div>
				<?php if ( $options['welcome_badge'] > 0 ) : ?>
					<span class="wpmesoch-welcome-badge" aria-hidden="true" hidden><?php echo esc_html( $options['welcome_badge'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<div class="wpmesoch-chat-options" id="wpmesoch-chat-options">
				<?php
				foreach ( $options['wpmesoch_links'] as $link ) :
					$service    = $this->service( $link['url'] );
					$service_labels = array( 'whatsapp' => 'WhatsApp', 'messenger' => 'Messenger', 'telegram' => 'Telegram' );
					$label      = $link['label'] ? $link['label'] : ( $service_labels[ $service ] ?? __( 'Open contact link', 'wpmethods-social-chat-floating-icons' ) );
					$background = $link['s_bg_color'] ? 'linear-gradient(120deg,' . $link['bg_color'] . ',' . $link['s_bg_color'] . ')' : $link['bg_color'];
					?>
					<div class="sfiw-icons" style="--wpmesoch-channel-bg:<?php echo esc_attr( $background ); ?>;--wpmesoch-channel-color:<?php echo esc_attr( $link['color'] ); ?>;">
						<?php if ( '1' === $options['show_labels'] ) : ?>
							<span class="label-sfiw" aria-hidden="true"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<a class="wpmesoch-chat-btn" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( $label ); ?>" data-label="<?php echo esc_attr( $label ); ?>"
							data-service="<?php echo esc_attr( $service ); ?>" data-chatbox="<?php echo esc_attr( $service ? $options[ $service . '_chatbox' ] : '0' ); ?>"
							data-base-message="<?php echo esc_attr( $message ); ?>" data-is-product-track="<?php echo esc_attr( $options['track_woo_product'] ); ?>"
							style="background:<?php echo esc_attr( $background ); ?>;color:<?php echo esc_attr( $link['color'] ); ?>;">
							<i class="<?php echo esc_attr( $link['icon'] ); ?>" aria-hidden="true"></i>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
			<section class="wpmesoch-panel" id="wpmesoch-panel" role="dialog" aria-labelledby="wpmesoch-panel-title" hidden>
				<header class="wpmesoch-panel-header">
					<div><strong id="wpmesoch-panel-title"><?php echo esc_html( $title ); ?></strong><span id="wpmesoch-panel-channel"></span></div>
					<button type="button" class="wpmesoch-panel-close" aria-label="<?php esc_attr_e( 'Close chat panel', 'wpmethods-social-chat-floating-icons' ); ?>"><span class="wpmesoch-close-icon" aria-hidden="true"></span></button>
				</header>
				<div class="wpmesoch-panel-body">
					<p class="wpmesoch-greeting"><?php echo esc_html( $greeting ); ?></p>
					<form class="wpmesoch-message-form">
						<div class="wpmesoch-message-field">
							<label for="wpmesoch-message"><?php esc_html_e( 'Your message', 'wpmethods-social-chat-floating-icons' ); ?></label>
							<textarea id="wpmesoch-message" rows="2" maxlength="2000" placeholder="<?php esc_attr_e( 'How can we help?', 'wpmethods-social-chat-floating-icons' ); ?>" aria-describedby="wpmesoch-message-help"></textarea>
						</div>
						<p class="wpmesoch-panel-note" id="wpmesoch-message-help" data-whatsapp="<?php esc_attr_e( 'Send and receive replies in WhatsApp.', 'wpmethods-social-chat-floating-icons' ); ?>" data-telegram="<?php esc_attr_e( 'Send and receive replies in Telegram.', 'wpmethods-social-chat-floating-icons' ); ?>"><?php esc_html_e( 'Send and receive replies in WhatsApp.', 'wpmethods-social-chat-floating-icons' ); ?></p>
						<button type="submit" class="wpmesoch-send" data-whatsapp="<?php esc_attr_e( 'Continue in WhatsApp', 'wpmethods-social-chat-floating-icons' ); ?>" data-telegram="<?php esc_attr_e( 'Continue in Telegram', 'wpmethods-social-chat-floating-icons' ); ?>"><?php esc_html_e( 'Continue in WhatsApp', 'wpmethods-social-chat-floating-icons' ); ?></button>
					</form>
					<div class="wpmesoch-messenger" hidden>
						<p class="wpmesoch-panel-note wpmesoch-messenger-note"><?php esc_html_e( 'Your conversation and replies will open in Messenger.', 'wpmethods-social-chat-floating-icons' ); ?></p>
						<a class="wpmesoch-send" target="_blank" rel="noopener noreferrer" data-messenger-label="<?php esc_attr_e( 'Continue in Messenger', 'wpmethods-social-chat-floating-icons' ); ?>" data-contact-label="<?php esc_attr_e( 'Open contact link', 'wpmethods-social-chat-floating-icons' ); ?>"><?php esc_html_e( 'Continue in Messenger', 'wpmethods-social-chat-floating-icons' ); ?></a>
					</div>
				</div>
			</section>
			<?php if ( ! $single ) : ?>
			<button type="button" class="wpmesoch-chat-toggle wpmesoch-pulse" id="lcWpmethodsChatToggle" hidden aria-expanded="false" aria-controls="wpmesoch-chat-options wpmesoch-panel" aria-label="<?php esc_attr_e( 'Toggle contact options', 'wpmethods-social-chat-floating-icons' ); ?>">
				<i class="<?php echo esc_attr( $options['toggle_icon_class'] ); ?>" aria-hidden="true"></i><span class="wpmesoch-toggle-close wpmesoch-close-icon" aria-hidden="true" hidden></span>
			</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/** Build product context once without loading every available variation. */
	private function wpmesoch_get_product_message() {
		$options = $this->settings();
		if ( '1' !== $options['track_woo_product'] || ! function_exists( 'wc_get_product' ) || ! is_singular( 'product' ) ) {
			return '';
		}
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product ) {
			return '';
		}
		$message  = ( $options['woo_product_msg'] ? $options['woo_product_msg'] : __( 'I am interested in this product', 'wpmethods-social-chat-floating-icons' ) ) . "\n";
		$message .= ( $options['woo_product_name'] ? $options['woo_product_name'] : __( 'Product', 'wpmethods-social-chat-floating-icons' ) ) . ': ' . $product->get_name() . "\n";
		$message .= ( $options['woo_product_price'] ? $options['woo_product_price'] : __( 'Price', 'wpmethods-social-chat-floating-icons' ) ) . ': ' . html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, 'UTF-8' ) . "\n";
		foreach ( $product->get_attributes() as $attribute ) {
			if ( $attribute->get_visible() && ! $attribute->get_variation() ) {
				$message .= wc_attribute_label( $attribute->get_name() ) . ': ' . $product->get_attribute( $attribute->get_name() ) . "\n";
			}
		}
		return $message . get_permalink( $product->get_id() );
	}
}
