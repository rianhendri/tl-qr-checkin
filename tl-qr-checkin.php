<?php
/**
 * Plugin Name: TL QR Check-in
 * Description: Lightweight, read-only Elementor widget that generates a guest QR check-in pass from the current URL entirely in the browser.
 * Version: 1.2.0
 * Author: TL Invitation
 * Text Domain: tl-qr-checkin
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Update URI: https://github.com/rianhendri/tl-qr-checkin
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class TL_QR_Checkin_Plugin {
    public const VERSION = '1.2.0';
    public const MIN_ELEMENTOR_VERSION = '3.24.0';

    private static $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        require_once __DIR__ . '/includes/class-tl-qr-checkin-updater.php';
        $updater = new TL_QR_Checkin_Updater( __FILE__, self::VERSION );
        $updater->register();

        add_action( 'plugins_loaded', [ $this, 'boot' ] );
    }

    public function boot(): void {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
            return;
        }

        if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'elementor_version_notice' ] );
            return;
        }

        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
    }

    public function register_assets(): void {
        $base_url = plugin_dir_url( __FILE__ );

        wp_register_script(
            'tl-qr-vendor',
            $base_url . 'assets/vendor/qrcode/qrcode.browser.js',
            [],
            '1.0.0',
            true
        );

        wp_register_script(
            'tl-qr-checkin',
            $base_url . 'assets/js/tl-qr-checkin.js',
            [ 'tl-qr-vendor', 'elementor-frontend' ],
            self::VERSION,
            true
        );

        wp_register_style(
            'tl-qr-checkin',
            $base_url . 'assets/css/tl-qr-checkin.css',
            [],
            self::VERSION
        );
    }

    public function register_widgets( $widgets_manager ): void {
        require_once __DIR__ . '/includes/class-tl-qr-checkin-widget.php';
        $widgets_manager->register( new \TL_QR_Checkin_Widget() );
    }

    public function register_category( $elements_manager ): void {
        $elements_manager->add_category(
            'tl-invitation',
            [
                'title' => esc_html__( 'TL Invitation', 'tl-qr-checkin' ),
                'icon'  => 'eicon-favorite',
            ]
        );
    }

    public function elementor_missing_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-warning"><p>' . esc_html__( 'TL QR Check-in membutuhkan Elementor agar widget dapat digunakan.', 'tl-qr-checkin' ) . '</p></div>';
    }

    public function elementor_version_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html( sprintf( 'TL QR Check-in membutuhkan Elementor %s atau lebih baru.', self::MIN_ELEMENTOR_VERSION ) )
        );
    }
}

TL_QR_Checkin_Plugin::instance();
