<div class="table-responsive p-0">
    <table class="table align-items-center mb-0 temuan-table">
        <thead>
            <tr>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">No</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe Temuan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prosedur</th>
                <th class="text-left text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Item Prosedur</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Temuan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Penanganan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Member</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Auditor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($temuans as $index => $temuan)
                @php
                    $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                @endphp
                <tr class="row-data">
                    <td class="align-middle text-center ps-2">
                        <span class="text-secondary text-xxs font-weight-bold">{{ $index + 1 }}</span>
                    </td>
                    <td class="align-middle text-center">
                        @if($temuan->ListReport)
                            <a href="{{ route('auditor-report.temuan_show', ['Id_Temuan' => $temuan->Id_Temuan]) }}"
                                class="text-info me-1" title="Lihat Detail">
                                <i class="material-symbols-rounded text-sm">visibility</i>
                            </a>
                        @else
                            <span class="text-xxs text-muted">-</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        @if($temuan->Tipe_Temuan)
                            <span class="badge badge-sm bg-gradient-info" style="font-size: 0.65rem;">{{ $temuan->Tipe_Temuan }}</span>
                        @else
                            <span class="badge badge-sm bg-gradient-secondary" style="font-size: 0.65rem;">-</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-column align-items-center">
                            <span class="text-xxs font-weight-bold">{{ $temuan->ListReport ? $temuan->ListReport->Name_Tractor : '-' }}</span>
                            <span class="text-xxs text-secondary">{{ $temuan->ListReport ? $temuan->ListReport->Name_Area : '' }}</span>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-sm">{{ $temuan->ListReport->Name_Procedure ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-left ps-3">
                        <span class="text-xs" title="{{ $temuan->ListReport->Item_Procedure ?? '-' }}">
                            {{ Str::limit($temuan->ListReport->Item_Procedure ?? '-', 20) }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        @if($temuan->Status_Temuan)
                            <span class="badge badge-sm bg-gradient-success" style="font-size: 0.65rem;">
                                <i class="material-symbols-rounded text-xxs me-1">check_circle</i>Selesai
                            </span>
                        @elseif($object->get('Is_Rejected'))
                            <span class="badge badge-sm bg-gradient-danger" style="font-size: 0.65rem;">
                                <i class="material-symbols-rounded text-xxs me-1">cancel</i>Ditolak
                            </span>
                        @elseif($object->Is_Submit_Penanganan)
                            <span class="badge badge-sm bg-gradient-info" style="font-size: 0.65rem;">
                                <i class="material-symbols-rounded text-xxs me-1">schedule</i>Penanganan
                            </span>
                        @else
                            <span class="badge badge-sm bg-gradient-warning" style="font-size: 0.65rem;">
                                <i class="material-symbols-rounded text-xxs me-1">pending</i>Validasi
                            </span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-sm">{{ $temuan->Time_Temuan ? \Carbon\Carbon::parse($temuan->Time_Temuan)->format('d/m/Y') : '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        @if($temuan->Time_Penanganan)
                            <span class="text-sm">{{ \Carbon\Carbon::parse($temuan->Time_Penanganan)->format('d/m/Y') }}</span>
                        @else
                            <span class="text-sm text-secondary">-</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xxs">{{ $temuan->ListReport->report->member->Name_Member ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xxs text-secondary font-weight-bold">{{ $temuan->User->Name_User ?? '-' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="material-symbols-rounded text-5xl text-secondary opacity-5 mb-3">search_off</i>
                            <span class="text-secondary text-sm font-weight-bold">
                                Tidak ada temuan pada kategori ini
                            </span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>