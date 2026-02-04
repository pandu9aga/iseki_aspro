@extends('layouts.leader')

@section('content')
    <header class="header-2">
        <div class="page-header min-vh-45 relative" style="background-image: url('{{ asset('assets/img/bg2.jpg') }}')">
            <span class="mask bg-gradient-primary opacity-4"></span>
            {{-- <div class="container">
                <div class="row">
                    <div class="col-lg-7 text-center mx-auto">
                        <h1 class="text-white pt-3 mt-n5">Audit Summary</h1>
                        <p class="lead text-white mt-3">Monthly Auditor Performance Tracking</p>
                    </div>
                </div>
            </div> --}}
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 z-index-2 border-radius-xl mt-n10 mx-auto py-3 blur shadow-blur">
                        <div class="row text-center">
                            <div class="col-md-4 position-relative">
                                <div class="p-3 text-center">
                                    <h1 class="text-gradient text-primary"><span id="state1" countTo="{{ count($auditStats) }}">0</span></h1>
                                    <h5 class="mt-3">Auditors</h5>
                                    <p class="text-sm font-weight-normal">Active in this month</p>
                                </div>
                                <hr class="vertical dark">
                            </div>
                            <div class="col-lg-4 col-md-6 position-relative">
                                <div class="p-3 text-center">
                                    <h1 class="text-gradient text-primary"><span id="state2" countTo="{{ array_sum(array_column($auditStats, 'total')) }}">0</span></h1>
                                    <h5 class="mt-3">Total Audits</h5>
                                    <p class="text-sm font-weight-normal">Performed this month</p>
                                </div>
                                <hr class="vertical dark">
                            </div>
                            <div class="col-lg-4 col-md-6 position-relative">
                                <div class="p-3 text-center">
                                    <h1 class="text-gradient text-primary"><span id="state3" countTo="{{ $daysInMonth }}">0</span></h1>
                                    <h5 class="mt-3">Days</h5>
                                    <p class="text-sm font-weight-normal">In {{ date('F Y', strtotime("$year-$month-01")) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mt-5">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">Auditor Monthly Stats</h3>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="monthPicker" style="width: auto;">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                            <select class="form-select" id="yearPicker" style="width: auto;">
                                @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary mb-0" id="filterBtn">Filter</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="bg-gradient-primary text-white">
                                <tr>
                                    <th class="text-uppercase text-xs font-weight-bolder opacity-9 px-3 text-white" style="min-width: 100px;">Date</th>
                                    @foreach($auditStats as $stat)
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-9 px-3 text-white">{{ $stat['name'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-light">
                                    <td class="font-weight-bold">Total</td>
                                    @foreach($auditStats as $stat)
                                        <td class="font-weight-bold">{{ $stat['total'] }}</td>
                                    @endforeach
                                </tr>
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    <tr>
                                        <td class="font-weight-bold text-secondary">{{ $day }}</td>
                                        @foreach($auditStats as $stat)
                                            <td class="p-0">
                                                @if(isset($stat['counts'][$day]) && $stat['counts'][$day] > 0)
                                                    <a href="{{ route('audit.detail', ['year' => $year, 'month' => $month, 'day' => $day, 'auditorName' => $stat['name']]) }}" 
                                                       class="d-block py-2 text-primary font-weight-bold hover-bg-light" 
                                                       style="text-decoration: none;">
                                                        {{ $stat['counts'][$day] }}
                                                    </a>
                                                @else
                                                    <span class="text-secondary opacity-5">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('assets/js/plugins/countup.min.js') }}"></script>
<script>
    if (document.getElementById('state1')) {
        const countUp = new CountUp('state1', document.getElementById("state1").getAttribute("countTo"));
        if (!countUp.error) {
            countUp.start();
        }
    }
    if (document.getElementById('state2')) {
        const countUp = new CountUp('state2', document.getElementById("state2").getAttribute("countTo"));
        if (!countUp.error) {
            countUp.start();
        }
    }
    if (document.getElementById('state3')) {
        const countUp = new CountUp('state3', document.getElementById("state3").getAttribute("countTo"));
        if (!countUp.error) {
            countUp.start();
        }
    }

    document.getElementById('filterBtn').addEventListener('click', function() {
        const month = document.getElementById('monthPicker').value;
        const year = document.getElementById('yearPicker').value;
        let url = "{{ route('audit.monthly', ['year' => ':year', 'month' => ':month']) }}";
        url = url.replace(':year', year).replace(':month', month);
        window.location.href = url;
    });
</script>
<style>
    .hover-bg-light:hover {
        background-color: rgba(0,0,0,0.05);
    }
    .table th {
        vertical-align: middle;
    }
</style>
@endsection
