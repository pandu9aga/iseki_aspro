@php
    $targetPhotos = $photos ?? ($listReport->Photos ?? null);
    $photosData = [];
    if (!empty($targetPhotos)) {
        $photosData = is_string($targetPhotos) ? json_decode($targetPhotos, true) : $targetPhotos;
    }

    $itemType = $itemType ?? (isset($listReport->Id_List_Training) ? 'training' : (isset($listReport->Id_List_Report_Replacement) ? 'replacement' : 'report'));
    $itemId = $itemId ?? ($listReport->Id_List_Report ?? $listReport->Id_List_Training ?? $listReport->Id_List_Report_Replacement ?? null);

    // Deteksi role user yang aktif jika belum dispesifikasi
    $sessionRole = null;
    if (session()->has('Id_Member')) {
        $sessionRole = 'member';
    } elseif (session('Id_Type_User') == 2) {
        $sessionRole = 'leader';
    } elseif (session('Id_Type_User') == 1) {
        $sessionRole = 'auditor';
    }
    $currentRole = $currentRole ?? ($sessionRole ?? 'member');
@endphp

<div class="card bg-gray-100 shadow-none border border-light mt-3 mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-xs font-weight-bolder text-uppercase mb-0 text-dark d-flex align-items-center">
                <i class="material-symbols-rounded text-sm me-1 text-primary">photo_library</i>
                Dokumentasi Foto Per User
            </h6>
        </div>

        <div class="row g-3">
            @php
                $roleConfigs = [
                    'member' => ['label' => 'Foto Member', 'badge' => 'bg-gradient-secondary'],
                    'leader' => ['label' => 'Foto Leader', 'badge' => 'bg-gradient-info'],
                    'auditor' => ['label' => 'Foto Auditor', 'badge' => 'bg-gradient-dark'],
                ];
            @endphp

            @foreach($roleConfigs as $roleKey => $cfg)
                <div class="col-12 col-md-4">
                    <div class="p-3 border rounded bg-white h-100 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge {{ $cfg['badge'] }} text-xxs">{{ $cfg['label'] }}</span>
                            <span class="text-xxs text-secondary">
                                {{ count($photosData[$roleKey] ?? []) }} Foto
                            </span>
                        </div>

                        <!-- Gallery Photos Grid -->
                        <div class="d-flex flex-wrap gap-2" id="gallery-container-{{ $roleKey }}">
                            @if(!empty($photosData[$roleKey]) && is_array($photosData[$roleKey]))
                                @foreach($photosData[$roleKey] as $p)
                                    <div class="position-relative border rounded p-1" style="width: 80px; height: 80px;" id="photo-box-{{ $p['id'] }}">
                                        <a href="{{ asset('storage/' . $p['path']) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $p['path']) }}" class="w-100 h-100 rounded object-fit-cover" alt="{{ $p['name'] ?? 'Foto' }}">
                                        </a>
                                        @if($currentRole === $roleKey)
                                            <button type="button" class="btn btn-xs btn-danger p-0 position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 18px; height: 18px; min-width: 18px;"
                                                    onclick="deleteItemPhoto('{{ $roleKey }}', '{{ $p['id'] }}')" title="Hapus Foto">
                                                <i class="material-symbols-rounded" style="font-size: 11px;">close</i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xxs text-muted mb-0 italic">Belum ada foto yang diunggah.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function deleteItemPhoto(role, photoId) {
        if (!confirm('Hapus foto ini?')) return;
        const url = "{{ url('item-manage/' . $itemType . '/' . $itemId . '/photo/delete') }}";

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role, photo_id: photoId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById('photo-box-' + photoId);
                if (el) el.remove();
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus foto');
            }
        })
        .catch(err => alert('Error: ' + err.message));
    }
</script>
