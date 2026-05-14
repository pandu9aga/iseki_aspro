@extends('layouts.public')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg10.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Procedure</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure_public') }}">Procedure</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-primary" href="{{ route('procedure_public.area.index', ['Name_Tractor' => $tractor]) }}">{{ $tractor }}</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('procedure_public.procedure.index', ['Name_Tractor' => $tractor, 'Name_Area' => $area]) }}">{{ $area }}</a></li>
        </ol>
    </nav>
    <br>

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            <div class="row mb-4">
                <div class="col-6">
                    <img src="{{ asset($photoTractor ?? 'storage/tractors/default.png') }}" alt="{{ $tractor }}" 
                        style="max-width: 100px; max-height: 100px; width: auto; height: auto;">
                </div>
            </div>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Procedure</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Item</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">PIC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $procedures as $p )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs">
                                    <a href="#" class="text-primary font-weight-bold"
                                        onclick="previewPdf('{{ asset('storage/procedures/' . $p->Name_Tractor . '/' . $p->Name_Area . '/' . $p->Name_Procedure . '.pdf') }}?t={{ time() }}', '{{ $p->Name_Procedure }}')">
                                        {{ $p->Name_Procedure }}
                                    </a>
                                </p>
                            </td>
                            <td class="align-middle text-left">
                                <p class="text-xs">
                                    {{ $p->Item_Procedure }}
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-secondary">
                                    {{ $p->pic_names ?: '-' }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Preview PDF -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white" id="previewModalLabel">Preview Procedure <span id="title"></span></h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdf-frame" src="" width="100%" height="600px" style="border:none;"></iframe>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary" id="prevPdfBtn" onclick="navigatePdf(-1)">
                    <i class="material-symbols-rounded text-sm align-middle">arrow_back</i> Previous
                </button>
                <span class="text-sm text-secondary" id="pdfCounter"></span>
                <button type="button" class="btn btn-outline-primary" id="nextPdfBtn" onclick="navigatePdf(1)">
                    Next <i class="material-symbols-rounded text-sm align-middle">arrow_forward</i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
<script>
  // Build PDF list from table data
  const pdfList = [
    @foreach($procedures as $p)
      {
        url: '{{ asset('storage/procedures/' . $p->Name_Tractor . '/' . $p->Name_Area . '/' . $p->Name_Procedure . '.pdf') }}?t={{ time() }}',
        title: '{{ $p->Name_Procedure }}'
      },
    @endforeach
  ];
  let currentPdfIndex = 0;

  function previewPdf(fileUrl, title) {
    // Find the index of the clicked PDF
    const idx = pdfList.findIndex(p => p.title === title);
    if (idx !== -1) currentPdfIndex = idx;

    loadPdfAtIndex(currentPdfIndex);

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  }

  function loadPdfAtIndex(index) {
    const item = pdfList[index];
    document.getElementById('pdf-frame').src = item.url;
    document.getElementById('title').textContent = '( ' + item.title + ' )';
    document.getElementById('pdfCounter').textContent = (index + 1) + ' / ' + pdfList.length;

    document.getElementById('prevPdfBtn').disabled = (index <= 0);
    document.getElementById('nextPdfBtn').disabled = (index >= pdfList.length - 1);
  }

  function navigatePdf(direction) {
    const newIndex = currentPdfIndex + direction;
    if (newIndex >= 0 && newIndex < pdfList.length) {
      currentPdfIndex = newIndex;
      loadPdfAtIndex(currentPdfIndex);
    }
  }
</script>
@endsection
