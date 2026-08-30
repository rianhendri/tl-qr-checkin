<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class TL_QR_Checkin_Updater {
    private const UPDATE_URI = 'https://github.com/rianhendri/tl-qr-checkin';
    private const API_URL = 'https://api.github.com/repos/rianhendri/tl-qr-checkin/releases/latest';
    private const API_VERSION = '2026-03-10';
    private const ASSET_NAME = 'tl-qr-checkin.zip';
    private const PLUGIN_SLUG = 'tl-qr-checkin';

    private $plugin_file;
    private $plugin_version;

    public function __construct( string $plugin_file, string $plugin_version ) {
        $this->plugin_file = plugin_basename( $plugin_file );
        $this->plugin_version = $plugin_version;
    }

    public function register(): void {
        add_filter( 'update_plugins_github.com', [ $this, 'filter_update' ], 10, 4 );
        add_filter( 'plugins_api', [ $this, 'filter_plugin_information' ], 10, 3 );
    }

    /**
     * Provide a small details modal from update data already cached by WordPress.
     *
     * @param mixed  $result Existing Plugins API result.
     * @param string $action Requested Plugins API action.
     * @param object $args Request arguments.
     * @return mixed
     */
    public function filter_plugin_information( $result, string $action, $args ) {
        if ( 'plugin_information' !== $action || ! is_object( $args ) || self::PLUGIN_SLUG !== ( $args->slug ?? '' ) ) {
            return $result;
        }

        $updates = get_site_transient( 'update_plugins' );
        if ( ! is_object( $updates ) || empty( $updates->response[ $this->plugin_file ] ) ) {
            return $result;
        }

        $update = (object) $updates->response[ $this->plugin_file ];
        $version = isset( $update->new_version ) ? (string) $update->new_version : (string) ( $update->version ?? '' );
        $release_url = isset( $update->url ) ? (string) $update->url : '';
        $package_url = isset( $update->package ) ? (string) $update->package : '';

        if ( ! self::is_stable_version( $version ) || ! self::is_valid_cached_update_url( $release_url, $package_url, $version ) ) {
            return $result;
        }

        return (object) [
            'name'          => 'TL QR Check-in',
            'slug'          => self::PLUGIN_SLUG,
            'version'       => $version,
            'author'        => 'TL Invitation',
            'homepage'      => self::UPDATE_URI,
            'requires'      => '6.5',
            'requires_php'  => '7.4',
            'download_link' => $package_url,
            'external'      => true,
            'sections'      => [
                'description' => esc_html__( 'Widget Elementor ringan untuk membuat kartu QR Check-in tamu langsung di browser.', 'tl-qr-checkin' ),
                'changelog'   => sprintf(
                    /* translators: %s: GitHub release URL. */
                    esc_html__( 'Catatan rilis lengkap tersedia di GitHub: %s', 'tl-qr-checkin' ),
                    esc_url( $release_url )
                ),
            ],
        ];
    }

    /**
     * Return update metadata only for this plugin and a valid stable GitHub release.
     *
     * @param array|false $update Existing update metadata.
     * @param array       $plugin_data Installed plugin headers.
     * @param string      $plugin_file Installed plugin basename.
     * @param string[]    $locales Installed locales (unused).
     * @return array|false
     */
    public function filter_update( $update, array $plugin_data, string $plugin_file, array $locales ) {
        unset( $locales );

        if ( $this->plugin_file !== $plugin_file ) {
            return $update;
        }

        $update_uri = isset( $plugin_data['UpdateURI'] ) ? untrailingslashit( (string) $plugin_data['UpdateURI'] ) : '';
        if ( self::UPDATE_URI !== $update_uri ) {
            return false;
        }

        $current_version = isset( $plugin_data['Version'] ) ? (string) $plugin_data['Version'] : $this->plugin_version;
        if ( ! self::is_stable_version( $current_version ) ) {
            return false;
        }

        $response = wp_remote_get(
            self::API_URL,
            [
                'timeout'             => 5,
                'redirection'         => 2,
                'sslverify'           => true,
                'reject_unsafe_urls'  => true,
                'limit_response_size' => 1048576,
                'user-agent'          => 'TL-QR-Checkin/' . $this->plugin_version,
                'headers'             => [
                    'Accept'               => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => self::API_VERSION,
                ],
            ]
        );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        if ( ! is_string( $body ) || '' === $body ) {
            return false;
        }

        $release = json_decode( $body, true );
        if ( ! is_array( $release ) ) {
            return false;
        }

        return self::build_update( $release, $current_version );
    }

    /**
     * Convert a GitHub release payload into WordPress update metadata.
     *
     * @param array  $release GitHub release data.
     * @param string $current_version Installed plugin version.
     * @return array|false
     */
    public static function build_update( array $release, string $current_version ) {
        if (
            ! array_key_exists( 'draft', $release ) ||
            ! array_key_exists( 'prerelease', $release ) ||
            false !== $release['draft'] ||
            false !== $release['prerelease']
        ) {
            return false;
        }

        if (
            empty( $release['published_at'] ) ||
            ! is_string( $release['published_at'] ) ||
            1 !== preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $release['published_at'] )
        ) {
            return false;
        }

        $tag = isset( $release['tag_name'] ) && is_string( $release['tag_name'] ) ? $release['tag_name'] : '';
        $version = self::normalize_version( $tag );
        if ( false === $version || ! self::is_stable_version( $current_version ) ) {
            return false;
        }

        if ( version_compare( $version, $current_version, '<=' ) ) {
            return false;
        }

        $release_url = isset( $release['html_url'] ) && is_string( $release['html_url'] ) ? $release['html_url'] : '';
        $expected_release_path = '/rianhendri/tl-qr-checkin/releases/tag/' . rawurlencode( $tag );
        if ( ! self::is_expected_github_url( $release_url, $expected_release_path, true ) ) {
            return false;
        }

        if ( empty( $release['assets'] ) || ! is_array( $release['assets'] ) ) {
            return false;
        }

        $package_url = '';
        foreach ( $release['assets'] as $asset ) {
            if ( ! is_array( $asset ) || self::ASSET_NAME !== ( $asset['name'] ?? '' ) ) {
                continue;
            }

            if ( ! isset( $asset['state'] ) || 'uploaded' !== $asset['state'] ) {
                return false;
            }

            if ( ! isset( $asset['size'] ) || ! is_int( $asset['size'] ) || $asset['size'] < 1 ) {
                return false;
            }

            $candidate = isset( $asset['browser_download_url'] ) && is_string( $asset['browser_download_url'] )
                ? $asset['browser_download_url']
                : '';
            $expected_asset_path = '/rianhendri/tl-qr-checkin/releases/download/' . rawurlencode( $tag ) . '/' . self::ASSET_NAME;
            if ( ! self::is_expected_github_url( $candidate, $expected_asset_path, true ) ) {
                return false;
            }

            $package_url = $candidate;
            break;
        }

        if ( '' === $package_url ) {
            return false;
        }

        return [
            'id'           => self::UPDATE_URI,
            'slug'         => self::PLUGIN_SLUG,
            'version'      => $version,
            'url'          => $release_url,
            'package'      => $package_url,
            'requires_php' => '7.4',
        ];
    }

    private static function normalize_version( string $tag ) {
        $version = 0 === strpos( $tag, 'v' ) ? substr( $tag, 1 ) : $tag;

        return self::is_stable_version( $version ) ? $version : false;
    }

    private static function is_stable_version( string $version ): bool {
        return 1 === preg_match( '/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/', $version );
    }

    private static function is_valid_cached_update_url( string $release_url, string $package_url, string $version ): bool {
        foreach ( [ $version, 'v' . $version ] as $tag ) {
            $release_path = '/rianhendri/tl-qr-checkin/releases/tag/' . rawurlencode( $tag );
            $package_path = '/rianhendri/tl-qr-checkin/releases/download/' . rawurlencode( $tag ) . '/' . self::ASSET_NAME;

            if (
                self::is_expected_github_url( $release_url, $release_path, true ) &&
                self::is_expected_github_url( $package_url, $package_path, true )
            ) {
                return true;
            }
        }

        return false;
    }

    private static function is_expected_github_url( string $url, string $expected_path, bool $exact_path ): bool {
        $parts = wp_parse_url( $url );
        if ( ! is_array( $parts ) ) {
            return false;
        }

        if (
            'https' !== ( $parts['scheme'] ?? '' ) ||
            'github.com' !== ( $parts['host'] ?? '' ) ||
            isset( $parts['user'] ) ||
            isset( $parts['pass'] ) ||
            isset( $parts['port'] ) ||
            isset( $parts['query'] ) ||
            isset( $parts['fragment'] )
        ) {
            return false;
        }

        $path = $parts['path'] ?? '';
        return $exact_path ? $expected_path === $path : 0 === strpos( $path, $expected_path );
    }
}
