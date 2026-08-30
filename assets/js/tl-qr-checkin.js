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

    function strokeRoundedRect(ctx, x, y, w, h, r, color, lineWidth) {
        ctx.save();
        roundedRectPath(ctx, x, y, w, h, r);
        ctx.strokeStyle = color;
        ctx.lineWidth = lineWidth || 1;
        ctx.stroke();
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
            for (var i = 0; i < word.length; i += 1) {
                var test = chunk + word.charAt(i);
                if (chunk && ctx.measureText(test).width > maxWidth) {
                    chunks.push(chunk);
                    chunk = word.charAt(i);
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

    function coverPosition(value) {
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

        return positions[value] || positions['center center'];
    }

    function drawCoverImage(ctx, img, x, y, w, h, radius, position) {
        var scale = Math.max(w / img.naturalWidth, h / img.naturalHeight);
        var sw = w / scale;
        var sh = h / scale;
        var anchor = coverPosition(position);
        var sx = (img.naturalWidth - sw) * anchor[0];
        var sy = (img.naturalHeight - sh) * anchor[1];

        ctx.save();
        roundedRectPath(ctx, x, y, w, h, radius);
        ctx.clip();
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
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
        if (!canvas || !qr) return;
        var size = 560;
        canvas.width = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) return;
        ctx.imageSmoothingEnabled = false;
        drawQrMatrix(ctx, qr, 0, 0, size);
    }

    function drawIcon(ctx, type, x, y, size, color) {
        var s = size;
        ctx.save();
        ctx.translate(x, y);
        ctx.strokeStyle = color;
        ctx.fillStyle = 'transparent';
        ctx.lineWidth = Math.max(2, s * 0.07);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();

        if (type === 'user') {
            ctx.arc(s * .5, s * .33, s * .16, 0, Math.PI * 2);
            ctx.moveTo(s * .2, s * .85);
            ctx.quadraticCurveTo(s * .25, s * .57, s * .5, s * .57);
            ctx.quadraticCurveTo(s * .75, s * .57, s * .8, s * .85);
        } else if (type === 'pax') {
            ctx.arc(s * .37, s * .34, s * .13, 0, Math.PI * 2);
            ctx.moveTo(s * .12, s * .82);
            ctx.quadraticCurveTo(s * .17, s * .58, s * .37, s * .58);
            ctx.quadraticCurveTo(s * .58, s * .58, s * .62, s * .82);
            ctx.moveTo(s * .64, s * .3);
            ctx.arc(s * .7, s * .3, s * .1, Math.PI, Math.PI * 3);
            ctx.moveTo(s * .67, s * .59);
            ctx.quadraticCurveTo(s * .85, s * .57, s * .9, s * .79);
        } else if (type === 'calendar') {
            roundedRectPath(ctx, s * .14, s * .2, s * .72, s * .64, s * .08);
            ctx.moveTo(s * .14, s * .39); ctx.lineTo(s * .86, s * .39);
            ctx.moveTo(s * .32, s * .12); ctx.lineTo(s * .32, s * .29);
            ctx.moveTo(s * .68, s * .12); ctx.lineTo(s * .68, s * .29);
        } else if (type === 'clock') {
            ctx.arc(s * .5, s * .5, s * .34, 0, Math.PI * 2);
            ctx.moveTo(s * .5, s * .29); ctx.lineTo(s * .5, s * .52); ctx.lineTo(s * .67, s * .62);
        } else if (type === 'pin') {
            ctx.moveTo(s * .5, s * .88);
            ctx.bezierCurveTo(s * .22, s * .61, s * .22, s * .42, s * .22, s * .38);
            ctx.arc(s * .5, s * .38, s * .28, Math.PI, 0, false);
            ctx.bezierCurveTo(s * .78, s * .55, s * .69, s * .7, s * .5, s * .88);
            ctx.moveTo(s * .5, s * .3); ctx.arc(s * .5, s * .4, s * .1, -Math.PI / 2, Math.PI * 1.5);
        } else if (type === 'note') {
            ctx.moveTo(s * .23, s * .14); ctx.lineTo(s * .66, s * .14); ctx.lineTo(s * .82, s * .3); ctx.lineTo(s * .82, s * .86); ctx.lineTo(s * .23, s * .86); ctx.closePath();
            ctx.moveTo(s * .66, s * .14); ctx.lineTo(s * .66, s * .31); ctx.lineTo(s * .82, s * .31);
            ctx.moveTo(s * .36, s * .48); ctx.lineTo(s * .68, s * .48);
            ctx.moveTo(s * .36, s * .63); ctx.lineTo(s * .66, s * .63);
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
        this.targetUrl = '';
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

    TLQRCheckin.prototype.getTargetUrl = function () {
        try {
            var url = new URL(window.location.href);
            url.hash = '';
            return url.href;
        } catch (error) {
            return window.location.href.split('#')[0];
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
        if (!window.TLQRVendor || !window.TLQRVendor.QRCode) {
            throw new Error('QR engine tidak tersedia.');
        }
        this.targetUrl = this.getTargetUrl();
        var Levels = window.TLQRVendor.QRErrorCorrectLevel;
        var qr = new window.TLQRVendor.QRCode(-1, Levels.M);
        qr.addData(this.targetUrl);
        qr.make();
        this.qr = qr;
        renderQrCanvas(this.qrCanvas, qr);
    };

    TLQRCheckin.prototype.open = function () {
        window.clearTimeout(this.closeTimer);
        this.lastFocus = document.activeElement;
        this.syncGuestData();
        this.setStatus('');

        try {
            this.makeQr();
        } catch (error) {
            this.setStatus('QR tidak dapat dibuat. Periksa panjang URL.');
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
        return {
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
            heroPosition: this.root.dataset.heroPosition || 'center center',
            logoUrl: this.root.dataset.logoUrl || ''
        };
    };

    TLQRCheckin.prototype.renderDownloadCanvas = async function () {
        if (!this.qr) this.makeQr();

        var data = this.collectCardData();
        var computed = window.getComputedStyle(this.root);
        var accent = computed.getPropertyValue('--tlqr-accent').trim() || '#b89a67';
        var text = computed.getPropertyValue('--tlqr-text').trim() || '#171717';
        var surface = computed.getPropertyValue('--tlqr-surface').trim() || '#ffffff';
        var muted = '#77736d';
        var line = '#e9e6e0';

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

        ctx.fillStyle = '#f7f7f6';
        ctx.fillRect(0, 0, 1080, 1920);

        var margin = 60;
        var cardW = 960;

        // Hero.
        var heroX = margin, heroY = 60, heroW = cardW, heroH = 650, heroR = 34;
        fillRoundedRect(ctx, heroX, heroY, heroW, heroH, heroR, '#202020');
        if (hero) {
            drawCoverImage(ctx, hero, heroX, heroY, heroW, heroH, heroR, data.heroPosition);
        } else {
            var fallbackGradient = ctx.createLinearGradient(heroX, heroY, heroX + heroW, heroY + heroH);
            fallbackGradient.addColorStop(0, '#3b3b3b');
            fallbackGradient.addColorStop(1, '#151515');
            ctx.save();
            roundedRectPath(ctx, heroX, heroY, heroW, heroH, heroR);
            ctx.clip();
            ctx.fillStyle = fallbackGradient;
            ctx.fillRect(heroX, heroY, heroW, heroH);
            ctx.restore();
        }

        ctx.save();
        roundedRectPath(ctx, heroX, heroY, heroW, heroH, heroR);
        ctx.clip();
        var shade = ctx.createLinearGradient(0, heroY + 180, 0, heroY + heroH);
        shade.addColorStop(0, 'rgba(0,0,0,0)');
        shade.addColorStop(1, 'rgba(0,0,0,.72)');
        ctx.fillStyle = shade;
        ctx.fillRect(heroX, heroY, heroW, heroH);
        ctx.restore();

        if (data.tag) {
            ctx.font = '700 24px Arial, sans-serif';
            var tagWidth = Math.min(330, Math.max(135, ctx.measureText(data.tag.toUpperCase()).width + 72));
            fillRoundedRect(ctx, heroX + 34, heroY + 34, tagWidth, 58, 29, 'rgba(255,255,255,.94)');
            ctx.strokeStyle = accent;
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(heroX + 56, heroY + 68);
            ctx.lineTo(heroX + 65, heroY + 55);
            ctx.lineTo(heroX + 76, heroY + 68);
            ctx.lineTo(heroX + 88, heroY + 55);
            ctx.lineTo(heroX + 95, heroY + 68);
            ctx.stroke();
            ctx.fillStyle = '#4b3d28';
            ctx.textBaseline = 'middle';
            ctx.fillText(data.tag.toUpperCase(), heroX + 112, heroY + 64);
        }

        ctx.textAlign = 'center';
        ctx.textBaseline = 'alphabetic';
        if (data.weddingTitle) {
            ctx.fillStyle = '#f2dfbd';
            ctx.font = '700 22px Arial, sans-serif';
            ctx.fillText(data.weddingTitle.toUpperCase(), 540, heroY + 480);
        }
        if (data.coupleName) {
            ctx.fillStyle = '#ffffff';
            ctx.font = '700 60px Georgia, serif';
            var coupleLines = wrapLines(ctx, data.coupleName, 820, 2);
            var coupleStartY = heroY + 548 - Math.max(0, coupleLines.length - 1) * 31;
            for (var c = 0; c < coupleLines.length; c += 1) {
                ctx.fillText(coupleLines[c], 540, coupleStartY + c * 66);
            }
        }
        if (data.subtitle) {
            ctx.fillStyle = 'rgba(255,255,255,.88)';
            ctx.font = '400 22px Arial, sans-serif';
            ctx.fillText(data.subtitle, 540, heroY + 625);
        }

        // Main card.
        var mainX = margin, mainY = 745, mainW = cardW, mainH = 825;
        fillRoundedRect(ctx, mainX, mainY, mainW, mainH, 34, surface);
        strokeRoundedRect(ctx, mainX, mainY, mainW, mainH, 34, line, 2);

        var qrX = 100, qrY = 810, qrSize = 425;
        fillRoundedRect(ctx, qrX, qrY, qrSize, qrSize, 24, '#ffffff');
        strokeRoundedRect(ctx, qrX, qrY, qrSize, qrSize, 24, line, 2);
        drawQrMatrix(ctx, this.qr, qrX + 14, qrY + 14, qrSize - 28);

        ctx.textAlign = 'center';
        ctx.fillStyle = accent;
        ctx.font = '700 27px Arial, sans-serif';
        ctx.fillText('Scan to check-in', qrX + qrSize / 2, qrY + qrSize + 54);
        ctx.fillStyle = muted;
        ctx.font = '400 20px Arial, sans-serif';
        drawWrappedText(ctx, 'Tunjukkan QR ini di pintu masuk venue.', qrX + qrSize / 2, qrY + qrSize + 92, 360, 28, 2);

        // Details.
        var detailsX = 575;
        var detailsW = 385;
        var rowY = 808;
        var rows = [];
        rows.push({ icon: 'user', label: 'Dear', value: data.guest, max: 2 });
        if (data.pax) rows.push({ icon: 'pax', label: 'Pax', value: data.pax, max: 1 });
        if (data.date) rows.push({ icon: 'calendar', label: 'Date', value: data.date, max: 2 });
        if (data.time) rows.push({ icon: 'clock', label: 'Time', value: data.time, max: 1 });
        if (data.venue) rows.push({ icon: 'pin', label: 'Venue', value: data.venue, max: 3 });
        if (data.notes) rows.push({ icon: 'note', label: 'Notes', value: data.notes, max: 3 });

        var availableH = 690;
        var rowH = Math.floor(availableH / Math.max(rows.length, 1));
        rowH = Math.min(124, Math.max(88, rowH));

        for (var r = 0; r < rows.length; r += 1) {
            var item = rows[r];
            if (r > 0) {
                ctx.strokeStyle = line;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(detailsX, rowY);
                ctx.lineTo(detailsX + detailsW, rowY);
                ctx.stroke();
            }
            fillRoundedRect(ctx, detailsX, rowY + 16, 52, 52, 14, '#f5f0e8');
            drawIcon(ctx, item.icon, detailsX + 8, rowY + 24, 36, accent);

            ctx.textAlign = 'left';
            ctx.fillStyle = muted;
            ctx.font = '400 18px Arial, sans-serif';
            ctx.fillText(item.label, detailsX + 70, rowY + 37);
            ctx.fillStyle = text;
            ctx.font = '700 24px Arial, sans-serif';
            drawWrappedText(ctx, item.value, detailsX + 70, rowY + 70, detailsW - 76, 30, item.max);
            rowY += rowH;
        }

        // Footer.
        var footerX = margin, footerY = 1620, footerW = cardW, footerH = 180;
        fillRoundedRect(ctx, footerX, footerY, footerW, footerH, 30, '#ffffff');
        strokeRoundedRect(ctx, footerX, footerY, footerW, footerH, 30, line, 2);

        if (logo) {
            var logoMaxW = 190, logoMaxH = 86;
            var logoScale = Math.min(logoMaxW / logo.naturalWidth, logoMaxH / logo.naturalHeight, 1);
            var lw = logo.naturalWidth * logoScale;
            var lh = logo.naturalHeight * logoScale;
            ctx.drawImage(logo, footerX + 38, footerY + (footerH - lh) / 2, lw, lh);
        } else {
            drawRings(ctx, footerX + 38, footerY + 54, accent);
        }

        if (data.poweredBy) {
            ctx.textAlign = 'right';
            ctx.fillStyle = muted;
            ctx.font = '400 19px Arial, sans-serif';
            ctx.fillText('Powered by', footerX + footerW - 42, footerY + 75);
            ctx.fillStyle = text;
            ctx.font = '700 27px Arial, sans-serif';
            var poweredLines = wrapLines(ctx, data.poweredBy, 430, 2);
            for (var p = 0; p < poweredLines.length; p += 1) {
                ctx.fillText(poweredLines[p], footerX + footerW - 42, footerY + 112 + p * 30);
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
            this.makeQr();
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
            this.downloadButton.disabled = false;
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
