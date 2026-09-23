(function () {
    'use strict';

    var OPEN_CLASS = 'is-open';
    var LOCK_CLASS = 'tlqr-lock';
    var TRANSITION_MS = 460;

    function qs(root, selector) {
        return root ? root.querySelector(selector) : null;
    }

    function textOf(root, selector) {
        var el = qs(root, selector);
        return el ? (el.textContent || '').trim() : '';
    }

    function safeString(value, maxLength) {
        var text = value == null ? '' : String(value);
        text = text.replace(/[\u0000-\u001F\u007F]/g, ' ').replace(/\s+/g, ' ').trim();
        return text.slice(0, maxLength || 500);
    }

    function textStyleOf(root, selector, fallback) {
        var element = qs(root, selector);
        var style = element ? window.getComputedStyle(element) : null;
        return {
            color: style ? style.color : fallback.color,
            fontFamily: style ? style.fontFamily : fallback.fontFamily,
            fontSize: style ? parseFloat(style.fontSize) : fallback.fontSize,
            fontStyle: style ? style.fontStyle : 'normal',
            fontWeight: style ? style.fontWeight : fallback.fontWeight,
            textTransform: style ? style.textTransform : (fallback.textTransform || 'none')
        };
    }

    function exportFontSize(style, previewBase, canvasBase, min, max) {
        var previewSize = parseFloat(style.fontSize);
        if (!isFinite(previewSize) || previewSize <= 0) previewSize = previewBase;
        return Math.max(min, Math.min(max, canvasBase * previewSize / previewBase));
    }

    function setCanvasFont(ctx, style, size) {
        var fontStyle = /^(?:italic|oblique)$/.test(style.fontStyle) ? style.fontStyle : 'normal';
        var fontWeight = /^(?:normal|bold|[1-9]00)$/.test(String(style.fontWeight)) ? style.fontWeight : '400';
        var fontFamily = safeString(style.fontFamily, 260) || 'Arial, sans-serif';
        ctx.font = fontStyle + ' ' + fontWeight + ' ' + Math.round(size) + 'px ' + fontFamily;
    }

    function transformText(value, transform) {
        var text = safeString(value, 1000);
        if (transform === 'uppercase') return text.toUpperCase();
        if (transform === 'lowercase') return text.toLowerCase();
        if (transform === 'capitalize') {
            return text.replace(/(^|\s)(\S)/g, function (match, space, letter) {
                return space + letter.toUpperCase();
            });
        }
        return text;
    }

    function slugify(value) {
        var source = safeString(value, 100).toLowerCase();
        if (source.normalize) {
            source = source.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        source = source.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        return source || 'tamu-undangan';
    }

    function roundedRectPath(ctx, x, y, w, h, r) {
        var radius = Math.max(0, Math.min(r, Math.min(w, h) / 2));
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.arcTo(x + w, y, x + w, y + h, radius);
        ctx.arcTo(x + w, y + h, x, y + h, radius);
        ctx.arcTo(x, y + h, x, y, radius);
        ctx.arcTo(x, y, x + w, y, radius);
        ctx.closePath();
    }

    function fillRoundedRect(ctx, x, y, w, h, r, color) {
        ctx.save();
        roundedRectPath(ctx, x, y, w, h, r);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.restore();
    }

    function wrapLines(ctx, text, maxWidth, maxLines) {
        var words = safeString(text, 1000).split(/\s+/).filter(Boolean);
        if (!words.length) return [];
        var lines = [];
        var current = '';

        function splitLongWord(word) {
            var chunks = [];
            var chunk = '';
            var characters = Array.from(word);
            for (var i = 0; i < characters.length; i += 1) {
                var test = chunk + characters[i];
                if (chunk && ctx.measureText(test).width > maxWidth) {
                    chunks.push(chunk);
                    chunk = characters[i];
                } else {
                    chunk = test;
                }
            }
            if (chunk) chunks.push(chunk);
            return chunks;
        }

        for (var i = 0; i < words.length; i += 1) {
            var word = words[i];
            if (ctx.measureText(word).width > maxWidth) {
                var parts = splitLongWord(word);
                if (current) {
                    lines.push(current);
                    current = '';
                }
                for (var p = 0; p < parts.length; p += 1) lines.push(parts[p]);
                continue;
            }
            var candidate = current ? current + ' ' + word : word;
            if (current && ctx.measureText(candidate).width > maxWidth) {
                lines.push(current);
                current = word;
            } else {
                current = candidate;
            }
        }
        if (current) lines.push(current);

        if (maxLines && lines.length > maxLines) {
            lines = lines.slice(0, maxLines);
            var last = lines[maxLines - 1];
            while (last && ctx.measureText(last + '…').width > maxWidth) {
                last = last.slice(0, -1);
            }
            lines[maxLines - 1] = last.replace(/[\s,.]+$/, '') + '…';
        }
        return lines;
    }

    function drawWrappedText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
        var lines = wrapLines(ctx, text, maxWidth, maxLines);
        for (var i = 0; i < lines.length; i += 1) {
            ctx.fillText(lines[i], x, y + i * lineHeight);
        }
        return lines.length;
    }

    function imagePosition(value) {
        var positions = {
            'left top': [0, 0],
            'center top': [.5, 0],
            'right top': [1, 0],
            'left center': [0, .5],
            'center center': [.5, .5],
            'right center': [1, .5],
            'left bottom': [0, 1],
            'center bottom': [.5, 1],
            'right bottom': [1, 1]
        };

        var normalized = safeString(value, 80).toLowerCase();
        if (positions[normalized]) return positions[normalized];

        var parts = normalized.split(/\s+/).filter(Boolean);
        function axisValue(part, fallback) {
            if (part === 'left' || part === 'top') return 0;
            if (part === 'center') return .5;
            if (part === 'right' || part === 'bottom') return 1;
            if (/^-?\d+(?:\.\d+)?%$/.test(part)) {
                return Math.max(0, Math.min(1, parseFloat(part) / 100));
            }
            return fallback;
        }

        return [axisValue(parts[0], .5), axisValue(parts[1], .5)];
    }

    function imageFit(value) {
        return value === 'contain' || value === 'none' ? value : 'cover';
    }

    function imageZoom(value) {
        var zoom = parseFloat(value);
        if (!isFinite(zoom)) zoom = 1;
        return Math.max(1, Math.min(2.5, zoom));
    }

    function drawFittedImage(ctx, img, x, y, w, h, radius, position, fitValue, zoomValue) {
        if (!img.naturalWidth || !img.naturalHeight) return;
        var fit = imageFit(fitValue);
        var scale = fit === 'cover'
            ? Math.max(w / img.naturalWidth, h / img.naturalHeight)
            : fit === 'contain'
                ? Math.min(w / img.naturalWidth, h / img.naturalHeight)
                : 1;
        scale *= imageZoom(zoomValue);
        var dw = img.naturalWidth * scale;
        var dh = img.naturalHeight * scale;
        var anchor = imagePosition(position);
        var dx = x + (w - dw) * anchor[0];
        var dy = y + (h - dh) * anchor[1];

        ctx.save();
        roundedRectPath(ctx, x, y, w, h, radius);
        ctx.clip();
        ctx.drawImage(img, dx, dy, dw, dh);
        ctx.restore();
    }

    function loadCanvasImage(url) {
        if (!url) return Promise.resolve(null);
        return new Promise(function (resolve) {
            var img = new Image();
            var timer = window.setTimeout(function () { resolve(null); }, 7000);
            try {
                var resolved = new URL(url, window.location.href);
                if (resolved.origin !== window.location.origin && resolved.protocol !== 'data:') {
                    img.crossOrigin = 'anonymous';
                }
            } catch (e) {
                // Leave URL as-is. The onerror handler will safely fall back.
            }
            img.onload = function () {
                window.clearTimeout(timer);
                resolve(img);
            };
            img.onerror = function () {
                window.clearTimeout(timer);
                resolve(null);
            };
            img.src = url;
        });
    }

    function drawQrMatrix(ctx, qr, x, y, size) {
        var count = qr.getModuleCount();
        var quiet = 4;
        var cells = count + quiet * 2;
        var cell = Math.max(1, Math.floor(size / cells));
        var actual = cell * cells;
        var startX = Math.round(x + (size - actual) / 2);
        var startY = Math.round(y + (size - actual) / 2);

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x, y, size, size);
        ctx.fillStyle = '#000000';

        for (var row = 0; row < count; row += 1) {
            for (var col = 0; col < count; col += 1) {
                if (qr.isDark(row, col)) {
                    ctx.fillRect(
                        startX + (col + quiet) * cell,
                        startY + (row + quiet) * cell,
                        cell,
                        cell
                    );
                }
            }
        }
    }

    function renderQrCanvas(canvas, qr) {
        if (!canvas) return;
        var size = 560;
        canvas.width = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) return;
        ctx.imageSmoothingEnabled = false;
        if (qr) drawQrMatrix(ctx, qr, 0, 0, size);
        else {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);
        }
    }

    function drawIcon(ctx, type, x, y, size, color) {
        ctx.save();
        ctx.translate(x, y);
        ctx.scale(size / 24, size / 24);
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.7;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();

        if (type === 'user') {
            ctx.moveTo(15.25, 8);
            ctx.arc(12, 8, 3.25, 0, Math.PI * 2);
            ctx.moveTo(5.5, 20);
            ctx.bezierCurveTo(6.2, 16, 8.3, 14, 12, 14);
            ctx.bezierCurveTo(15.7, 14, 17.8, 16, 18.5, 20);
        } else if (type === 'pax') {
            ctx.moveTo(11.5, 8);
            ctx.arc(9, 8, 2.5, 0, Math.PI * 2);
            ctx.moveTo(18.5, 9);
            ctx.arc(16.5, 9, 2, 0, Math.PI * 2);
            ctx.moveTo(3.8, 19);
            ctx.bezierCurveTo(4.3, 15.4, 6, 13.6, 9, 13.6);
            ctx.bezierCurveTo(12, 13.6, 13.7, 15.4, 14.2, 19);
            ctx.moveTo(14.2, 14.3);
            ctx.bezierCurveTo(17.6, 13.8, 19.5, 15.4, 20, 19);
        } else if (type === 'calendar') {
            ctx.moveTo(6, 5.5);
            ctx.lineTo(18, 5.5);
            ctx.quadraticCurveTo(20, 5.5, 20, 7.5);
            ctx.lineTo(20, 17.5);
            ctx.quadraticCurveTo(20, 19.5, 18, 19.5);
            ctx.lineTo(6, 19.5);
            ctx.quadraticCurveTo(4, 19.5, 4, 17.5);
            ctx.lineTo(4, 7.5);
            ctx.quadraticCurveTo(4, 5.5, 6, 5.5);
            ctx.moveTo(8, 3.5); ctx.lineTo(8, 7.5);
            ctx.moveTo(16, 3.5); ctx.lineTo(16, 7.5);
            ctx.moveTo(4, 10); ctx.lineTo(20, 10);
        } else if (type === 'clock') {
            ctx.moveTo(20, 12);
            ctx.arc(12, 12, 8, 0, Math.PI * 2);
            ctx.moveTo(12, 7); ctx.lineTo(12, 12); ctx.lineTo(15, 14);
        } else if (type === 'pin') {
            ctx.moveTo(12, 21);
            ctx.bezierCurveTo(12, 21, 6, 15.3, 6, 10);
            ctx.bezierCurveTo(6, 6.7, 8.7, 4, 12, 4);
            ctx.bezierCurveTo(15.3, 4, 18, 6.7, 18, 10);
            ctx.bezierCurveTo(18, 15.3, 12, 21, 12, 21);
            ctx.moveTo(14, 10);
            ctx.arc(12, 10, 2, 0, Math.PI * 2);
        } else if (type === 'note') {
            ctx.moveTo(6, 3.5); ctx.lineTo(15, 3.5); ctx.lineTo(18, 6.5);
            ctx.lineTo(18, 20); ctx.lineTo(6, 20); ctx.closePath();
            ctx.moveTo(14.5, 3.5); ctx.lineTo(14.5, 7); ctx.lineTo(18, 7);
            ctx.moveTo(9, 11); ctx.lineTo(15, 11);
            ctx.moveTo(9, 14); ctx.lineTo(15, 14);
            ctx.moveTo(9, 17); ctx.lineTo(13, 17);
        }
        ctx.stroke();
        ctx.restore();
    }

    function drawRings(ctx, x, y, color) {
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 5;
        ctx.beginPath();
        ctx.arc(x + 35, y + 34, 26, 0, Math.PI * 2);
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(x + 67, y + 34, 26, 0, Math.PI * 2);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x + 35, y - 2);
        ctx.lineTo(x + 48, y + 10);
        ctx.lineTo(x + 35, y + 21);
        ctx.lineTo(x + 22, y + 10);
        ctx.closePath();
        ctx.stroke();
        ctx.restore();
    }

    function canvasToBlob(canvas) {
        return new Promise(function (resolve, reject) {
            try {
                if (canvas.toBlob) {
                    canvas.toBlob(function (blob) {
                        if (blob) resolve(blob);
                        else reject(new Error('PNG export gagal.'));
                    }, 'image/png');
                    return;
                }
                var dataUrl = canvas.toDataURL('image/png');
                var parts = dataUrl.split(',');
                var binary = atob(parts[1]);
                var bytes = new Uint8Array(binary.length);
                for (var i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
                resolve(new Blob([bytes], { type: 'image/png' }));
            } catch (error) {
                reject(error);
            }
        });
    }

    function triggerDownload(blob, filename) {
        var url = URL.createObjectURL(blob);
        var anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = filename;
        anchor.rel = 'noopener';
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        window.setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
    }

    function hasOpenOverlay() {
        return !!document.querySelector('.tlqr-overlay.' + OPEN_CLASS);
    }

    function TLQRCheckin(root) {
        this.root = root;
        this.trigger = qs(root, '.tlqr-trigger');
        this.overlay = qs(root, '.tlqr-overlay');
        this.backdrop = qs(root, '.tlqr-backdrop');
        this.sheet = qs(root, '.tlqr-sheet');
        this.closeButton = qs(root, '.tlqr-close');
        this.downloadButton = qs(root, '.tlqr-download');
        this.status = qs(root, '.tlqr-status');
        this.qrCanvas = qs(root, '.tlqr-qr-canvas');
        this.qr = null;
        this.qrCode = '';
        this.closeTimer = 0;
        this.lastFocus = null;
        this.isEditor = root.dataset.editorPreview === '1';
        this.init();
    }

    TLQRCheckin.prototype.init = function () {
        if (!this.trigger || !this.overlay || this.root.dataset.tlqrReady === '1') return;
        this.root.dataset.tlqrReady = '1';
        this.syncGuestData();

        this.trigger.addEventListener('click', this.open.bind(this));
        if (this.backdrop) this.backdrop.addEventListener('click', this.close.bind(this));
        if (this.closeButton) this.closeButton.addEventListener('click', this.close.bind(this));
        if (this.downloadButton) this.downloadButton.addEventListener('click', this.download.bind(this));

        this.overlay.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') this.close();
        }.bind(this));
    };

    TLQRCheckin.prototype.getParam = function (name) {
        if (!name) return '';
        try {
            return safeString(new URL(window.location.href).searchParams.get(name) || '', 220);
        } catch (error) {
            return '';
        }
    };

    TLQRCheckin.prototype.getQrCode = function () {
        try {
            var code = new URL(window.location.href).searchParams.get('qr');
            if (code === null && this.isEditor) code = 'KODEQRNYA';
            if (code === null || !code || code.length > 120 || /\s|[\u0000-\u001F\u007F]/.test(code)) return '';
            return code;
        } catch (error) {
            return '';
        }
    };

    TLQRCheckin.prototype.syncGuestData = function () {
        var guest = this.getParam(this.root.dataset.guestParam || 'to');
        var pax = this.getParam(this.root.dataset.paxParam || 'guest');
        var tag = this.getParam(this.root.dataset.tagParam || 'tag');
        var fallback = safeString(this.root.dataset.guestFallback || 'Tamu Undangan', 160);

        if (!guest) guest = fallback;
        if (this.isEditor) {
            if (!pax) pax = '2';
            if (!tag) tag = 'VIP';
        }

        var guestNode = qs(this.root, '[data-tlqr-guest]');
        var paxNode = qs(this.root, '[data-tlqr-pax]');
        var tagNode = qs(this.root, '[data-tlqr-tag]');
        var tagWrap = qs(this.root, '.tlqr-tag');
        var paxRow = qs(this.root, '[data-tlqr-pax-row]');

        if (guestNode) guestNode.textContent = guest;
        if (paxNode) paxNode.textContent = /^\d+(?:[.,]\d+)?$/.test(pax) ? pax + ' Pax' : pax;
        if (paxRow) paxRow.hidden = !pax;
        if (tagNode) tagNode.textContent = tag;
        if (tagWrap) tagWrap.hidden = !tag;
    };

    TLQRCheckin.prototype.makeQr = function () {
        this.qrCode = this.getQrCode();
        this.qr = null;
        var codeNode = qs(this.root, '[data-tlqr-code]');
        var codeWrap = qs(this.root, '.tlqr-code');
        if (codeNode) codeNode.textContent = this.qrCode;
        if (codeWrap) codeWrap.hidden = !this.qrCode;
        if (!this.qrCode) {
            renderQrCanvas(this.qrCanvas, null);
            if (this.downloadButton) this.downloadButton.disabled = true;
            this.setStatus('Kode QR tidak tersedia. Tambahkan ?qr=KODEQRNYA pada URL (maksimal 120 karakter).');
            return false;
        }
        if (!window.TLQRVendor || !window.TLQRVendor.QRCode) {
            throw new Error('QR engine tidak tersedia.');
        }
        var Levels = window.TLQRVendor.QRErrorCorrectLevel;
        var qr = new window.TLQRVendor.QRCode(-1, Levels.M);
        qr.addData(this.qrCode);
        qr.make();
        this.qr = qr;
        renderQrCanvas(this.qrCanvas, qr);
        if (this.downloadButton) this.downloadButton.disabled = false;
        return true;
    };

    TLQRCheckin.prototype.open = function () {
        window.clearTimeout(this.closeTimer);
        this.lastFocus = document.activeElement;
        this.syncGuestData();
        this.setStatus('');

        try {
            this.makeQr();
        } catch (error) {
            this.qr = null;
            renderQrCanvas(this.qrCanvas, null);
            if (this.downloadButton) this.downloadButton.disabled = true;
            this.setStatus('QR tidak dapat dibuat. Periksa kode QR pada URL.');
            if (window.console && console.error) console.error('[TL QR Check-in]', error);
        }

        this.overlay.hidden = false;
        this.trigger.setAttribute('aria-expanded', 'true');
        document.body.classList.add(LOCK_CLASS);
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                this.overlay.classList.add(OPEN_CLASS);
                if (this.closeButton) this.closeButton.focus({ preventScroll: true });
            }.bind(this));
        }.bind(this));
    };

    TLQRCheckin.prototype.close = function () {
        if (!this.overlay || this.overlay.hidden) return;
        this.overlay.classList.remove(OPEN_CLASS);
        this.trigger.setAttribute('aria-expanded', 'false');

        window.clearTimeout(this.closeTimer);
        this.closeTimer = window.setTimeout(function () {
            this.overlay.hidden = true;
            if (!hasOpenOverlay()) document.body.classList.remove(LOCK_CLASS);
            if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
                this.lastFocus.focus({ preventScroll: true });
            }
        }.bind(this), TRANSITION_MS);
    };

    TLQRCheckin.prototype.setStatus = function (message) {
        if (this.status) this.status.textContent = message || '';
    };

    TLQRCheckin.prototype.collectCardData = function () {
        var heroImage = qs(this.root, '.tlqr-hero-image');
        var heroStyle = heroImage ? window.getComputedStyle(heroImage) : null;
        return {
            qrCode: this.qrCode,
            tag: textOf(this.root, '[data-tlqr-tag]'),
            weddingTitle: textOf(this.root, '.tlqr-wedding-title'),
            coupleName: textOf(this.root, '.tlqr-couple-name'),
            subtitle: textOf(this.root, '.tlqr-subtitle-text'),
            guest: textOf(this.root, '[data-tlqr-guest]'),
            pax: textOf(this.root, '[data-tlqr-pax]'),
            date: textOf(this.root, '[data-tlqr-date]'),
            time: textOf(this.root, '[data-tlqr-time]'),
            venue: textOf(this.root, '[data-tlqr-venue]'),
            notes: textOf(this.root, '[data-tlqr-notes]'),
            poweredBy: textOf(this.root, '.tlqr-powered strong'),
            heroUrl: this.root.dataset.heroUrl || '',
            heroPosition: heroStyle ? heroStyle.objectPosition : (this.root.dataset.heroPosition || 'center center'),
            heroSize: imageFit(heroStyle ? heroStyle.objectFit : this.root.dataset.heroSize),
            heroZoom: imageZoom(this.root.dataset.heroZoom),
            logoUrl: this.root.dataset.logoUrl || ''
        };
    };

    TLQRCheckin.prototype.renderDownloadCanvas = async function () {
        if (!this.qr && !this.makeQr()) throw new Error('Kode QR tidak tersedia.');

        var data = this.collectCardData();
        var computed = window.getComputedStyle(this.root);
        var accent = computed.getPropertyValue('--tlqr-accent').trim() || '#b89a67';
        var text = computed.getPropertyValue('--tlqr-text').trim() || '#171717';
        var surface = computed.getPropertyValue('--tlqr-surface').trim() || '#ffffff';
        var muted = '#77736d';
        var line = '#e9e6e0';
        var textStyles = {
            weddingTitle: textStyleOf(this.root, '.tlqr-wedding-title', { color: '#f2dfbd', fontFamily: 'Arial, sans-serif', fontSize: 8, fontWeight: '700', textTransform: 'uppercase' }),
            coupleName: textStyleOf(this.root, '.tlqr-couple-name', { color: '#ffffff', fontFamily: 'Georgia, serif', fontSize: 31, fontWeight: '400' }),
            subtitle: textStyleOf(this.root, '.tlqr-subtitle-text', { color: 'rgba(255,255,255,.88)', fontFamily: 'Arial, sans-serif', fontSize: 8, fontWeight: '400' }),
            scanTitle: textStyleOf(this.root, '.tlqr-scan-title', { color: accent, fontFamily: 'Arial, sans-serif', fontSize: 10, fontWeight: '800' }),
            scanHelp: textStyleOf(this.root, '.tlqr-scan-help', { color: muted, fontFamily: 'Arial, sans-serif', fontSize: 7.5, fontWeight: '400' }),
            detailLabel: textStyleOf(this.root, '.tlqr-detail-label', { color: muted, fontFamily: 'Arial, sans-serif', fontSize: 7, fontWeight: '400' }),
            detailValue: textStyleOf(this.root, '.tlqr-detail-copy strong', { color: text, fontFamily: 'Arial, sans-serif', fontSize: 8.4, fontWeight: '700' }),
            poweredLabel: textStyleOf(this.root, '.tlqr-powered', { color: muted, fontFamily: 'Arial, sans-serif', fontSize: 7, fontWeight: '400' }),
            poweredValue: textStyleOf(this.root, '.tlqr-powered strong', { color: text, fontFamily: 'Arial, sans-serif', fontSize: 7, fontWeight: '700' })
        };

        if (document.fonts && document.fonts.ready) {
            try {
                await document.fonts.ready;
            } catch (fontError) {
                // The browser will use its normal fallback font in the exported Canvas.
            }
        }

        var results = await Promise.all([
            loadCanvasImage(data.heroUrl),
            loadCanvasImage(data.logoUrl)
        ]);
        var hero = results[0];
        var logo = results[1];

        var canvas = document.createElement('canvas');
        canvas.width = 1080;
        canvas.height = 1920;
        var ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) throw new Error('Canvas tidak tersedia.');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        // Full-bleed, square-edged print layout. Only the photo itself is cropped.
        var heroH = 730;
        var footerY = 1730;
        ctx.fillStyle = surface;
        ctx.fillRect(0, 0, 1080, 1920);
        ctx.fillStyle = '#202020';
        ctx.fillRect(0, 0, 1080, heroH);
        if (hero) {
            drawFittedImage(ctx, hero, 0, 0, 1080, heroH, 0, data.heroPosition, data.heroSize, data.heroZoom);
        } else {
            var fallbackGradient = ctx.createLinearGradient(0, 0, 1080, heroH);
            fallbackGradient.addColorStop(0, '#3b3b3b');
            fallbackGradient.addColorStop(1, '#151515');
            ctx.fillStyle = fallbackGradient;
            ctx.fillRect(0, 0, 1080, heroH);
        }

        var shade = ctx.createLinearGradient(0, 210, 0, heroH);
        shade.addColorStop(0, 'rgba(0,0,0,0)');
        shade.addColorStop(1, 'rgba(0,0,0,.76)');
        ctx.fillStyle = shade;
        ctx.fillRect(0, 0, 1080, heroH);

        if (data.tag) {
            ctx.font = '700 24px Arial, sans-serif';
            var tagWidth = Math.min(330, Math.max(135, ctx.measureText(data.tag.toUpperCase()).width + 72));
            fillRoundedRect(ctx, 48, 48, tagWidth, 58, 29, 'rgba(255,255,255,.94)');
            ctx.strokeStyle = accent;
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(70, 82);
            ctx.lineTo(79, 69);
            ctx.lineTo(90, 82);
            ctx.lineTo(102, 69);
            ctx.lineTo(109, 82);
            ctx.stroke();
            ctx.fillStyle = '#4b3d28';
            ctx.textBaseline = 'middle';
            ctx.fillText(data.tag.toUpperCase(), 126, 78, tagWidth - 92);
        }

        ctx.textAlign = 'center';
        ctx.textBaseline = 'alphabetic';
        if (data.weddingTitle) {
            var weddingTitleSize = exportFontSize(textStyles.weddingTitle, 8, 27, 14, 52);
            ctx.fillStyle = textStyles.weddingTitle.color;
            setCanvasFont(ctx, textStyles.weddingTitle, weddingTitleSize);
            ctx.fillText(transformText(data.weddingTitle, textStyles.weddingTitle.textTransform), 540, 515, 920);
        }
        if (data.coupleName) {
            var coupleNameSize = exportFontSize(textStyles.coupleName, 31, 70, 26, 120);
            var coupleLineHeight = Math.round(coupleNameSize * 1.1);
            ctx.fillStyle = textStyles.coupleName.color;
            setCanvasFont(ctx, textStyles.coupleName, coupleNameSize);
            var coupleLines = wrapLines(ctx, transformText(data.coupleName, textStyles.coupleName.textTransform), 920, 2);
            var coupleStartY = 610 - Math.max(0, coupleLines.length - 1) * coupleLineHeight / 2;
            for (var c = 0; c < coupleLines.length; c += 1) {
                ctx.fillText(coupleLines[c], 540, coupleStartY + c * coupleLineHeight);
            }
        }
        if (data.subtitle) {
            var subtitleSize = exportFontSize(textStyles.subtitle, 8, 22, 12, 46);
            ctx.fillStyle = textStyles.subtitle.color;
            setCanvasFont(ctx, textStyles.subtitle, subtitleSize);
            ctx.fillText(transformText(data.subtitle, textStyles.subtitle.textTransform), 540, 685, 900);
        }

        // The QR has its own white quiet zone, without a second rounded card.
        ctx.fillStyle = surface;
        ctx.fillRect(0, heroH, 1080, footerY - heroH);
        ctx.fillStyle = accent;
        ctx.fillRect(80, 790, 6, 42);
        ctx.fillStyle = textStyles.detailValue.color;
        ctx.textAlign = 'left';
        setCanvasFont(ctx, textStyles.detailValue, 30);
        ctx.fillText('KARTU CHECK-IN', 112, 822);
        ctx.strokeStyle = line;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(80, 850);
        ctx.lineTo(1000, 850);
        ctx.stroke();

        var qrX = 107, qrY = 878, qrSize = 440;
        drawQrMatrix(ctx, this.qr, qrX, qrY, qrSize);

        ctx.textAlign = 'center';
        var scanTitleSize = exportFontSize(textStyles.scanTitle, 10, 31, 14, 54);
        ctx.fillStyle = textStyles.scanTitle.color;
        setCanvasFont(ctx, textStyles.scanTitle, scanTitleSize);
        ctx.fillText(transformText(textOf(this.root, '.tlqr-scan-title'), textStyles.scanTitle.textTransform), qrX + qrSize / 2, 1363, 455);
        var scanHelpSize = exportFontSize(textStyles.scanHelp, 7.5, 21, 10, 42);
        ctx.fillStyle = textStyles.scanHelp.color;
        setCanvasFont(ctx, textStyles.scanHelp, scanHelpSize);
        drawWrappedText(ctx, transformText(textOf(this.root, '.tlqr-scan-help'), textStyles.scanHelp.textTransform), qrX + qrSize / 2, 1399, 460, Math.round(scanHelpSize * 1.3), 2);

        ctx.strokeStyle = line;
        ctx.beginPath();
        ctx.moveTo(108, 1440);
        ctx.lineTo(547, 1440);
        ctx.stroke();
        ctx.fillStyle = textStyles.scanTitle.color;
        setCanvasFont(ctx, textStyles.scanTitle, 22);
        ctx.fillText(textOf(this.root, '.tlqr-code-label').toUpperCase(), 327, 1483, 440);

        // Shrink and wrap the whole token; never ellipsize a manual check-in code.
        var codeSize = 46;
        var codeLines;
        do {
            setCanvasFont(ctx, textStyles.detailValue, codeSize);
            codeLines = wrapLines(ctx, data.qrCode, 440);
            if (codeLines.length <= 4) break;
            codeSize -= 2;
        } while (codeSize >= 14);
        ctx.fillStyle = textStyles.detailValue.color;
        for (var codeLine = 0; codeLine < codeLines.length; codeLine += 1) {
            ctx.fillText(codeLines[codeLine], 327, 1534 + codeLine * Math.round(codeSize * 1.22), 440);
        }

        // Details.
        var detailsX = 615;
        var detailsW = 385;
        var rowY = 880;
        var rows = [];
        rows.push({ icon: 'user', label: 'Dear', value: data.guest, max: 2 });
        if (data.pax) rows.push({ icon: 'pax', label: 'Pax', value: data.pax, max: 1 });
        if (data.date) rows.push({ icon: 'calendar', label: 'Date', value: data.date, max: 2 });
        if (data.time) rows.push({ icon: 'clock', label: 'Time', value: data.time, max: 1 });
        if (data.venue) rows.push({ icon: 'pin', label: 'Venue', value: data.venue, max: 3 });
        if (data.notes) rows.push({ icon: 'note', label: 'Notes', value: data.notes, max: 3 });

        var availableH = 840;
        var rowH = Math.floor(availableH / Math.max(rows.length, 1));
        rowH = Math.min(165, Math.max(108, rowH));

        for (var r = 0; r < rows.length; r += 1) {
            var item = rows[r];
            if (r > 0) {
                ctx.strokeStyle = line;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(detailsX, rowY - 16);
                ctx.lineTo(detailsX + detailsW, rowY - 16);
                ctx.stroke();
            }
            drawIcon(ctx, item.icon, detailsX, rowY + 16, 54, accent);

            ctx.textAlign = 'left';
            var detailLabelSize = exportFontSize(textStyles.detailLabel, 7, 22, 12, 38);
            ctx.fillStyle = textStyles.detailLabel.color;
            setCanvasFont(ctx, textStyles.detailLabel, detailLabelSize);
            ctx.fillText(transformText(item.label, textStyles.detailLabel.textTransform), detailsX + 86, rowY + 37);
            var detailValueSize = Math.min(
                exportFontSize(textStyles.detailValue, 8.4, 30, 14, 52),
                Math.max(26, rowH * .25)
            );
            ctx.fillStyle = textStyles.detailValue.color;
            setCanvasFont(ctx, textStyles.detailValue, detailValueSize);
            drawWrappedText(ctx, transformText(item.value, textStyles.detailValue.textTransform), detailsX + 86, rowY + 76, detailsW - 86, Math.round(detailValueSize * 1.2), Math.min(item.max, 2));
            rowY += rowH;
        }

        // Edge-to-edge print footer.
        var footerH = 192;
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, footerY, 1080, footerH);
        ctx.strokeStyle = line;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, footerY);
        ctx.lineTo(1080, footerY);
        ctx.stroke();

        if (logo) {
            var logoMaxW = 230, logoMaxH = 100;
            var logoScale = Math.min(logoMaxW / logo.naturalWidth, logoMaxH / logo.naturalHeight, 1);
            var lw = logo.naturalWidth * logoScale;
            var lh = logo.naturalHeight * logoScale;
            ctx.drawImage(logo, 80, footerY + (footerH - lh) / 2, lw, lh);
        } else {
            drawRings(ctx, 80, footerY + 60, accent);
        }

        if (data.poweredBy) {
            ctx.textAlign = 'right';
            var poweredLabelSize = exportFontSize(textStyles.poweredLabel, 7, 22, 12, 40);
            ctx.fillStyle = textStyles.poweredLabel.color;
            setCanvasFont(ctx, textStyles.poweredLabel, poweredLabelSize);
            ctx.fillText(transformText('Powered by', textStyles.poweredLabel.textTransform), 1000, footerY + 76);
            var poweredValueSize = exportFontSize(textStyles.poweredValue, 7, 31, 14, 58);
            ctx.fillStyle = textStyles.poweredValue.color;
            setCanvasFont(ctx, textStyles.poweredValue, poweredValueSize);
            var poweredLines = wrapLines(ctx, transformText(data.poweredBy, textStyles.poweredValue.textTransform), 430, 2);
            for (var p = 0; p < poweredLines.length; p += 1) {
                ctx.fillText(poweredLines[p], 1000, footerY + 119 + p * Math.round(poweredValueSize * 1.1));
            }
        }

        return { canvas: canvas, heroMissing: !!data.heroUrl && !hero, logoMissing: !!data.logoUrl && !logo };
    };

    TLQRCheckin.prototype.download = async function () {
        if (!this.downloadButton || this.downloadButton.disabled) return;
        this.downloadButton.disabled = true;
        this.setStatus('Menyiapkan PNG 1080 × 1920…');

        try {
            this.syncGuestData();
            if (!this.makeQr()) return;
            var result = await this.renderDownloadCanvas();
            var blob = await canvasToBlob(result.canvas);
            var guest = textOf(this.root, '[data-tlqr-guest]');
            triggerDownload(blob, 'qr-checkin-' + slugify(guest) + '.png');

            if (result.heroMissing || result.logoMissing) {
                this.setStatus('QR terunduh. Foto/logo CDN yang tidak mengizinkan CORS dilewati pada PNG.');
            } else {
                this.setStatus('QR berhasil diunduh.');
            }
        } catch (error) {
            this.setStatus('Download gagal. Coba lagi atau periksa CORS foto/logo.');
            if (window.console && console.error) console.error('[TL QR Check-in]', error);
        } finally {
            this.downloadButton.disabled = !this.qr;
        }
    };

    function initRoot(root) {
        if (!root || root.dataset.tlqrReady === '1') return;
        new TLQRCheckin(root);
    }

    function initAll(scope) {
        var context = scope || document;
        if (context.matches && context.matches('.tlqr-widget')) initRoot(context);
        var roots = context.querySelectorAll ? context.querySelectorAll('.tlqr-widget') : [];
        for (var i = 0; i < roots.length; i += 1) initRoot(roots[i]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initAll(document); });
    } else {
        initAll(document);
    }

    window.addEventListener('elementor/frontend/init', function () {
        if (!window.elementorFrontend || !window.elementorFrontend.hooks) return;
        window.elementorFrontend.hooks.addAction('frontend/element_ready/tl_qr_checkin.default', function ($scope) {
            var node = $scope && $scope[0] ? $scope[0] : null;
            initAll(node || document);
        });
    });
}());
