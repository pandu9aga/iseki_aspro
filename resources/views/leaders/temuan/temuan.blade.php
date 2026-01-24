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
                <!-- Tombol Back -->
                <a class="btn btn-primary mx-3" href="{{ route('leader-temuan.list') }}">
                    <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
                </a>

                <br><br>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="pt-2">Temuan : <span class="text-primary">{{ $listReport->Name_Procedure }}</span>
                    </h4>
                </div>
                <br>

                <div id="pdf-container" class="mt-2 mb-3" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                    <canvas id="default-pdf-canvas"></canvas>
                </div>

                @if($totalListTemuan > 0)
                    @foreach($listTemuan as $temuan)
                        @php
                            $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                            $comments = $object->get('Comments', []);
                        @endphp
                        <hr class="mt-2">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5>Submitted Auditor : <span class="text-primary">{{ $object->get('Name_User', 'N/A') }}</span></h5>
                                <h5>Submitted Date : <span class="text-primary">{{ \Carbon\Carbon::parse($temuan->Time_Temuan)->format('d-m-Y H:i:s') }}</span></h5>
                                <a href="{{ asset($object->get('File_Path', '')) }}" download="Temuan_{{ $listReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf" class="btn btn-success mb-3">
                                    <i class="material-symbols-rounded text-sm">download</i> Download PDF
                                </a>

                                <div id="pdf-container-{{$temuan->Id_Temuan}}" class="mt-2 " style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                                    <canvas id="default-pdf-canvas-{{$temuan->Id_Temuan}}"></canvas>
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
                    @endforeach
                @endif
            </div>
        </section>
    </div>
    <style>
        .selected {
            outline: 2px dashed red;
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
        let listTemuan = @json($listTemuan);
        listTemuan.forEach(temuan => {
            let object = JSON.parse(temuan.Object_Temuan);
            RenderPDF("{{ asset('') }}"+object.File_Path+"?t=" + new Date().getTime(), "default-pdf-canvas-"+temuan.Id_Temuan);
        });

    </script>
@endsection
