<?php

define( 'ABSPATH', __DIR__ . '/' );

final class WP_Error {}

$tlqr_http_response = null;
$tlqr_http_request = null;
$tlqr_site_transient = null;

function plugin_basename( $file ) {
    return basename( dirname( $file ) ) . '/' . basename( $file );
}

function add_filter() {}

function untrailingslashit( $value ) {
    return rtrim( $value, '/\\' );
}

function wp_parse_url( $url ) {
    return parse_url( $url );
}

function wp_remote_get( $url, $args ) {
    global $tlqr_http_request, $tlqr_http_response;
    $tlqr_http_request = [ 'url' => $url, 'args' => $args ];
    return $tlqr_http_response;
}

function is_wp_error( $value ) {
    return $value instanceof WP_Error;
}

function wp_remote_retrieve_response_code( $response ) {
    return isset( $response['response']['code'] ) ? $response['response']['code'] : 0;
}

function wp_remote_retrieve_body( $response ) {
    return isset( $response['body'] ) ? $response['body'] : '';
}

function get_site_transient() {
    global $tlqr_site_transient;
    return $tlqr_site_transient;
}

function esc_html__( $value ) {
    return $value;
}

function esc_url( $value ) {
    return $value;
}

require_once __DIR__ . '/../includes/class-tl-qr-checkin-updater.php';

$tests_run = 0;

