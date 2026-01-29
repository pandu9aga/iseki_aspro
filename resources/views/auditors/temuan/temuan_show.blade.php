@extends('layouts.auditor')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Report</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="material-symbols-rounded text-sm align-middle me-2">check_circle</i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="material-symbols-rounded text-sm align-middle me-2">error</i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Tombol Back & List Temuan -->
                <div class="d-flex gap-2 mb-4">
                    <a class="btn btn-primary" href="{{ route('report_auditor.detail', ['Id_List_Report' => $temuan->Id_List_Report]) }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('auditor-report.temuan_index') }}">
                        <i class="material-symbols-rounded text-sm">list</i> List Temuan
                    </a>
                </div>

                <!-- Page Title -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <i class="material-symbols-rounded text-2xl text-primary me-2">report_problem</i>
                            <h4 class="mb-0">Detail Temuan: <span class="text-primary">{{ $temuan->ListReport->Name_Procedure }}</span></h4>
                        </div>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">
                            <i class="material-symbols-rounded text-sm align-middle me-1">picture_as_pdf</i>
                            Preview Dokumen Asli
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="pdf-container" class="border rounded" style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                            <canvas id="default-pdf-canvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Temuan Section -->
                @php
                    $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                    $comments_temuan = $object->get('Comments_Temuan', []);
                    $comments_penanganan = $object->get('Comments_Penanganan', []);
                @endphp

                <div class="card mb-4 shadow-sm">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-2">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">person</i>
                                    Auditor: <span class="text-primary">{{ $object->get('Name_User_Temuan', 'N/A') }}</span>
                                </h6>
                                <p class="text-xs text-secondary mb-0">
                                    <i class="material-symbols-rounded text-xs align-middle me-1">schedule</i>
                                    {{ \Carbon\Carbon::parse($temuan->Time_Temuan)->format('d F Y, H:i') }} WIB
                                </p>
                            </div>
                            <div class="d-flex flex-column gap-2 align-items-end">
                                @if($temuan->Status_Temuan)
                                    <span class="badge bg-gradient-success">
                                        <i class="material-symbols-rounded text-xs me-1">check_circle</i>Selesai
                                    </span>
                                @elseif($object->Is_Submit_Penanganan)
                                    <span class="badge bg-gradient-info">
                                        <i class="material-symbols-rounded text-xs me-1">schedule</i>Menunggu Validasi
                                    </span>
                                @else
                                    <span class="badge bg-gradient-warning">
                                        <i class="material-symbols-rounded text-xs me-1">pending</i>Menunggu Penanganan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <a href="{{ asset($object->get('File_Path_Temuan', '')) }}" download="Temuan_{{ $temuan->ListReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf" class="btn btn-success mb-3">
                            <i class="material-symbols-rounded text-sm">download</i> Download PDF Temuan
                        </a>

                        <div id="pdf-container-temuan" class="border rounded mb-3" style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                            <canvas id="default-pdf-canvas-temuan"></canvas>
                        </div>

                        @if(!empty($comments_temuan) && is_array($comments_temuan))
                            <div class="mt-3">
                                <h6 class="text-dark mb-3">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">format_list_bulleted</i>
                                    Daftar Temuan ({{ count($comments_temuan) }} item)
                                </h6>
                                <ul class="list-group">
                                    @foreach($comments_temuan as $index => $comment)
                                        @php
                                            $commentText = is_array($comment) ? ($comment['text'] ?? '') : (is_object($comment) ? ($comment->text ?? '') : $comment);
                                        @endphp
                                        @if(!empty($commentText))
                                            <li class="list-group-item d-flex align-items-start">
                                                <span class="badge bg-primary me-2 mt-1">{{ $index + 1 }}</span>
                                                <span class="text-sm">{{ $commentText }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="material-symbols-rounded text-sm align-middle me-1">info</i>
                                Tidak ada komentar temuan
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Penanganan Section -->
                @if($object->Is_Submit_Penanganan)
                    <div class="card mt-4 mb-4 shadow-sm">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-2">
                                        <i class="material-symbols-rounded text-sm align-middle me-1">build</i>
                                        Penanggung Jawab: <span class="text-primary">{{ $object->get('Name_User_Penanganan', 'N/A') }}</span>
                                    </h6>
                                    <p class="text-xs text-secondary mb-0">
                                        <i class="material-symbols-rounded text-xs align-middle me-1">schedule</i>
                                        {{ $temuan->Time_Penanganan ? \Carbon\Carbon::parse($temuan->Time_Penanganan)->format('d F Y, H:i') : '-' }} WIB
                                    </p>
                                </div>
                                <div class="d-flex flex-column gap-2 align-items-end">
                                    @if($temuan->Status_Temuan)
                                        <span class="badge bg-gradient-success">
                                            <i class="material-symbols-rounded text-xs me-1">check_circle</i>Tervalidasi
                                        </span>
                                    @else
                                        <span class="badge bg-gradient-info">
                                            <i class="material-symbols-rounded text-xs me-1">schedule</i>Menunggu Validasi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            @if($temuan->Status_Temuan)
                                <div class="alert alert-success mb-3" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="material-symbols-rounded text-sm me-2">check_circle</i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2">
                                                <strong>Status Validasi:</strong>
                                                <span class="text-uppercase text-success">✓ Disetujui</span>
                                            </h6>
                                            @if($object->get('Validation_Time'))
                                                <small class="d-block mb-2">
                                                    <i class="material-symbols-rounded text-xs align-middle">schedule</i>
                                                    <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($object->get('Validation_Time'))->format('d F Y, H:i') }} WIB
                                                </small>
                                            @endif
                                            @if($object->get('Validation_Notes'))
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small>
                                                        <i class="material-symbols-rounded text-xs align-middle">comment</i>
                                                        <strong>Catatan Validasi:</strong><br>
                                                        <em class="text-dark">"{{ $object->get('Validation_Notes') }}"</em>
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <a href="{{ asset($object->get('File_Path_Penanganan', '')) }}" download="Penanganan_{{ $temuan->ListReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf" class="btn btn-success mb-3">
                                <i class="material-symbols-rounded text-sm">download</i> Download PDF Penanganan
                            </a>

                            <div id="pdf-container-penanganan" class="border rounded mb-3" style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                                <canvas id="default-pdf-canvas-penanganan"></canvas>
                            </div>

                            @if(!empty($comments_penanganan) && is_array($comments_penanganan))
                                <div class="mt-3">
                                    <h6 class="text-dark mb-3">
                                        <i class="material-symbols-rounded text-sm align-middle me-1">format_list_bulleted</i>
                                        Daftar Penanganan ({{ count($comments_penanganan) }} item)
                                    </h6>
                                    <ul class="list-group">
                                        @foreach($comments_penanganan as $index => $comment)
                                            @php
                                                $commentText = is_array($comment) ? ($comment['text'] ?? '') : (is_object($comment) ? ($comment->text ?? '') : $comment);
                                            @endphp
                                            @if(!empty($commentText))
                                                <li class="list-group-item d-flex align-items-start">
                                                    <span class="badge bg-success me-2 mt-1">{{ $index + 1 }}</span>
                                                    <span class="text-sm">{{ $commentText }}</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">info</i>
                                    Tidak ada komentar penanganan
                                </div>
                            @endif

                            @if(!$temuan->Status_Temuan)
                                <div class="card bg-light mt-4">
                                    <div class="card-body">
                                        <h6 class="text-dark mb-3">
                                            <i class="material-symbols-rounded text-sm align-middle me-1">task_alt</i>
                                            Form Validasi Penanganan
                                        </h6>
                                        <form action="{{ route('auditor-temuan.validate', $temuan->Id_Temuan) }}" method="POST" id="validateForm">
                                            @csrf
                                            @method('PATCH')

                                            <div class="mb-3">
                                                <label for="validation_notes" class="form-label text-sm font-weight-bold">
                                                    <i class="material-symbols-rounded text-xs align-middle me-1">comment</i>
                                                    Catatan Validasi (Opsional)
                                                </label>
                                                <textarea class="form-control" id="validation_notes" name="validation_notes" rows="3" placeholder="Tambahkan catatan validasi jika diperlukan..."></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success">
                                                <i class="material-symbols-rounded text-sm">check_circle</i> Validasi Selesai
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success mt-4">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">check_circle</i>
                                    Penanganan telah divalidasi
                                </div>
                            @endif
                        </div>
                    </div>
                @endif


            </div>
        </section>
    </div>
