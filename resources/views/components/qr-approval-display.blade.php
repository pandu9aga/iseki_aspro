@php
    $targetQrs = $qrCodes ?? ($listReport->Qr_Codes ?? null);
    $qrCodesData = [];
    if (!empty($targetQrs)) {
        $qrCodesData = is_string($targetQrs) ? json_decode($targetQrs, true) : $targetQrs;
    }
@endphp

@if(!empty($qrCodesData) && is_array($qrCodesData))
    <div class="card bg-gray-100 shadow-none border border-light mt-3 mb-3">
        <div class="card-body p-3">
            <h6 class="text-xs font-weight-bolder text-uppercase mb-2 text-dark d-flex align-items-center">
                <i class="material-symbols-rounded text-sm me-1 text-primary">qr_code_2</i>
                Hasil Scan QR Code
            </h6>
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
        </div>
    </div>
@endif
