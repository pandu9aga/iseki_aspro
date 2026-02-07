<div class="table-responsive p-0">
    <table class="table align-items-center mb-0 temuan-table">
        <thead>
            <tr>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">No</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe Temuan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prosedur</th>
                <th class="text-left text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Item Prosedur</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Member</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Auditor</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Temuan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Penanganan</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($temuans as $index => $temuan)
                <tr class="row-data">
                    <td class="align-middle text-center ps-2">
                        <span class="text-secondary text-xs font-weight-bold">{{ $index + 1 }}</span>
                    </td>
                    <td class="align-middle text-center">
                        @if($current_user && $current_user->Username_User === 'saiful')
                            <button class="btn btn-sm btn-link p-0 m-0" onclick="openTipeTemuanModal('{{ $temuan->Id_Temuan }}', '{{ $temuan->Tipe_Temuan }}')">
                                @if($temuan->Tipe_Temuan)
                                    <span class="badge badge-sm bg-gradient-info">
                                        {{ $temuan->Tipe_Temuan }}
                                        <i class="material-symbols-rounded text-xs ms-1">edit</i>
                                    </span>
                                @else
                                    <span class="badge badge-sm bg-gradient-secondary">
                                        Belum ditentukan
                                        <i class="material-symbols-rounded text-xs ms-1">edit</i>
                                    </span>
                                @endif
                            </button>
                        @else
                            @if($temuan->Tipe_Temuan)
                                <span class="badge badge-sm bg-gradient-info">{{ $temuan->Tipe_Temuan }}</span>
                            @else
                                <span class="badge badge-sm bg-gradient-secondary">-</span>
                            @endif
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-column align-items-center">
                            <span class="text-xs font-weight-bold">{{ $temuan->ListReport ? $temuan->ListReport->Name_Tractor : '-' }}</span>
                            <span class="text-xxs text-secondary">{{ $temuan->ListReport ? $temuan->ListReport->Name_Area : '' }}</span>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xs">{{ $temuan->ListReport->Name_Procedure ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-left ps-3">
                        <span class="text-xs">{{ $temuan->ListReport->Item_Procedure ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xs">{{ $temuan->ListReport->report->member->Name_Member ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xs">{{ $temuan->User->Name_User ?? '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="text-xs">{{ $temuan->Time_Temuan ? \Carbon\Carbon::parse($temuan->Time_Temuan)->format('d/m/Y') : '-' }}</span>
                    </td>
                    <td class="align-middle text-center">
                        @php
                            $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                        @endphp
                        @if($temuan->Time_Penanganan)
                            <span class="text-xs">{{ \Carbon\Carbon::parse($temuan->Time_Penanganan)->format('d/m/Y') }}</span>
                        @else
                            <span class="text-xs text-secondary">-</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        @if($temuan->Status_Temuan)
                            <span class="badge badge-sm bg-gradient-success">
                                <i class="material-symbols-rounded text-xs me-1">check_circle</i>Selesai
                            </span>
                        @elseif($object->Is_Submit_Penanganan)
                            <span class="badge badge-sm bg-gradient-info">
                                <i class="material-symbols-rounded text-xs me-1">schedule</i>Menunggu Validasi
                            </span>
                        @else
                            <span class="badge badge-sm bg-gradient-warning">
                                <i class="material-symbols-rounded text-xs me-1">pending</i>Menunggu Penanganan
                            </span>
                        @endif
                    </td>
                    <td class="align-middle text-center pe-2">
                        @if($temuan->ListReport)
                            <a href="{{ route('leader-temuan.show', ['Id_Temuan' => $temuan->Id_Temuan]) }}" class="btn btn-sm btn-info mb-0" title="Lihat Detail">
                                <i class="material-symbols-rounded text-sm">visibility</i>
                            </a>
                        @else
                            <span class="text-xs text-muted">-</span>
                        @endif
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