function assert_same( $expected, $actual, $message ) {
    global $tests_run;
    $tests_run++;

    if ( $expected !== $actual ) {
        fwrite( STDERR, "FAIL: {$message}\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
        exit( 1 );
    }
}

function release_fixture( $tag = 'v1.2.0' ) {
    return [
        'draft'        => false,
        'prerelease'   => false,
        'published_at' => '2026-08-30T08:00:00Z',
        'tag_name'     => $tag,
        'html_url'     => 'https://github.com/rianhendri/tl-qr-checkin/releases/tag/' . $tag,
        'assets'       => [
            [
                'name'                 => 'tl-qr-checkin.zip',
                'state'                => 'uploaded',
                'size'                 => 123456,
                'browser_download_url' => 'https://github.com/rianhendri/tl-qr-checkin/releases/download/' . $tag . '/tl-qr-checkin.zip',
            ],
        ],
    ];
}

function http_response( $body, $code = 200 ) {
    return [
        'response' => [ 'code' => $code ],
        'body'     => $body,
    ];
}

$valid = TL_QR_Checkin_Updater::build_update( release_fixture(), '1.1.0' );
assert_same( '1.2.0', $valid['version'], 'A stable newer release is accepted.' );
assert_same( 'https://github.com/rianhendri/tl-qr-checkin/releases/download/v1.2.0/tl-qr-checkin.zip', $valid['package'], 'The exact release asset is selected.' );

assert_same( false, TL_QR_Checkin_Updater::build_update( release_fixture( 'v1.1.0' ), '1.1.0' ), 'An equal version is ignored.' );
assert_same( false, TL_QR_Checkin_Updater::build_update( release_fixture( 'v1.0.9' ), '1.1.0' ), 'An older version is ignored.' );

$draft = release_fixture();
$draft['draft'] = true;
assert_same( false, TL_QR_Checkin_Updater::build_update( $draft, '1.1.0' ), 'Draft releases are ignored.' );

$prerelease = release_fixture();
$prerelease['prerelease'] = true;
assert_same( false, TL_QR_Checkin_Updater::build_update( $prerelease, '1.1.0' ), 'Prereleases are ignored.' );

$invalid_version = release_fixture( 'release-next' );
assert_same( false, TL_QR_Checkin_Updater::build_update( $invalid_version, '1.1.0' ), 'Invalid version tags are ignored.' );

$missing_asset = release_fixture();
$missing_asset['assets'] = [];
assert_same( false, TL_QR_Checkin_Updater::build_update( $missing_asset, '1.1.0' ), 'A missing ZIP asset is ignored.' );

$wrong_asset = release_fixture();
$wrong_asset['assets'][0]['name'] = 'plugin.zip';
assert_same( false, TL_QR_Checkin_Updater::build_update( $wrong_asset, '1.1.0' ), 'An unexpected ZIP asset name is ignored.' );

$wrong_repository = release_fixture();
$wrong_repository['html_url'] = 'https://github.com/example/tl-qr-checkin/releases/tag/v1.2.0';
assert_same( false, TL_QR_Checkin_Updater::build_update( $wrong_repository, '1.1.0' ), 'A release URL from another repository is ignored.' );

$wrong_package_repository = release_fixture();
$wrong_package_repository['assets'][0]['browser_download_url'] = 'https://github.com/example/tl-qr-checkin/releases/download/v1.2.0/tl-qr-checkin.zip';
assert_same( false, TL_QR_Checkin_Updater::build_update( $wrong_package_repository, '1.1.0' ), 'A package URL from another repository is ignored.' );

$updater = new TL_QR_Checkin_Updater( '/var/www/wp-content/plugins/tl-qr-checkin/tl-qr-checkin.php', '1.1.0' );
$plugin_data = [
    'UpdateURI' => 'https://github.com/rianhendri/tl-qr-checkin',
    'Version'   => '1.1.0',
];

$tlqr_http_response = http_response( json_encode( release_fixture() ) );
$filtered = $updater->filter_update( false, $plugin_data, 'tl-qr-checkin/tl-qr-checkin.php', [] );
assert_same( '1.2.0', $filtered['version'], 'A valid HTTP response produces update metadata.' );
assert_same( 'https://api.github.com/repos/rianhendri/tl-qr-checkin/releases/latest', $tlqr_http_request['url'], 'Only the configured GitHub API endpoint is requested.' );
assert_same( 5, $tlqr_http_request['args']['timeout'], 'The HTTP timeout is bounded.' );
assert_same( 2, $tlqr_http_request['args']['redirection'], 'HTTP redirects are bounded.' );
assert_same( true, $tlqr_http_request['args']['sslverify'], 'TLS verification remains enabled.' );
assert_same( true, $tlqr_http_request['args']['reject_unsafe_urls'], 'Unsafe URLs are rejected by the WordPress HTTP API.' );
assert_same( false, false !== strpos( $tlqr_http_request['args']['user-agent'], 'http' ), 'The updater User-Agent contains no site URL.' );

$tlqr_http_response = http_response( '{invalid-json' );
assert_same( false, $updater->filter_update( false, $plugin_data, 'tl-qr-checkin/tl-qr-checkin.php', [] ), 'Invalid JSON fails closed.' );

$tlqr_http_response = http_response( '{}', 500 );
assert_same( false, $updater->filter_update( false, $plugin_data, 'tl-qr-checkin/tl-qr-checkin.php', [] ), 'HTTP errors fail closed.' );

$tlqr_http_response = new WP_Error();
assert_same( false, $updater->filter_update( false, $plugin_data, 'tl-qr-checkin/tl-qr-checkin.php', [] ), 'Timeouts and transport errors fail closed.' );

$tlqr_http_request = null;
$existing = [ 'version' => '9.9.9' ];
assert_same( $existing, $updater->filter_update( $existing, $plugin_data, 'another-plugin/plugin.php', [] ), 'Other GitHub-hosted plugins are untouched.' );
assert_same( null, $tlqr_http_request, 'Other plugins do not trigger this updater request.' );

$tlqr_http_request = null;
$wrong_uri_data = $plugin_data;
$wrong_uri_data['UpdateURI'] = 'https://github.com/example/tl-qr-checkin';
assert_same( false, $updater->filter_update( false, $wrong_uri_data, 'tl-qr-checkin/tl-qr-checkin.php', [] ), 'A mismatched Update URI fails closed.' );
assert_same( null, $tlqr_http_request, 'A mismatched Update URI causes no network request.' );

$tlqr_site_transient = (object) [
    'response' => [
        'tl-qr-checkin/tl-qr-checkin.php' => (object) [
            'new_version' => '1.2.0',
            'url'         => 'https://github.com/rianhendri/tl-qr-checkin/releases/tag/v1.2.0',
            'package'     => 'https://github.com/rianhendri/tl-qr-checkin/releases/download/v1.2.0/tl-qr-checkin.zip',
        ],
    ],
];
$plugin_info = $updater->filter_plugin_information( false, 'plugin_information', (object) [ 'slug' => 'tl-qr-checkin' ] );
assert_same( '1.2.0', $plugin_info->version, 'The plugin details modal uses validated cached update data.' );
assert_same( $existing, $updater->filter_plugin_information( $existing, 'plugin_information', (object) [ 'slug' => 'another-plugin' ] ), 'Plugin information for other plugins is untouched.' );

$tlqr_site_transient->response['tl-qr-checkin/tl-qr-checkin.php']->package = 'https://example.com/tl-qr-checkin.zip';
assert_same( false, $updater->filter_plugin_information( false, 'plugin_information', (object) [ 'slug' => 'tl-qr-checkin' ] ), 'Invalid cached package metadata is ignored.' );

fwrite( STDOUT, "Updater smoke tests passed ({$tests_run} assertions).\n" );
