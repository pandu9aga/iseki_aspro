@extends('layouts.leader')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Temuan</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="text-white">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="text-white">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="row">
                    @foreach ($errors->all() as $error)
                        <div class="col-12 col-lg-6">
                            <div class="alert alert-danger text-white text-xs alert-dismissible fade show" role="alert">
                                {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Tombol Back -->
            <a class="btn btn-primary mx-3" href="{{ route('dashboard') }}">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </a>

            <br><br>

            <!-- Report Information -->
            <div class="row">
                <div class="col-12 mx-auto">
                    <div class="mb-3">
                        <strong>Total Temuan:</strong>
                        <span class="text-primary">{{ $listTemuan->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Start Training</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Tractor - Area</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Member</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Auditor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Total Temuan</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 1; @endphp
                        @foreach ($listTemuan as $listReport)
                            @php
                                $temuansByAuditor = $listReport->Temuans->groupBy('Id_User');
                            @endphp

                            @foreach($temuansByAuditor as $auditorId => $auditorTemuans)
                                @php
                                    $firstTemuan = $auditorTemuans->first();
                                    $object = new \App\Http\Helper\JsonHelper($firstTemuan->Object_Temuan);
                                    $auditorName = $object->get('Name_User', $firstTemuan->user->Name_User ?? '-');
                                    $totalTemuanForAuditor = $auditorTemuans->count();
                                @endphp
                                <tr class="row-data">
                                    <td class="align-middle text-center">
                                        <span class="text-xs font-weight-bold text-secondary">{{ $rowNum++ }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ \Illuminate\Support\Carbon::parse($listReport->report->Start_Report)->format('Y-m-d') }}</span>
                                    </td><td class="align-middle text-center">
                                        <span class="text-xs">{{ $listReport->Name_Tractor }} - {{ $listReport->Name_Area }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $listReport->Name_Procedure }}</span>
                                    </td>
                                    <td class="align-middle text-left">
                                        <span class="text-xs">{{ $listReport->Item_Procedure }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $listReport->report->member->Name_Member ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">{{ $auditorName }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-sm bg-gradient-info">{{ $totalTemuanForAuditor }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('leader-temuan.list-show', ['Id_List_User' => $listReport->Id_List_Report, 'Id_User' => $auditorId]) }}" class="btn btn-sm btn-info mb-0">
                                            <i class="material-symbols-rounded text-sm">visibility</i> View
                                        </a>

                                        @php
                                            $hasSubmittedTemuan = $auditorTemuans->where('Time_Temuan', '!=', null)->isNotEmpty();
                                            $temuanIds = $auditorTemuans->where('Time_Temuan', '!=', null)->pluck('Id_Temuan')->toArray();
                                        @endphp

                                        @if($hasSubmittedTemuan)
                                            <button onclick="confirmDelete({{ json_encode($temuanIds) }}, '{{ $auditorName }}', '{{ $listReport->Name_Procedure }}')" class="btn btn-sm btn-danger mb-0">
                                                <i class="material-symbols-rounded text-sm">delete</i> Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
<style>
    .row-data {
        transition: background-color 0.2s;
    }

    .row-data:hover {
        background-color: #f8f9fa !important;
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');

function confirmDelete(temuanIds, auditorName, nameProcedure) {
    const ids = Array.isArray(temuanIds) ? temuanIds : [temuanIds];
    const count = ids.length;
    const message = count > 1
        ? `Are you sure you want to delete ${count} temuans from ${auditorName} for "${nameProcedure}"?\n\nThis action cannot be undone.`
        : `Are you sure you want to delete this temuan from ${auditorName} for "${nameProcedure}"?\n\nThis action cannot be undone.`;

    if (confirm(message)) {
        // Delete each temuan
        let completed = 0;
        let hasError = false;

        ids.forEach((id, index) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("leader-temuan.delete", ":id") }}'.replace(':id', id);
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add DELETE method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);

            // Submit only the last form (to trigger page reload)
            if (index === ids.length - 1) {
                form.submit();
            } else {
                // Submit other forms via AJAX to avoid multiple page reloads
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).finally(() => {
                    document.body.removeChild(form);
                });
            }
        });
    }
}
</script>
@endsection

