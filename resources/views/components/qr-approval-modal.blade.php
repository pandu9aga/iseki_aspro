<!-- Modal QR Scanner Sebelum Approvement -->
<div class="modal fade" id="modalQrApprovalScanner" tabindex="-1" role="dialog" aria-labelledby="modalQrApprovalScannerTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h6 class="modal-title text-white" id="modalQrApprovalScannerTitle">
                    <i class="material-symbols-rounded text-sm align-middle me-1">qr_code_scanner</i> Wajib Scan QR Code
                </h6>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelQrScanApproval()"></button>
            </div>
            <div class="modal-body p-3">
                <div class="alert alert-info text-white text-xs py-2 px-3 mb-2" role="alert">
                    <i class="material-symbols-rounded text-xs align-middle">info</i> 
                    Scan QR code menggunakan kamera HP. Diambil 3 segmen awal pemisah (;). Bisa scan lebih dari 1 QR jika diperlukan.
                </div>

                <!-- Camera Box -->
                <div id="qrScannerBox" class="text-center mb-3">
                    <div id="qrApprovalReader" class="overflow-hidden rounded border bg-dark mx-auto" style="width: 100%; max-width: 320px; min-height: 240px;"></div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-0" id="btnRestartQrCamera" onclick="startQrApprovalCamera()">
                            <i class="material-symbols-rounded text-xs">videocam</i> Buka Kamera
                        </button>
                    </div>
                </div>

                <!-- List Scanned QR -->
                <div class="mb-2">
                    <label class="form-label text-xs font-weight-bold text-uppercase mb-1">
                        QR Yang Sudah Di-Scan (<span id="qrCountBadge">0</span>)
                    </label>
                    <div id="scannedQrListContainer" class="d-flex flex-wrap gap-1 p-2 border rounded bg-light" style="min-height: 50px; max-height: 140px; overflow-y: auto;">
                        <span class="text-muted text-xs w-100 text-center my-auto" id="noQrPlaceholder">Belum ada QR yang discan</span>
                    </div>
                </div>

                <div id="qrScanFeedback" class="text-center text-xs font-weight-bold" style="min-height: 20px;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary mb-0" data-bs-dismiss="modal" onclick="cancelQrScanApproval()">
                    Batal
                </button>
                <button type="button" class="btn btn-sm btn-primary mb-0" id="btnConfirmQrApproval" onclick="confirmQrApprovalSubmit()" disabled>
                    <i class="material-symbols-rounded text-sm align-middle me-1">check_circle</i> Konfirmasi & Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script>
    let html5QrScannerInstance = null;
    let isQrCameraRunning = false;
    let scannedQrApprovalList = [];
    let onQrApprovalSuccessCallback = null;

    function setQrScanFeedback(text, type = 'info') {
        const el = document.getElementById('qrScanFeedback');
        if (!el) return;
        el.textContent = text;
        el.className = 'text-center text-xs font-weight-bold';
        if (type === 'success') el.classList.add('text-success');
        else if (type === 'danger') el.classList.add('text-danger');
        else if (type === 'warning') el.classList.add('text-warning');
        else if (type === 'info') el.classList.add('text-info');
    }

    function setRestartCameraButtonVisible(visible) {
        const btn = document.getElementById('btnRestartQrCamera');
        if (btn) {
            btn.style.display = visible ? 'inline-block' : 'none';
        }
    }

    function getQrModalInstance() {
        const modalEl = document.getElementById('modalQrApprovalScanner');
        if (!modalEl) return null;
        if (window.bootstrap && bootstrap.Modal) {
            return bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return null;
    }

    function openQrApprovalScanner(callback) {
        onQrApprovalSuccessCallback = callback;
        scannedQrApprovalList = [];
        renderScannedQrList();
        setQrScanFeedback('');

        const modalEl = document.getElementById('modalQrApprovalScanner');
        const modal = getQrModalInstance();
        if (modal) {
            modal.show();
        } else if (modalEl) {
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
        }

        if (modalEl) {
            let cameraStarted = false;
            const onShown = function() {
                if (!cameraStarted) {
                    cameraStarted = true;
                    modalEl.removeEventListener('shown.bs.modal', onShown);
                    startQrApprovalCamera();
                }
            };
            modalEl.addEventListener('shown.bs.modal', onShown);
            // Fallback jika event shown.bs.modal tidak terpanggil
            setTimeout(function() {
                if (!cameraStarted && !isQrCameraRunning) {
                    cameraStarted = true;
                    startQrApprovalCamera();
                }
            }, 350);
        }
    }

    function startQrApprovalCamera() {
        if (isQrCameraRunning) return;

        const readerEl = document.getElementById('qrApprovalReader');
        if (!readerEl) return;

        setQrScanFeedback('Menghubungkan kamera...', 'info');

        try {
            html5QrScannerInstance = new Html5Qrcode("qrApprovalReader");
            html5QrScannerInstance.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                function(decodedText) {
                    handleScannedQrResult(decodedText);
                },
                function(errorMessage) {
                    // Ignore scanning per-frame errors
                }
            ).then(function() {
                isQrCameraRunning = true;
                setQrScanFeedback('Kamera siap. Arahkan ke QR Code.', 'success');
                setRestartCameraButtonVisible(false);
            }).catch(function(err) {
                isQrCameraRunning = false;
                console.error('Camera start error:', err);
                setQrScanFeedback('Gagal membuka kamera: pastikan izin kamera diizinkan.', 'danger');
                setRestartCameraButtonVisible(true);
            });
        } catch(e) {
            isQrCameraRunning = false;
            console.error('Html5Qrcode instance error:', e);
            setQrScanFeedback('Gagal memuat scanner kamera: ' + e.message, 'danger');
            setRestartCameraButtonVisible(true);
        }
    }

    function stopQrApprovalCamera() {
        if (html5QrScannerInstance && isQrCameraRunning) {
            return html5QrScannerInstance.stop().then(function() {
                isQrCameraRunning = false;
                setRestartCameraButtonVisible(true);
            }).catch(function(err) {
                console.warn('Error stopping camera:', err);
                isQrCameraRunning = false;
                setRestartCameraButtonVisible(true);
            });
        }
        return Promise.resolve();
    }

    function cancelQrScanApproval() {
        stopQrApprovalCamera();
        scannedQrApprovalList = [];
        onQrApprovalSuccessCallback = null;

        const modal = getQrModalInstance();
        if (modal) {
            modal.hide();
        } else {
            const modalEl = document.getElementById('modalQrApprovalScanner');
            if (modalEl) {
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
            }
        }
    }

    function handleScannedQrResult(decodedText) {
        if (!decodedText) return;
        const raw = decodedText.trim();
        if (!raw) return;

        // Split by ';' dan ambil 3 bagian awal
        const parts = raw.split(';');
        const sanitized = parts.slice(0, 3).map(p => p.trim()).join(';');

        if (!sanitized) {
            setQrScanFeedback('QR tidak valid!', 'danger');
            return;
        }

        // Cek duplikat
        if (scannedQrApprovalList.includes(sanitized)) {
            setQrScanFeedback('QR ini sudah discan: ' + sanitized, 'warning');
            return;
        }

        // Play feedback beep
        playQrBeep();

        scannedQrApprovalList.push(sanitized);
        renderScannedQrList();

        setQrScanFeedback('Berhasil scan: ' + sanitized, 'success');
    }

    function renderScannedQrList() {
        const container = document.getElementById('scannedQrListContainer');
        const badge = document.getElementById('qrCountBadge');
        const btnConfirm = document.getElementById('btnConfirmQrApproval');

        if (!container) return;

        if (badge) badge.textContent = scannedQrApprovalList.length;

        container.innerHTML = '';

        if (scannedQrApprovalList.length === 0) {
            container.innerHTML = '<span class="text-muted text-xs w-100 text-center my-auto" id="noQrPlaceholder">Belum ada QR yang discan</span>';
            if (btnConfirm) btnConfirm.disabled = true;
            return;
        }

        scannedQrApprovalList.forEach((qr, idx) => {
            const chip = document.createElement('span');
            chip.className = 'badge bg-gradient-primary d-inline-flex align-items-center gap-1 text-xs py-1 px-2 m-1';

            const textSpan = document.createElement('span');
            textSpan.textContent = qr;

            const closeIcon = document.createElement('i');
            closeIcon.className = 'material-symbols-rounded text-xs cursor-pointer';
            closeIcon.title = 'Hapus';
            closeIcon.textContent = 'close';
            closeIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                removeScannedQrItem(idx);
            });

            chip.appendChild(textSpan);
            chip.appendChild(closeIcon);
            container.appendChild(chip);
        });

        if (btnConfirm) btnConfirm.disabled = false;
    }

    function removeScannedQrItem(index) {
        if (index >= 0 && index < scannedQrApprovalList.length) {
            scannedQrApprovalList.splice(index, 1);
            renderScannedQrList();
        }
    }

    function playQrBeep() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            const audioCtx = new AudioContextClass();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.type = 'sine';
            oscillator.frequency.value = 880;
            gainNode.gain.setValueAtTime(0.2, audioCtx.currentTime);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.12);
        } catch(e) {
            // AudioContext silent ignore
        }
    }

    function confirmQrApprovalSubmit() {
        if (scannedQrApprovalList.length === 0) {
            alert('Wajib scan minimal 1 QR code sebelum melakukan submit!');
            return;
        }

        const listToSubmit = [...scannedQrApprovalList];

        stopQrApprovalCamera().then(function() {
            const modal = getQrModalInstance();
            if (modal) {
                modal.hide();
            } else {
                const modalEl = document.getElementById('modalQrApprovalScanner');
                if (modalEl) {
                    modalEl.style.display = 'none';
                    modalEl.classList.remove('show');
                }
            }

            if (typeof onQrApprovalSuccessCallback === 'function') {
                onQrApprovalSuccessCallback(listToSubmit);
            }
        });
    }
</script>
