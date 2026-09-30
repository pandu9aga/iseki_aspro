@php
    $targetQrs = $qrCodes ?? ($listReport->Qr_Codes ?? null);
    $qrCodesData = [];
    if (!empty($targetQrs)) {
        $qrCodesData = is_string($targetQrs) ? json_decode($targetQrs, true) : $targetQrs;
    }

    $itemType = $itemType ?? (isset($listReport->Id_List_Training) ? 'training' : (isset($listReport->Id_List_Report_Replacement) ? 'replacement' : 'report'));
    $itemId = $itemId ?? ($listReport->Id_List_Report ?? $listReport->Id_List_Training ?? $listReport->Id_List_Report_Replacement ?? null);
    $canManageQr = $canManageQr ?? true;

    // Deteksi role user yang sedang aktif
    $currentUserRole = null;
    if (session()->has('Id_Member')) {
        $currentUserRole = 'member';
    } elseif (session('Id_Type_User') == 2) {
        $currentUserRole = 'leader';
    } elseif (session('Id_Type_User') == 1) {
        $currentUserRole = 'auditor';
    }
@endphp

@php
    $hasAnyQr = !empty($qrCodesData) && is_array($qrCodesData) && (
        !empty($qrCodesData['member']) || !empty($qrCodesData['leader']) || !empty($qrCodesData['auditor'])
    );
@endphp

