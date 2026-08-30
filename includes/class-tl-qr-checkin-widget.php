<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TL_QR_Checkin_Widget extends \Elementor\Widget_Base {
    public function get_name(): string {
        return 'tl_qr_checkin';
    }

    public function get_title(): string {
        return esc_html__( 'TL QR Check-in', 'tl-qr-checkin' );
    }

    public function get_icon(): string {
        return 'eicon-barcode';
    }

    public function get_categories(): array {
        return [ 'tl-invitation' ];
    }

    public function get_keywords(): array {
        return [ 'qr', 'checkin', 'check-in', 'wedding', 'invitation', 'guest', 'tamu' ];
    }

    public function get_script_depends(): array {
        return [ 'tl-qr-checkin' ];
    }

    public function get_style_depends(): array {
        return [ 'tl-qr-checkin' ];
    }

    public function has_widget_inner_wrapper(): bool {
        return false;
    }

    protected function is_dynamic_content(): bool {
        return true;
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_wedding',
            [
                'label' => esc_html__( 'Data Undangan', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'hero_image',
            [
                'label'   => esc_html__( 'Foto Mempelai', 'tl-qr-checkin' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'dynamic' => [ 'active' => true ],
            ]
        );

        $this->add_control(
            'wedding_title',
            [
                'label'       => esc_html__( 'The Wedding Of', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'THE WEDDING OF',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'groom_name',
            [
                'label'       => esc_html__( 'Nama Groom', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Groom',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'bride_name',
            [
                'label'       => esc_html__( 'Nama Bride', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Bride',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'subtitle_text',
            [
                'label'       => esc_html__( 'Subtitle', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'event_date',
            [
                'label'       => esc_html__( 'Tanggal', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'event_time',
            [
                'label'       => esc_html__( 'Waktu', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'event_venue',
            [
                'label'       => esc_html__( 'Venue', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'event_notes',
            [
                'label'       => esc_html__( 'Notes', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => '',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'ring_logo',
            [
                'label'   => esc_html__( 'Logo Cincin / Logo Brand', 'tl-qr-checkin' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'dynamic' => [ 'active' => true ],
            ]
        );

        $this->add_control(
            'powered_by',
            [
                'label'       => esc_html__( 'Powered By', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'TL Invitation',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_url',
            [
                'label' => esc_html__( 'Parameter URL Tamu', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'guest_param',
            [
                'label'       => esc_html__( 'Parameter Nama', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'to',
                'description' => esc_html__( 'Contoh: ?to=Budi', 'tl-qr-checkin' ),
            ]
        );

        $this->add_control(
            'pax_param',
            [
                'label'       => esc_html__( 'Parameter Pax', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'guest',
                'description' => esc_html__( 'Contoh: &guest=2', 'tl-qr-checkin' ),
            ]
        );

        $this->add_control(
            'tag_param',
            [
                'label'       => esc_html__( 'Parameter Tag', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'tag',
                'description' => esc_html__( 'Contoh: &tag=VIP atau &tag=VVIP. Jika kosong, badge disembunyikan.', 'tl-qr-checkin' ),
            ]
        );

        $this->add_control(
            'guest_fallback',
            [
                'label'       => esc_html__( 'Nama Tamu Fallback', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Tamu Undangan',
                'dynamic'     => [ 'active' => true ],
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_trigger',
            [
                'label' => esc_html__( 'Tombol QR', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'trigger_mode',
            [
                'label'   => esc_html__( 'Posisi', 'tl-qr-checkin' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'fixed',
                'options' => [
                    'fixed'  => esc_html__( 'Fixed kanan atas', 'tl-qr-checkin' ),
                    'inline' => esc_html__( 'Mengikuti posisi widget', 'tl-qr-checkin' ),
                ],
            ]
        );

        $this->add_control(
            'trigger_label',
            [
                'label'       => esc_html__( 'Accessible Label', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'Buka QR Check-in',
                'label_block' => true,
            ]
        );

        $this->add_responsive_control(
            'trigger_top',
            [
                'label'      => esc_html__( 'Jarak Atas', 'tl-qr-checkin' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
                'default'    => [ 'size' => 22, 'unit' => 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tlqr-widget' => '--tlqr-trigger-top: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [ 'trigger_mode' => 'fixed' ],
            ]
        );

        $this->add_responsive_control(
            'trigger_right',
            [
                'label'      => esc_html__( 'Jarak Kanan', 'tl-qr-checkin' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
                'default'    => [ 'size' => 18, 'unit' => 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tlqr-widget' => '--tlqr-trigger-right: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [ 'trigger_mode' => 'fixed' ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'accent_color',
            [
                'label'     => esc_html__( 'Accent', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#B89A67',
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget' => '--tlqr-accent: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__( 'Teks', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#171717',
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget' => '--tlqr-text: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'surface_color',
            [
                'label'     => esc_html__( 'Background Kartu', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget' => '--tlqr-surface: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $clean = static function ( $value ): string {
            return trim( wp_strip_all_tags( (string) $value ) );
        };

        $groom = $clean( $settings['groom_name'] ?? '' );
        $bride = $clean( $settings['bride_name'] ?? '' );
        $couple_name = trim( implode( ' & ', array_filter( [ $groom, $bride ] ) ) );

        $hero_url = '';
        if ( ! empty( $settings['hero_image']['url'] ) ) {
            $hero_url = esc_url_raw( $settings['hero_image']['url'] );
        }

        $logo_url = '';
        if ( ! empty( $settings['ring_logo']['url'] ) ) {
            $logo_url = esc_url_raw( $settings['ring_logo']['url'] );
        }

        $view = [
            'id'             => 'tlqr-' . $this->get_id(),
            'trigger_mode'   => in_array( $settings['trigger_mode'] ?? '', [ 'fixed', 'inline' ], true ) ? $settings['trigger_mode'] : 'fixed',
            'trigger_label'  => $clean( $settings['trigger_label'] ?? 'Buka QR Check-in' ),
            'hero_url'       => $hero_url,
            'wedding_title'  => $clean( $settings['wedding_title'] ?? 'THE WEDDING OF' ),
            'couple_name'    => $couple_name,
            'subtitle_text'  => $clean( $settings['subtitle_text'] ?? '' ),
            'event_date'     => $clean( $settings['event_date'] ?? '' ),
            'event_time'     => $clean( $settings['event_time'] ?? '' ),
            'event_venue'    => $clean( $settings['event_venue'] ?? '' ),
            'event_notes'    => $clean( $settings['event_notes'] ?? '' ),
            'logo_url'       => $logo_url,
            'powered_by'     => $clean( $settings['powered_by'] ?? '' ),
            'guest_param'    => sanitize_key( $settings['guest_param'] ?? 'to' ) ?: 'to',
            'pax_param'      => sanitize_key( $settings['pax_param'] ?? 'guest' ) ?: 'guest',
            'tag_param'      => sanitize_key( $settings['tag_param'] ?? 'tag' ) ?: 'tag',
            'guest_fallback' => $clean( $settings['guest_fallback'] ?? 'Tamu Undangan' ),
            'is_editor'      => $is_editor,
        ];

        require __DIR__ . '/../templates/qr-checkin.php';
    }
}