@endsection

@section('style')
    <style>
        /* Selection Outline */
        .selected {
            outline: 2px dashed red;
        }

        /* Card Styling */
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: none;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f0f2f5;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge i {
            font-size: 0.875rem;
        }

        /* .bg-gradient-success {
            background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
            box-shadow: 0 2px 4px rgba(67, 160, 71, 0.3);
        }

        .bg-gradient-info {
            background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);
            box-shadow: 0 2px 4px rgba(26, 115, 232, 0.3);
        }

        .bg-gradient-warning {
            background: linear-gradient(195deg, #FFA726 0%, #FB8C00 100%);
            box-shadow: 0 2px 4px rgba(251, 140, 0, 0.3);
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-info {
            background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
            border: none;
        } */

        /* List Group Styling */
        .list-group-item {
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        /* Alert Styling */
        .alert {
            border-radius: 0.75rem;
        }

        .alert-info {
            background: linear-gradient(195deg, #e3f2fd 0%, #bbdefb 100%);
            border: none;
        }

        .alert-success {
            background: linear-gradient(195deg, #d4edda 0%, #c3e6cb 100%);
            border: none;
        }

        .alert-warning {
            background: linear-gradient(195deg, #fff3cd 0%, #ffeaa7 100%);
            border: none;
        }

        /* Form Styling */
        #validateForm .btn {
            min-width: 180px;
            font-weight: 600;
        }

        #validateForm .form-control {
            border-radius: 0.5rem;
            border: 1px solid #d2d6da;
        }

        #validateForm .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.15);
        }

        .form-label {
            color: #344767;
            margin-bottom: 0.5rem;
        }

        /* Gap Utilities */
        .gap-2 {
            gap: 0.5rem;
        }

        /* PDF Container */
        .border.rounded {
            border-color: #d2d6da !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .badge {
                padding: 0.375rem 0.625rem;
                font-size: 0.75rem;
            }

            #validateForm .btn {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('assets/js/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/js/pdf-lib.min.js') }}"></script>

    {{-- Render Default PDF to get their Size (Isolated) this will be reuse for the loop submitted Temuan --}}
    <script>
        const PDF_SCALE = 1.5;


        function RenderPDF(pdfUrl, canvasId) {
            let PAGE_VIEWPORT_HEIGHTS = [];
            const canvasPDFDefault = document.getElementById(canvasId);
            const defaultPDFUrl = pdfUrl;

            async function renderDefaultPDF() {
                try {
                    let pdf;
                    try {
                        pdf = await pdfjsLib.getDocument(defaultPDFUrl).promise;
                    } catch (err) {
                        console.warn('pdfjsLib.getDocument(url) failed, trying fetch fallback:', err);
                        const resp = await fetch(defaultPDFUrl, { credentials: 'same-origin' });
                        if (!resp.ok) {
                            throw new Error(`Failed to fetch PDF for fallback: ${resp.status} ${resp.statusText}`);
                        }
                        const buffer = await resp.arrayBuffer();
                        pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
                    }

                    const ctx = canvasPDFDefault.getContext('2d');

                    // Calculate total dimensions
                    const pageData = await calculatePageDimensions(pdf);
                    setupCanvasSize(pageData.totalHeight, pageData.maxWidth);

                    // Render all pages
                    await renderAllPages(pdf, pageData.viewports, ctx);

                    console.log('Default PDF rendered successfully');
                    console.log('Page heights:', PAGE_VIEWPORT_HEIGHTS);
                } catch (error) {
                    console.error('Error rendering default PDF:', error);
                }
            }

            async function calculatePageDimensions(pdf) {
                const viewports = [];
                let totalHeight = 0;
                let maxWidth = 0;

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale: PDF_SCALE });

                    viewports.push({ page, viewport });
                    totalHeight += viewport.height;
                    maxWidth = Math.max(maxWidth, viewport.width);
                    PAGE_VIEWPORT_HEIGHTS.push(viewport.height);
                }

                return { viewports, totalHeight, maxWidth };
            }

            function setupCanvasSize(height, width) {
                canvasPDFDefault.width = width;
                canvasPDFDefault.height = height;
            }

            async function renderAllPages(pdf, viewports, ctx) {
                let currentY = 0;

                for (const { page, viewport } of viewports) {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = viewport.width;
                    tempCanvas.height = viewport.height;
                    const tempCtx = tempCanvas.getContext('2d');

                    await page.render({ canvasContext: tempCtx, viewport }).promise;
                    ctx.drawImage(tempCanvas, 0, currentY);
                    currentY += viewport.height;
                }
            }

            // Auto-initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', renderDefaultPDF);
            } else {
                renderDefaultPDF();
            }
        }

        RenderPDF("{{ asset($pdfPath) }}?t=" + new Date().getTime(), "default-pdf-canvas");
        RenderPDF("{{ asset($object->File_Path_Temuan) }}?t=" + new Date().getTime(), "default-pdf-canvas-temuan");

        @if($object->Is_Submit_Penanganan)
            RenderPDF("{{ asset($object->File_Path_Penanganan) }}?t=" + new Date().getTime(), "default-pdf-canvas-penanganan");
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validateForm = document.getElementById('validateForm');

            if (validateForm) {
                validateForm.addEventListener('submit', function(e) {
                    if (!confirm('Apakah Anda yakin ingin memvalidasi dan menandai temuan ini sebagai SELESAI?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>



@endsection