<div class="card bg-gray-100 shadow-none border border-light mt-3 mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark d-flex align-items-center">
                <i class="material-symbols-rounded text-sm me-1 text-primary">qr_code_2</i>
                Hasil Scan QR Code
            </h6>
            @if($canManageQr && $itemId)
                <button type="button" class="btn btn-xs btn-outline-primary mb-0" onclick="openQrManagerModal()">
                    <i class="material-symbols-rounded text-xs align-middle">edit_note</i> Kelola QR {{ $currentUserRole ? '(' . ucfirst($currentUserRole) . ')' : '' }}
                </button>
            @endif
        </div>

        @if($hasAnyQr)
            <div class="row g-2">
                @php
                    $roleConfigs = [
                        'member' => ['label' => 'Member', 'badge' => 'bg-gradient-secondary'],
                        'leader' => ['label' => 'Leader', 'badge' => 'bg-gradient-info'],
                        'auditor' => ['label' => 'Auditor', 'badge' => 'bg-gradient-dark'],
                    ];
                @endphp
                @foreach($roleConfigs as $roleKey => $cfg)
                    @if(!empty($qrCodesData[$roleKey]) && is_array($qrCodesData[$roleKey]))
                        <div class="col-12 col-md-4">
                            <div class="p-2 border rounded bg-white h-100">
                                <span class="badge {{ $cfg['badge'] }} text-xxs mb-1 d-inline-block">{{ $cfg['label'] }}</span>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($qrCodesData[$roleKey] as $qr)
                                        <span class="badge badge-sm border text-dark bg-light font-weight-normal py-1 px-2" style="font-family: monospace; font-size: 0.75rem;">
                                            {{ $qr }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-xs text-muted mb-0 font-italic">Belum ada QR code yang tersimpan.</p>
        @endif
    </div>
</div>

@if($canManageQr && $itemId)
    <!-- Modal Kelola QR Code -->
    <div class="modal fade" id="qrManagerModal" tabindex="-1" aria-labelledby="qrManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h6 class="modal-title text-white d-flex align-items-center" id="qrManagerModalLabel">
                        <i class="material-symbols-rounded me-2">qr_code_2</i> Kelola Data QR Code {{ $currentUserRole ? '(' . ucfirst($currentUserRole) . ')' : '' }}
                    </h6>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Form Tambah QR Baru -->
                    <div class="card p-3 mb-3 bg-light border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-xs font-weight-bold mb-0 text-uppercase">Tambah / Scan QR Baru</h6>
                                <p class="text-xxs text-muted mb-0">Klik tombol scan untuk memindai QR code via kamera (otomatis tersimpan)</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary mb-0" onclick="scanQrForAdd()">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">qr_code_scanner</i> Scan Kamera
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- List QR Terkini -->
                    <h6 class="text-xs font-weight-bold mb-2 text-uppercase">Daftar QR Code Aktif</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Role</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">QR Code</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="qrManagerTableBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentQrData = @json($qrCodesData ?? []);
        const itemManageBase = "{{ url('item-manage/' . $itemType . '/' . $itemId) }}";
        const csrfToken = "{{ csrf_token() }}";
        const currentSessionRole = "{{ $currentUserRole ?? '' }}";

        // Pindahkan modal ke document.body jika berada di dalam container/card (agar tidak terhalang stacking context / backdrop)
        document.addEventListener('DOMContentLoaded', function() {
            const managerModalEl = document.getElementById('qrManagerModal');
            if (managerModalEl && managerModalEl.parentElement !== document.body) {
                document.body.appendChild(managerModalEl);
            }
        });

        function openQrManagerModal() {
            renderQrManagerTable();

            const scannerModalEl = document.getElementById('modalQrApprovalScanner');
            const managerModalEl = document.getElementById('qrManagerModal');
            if (!managerModalEl) return;

            // Pastikan modal selalu berada langsung di bawah document.body
            if (managerModalEl.parentElement !== document.body) {
                document.body.appendChild(managerModalEl);
            }

            function doOpen() {
                // Bersihkan backdrop yatim sebelum membuka modal
                const anyOpenModal = document.querySelector('.modal.show');
                if (!anyOpenModal) {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                // Gunakan getOrCreateInstance agar tidak membuat duplikasi instance modal
                const modal = bootstrap.Modal.getOrCreateInstance(managerModalEl);
                modal.show();
            }

            // Jika scanner modal masih tampil, sembunyikan dan tunggu hingga selesai
            if (scannerModalEl && scannerModalEl.classList.contains('show')) {
                scannerModalEl.addEventListener('hidden.bs.modal', function onScannerHidden() {
                    scannerModalEl.removeEventListener('hidden.bs.modal', onScannerHidden);
                    setTimeout(doOpen, 100);
                }, { once: true });
                const scannerInst = window.bootstrap && bootstrap.Modal.getInstance(scannerModalEl);
                if (scannerInst) { 
                    scannerInst.hide(); 
                } else {
                    scannerModalEl.classList.remove('show');
                    scannerModalEl.style.display = 'none';
                    doOpen();
                }
            } else {
                doOpen();
            }
        }

        // Listener cleanup saat qrManagerModal ditutup
        document.addEventListener('DOMContentLoaded', function() {
            const managerModalEl = document.getElementById('qrManagerModal');
            if (managerModalEl) {
                managerModalEl.addEventListener('hidden.bs.modal', function() {
                    const anyOtherOpen = document.querySelector('.modal.show');
                    if (!anyOtherOpen) {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }
                });
            }
        });

        function renderQrManagerTable() {
            const tbody = document.getElementById('qrManagerTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            const allRoles = ['member', 'leader', 'auditor'];
            const rolesToRender = (currentSessionRole && allRoles.includes(currentSessionRole)) 
                ? [currentSessionRole] 
                : allRoles;
            let hasItem = false;

            rolesToRender.forEach(role => {
                const list = currentQrData[role] || [];
                list.forEach((qrStr, index) => {
                    hasItem = true;
                    const canEdit = !currentSessionRole || currentSessionRole === role;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="align-middle">
                            <span class="badge bg-gradient-${role === 'leader' ? 'info' : (role === 'auditor' ? 'dark' : 'secondary')} text-xxs">${role.toUpperCase()}</span>
                        </td>
                        <td class="align-middle">
                            <span id="qr-text-${role}-${index}" class="font-weight-normal text-xs text-dark" style="font-family: monospace;">${qrStr}</span>
                            <div id="qr-edit-container-${role}-${index}" class="d-none mt-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="qr-input-${role}-${index}" value="${qrStr}">
                                    <button type="button" class="btn btn-outline-primary mb-0" onclick="scanQrForEdit('${role}', ${index})" title="Scan Kamera">
                                        <i class="material-symbols-rounded text-xs align-middle">qr_code_scanner</i>
                                    </button>
                                    <button class="btn btn-xs btn-success mb-0" onclick="submitEditQr('${role}', ${index})">Simpan</button>
                                    <button class="btn btn-xs btn-outline-secondary mb-0" onclick="cancelEditQr('${role}', ${index})">Batal</button>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            ${canEdit ? `
                                <button type="button" class="btn btn-link text-info px-2 mb-0" onclick="showEditQr('${role}', ${index})" title="Edit">
                                    <i class="material-symbols-rounded text-sm">edit</i>
                                </button>
                                <button type="button" class="btn btn-link text-danger px-2 mb-0" onclick="submitDeleteQr('${role}', ${index})" title="Hapus">
                                    <i class="material-symbols-rounded text-sm">delete</i>
                                </button>
                            ` : `<span class="text-xxs text-muted">Tidak berhak</span>`}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });

            if (!hasItem) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center text-xs text-muted py-3">Belum ada data QR Code</td></tr>`;
            }
        }

        function showEditQr(role, index) {
            document.getElementById(`qr-text-${role}-${index}`).classList.add('d-none');
            document.getElementById(`qr-edit-container-${role}-${index}`).classList.remove('d-none');
        }

        function cancelEditQr(role, index) {
            document.getElementById(`qr-text-${role}-${index}`).classList.remove('d-none');
            document.getElementById(`qr-edit-container-${role}-${index}`).classList.add('d-none');
        }

        // Integrasi Scan QR dari Kamera untuk Kelola QR
        function scanQrForAdd() {
            if (typeof openQrApprovalScanner !== 'function') {
                alert('Scanner QR tidak tersedia di halaman ini.');
                return;
            }

            const managerModalEl = document.getElementById('qrManagerModal');
            const managerModal = bootstrap.Modal.getInstance(managerModalEl);
            if (managerModal) {
                managerModal.hide();
            }

            openQrApprovalScanner(function(scannedList) {
                if (!scannedList || scannedList.length === 0) {
                    setTimeout(openQrManagerModal, 300);
                    return;
                }

                const role = document.getElementById('qrAddRole') ? document.getElementById('qrAddRole').value : (currentSessionRole || 'leader');
                const existingList = currentQrData[role] || [];

                // Filter hanya QR yang belum ada (skip yang duplikat)
                const newQrs = scannedList.filter(qr => !existingList.includes(qr));

                if (newQrs.length === 0) {
                    alert('Semua QR yang discan sudah ada di dalam data (dilewati).');
                    setTimeout(openQrManagerModal, 300);
                    return;
                }

                // Simpan setiap QR yang baru secara langsung
                const qrStringToSave = newQrs.join(';');
                fetch(`${itemManageBase}/qr/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role, qr: qrStringToSave })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'QR Code berhasil ditambahkan!');
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal menambahkan QR');
                        setTimeout(openQrManagerModal, 300);
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    setTimeout(openQrManagerModal, 300);
                });
            });
        }

        function scanQrForEdit(role, index) {
            if (typeof openQrApprovalScanner !== 'function') {
                alert('Scanner QR tidak tersedia di halaman ini.');
                return;
            }

            const managerModalEl = document.getElementById('qrManagerModal');
            const managerModal = bootstrap.Modal.getInstance(managerModalEl);
            if (managerModal) {
                managerModal.hide();
            }

            openQrApprovalScanner(function(scannedList) {
                if (!scannedList || scannedList.length === 0) {
                    setTimeout(openQrManagerModal, 300);
                    return;
                }

                const newQrString = scannedList[0]; // Ambil hasil scan terkini
                const existingList = currentQrData[role] || [];

                // Jika nilainya sama persis dengan yang sudah ada di posisi lain
                const isDuplicateElsewhere = existingList.some((item, idx) => idx !== index && item === newQrString);
                if (isDuplicateElsewhere) {
                    alert('QR Code ini sudah ada di daftar QR lain untuk role ini (dilewati).');
                    setTimeout(function() {
                        openQrManagerModal();
                        setTimeout(function() { showEditQr(role, index); }, 200);
                    }, 300);
                    return;
                }

                // Langsung simpan perubahan QR ke database
                fetch(`${itemManageBase}/qr/update`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role, index, qr: newQrString })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'QR Code berhasil diperbarui!');
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal mengubah QR');
                        setTimeout(openQrManagerModal, 300);
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    setTimeout(openQrManagerModal, 300);
                });
            });
        }

        function submitAddQr() {
            const role = document.getElementById('qrAddRole').value;
            const qr = document.getElementById('qrAddString').value.trim();
            if (!qr) {
                alert('Silakan masukkan string QR Code!');
                return;
            }

            fetch(`${itemManageBase}/qr/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role, qr })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'QR Code berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menambahkan QR');
                }
            })
            .catch(err => alert('Error: ' + err.message));
        }

        function submitEditQr(role, index) {
            const qr = document.getElementById(`qr-input-${role}-${index}`).value.trim();
            if (!qr) {
                alert('String QR tidak boleh kosong!');
                return;
            }

            fetch(`${itemManageBase}/qr/update`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role, index, qr })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'QR Code berhasil diubah!');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah QR');
                }
            })
            .catch(err => alert('Error: ' + err.message));
        }

        function submitDeleteQr(role, index) {
            if (!confirm(`Hapus QR code ini untuk ${role.toUpperCase()}?`)) return;

            fetch(`${itemManageBase}/qr/delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role, index })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentQrData = data.qr_codes;
                    renderQrManagerTable();
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus QR');
                }
            })
            .catch(err => alert('Error: ' + err.message));
        }
    </script>
@endif
