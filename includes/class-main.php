<?php
/**7
 * @package wpl-elementor-events-tracker
 */
namespace WPL\Elementor_Events_Tracker;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Settings;
use Elementor\Widget_Base;

class Main {
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	public function hooks() {
		add_action( 'elementor/element/button/section_button/after_section_end', array( $this, 'add_tracking_controls' ), 10, 2 );
		add_action( 'elementor/element/form/section_form_fields/after_section_end', array( $this, 'add_tracking_controls' ), 10, 2 );
		add_action( 'elementor/widget/before_render_content', array( $this, 'before_render' ) );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );
		add_action( 'elementor/admin/after_create_settings/elementor', [ $this, 'register_settings' ] );
		add_action( 'wp_footer', [ $this, 'add_tracker_code' ] );
	}

	/**
	 * Get option value for plugin.
	 *
	 * @param string $key
	 * @param bool   $default
	 *
	 * @return mixed|void
	 */
	public function get_option( $key, $default = false ) {
		return get_option( 'elementor_' . WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_' . $key, $default );
	}

	/**
	 * Add tracker codes to site footer.
	 */
	public function add_tracker_code() {
		$vkontakte_pixel_id = $this->get_option( 'vkontakte_pixel_id' );
		$yandex_metrika_id  = $this->get_option( 'yandex_metrika_id' );
		$facebook_pixel_id  = $this->get_option( 'facebook_pixel_id' );
		$gtag_id            = $this->get_option( 'gtag_id' );
		$analytics_id       = $this->get_option( 'analytics_id' );

		if ( $vkontakte_pixel_id ) {
			?>
			<div id="vk_api_transport"></div>
			<script>
				var pixel;
				window.vkAsyncInit = function() {
					pixel = new VK.Pixel( '<?php echo esc_js( $vkontakte_pixel_id ); ?>' );
				};
				setTimeout(function() {
					var el = document.createElement( 'script' );
					el.type = 'text/javascript';
					el.src = 'https://vk.com/js/api/openapi.js?159';
					el.async = true;
					document.getElementById( 'vk_api_transport' ).appendChild(el);
				}, 0);
			</script>
			<?php
		}

		if ( $yandex_metrika_id ) {
			?>
			<!-- Yandex.Metrika counter -->
			<script type="text/javascript" >
				(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
					m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
				(window, document, "script", "https://cdn.jsdelivr.net/npm/yandex-metrica-watch/tag.js", "ym");

				ym(<?php echo esc_js( $yandex_metrika_id ); ?>, "init", {
					clickmap:true,
					trackLinks:true,
					accurateTrackBounce:true,
					webvisor:true,
					trackHash:true
				});
			</script>
			<noscript><div><img src="https://mc.yandex.ru/watch/5695870" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
			<!-- /Yandex.Metrika counter -->
			<?php
		}

		if ( $facebook_pixel_id ) {
			?>
			<!-- Facebook Pixel Code -->
			<script>
				!function(f,b,e,v,n,t,s)
				{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
					n.callMethod.apply(n,arguments):n.queue.push(arguments)};
					if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
					n.queue=[];t=b.createElement(e);t.async=!0;
					t.src=v;s=b.getElementsByTagName(e)[0];
					s.parentNode.insertBefore(t,s)}(window, document,'script',
					'https://connect.facebook.net/en_US/fbevents.js');
				fbq('init', '<?php echo esc_js( $facebook_pixel_id ); ?>');
				fbq('track', 'PageView');
			</script>
			<noscript><img height="1" width="1" style="display:none"
			               src="https://www.facebook.com/tr?id=<?php echo esc_js( $facebook_pixel_id ); ?>&ev=PageView&noscript=1"
				/></noscript>
			<!-- End Facebook Pixel Code -->
			<?php
		}

		if ( $gtag_id ) {
			?>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $gtag_id ); ?>"></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());
				gtag('config', '<?php echo esc_js( $gtag_id ); ?>');
			</script>
			<?php
		}

		if ( $analytics_id ) {
			?>
			<!-- Google Analytics -->
			<script>
				window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
				ga('create', '<?php echo esc_js( $analytics_id ); ?>', 'auto');
				ga('send', 'pageview');
			</script>
			<script async src='https://www.google-analytics.com/analytics.js'></script>
			<!-- End Google Analytics -->
			<?php
		}
	}

	/**
	 * Create Setting Tab
	 *
	 * @param Settings $settings Elementor "Settings" page in WordPress Dashboard.
	 *
	 * @since 1.3
	 *
	 * @access public
	 */
	public function register_settings( Settings $settings ) {
		$settings->add_section(
			Settings::TAB_INTEGRATIONS,
			WPL_ELEMENTOR_EVENTS_TRACKER_SLUG,
			[
				'label'    => __( 'Events Tracker', 'wpl-elementor-events-tracker' ),
				'callback' => function() {
					$message = __( '<p>After you select the service, the form appears. In this form, you need to provide your contact information. After you fill in the form, the “Service successfully connected” text appears. The created key is now available in the “Keys” section. Use it when you enable the API.</p>', 'wpl-elementor-events-tracker' );

					echo $message;
				},
				'fields'   => [
					WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_vkontakte_pixel_id' => [
						'label'      => __( 'Vkontakte Pixel ID', 'wpl-elementor-events-tracker' ),
						'field_args' => [
							'type' => 'text',
							'desc' => 'description',
						],
					],
					WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_yandex_metrika_id' => [
						'label'      => __( 'Yandex Metrika ID', 'wpl-elementor-events-tracker' ),
						'field_args' => [
							'type' => 'text',
							'desc' => 'description',
						],
					],
					WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_facebook_pixel_id' => [
						'label'      => __( 'Facebook Pixel ID', 'wpl-elementor-events-tracker' ),
						'field_args' => [
							'type' => 'text',
							'desc' => 'description',
						],
					],
					WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_gtag_id' => [
						'label'      => __( 'Global Site Tag ID (gtag.js)', 'wpl-elementor-events-tracker' ),
						'field_args' => [
							'type' => 'text',
							'desc' => 'description',
						],
					],
					WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_analytics_id' => [
						'label'      => __( 'Google Analytics ID (analytics.js)', 'wpl-elementor-events-tracker' ),
						'field_args' => [
							'type' => 'text',
							'desc' => 'description',
						],
					],
				],
			]
		);
	}

	/**
	 * Add required scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			WPL_ELEMENTOR_EVENTS_TRACKER_SLUG . '_app',
			WPL_ELEMENTOR_EVENTS_TRACKER_URL . 'frontend/js/app.js',
			array( 'jquery', 'elementor-frontend' ),
			filemtime( WPL_ELEMENTOR_EVENTS_TRACKER_DIR . 'frontend/js/app.js' ),
			true
		);
	}

	/**
	 * Add new Events Tracking section to buttons/forms
	 * @param Element_Base $element
	 * @param array $args
	 */
	public function add_tracking_controls( $element, $args ) {

		$element->start_controls_section(
			'wpl_elementor_events_tracker',
			array(
				'label' => esc_html__( 'Events Tracking', 'wpl-elementor-events-tracker' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_facebook',
			array(
				'label'       => esc_html__( 'Track with Facebook', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_facebook_event_name',
			array(
				'label'       => esc_html__( 'Facebook Event', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'AddPaymentInfo'       => esc_html__( 'AddPaymentInfo', 'wpl-elementor-events-tracker' ),
					'AddToCart'            => esc_html__( 'AddToCart', 'wpl-elementor-events-tracker' ),
					'AddToWishlist'        => esc_html__( 'AddToWishlist', 'wpl-elementor-events-tracker' ),
					'CompleteRegistration' => esc_html__( 'CompleteRegistration', 'wpl-elementor-events-tracker' ),
					'Contact'              => esc_html__( 'Contact', 'wpl-elementor-events-tracker' ),
					'CustomizeProduct'     => esc_html__( 'CustomizeProduct', 'wpl-elementor-events-tracker' ),
					'Donate'               => esc_html__( 'Donate', 'wpl-elementor-events-tracker' ),
					'FindLocation'         => esc_html__( 'FindLocation', 'wpl-elementor-events-tracker' ),
					'InitiateCheckout'     => esc_html__( 'InitiateCheckout', 'wpl-elementor-events-tracker' ),
					'Lead'                 => esc_html__( 'Lead', 'wpl-elementor-events-tracker' ),
					'Purchase'             => esc_html__( 'Purchase', 'wpl-elementor-events-tracker' ),
					'Schedule'             => esc_html__( 'Schedule', 'wpl-elementor-events-tracker' ),
					'Search'               => esc_html__( 'Search', 'wpl-elementor-events-tracker' ),
					'StartTrial'           => esc_html__( 'StartTrial', 'wpl-elementor-events-tracker' ),
					'SubmitApplication'    => esc_html__( 'SubmitApplication', 'wpl-elementor-events-tracker' ),
					'Subscribe'            => esc_html__( 'Subscribe', 'wpl-elementor-events-tracker' ),
					'ViewContent'          => esc_html__( 'ViewContent', 'wpl-elementor-events-tracker' ),
					'Custom'               => esc_html__( 'Custom', 'wpl-elementor-events-tracker' ),
				],
				'default'     => 'ViewContent',
				'condition'   => array(
					'wpl_elementor_events_tracker_facebook' => 'yes',
				),
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_facebook_event_name_custom',
			array(
				'label'       => esc_html__( 'Custom Event', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Whatsapp', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_facebook'            => 'yes',
					'wpl_elementor_events_tracker_facebook_event_name' => 'Custom',
				),
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_analytics',
			array(
				'label'       => esc_html__( 'Track with Google Analytics (analytics.js)', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_analytics_category',
			array(
				'label'       => esc_html__( 'Event Category', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Outbound Link', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_analytics' => 'yes',
				),
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_analytics_action',
			array(
				'label'       => esc_html__( 'Event Action', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e click', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_analytics' => 'yes',
				),
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_analytics_label',
			array(
				'label'       => esc_html__( 'Event Label', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Fall Campaign', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_analytics' => 'yes',
				),
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_gtag',
			array(
				'label'       => esc_html__( 'Track with Google Universal Tag (gtag.js)', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_gtag_event_name',
			array(
				'label'       => esc_html__( 'Event Name', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Lead', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_gtag' => 'yes',
				),
				'render_type' => 'none',
			)
		);
		/*$element->add_control(
			'wpl_elementor_events_tracker_aw',
			array(
				'label'       => esc_html__( 'Track Adwords Converstion (gtag.js)', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);
		$element->add_control(
			'wpl_elementor_events_tracker_aw_conversion',
			array(
				'label'       => esc_html__( 'Conversion', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e AW-XXXXXXXX/XXXXXXXXX', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_aw' => 'yes',
				),
				'render_type' => 'none',
			)
		);*/

		$element->add_control(
			'wpl_elementor_events_tracker_vkontakte',
			array(
				'label'       => esc_html__( 'Track with Vkontakte', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_vkontakte_event_name',
			array(
				'label'       => esc_html__( 'Event Name', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Lead', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_vkontakte' => 'yes',
				),
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_yandex_metrika',
			array(
				'label'       => esc_html__( 'Track with Yandex Metrika', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::SWITCHER,
				'render_type' => 'none',
			)
		);

		$element->add_control(
			'wpl_elementor_events_tracker_yandex_metrika_event_name',
			array(
				'label'       => esc_html__( 'Event Name', 'wpl-elementor-events-tracker' ),
				'type'        => Controls_Manager::TEXT,
				'show_label'  => true,
				'placeholder' => esc_html__( 'i.e Lead', 'wpl-elementor-events-tracker' ),
				'condition'   => array(
					'wpl_elementor_events_tracker_yandex_metrika' => 'yes',
				),
				'render_type' => 'none',
			)
		);

		$element->end_controls_section();
	}

	/**
	 * @param Widget_Base $element
	 */
	public function before_render( $element ) {

		if ( in_array( $element->get_name(), array( 'button', 'form' ) ) ) {

			$data = $element->get_data();

			$settings     = $data['settings'];
			$attr         = array();
			$has_tracking = true;

			// Vkontakte.
			if ( isset( $settings['wpl_elementor_events_tracker_vkontakte'] ) ) {
				$has_tracking                 = true;
				$attr['vkontakte']            = true;
				$attr['vkontakte_event_name'] = $settings['wpl_elementor_events_tracker_vkontakte_event_name'];
			}

			// Yandex Metrika.
			if ( isset( $settings['wpl_elementor_events_tracker_yandex_metrika'] ) ) {
				$has_tracking                      = true;
				$attr['yandex_metrika']            = true;
				$attr['yandex_metrika_event_name'] = $settings['wpl_elementor_events_tracker_yandex_metrika_event_name'];
				$attr['yandex_metrika_id']         = $this->get_option( 'yandex_metrika_id' );
			}

			// Facebook.
			if ( isset( $settings['wpl_elementor_events_tracker_facebook'] ) ) {
				$has_tracking                = true;
				$attr['facebook']            = true;
				$attr['facebook_event_name'] = $settings['wpl_elementor_events_tracker_facebook_event_name'];

				if ( isset( $settings['wpl_elementor_events_tracker_facebook_event_name_custom'] ) ) {
					$attr['facebook_event_name_custom'] = $settings['wpl_elementor_events_tracker_facebook_event_name_custom'];
				}
			}

			// Google Analytics.
			if ( isset( $settings['wpl_elementor_events_tracker_analytics'] ) ) {
				$has_tracking               = true;
				$attr['analytics']          = true;
				$attr['analytics_category'] = $settings['wpl_elementor_events_tracker_analytics_category'];
				$attr['analytics_action']   = $settings['wpl_elementor_events_tracker_analytics_action'];
				$attr['analytics_label']    = $settings['wpl_elementor_events_tracker_analytics_label'];
			}

			// Google Global Tag (gtag).
			if ( isset( $settings['wpl_elementor_events_tracker_gtag'] ) ) {
				$has_tracking            = true;
				$attr['gtag']            = true;
				$attr['gtag_event_name'] = $settings['wpl_elementor_events_tracker_gtag_event_name'];
			}

			if ( $has_tracking ) {
				$element->add_render_attribute(
					$element->get_name(),
					array(
						'data-wpl_tracker' => json_encode( $attr ),
					)
				);
			}
		}
	}
}

// eol.