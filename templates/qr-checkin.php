<?php
/** @var array $view */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div
    id="<?php echo esc_attr( $view['id'] ); ?>"
    class="tlqr-widget tlqr-trigger--<?php echo esc_attr( $view['trigger_mode'] ); ?>"
    data-guest-param="<?php echo esc_attr( $view['guest_param'] ); ?>"
    data-pax-param="<?php echo esc_attr( $view['pax_param'] ); ?>"
    data-tag-param="<?php echo esc_attr( $view['tag_param'] ); ?>"
    data-guest-fallback="<?php echo esc_attr( $view['guest_fallback'] ); ?>"
    data-editor-preview="<?php echo $view['is_editor'] ? '1' : '0'; ?>"
    data-hero-url="<?php echo esc_url( $view['hero_url'] ); ?>"
    data-logo-url="<?php echo esc_url( $view['logo_url'] ); ?>"
>
    <button class="tlqr-trigger" type="button" aria-label="<?php echo esc_attr( $view['trigger_label'] ); ?>" aria-haspopup="dialog" aria-expanded="false">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 4h6v6H4V4Zm2 2v2h2V6H6Zm8-2h6v6h-6V4Zm2 2v2h2V6h-2ZM4 14h6v6H4v-6Zm2 2v2h2v-2H6Zm8-2h2v2h-2v-2Zm4 0h2v4h-2v-4Zm-4 4h4v2h-4v-2Zm6 2h2v2h-2v-2Zm-8-8h2v2h-2v-2Zm4 0h4v2h-4v-2Z"/>
        </svg>
    </button>

    <div class="tlqr-overlay" hidden>
        <button class="tlqr-backdrop" type="button" aria-label="<?php esc_attr_e( 'Tutup QR Check-in', 'tl-qr-checkin' ); ?>"></button>

        <section class="tlqr-sheet" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'QR Check-in', 'tl-qr-checkin' ); ?>">
            <div class="tlqr-sheet-handle" aria-hidden="true"></div>

            <div class="tlqr-sheet-head">
                <div>
                    <div class="tlqr-sheet-title"><?php esc_html_e( 'QR Check-in', 'tl-qr-checkin' ); ?></div>
                    <div class="tlqr-sheet-subtitle"><?php esc_html_e( 'Tunjukkan kartu ini kepada petugas saat check-in.', 'tl-qr-checkin' ); ?></div>
                </div>
                <button class="tlqr-close" type="button" aria-label="<?php esc_attr_e( 'Tutup', 'tl-qr-checkin' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 7 10 10M17 7 7 17"/></svg>
                </button>
            </div>

            <div class="tlqr-pass-wrap">
                <article class="tlqr-pass" aria-label="<?php esc_attr_e( 'Kartu QR Check-in 9:16', 'tl-qr-checkin' ); ?>">
                    <header class="tlqr-hero<?php echo $view['hero_url'] ? '' : ' tlqr-hero--empty'; ?>">
                        <?php if ( $view['hero_url'] ) : ?>
                            <img class="tlqr-hero-image" src="<?php echo esc_url( $view['hero_url'] ); ?>" alt="" decoding="async" />
                        <?php endif; ?>
                        <div class="tlqr-hero-shade" aria-hidden="true"></div>

                        <div class="tlqr-tag" hidden>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7.5 7.5 11 12 4l4.5 7L21 7.5 19.2 17H4.8L3 7.5Zm3.5 11h11"/></svg>
                            <span data-tlqr-tag></span>
                        </div>

                        <div class="tlqr-hero-copy">
                            <?php if ( $view['wedding_title'] ) : ?>
                                <div class="tlqr-wedding-title"><?php echo esc_html( $view['wedding_title'] ); ?></div>
                            <?php endif; ?>
                            <?php if ( $view['couple_name'] ) : ?>
                                <div class="tlqr-couple-name"><?php echo esc_html( $view['couple_name'] ); ?></div>
                            <?php endif; ?>
                            <?php if ( $view['subtitle_text'] ) : ?>
                                <div class="tlqr-subtitle-text"><?php echo esc_html( $view['subtitle_text'] ); ?></div>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="tlqr-main-card">
                        <div class="tlqr-qr-column">
                            <div class="tlqr-qr-frame" aria-label="<?php esc_attr_e( 'QR code', 'tl-qr-checkin' ); ?>">
                                <canvas class="tlqr-qr-canvas" width="560" height="560"></canvas>
                            </div>
                            <div class="tlqr-scan-title"><?php esc_html_e( 'Scan to check-in', 'tl-qr-checkin' ); ?></div>
                            <div class="tlqr-scan-help"><?php esc_html_e( 'Tunjukkan QR ini di pintu masuk venue.', 'tl-qr-checkin' ); ?></div>
                        </div>

                        <div class="tlqr-details">
                            <div class="tlqr-detail-row">
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.25"/><path d="M5.5 20c.7-4 2.8-6 6.5-6s5.8 2 6.5 6"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Dear', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-guest><?php echo esc_html( $view['guest_fallback'] ); ?></strong>
                                </div>
                            </div>

                            <div class="tlqr-detail-row" data-tlqr-pax-row>
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="2.5"/><circle cx="16.5" cy="9" r="2"/><path d="M3.8 19c.5-3.6 2.2-5.4 5.2-5.4s4.7 1.8 5.2 5.4M14.2 14.3c3.4-.5 5.3 1.1 5.8 4.7"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Pax', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-pax>—</strong>
                                </div>
                            </div>

                            <div class="tlqr-detail-row"<?php echo $view['event_date'] ? '' : ' hidden'; ?> data-tlqr-date-row>
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3.5v4M16 3.5v4M4 10h16"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Date', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-date><?php echo esc_html( $view['event_date'] ); ?></strong>
                                </div>
                            </div>

                            <div class="tlqr-detail-row"<?php echo $view['event_time'] ? '' : ' hidden'; ?> data-tlqr-time-row>
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Time', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-time><?php echo esc_html( $view['event_time'] ); ?></strong>
                                </div>
                            </div>

                            <div class="tlqr-detail-row"<?php echo $view['event_venue'] ? '' : ' hidden'; ?> data-tlqr-venue-row>
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M12 21s6-5.7 6-11a6 6 0 1 0-12 0c0 5.3 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Venue', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-venue><?php echo esc_html( $view['event_venue'] ); ?></strong>
                                </div>
                            </div>

                            <div class="tlqr-detail-row"<?php echo $view['event_notes'] ? '' : ' hidden'; ?> data-tlqr-notes-row>
                                <span class="tlqr-detail-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M6 3.5h9l3 3V20H6V3.5Z"/><path d="M14.5 3.5V7H18M9 11h6M9 14h6M9 17h4"/></svg>
                                </span>
                                <div class="tlqr-detail-copy">
                                    <span class="tlqr-detail-label"><?php esc_html_e( 'Notes', 'tl-qr-checkin' ); ?></span>
                                    <strong data-tlqr-notes><?php echo esc_html( $view['event_notes'] ); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer class="tlqr-pass-footer">
                        <div class="tlqr-brand-mark">
                            <?php if ( $view['logo_url'] ) : ?>
                                <img class="tlqr-logo-image" src="<?php echo esc_url( $view['logo_url'] ); ?>" alt="" decoding="async" />
                            <?php else : ?>
                                <span class="tlqr-rings" aria-hidden="true">
                                    <svg viewBox="0 0 40 26"><circle cx="15" cy="14" r="8"/><circle cx="25" cy="14" r="8"/><path d="M15 3 18 7l-3 3-3-3 3-4Z"/></svg>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ( $view['powered_by'] ) : ?>
                            <div class="tlqr-powered"><span><?php esc_html_e( 'Powered by', 'tl-qr-checkin' ); ?></span> <strong><?php echo esc_html( $view['powered_by'] ); ?></strong></div>
                        <?php endif; ?>
                    </footer>
                </article>
            </div>

            <div class="tlqr-actions">
                <button class="tlqr-download" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v11m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                    <span><?php esc_html_e( 'Download QR', 'tl-qr-checkin' ); ?></span>
                </button>
                <div class="tlqr-status" role="status" aria-live="polite"></div>
            </div>
        </section>
    </div>
</div>
