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
            'hero_image_position',
            [
                'label'       => esc_html__( 'Posisi Foto', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'center center',
                'options'     => [
                    'left top'      => esc_html__( 'Kiri Atas', 'tl-qr-checkin' ),
                    'center top'    => esc_html__( 'Tengah Atas', 'tl-qr-checkin' ),
                    'right top'     => esc_html__( 'Kanan Atas', 'tl-qr-checkin' ),
                    'left center'   => esc_html__( 'Kiri Tengah', 'tl-qr-checkin' ),
                    'center center' => esc_html__( 'Tengah (Default)', 'tl-qr-checkin' ),
                    'right center'  => esc_html__( 'Kanan Tengah', 'tl-qr-checkin' ),
                    'left bottom'   => esc_html__( 'Kiri Bawah', 'tl-qr-checkin' ),
                    'center bottom' => esc_html__( 'Tengah Bawah', 'tl-qr-checkin' ),
                    'right bottom'  => esc_html__( 'Kanan Bawah', 'tl-qr-checkin' ),
                ],
                'description' => esc_html__( 'Tentukan titik fokus foto seperti pengaturan background Elementor.', 'tl-qr-checkin' ),
                'selectors'   => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-hero > .tlqr-hero-image' => 'object-position: {{VALUE}} !important; transform-origin: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'hero_image_size',
            [
                'label'       => esc_html__( 'Display Size', 'tl-qr-checkin' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'cover',
                'options'     => [
                    'cover'   => esc_html__( 'Cover', 'tl-qr-checkin' ),
                    'contain' => esc_html__( 'Contain', 'tl-qr-checkin' ),
                    'none'    => esc_html__( 'Auto', 'tl-qr-checkin' ),
                ],
                'description' => esc_html__( 'Cover memenuhi area; Contain menampilkan seluruh foto; Auto memakai ukuran asli.', 'tl-qr-checkin' ),
                'selectors'   => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-hero > .tlqr-hero-image' => 'object-fit: {{VALUE}} !important;',
                ],
            ]
        );

        // Keep previously saved zoom values working without exposing the retired control on new edits.
        $this->add_control(
            'hero_image_zoom',
            [
                'type'      => \Elementor\Controls_Manager::HIDDEN,
                'default'   => 1,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-hero > .tlqr-hero-image' => 'transform: scale({{VALUE}}) !important;',
                ],
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
            'section_hero_text_style',
            [
                'label' => esc_html__( 'Teks Hero', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'wedding_title_style_heading',
            [
                'label' => esc_html__( 'The Wedding Of', 'tl-qr-checkin' ),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'wedding_title_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-wedding-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'wedding_title_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-wedding-title',
            ]
        );

        $this->add_control(
            'couple_name_style_heading',
            [
                'label'     => esc_html__( 'Nama Pasangan', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'couple_name_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-couple-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'couple_name_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-couple-name',
            ]
        );

        $this->add_control(
            'subtitle_style_heading',
            [
                'label'     => esc_html__( 'Subtitle', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-subtitle-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-subtitle-text',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_card_text_style',
            [
                'label' => esc_html__( 'Teks Kartu', 'tl-qr-checkin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'scan_title_style_heading',
            [
                'label' => esc_html__( 'Judul Scan', 'tl-qr-checkin' ),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'scan_title_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-scan-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'scan_title_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-scan-title',
            ]
        );

        $this->add_control(
            'scan_help_style_heading',
            [
                'label'     => esc_html__( 'Petunjuk Scan', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'scan_help_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-scan-help' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'scan_help_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-scan-help',
            ]
        );

        $this->add_control(
            'detail_label_style_heading',
            [
                'label'     => esc_html__( 'Label Detail', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'detail_label_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-detail-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'detail_label_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-detail-label',
            ]
        );

        $this->add_control(
            'detail_value_style_heading',
            [
                'label'     => esc_html__( 'Isi Detail', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'detail_value_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-detail-copy strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'detail_value_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-detail-copy strong',
            ]
        );

        $this->add_control(
            'powered_style_heading',
            [
                'label'     => esc_html__( 'Powered By', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'powered_color',
            [
                'label'     => esc_html__( 'Warna', 'tl-qr-checkin' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tlqr-widget .tlqr-powered, {{WRAPPER}} .tlqr-widget .tlqr-powered strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'powered_typography',
                'selector' => '{{WRAPPER}} .tlqr-widget .tlqr-powered',
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

        $hero_positions = [
            'left top',
            'center top',
            'right top',
            'left center',
            'center center',
            'right center',
            'left bottom',
            'center bottom',
            'right bottom',
        ];
        $hero_position = in_array( $settings['hero_image_position'] ?? '', $hero_positions, true )
            ? $settings['hero_image_position']
            : 'center center';
        $hero_size = in_array( $settings['hero_image_size'] ?? '', [ 'cover', 'contain', 'none' ], true )
            ? $settings['hero_image_size']
            : 'cover';
        $hero_zoom = isset( $settings['hero_image_zoom'] ) ? (float) $settings['hero_image_zoom'] : 1;
        $hero_zoom = max( 1, min( 2.5, $hero_zoom ) );

        $view = [
            'id'             => 'tlqr-' . $this->get_id(),
            'trigger_mode'   => in_array( $settings['trigger_mode'] ?? '', [ 'fixed', 'inline' ], true ) ? $settings['trigger_mode'] : 'fixed',
            'trigger_label'  => $clean( $settings['trigger_label'] ?? 'Buka QR Check-in' ),
            'hero_url'       => $hero_url,
            'hero_position'  => $hero_position,
            'hero_size'      => $hero_size,
            'hero_zoom'      => $hero_zoom,
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
