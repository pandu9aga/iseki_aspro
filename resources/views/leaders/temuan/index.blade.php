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

            @php
                $selectedYear = request('year', now()->year);
                $months = [
                    '01' => 'January', '02' => 'February', '03' => 'March',
                    '04' => 'April', '05' => 'May', '06' => 'June',
                    '07' => 'July', '08' => 'August', '09' => 'September',
                    '10' => 'October', '11' => 'November', '12' => 'December'
                ];
                $nowYear = now()->year;
            @endphp

            <div class="container mt-4">
                <h4 class="text-dark mb-4">Pilih Bulan dan Tahun untuk Melihat Daftar Temuan</h4>

                <form method="GET" class="d-flex align-items-center gap-2 my-3">
                    <div class="input-group input-group-outline is-filled" style="width: 150px">
                        <label class="form-label" for="year">Select Year</label>
                        <select name="year" id="year" class="form-control">
                            @for ($year = $nowYear; $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex align-items-center" style="height: 100%">
                        <button type="submit" class="btn btn-primary mt-3">Apply</button>
                    </div>
                </form>

                <div class="row">
                    @foreach ($months as $num => $name)
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <a href="{{ route('leader-temuan.list-date', ['year' => $selectedYear, 'month' => $num]) }}">
                                <div class="border rounded text-center py-3 bg-light card-hover">
                                    <strong>{{ $name }} {{ $selectedYear }}</strong>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('style')
<style>
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        background-color: #e91e63 !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script>
// Auto-submit form on year change
$('#year').on('change', function() {
    $(this).closest('form').submit();
});
</script>
@endsection
