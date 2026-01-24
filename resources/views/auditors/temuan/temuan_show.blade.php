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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="material-symbols-rounded text-sm align-middle me-2">check_circle</i>
                        <strong>Berhasil!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="material-symbols-rounded text-sm align-middle me-2">error</i>
                        <strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Tombol Back & List Temuan -->
                <div class="d-flex gap-2 mx-3 mb-3">
                    <a class="btn btn-primary" href="{{ route('report_auditor.detail', ['Id_List_Report' => $temuan->Id_List_Report]) }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('auditor-report.temuan_index') }}">
                        <i class="material-symbols-rounded text-sm">list</i> List Temuan
                    </a>
                </div>

                <br>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="pt-2">Temuan : <span class="text-primary">{{ $temuan->ListReport->Name_Procedure }}</span>
                    </h4>
                </div>
                <br>

                <div id="pdf-container" class="mt-2 mb-3" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                    <canvas id="default-pdf-canvas"></canvas>
                </div>

                <hr class="mt-2">
                @php
                    $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan)
                @endphp
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5>Submitted Auditor : <span class="text-primary">{{ $object->get('Name_User_Temuan', 'N/A') }}</span></h5>
                                <h5>Submitted Date : <span class="text-primary">{{ \Carbon\Carbon::parse($temuan->Time_Temuan)->format('d-m-Y H:i:s') }}</span></h5>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @if($temuan->Time_Penanganan && $temuan->Status_Temuan)
                                    <span class="badge bg-gradient-success">Selesai</span>
                                @else
                                    <span class="badge bg-gradient-warning">Pending</span>
                                @endif
                            </div>
                        </div>

                        @if($object->get('Validation_Status'))
                            <div class="alert alert-{{ $object->get('Validation_Status') === 'approved' ? 'success' : 'warning' }} mb-3" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="material-symbols-rounded text-sm me-2">
                                        {{ $object->get('Validation_Status') === 'approved' ? 'check_circle' : 'info' }}
                                    </i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <strong>Status Validasi:</strong>
                                            <span class="text-uppercase">{{ $object->get('Validation_Status') === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                                        </h6>
                                        @if($object->get('Validation_Time'))
                                            <small class="d-block mb-1">
                                                <strong>Waktu Validasi:</strong> {{ \Carbon\Carbon::parse($object->get('Validation_Time'))->format('d-m-Y H:i:s') }}
                                            </small>
                                        @endif
                                        @if($object->get('Validation_Notes'))
                                            <small class="d-block">
                                                <strong>Catatan:</strong> {{ $object->get('Validation_Notes') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <a href="{{ asset($object->get('File_Path_Temuan', '')) }}" download="Temuan_{{ $temuan->ListReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf" class="btn btn-success mb-3">
                            <i class="material-symbols-rounded text-sm">download</i> Download PDF
                        </a>

                        <div id="pdf-container-temuan" class="mt-2" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                            <canvas id="default-pdf-canvas-temuan"></canvas>
                        </div>
                        <div class="card-body mb-4">
                            @if(!empty($comments) && is_array($comments))
                                <div class="mt-3">
                                    <h5 class="text-dark">Daftar Temuan:</h5>
                                    <ul class="list-group">
                                        @foreach($comments as $index => $comment)
                                            @php
                                                $commentText = is_array($comment) ? ($comment['text'] ?? '') : (is_object($comment) ? ($comment->text ?? '') : $comment);
                                            @endphp
                                            @if(!empty($commentText))
                                                <li class="list-group-item">
                                                    <strong>{{ $index + 1 }}.</strong> {{ $commentText }}
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($object->Is_Submit_Penanganan)
                    <hr class="mt-2">
                    <div class="card mt-3 mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5>Handled By : <span class="text-primary">{{ $object->get('Name_User_Penanganan', 'N/A') }}</span></h5>
                                    <h5>Handled Date : <span class="text-primary">{{ $temuan->Time_Penanganan ? \Carbon\Carbon::parse($temuan->Time_Penanganan)->format('d-m-Y H:i:s') : '-' }}</span></h5>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @if($temuan->Status_Temuan)
                                        <span class="badge bg-gradient-success">Tervalidasi</span>
                                    @else
                                        <span class="badge bg-gradient-info">Menunggu Validasi</span>
                                    @endif
                                </div>
                            </div>

                            @if($object->get('Validation_Status'))
                                <div class="alert alert-{{ $object->get('Validation_Status') === 'approved' ? 'success' : 'danger' }} mb-3" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="material-symbols-rounded text-sm me-2">
                                            {{ $object->get('Validation_Status') === 'approved' ? 'check_circle' : 'cancel' }}
                                        </i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <strong>Hasil Validasi Auditor:</strong>
                                                <span class="text-uppercase">{{ $object->get('Validation_Status') === 'approved' ? 'Disetujui ✓' : 'Ditolak ✗' }}</span>
                                            </h6>
                                            @if($object->get('Validation_Time'))
                                                <small class="d-block mb-1">
                                                    <i class="material-symbols-rounded text-xs align-middle">schedule</i>
                                                    <strong>Waktu Validasi:</strong> {{ \Carbon\Carbon::parse($object->get('Validation_Time'))->format('d-m-Y H:i:s') }}
                                                </small>
                                            @endif
                                            @if($object->get('Validation_Notes'))
                                                <small class="d-block mt-2">
                                                    <i class="material-symbols-rounded text-xs align-middle">comment</i>
                                                    <strong>Catatan Validasi:</strong><br>
                                                    <em>"{{ $object->get('Validation_Notes') }}"</em>
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <a href="{{ asset($object->get('File_Path_Penanganan', '')) }}" download="Penanganan_{{ $temuan->ListReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf" class="btn btn-success mb-3">
                                <i class="material-symbols-rounded text-sm">download</i> Download Penanganan PDF
                            </a>

                            <div id="pdf-container-penanganan" class="mt-2" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                                <canvas id="default-pdf-canvas-penanganan"></canvas>
                            </div>

                            @if(!$temuan->Status_Temuan)
                                <hr class="mt-4">
                                <div class="mt-4">
                                    <h5 class="text-dark mb-3">Validasi Penanganan</h5>
                                    <form action="{{ route('auditor-temuan.validate', $temuan->Id_Temuan) }}" method="POST" id="validateForm">
                                        @csrf
                                        @method('PATCH')

                                        <div class="mb-3">
                                            <label for="validation_notes" class="form-label">Catatan Validasi (Opsional)</label>
                                            <textarea class="form-control" id="validation_notes" name="validation_notes" rows="3" placeholder="Tambahkan catatan validasi jika diperlukan..."></textarea>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                                <i class="material-symbols-rounded text-sm">check_circle</i> Validasi Selesai
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                                <i class="material-symbols-rounded text-sm">cancel</i> Tolak Penanganan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-success mt-4" role="alert">
                                    <i class="material-symbols-rounded text-sm align-middle">check_circle</i>
                                    <strong>Tervalidasi:</strong> Penanganan temuan ini telah divalidasi dan ditandai selesai.
                                </div>
                            @endif
                        </div>
                    </div>

                @else
                    <div class="alert alert-warning mt-3" role="alert">
                        <strong>Info:</strong> Penanganan untuk temuan ini belum disubmit.
                    </div>
                @endif


            </div>
        </section>
    </div>
    <style>
        .selected {
            outline: 2px dashed red;
        }

        .badge {
            padding: 0.5rem 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }

        .bg-gradient-success {
            background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(195deg, #FFA726 0%, #FB8C00 100%);
        }

        .d-flex.gap-2 {
            gap: 0.5rem;
        }

        .flex-column.gap-2 {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        #validateForm .btn {
            min-width: 180px;
            font-weight: 600;
        }

        #validateForm .form-control {
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        #validateForm .form-label {
            font-weight: 600;
            color: #344767;
            margin-bottom: 0.5rem;
        }

        .alert {
            border-radius: 0.75rem;
        }

        .alert h6 {
            margin: 0;
            font-size: 0.95rem;
        }

        .alert small {
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .alert .material-symbols-rounded {
            vertical-align: middle;
        }

        .bg-gradient-info {
            background: linear-gradient(195deg, #49A3F1 0%, #1A73E8 100%);
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
                    const action = e.submitter.value;
                    let message = '';

                    if (action === 'approve') {
                        message = 'Apakah Anda yakin ingin memvalidasi dan menandai temuan ini sebagai SELESAI?';
                    } else if (action === 'reject') {
                        message = 'Apakah Anda yakin ingin MENOLAK penanganan ini? Temuan akan dikembalikan untuk diperbaiki.';
                    }

                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>



@endsection
