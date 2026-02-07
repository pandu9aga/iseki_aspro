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
                <!-- Tombol Back & List Temuan -->
                <div class="d-flex gap-2 mb-4">
                    <a class="btn btn-primary"
                        href="{{ route('report_auditor.detail', ['Id_List_Report' => $listReport->Id_List_Report]) }}">
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
                            <h4 class="mb-0">Temuan: <span class="text-primary">{{ $listReport->Name_Procedure }}</span>
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">
                            <i class="material-symbols-rounded text-sm align-middle me-1">picture_as_pdf</i>
                            Preview Dokumen
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="pdf-container" class="border rounded"
                            style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                            <canvas id="default-pdf-canvas"></canvas>
                        </div>
                    </div>
                </div>

                @if($totalListTemuan > 0)
                    <div class="mb-3">
                        <h5 class="text-dark">
                            <i class="material-symbols-rounded text-lg align-middle me-1">checklist</i>
                            Daftar Temuan yang Telah Disubmit ({{ $totalListTemuan }})
                        </h5>
                    </div>

                    @foreach($listTemuan as $index => $temuan)
                        @php
                            $object = new \App\Http\Helper\JsonHelper($temuan->Object_Temuan);
                            $comments = $object->get('Comments_Temuan', []);
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
                                        @elseif($temuan->Is_Submit_Temuan)
                                            <span class="badge bg-gradient-info">
                                                <i class="material-symbols-rounded text-xs me-1">schedule</i>Menunggu Validasi
                                            </span>
                                        @else
                                            <span class="badge bg-gradient-warning">
                                                <i class="material-symbols-rounded text-xs me-1">pending</i>Menunggu Penanganan
                                            </span>
                                        @endif
                                        <a href="{{ route('auditor-report.temuan_show', ['Id_Temuan' => $temuan->Id_Temuan]) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="material-symbols-rounded text-sm">visibility</i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">

                                <a href="{{ asset($object->get('File_Path_Temuan', '')) }}"
                                    download="Temuan_{{ $listReport->Name_Procedure }}_{{ $temuan->Id_Temuan }}.pdf"
                                    class="btn btn-success mb-3">
                                    <i class="material-symbols-rounded text-sm">download</i> Download PDF Temuan
                                </a>

                                <div id="pdf-container-{{$temuan->Id_Temuan}}" class="border rounded mb-3"
                                    style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                                    <canvas id="default-pdf-canvas-{{$temuan->Id_Temuan}}"></canvas>
                                </div>

                                @if(!empty($comments) && is_array($comments))
                                    <div class="mt-3">
                                        <h6 class="text-dark mb-3">
                                            <i class="material-symbols-rounded text-sm align-middle me-1">format_list_bulleted</i>
                                            Daftar Temuan ({{ count($comments) }} item)
                                        </h6>
                                        <ul class="list-group">
                                            @foreach($comments as $index => $comment)
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

                    @endforeach
                @endif


                @if($totalListTemuanNull == 0)
                    <!-- Form Tambah Temuan Baru -->
                    <div class="card shadow-sm">
                        <div class="card-header pb-0">
                            <h6 class="mb-0">
                                <i class="material-symbols-rounded text-sm align-middle me-1">add_circle</i>
                                Tambah Temuan Baru
                            </h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label text-sm font-weight-bold">
                                <i class="material-symbols-rounded text-sm align-middle me-1">photo_camera</i>
                                Upload Foto untuk: <span class="text-primary">{{ $listReport->Name_Procedure }}</span>
                            </label>
                            <div class="input-group input-group-outline mb-3">
                                <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*"
                                    capture="environment">
                            </div>
                            <div id="preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>

                            <div class="mt-4">
                                <button class="btn btn-primary" onclick="TambahkanTemuan()">
                                    <i class="material-symbols-rounded text-sm">add</i> Tambahkan Temuan
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Editor Temuan -->
                    <div class="card shadow-sm">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="material-symbols-rounded text-sm align-middle me-1">edit_note</i>
                                    Editor Temuan
                                </h6>
                                <span class="badge bg-gradient-info">Mode Editor</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Toolbar -->
                            <div class="d-flex flex-wrap gap-2 mb-3 p-3 bg-light rounded">
                                <button class="btn btn-sm btn-primary" id="checklist-btn" onclick="toggleChecklist('check')"
                                    title="Checklist">
                                    <i class="material-symbols-rounded text-sm" id="checklist-btn-icon">edit_off</i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="undo()" title="Undo">
                                    <i class="material-symbols-rounded text-sm">undo</i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="redo()" title="Redo">
                                    <i class="material-symbols-rounded text-sm">redo</i>
                                </button>
                                <button class="btn btn-sm btn-danger" id="delete-btn" onclick="deleteSelected()" disabled
                                    title="Delete">
                                    <i class="material-symbols-rounded text-sm">delete</i>
                                </button>
                                <button class="btn btn-sm btn-primary" id="ng-btn" onclick="toggleChecklist('ng')"
                                    title="Mark NG">
                                    <i class="material-symbols-rounded text-sm" id="ng-btn-icon">block</i>
                                </button>
                                <button class="btn btn-sm btn-primary" id="x-btn" onclick="toggleChecklist('x')" title="Mark X">
                                    <i class="material-symbols-rounded text-sm" id="x-btn-icon">close</i>
                                </button>
                                <button class="btn btn-sm btn-primary" id="comment-btn" onclick="toggleChecklist('comment')"
                                    title="Add Comment">
                                    <i class="material-symbols-rounded text-sm" id="comment-btn-icon">comment</i>
                                </button>
                            </div>

                            <!-- PDF Editor Canvas -->
                            <div id="pdf-container-editor" class="border rounded"
                                style="height:600px; overflow:auto; position:relative; background: #f5f5f5;">
                                <canvas id="pdf-canvas-editor"></canvas>
                                <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-3">
                                <button onclick="submitReport()" class="btn btn-success">
                                    <i class="material-symbols-rounded text-sm">send</i> Submit Temuan
                                </button>
                            </div>
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
                                        } */

        /* Button Styling */
        /* .btn {
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

        /* Toolbar Styling */
        /* .bg-light {
                                            background-color: #f8f9fa !important;
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
        .alert-info {
            background: linear-gradient(195deg, #e3f2fd 0%, #bbdefb 100%);
            border: none;
            border-radius: 0.5rem;
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
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('assets/js/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/js/pdf-lib.min.js') }}"></script>

    {{-- Render Default PDF to get their Size (Isolated) this will be reuse for the loop submitted Temuan --}}
    <script>
        const PDF_SCALE = 1.5;


        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/js/pdf.worker.min.js') }}";

        function RenderPDF(pdfUrl, canvasId) {
            async function renderDefaultPDF() {
                const canvasPDFDefault = document.getElementById(canvasId);
                if (!canvasPDFDefault) return;

                try {
                    let pdf;
                    try {
                        pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                    } catch (err) {
                        console.warn('pdfjsLib.getDocument failed, trying fetch fallback:', err);
                        const resp = await fetch(pdfUrl);
                        const buffer = await resp.arrayBuffer();
                        pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
                    }

                    const ctx = canvasPDFDefault.getContext('2d');
                    const viewports = [];
                    let totalHeight = 0;
                    let maxWidth = 0;

                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        const viewport = page.getViewport({ scale: PDF_SCALE });
                        viewports.push({ page, viewport });
                        totalHeight += viewport.height;
                        maxWidth = Math.max(maxWidth, viewport.width);
                    }

                    canvasPDFDefault.width = maxWidth;
                    canvasPDFDefault.height = totalHeight;

                    let currentY = 0;
                    for (const { page, viewport } of viewports) {
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = viewport.width;
                        tempCanvas.height = viewport.height;
                        await page.render({ canvasContext: tempCanvas.getContext('2d'), viewport }).promise;
                        ctx.drawImage(tempCanvas, 0, currentY);
                        currentY += viewport.height;
                    }
                } catch (error) {
                    console.error('RenderPDF error:', error, canvasId);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', renderDefaultPDF);
            } else {
                renderDefaultPDF();
            }
        }

        function getPdfUrl(path) {
            if (!path) return null;
            let baseUrl = "{{ asset('') }}";
            if (baseUrl.endsWith('/')) baseUrl = baseUrl.slice(0, -1);
            let p = path.replace(/\\/g, '/');
            if (!p.startsWith('/') && !p.startsWith('http')) p = '/' + p;

            let url = p.startsWith('http') ? p : baseUrl + p;
            url = url.replace(/ /g, '%20');
            return url + (url.includes('?') ? '&' : '?') + "t=" + new Date().getTime();
        }

        const urlOriginal = getPdfUrl("{!! str_replace('\\', '/', $pdfPath) !!}");
        if (urlOriginal) RenderPDF(urlOriginal, "default-pdf-canvas");

        const listTemuan = @json($listTemuan);
        if (listTemuan && Array.isArray(listTemuan)) {
            listTemuan.forEach(temuan => {
                let object = JSON.parse(temuan.Object_Temuan);
                const urlList = getPdfUrl(object.File_Path_Temuan);
                if (urlList) RenderPDF(urlList, "default-pdf-canvas-" + temuan.Id_Temuan);
            });
        }

    </script>


    @if($totalListTemuanNull == 0)
        {{--Foto Uploud--}}
        <script>
            // Photo state
            images = [];

            // Handle image selection
            document.getElementById('imageInput').addEventListener('change', function (e) {
                for (let file of e.target.files) {
                    images.push(file);
                    showPreview(file);
                }
            });

            async function resizeImage(file, maxWidth, maxHeight) {
                return new Promise(resolve => {
                    const img = new Image();
                    img.onload = function () {
                        let width = img.width;
                        let height = img.height;

                        const scale = Math.min(maxWidth / width, maxHeight / height);
                        width *= scale;
                        height *= scale;

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob(blob => resolve(blob), file.type, 0.7); // 0.7 = quality (for jpeg)
                    };
                    img.src = URL.createObjectURL(file);
                });
            }

            // Display preview with delete button
            function showPreview(file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const container = document.createElement('div');
                    container.style.position = 'relative';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '150px';
                    img.style.maxHeight = '150px';
                    img.style.border = '1px solid #ccc';
                    img.style.padding = '2px';

                    const delBtn = document.createElement('button');
                    delBtn.textContent = 'X';
                    delBtn.style.position = 'absolute';
                    delBtn.style.top = '0';
                    delBtn.style.right = '0';
                    delBtn.style.background = 'red';
                    delBtn.style.color = 'white';
                    delBtn.style.border = 'none';
                    delBtn.style.cursor = 'pointer';
                    delBtn.style.width = '20px';
                    delBtn.style.height = '20px';
                    delBtn.style.padding = '0';
                    delBtn.style.display = 'flex';
                    delBtn.style.alignItems = 'center';
                    delBtn.style.justifyContent = 'center';
                    delBtn.style.fontSize = '12px';

                    delBtn.onclick = function () {
                        const index = Array.from(container.parentNode.children).indexOf(container);
                        images.splice(index, 1);
                        container.remove();
                    };

                    container.appendChild(img);
                    container.appendChild(delBtn);
                    document.getElementById('preview').appendChild(container);
                };
                reader.readAsDataURL(file);
            }

            // Generate standalone photo PDF (for download only, not used in submit)
            async function createPhotoPDF() {
                if (images.length === 0) return null;

                const pdfDoc = await PDFLib.PDFDocument.create();
                const PAGE_WIDTH = 841.89;
                const PAGE_HEIGHT = 595.28;
                const MARGIN = 20;
                const SLOT_COLS = 2;
                const SLOT_ROWS = 2;
                const SLOT_W = (PAGE_WIDTH - MARGIN * 2) / SLOT_COLS;
                const SLOT_H = (PAGE_HEIGHT - MARGIN * 2) / SLOT_ROWS;

                let page = null;
                let slotIndex = 0;

                for (let file of images) {
                    if (slotIndex % 4 === 0) {
                        page = pdfDoc.addPage([PAGE_WIDTH, PAGE_HEIGHT]);
                    }

                    if (!page) {
                        console.error('Page is null, cannot draw image');
                        continue;
                    }

                    try {
                        const resizedBlob = await resizeImage(file, 1000, 1000);
                        const imgBytes = await resizedBlob.arrayBuffer();
                        let imgEmbed = file.type.includes('png') ?
                            await pdfDoc.embedPng(imgBytes) :
                            await pdfDoc.embedJpg(imgBytes);

                        const { width, height } = imgEmbed.size();
                        const scale = Math.min(SLOT_W / width, SLOT_H / height);
                        const col = slotIndex % 2;
                        const row = Math.floor((slotIndex % 4) / 2);
                        const x = MARGIN + col * SLOT_W + (SLOT_W - width * scale) / 2;
                        const y = PAGE_HEIGHT - MARGIN - ((row + 1) * SLOT_H) + (SLOT_H - height * scale) / 2;

                        page.drawImage(imgEmbed, {
                            x,
                            y,
                            width: width * scale,
                            height: height * scale
                        });

                        slotIndex++;
                    } catch (err) {
                        console.error('Error processing image:', err);
                    }
                }

                return await pdfDoc.save();
            }

            async function generatePDFReturnBlob() {
                const pdfBytes = await createPhotoPDF();
                return new Blob([pdfBytes], { type: 'application/pdf' });
            }

            async function TambahkanTemuan() {
                if (images.length === 0) {
                    alert('Please select at least one image.');
                    return;
                }

                const photoPDFBlob = await generatePDFReturnBlob();
                const formData = new FormData();
                formData.append('Id_List_Report', '{{ $listReport->Id_List_Report }}');
                formData.append('photo_pdf', photoPDFBlob, 'temuan_photos.pdf');

                try {
                    const response = await fetch("{{ route('auditor-report.temuan_create') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    console.log(await response.json());

                    if (response.ok) {
                        alert('Temuan added successfully!');
                        window.location.reload();
                    } else {
                        alert('Failed to add Temuan.');
                    }
                } catch (error) {
                    console.error('Error submitting Temuan:', error);
                    alert('An error occurred while submitting Temuan.');
                }
            }
        </script>
    @else
        {{--editable layout however this was for only for annotation and editable layout--}}
        <script>
            // ============================================
            // GLOBAL VARIABLES & CONFIGURATION
            // ============================================

            const listTemuanNull = @json($ListTemuanNull);

            const CONFIG = {
                pdfUrl: getPdfUrl(listTemuanNull ? listTemuanNull.File_Path_Temuan : null),
                pdfScale: 1.5,
                fontSize: {
                    timestamp: 8,
                    comment: 12,
                    mark: 18
                },
                colors: {
                    check: { text: 'blue', bg: 'rgba(0,255,0,0.3)' },
                    ng: { text: 'blue', bg: 'rgba(255,0,0,0.3)' },
                    x: { text: 'blue', bg: 'rgba(255,0,0,0.3)' },
                    comment: { text: 'white', bg: '#E91E63', border: 'transparent' }
                }
            };

            // DOM Elements
            const DOM = {
                canvas: document.getElementById('pdf-canvas-editor'),
                editorLayer: document.getElementById('editor-layer'),
                buttons: {
                    check: document.getElementById('checklist-btn'),
                    ng: document.getElementById('ng-btn'),
                    x: document.getElementById('x-btn'),
                    comment: document.getElementById('comment-btn'),
                    delete: document.getElementById('delete-btn')
                },
                icons: {
                    check: document.getElementById('checklist-btn-icon'),
                    ng: document.getElementById('ng-btn-icon'),
                    x: document.getElementById('x-btn-icon'),
                    comment: document.getElementById('comment-btn-icon')
                }
            };

            // State Management
            const STATE = {
                checklistMode: false,
                currentMode: null, // 'check', 'ng', 'x', 'comment'
                selectedObject: null,
                history: [],
                redoStack: [],
                pageViewportHeights: [],
                images: []
            };

            const FINAL_STATE = {
                comments: [],
            }

            // ============================================
            // PDF RENDERING
            // ============================================
            async function renderPDF() {
                try {
                    let pdf;
                    try {
                        pdf = await pdfjsLib.getDocument(CONFIG.pdfUrl).promise;
                    } catch (err) {
                        console.warn('pdfjsLib.getDocument failed, trying fetch fallback:', err);
                        const resp = await fetch(CONFIG.pdfUrl);
                        const buffer = await resp.arrayBuffer();
                        pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
                    }

                    const ctx = DOM.canvas.getContext('2d');

                    // Calculate total dimensions
                    const pageData = await calculatePageDimensions(pdf);
                    setupCanvasSize(pageData.totalHeight, pageData.maxWidth);

                    // Render all pages
                    await renderAllPages(pdf, pageData.viewports, ctx);
                } catch (error) {
                    console.error('Editor PDF rendering error:', error);
                }
            }

            async function calculatePageDimensions(pdf) {
                const viewports = [];
                let totalHeight = 0;
                let maxWidth = 0;

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale: CONFIG.pdfScale });

                    viewports.push({ page, viewport });
                    totalHeight += viewport.height;
                    maxWidth = Math.max(maxWidth, viewport.width);
                    STATE.pageViewportHeights.push(viewport.height);
                }

                return { viewports, totalHeight, maxWidth };
            }

            function setupCanvasSize(height, width) {
                DOM.canvas.width = width;
                DOM.canvas.height = height;
                DOM.editorLayer.style.width = width + 'px';
                DOM.editorLayer.style.height = height + 'px';
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

            // Initialize PDF rendering
            if (CONFIG.pdfUrl) {
                renderPDF();
            } else {
                console.warn('PDF URL is empty in CONFIG');
            }

            // ============================================
            // STATE MANAGEMENT
            // ============================================
            function saveState() {
                STATE.history.push(DOM.editorLayer.innerHTML);
                STATE.redoStack = [];
                console.log(STATE)
            }

            function undo() {
                if (STATE.history.length > 0) {
                    STATE.redoStack.push(STATE.history.pop());
                    DOM.editorLayer.innerHTML = STATE.history[STATE.history.length - 1] || '';
                    rebindEvents();
                    clearSelection();
                }
            }

            function redo() {
                if (STATE.redoStack.length > 0) {
                    const state = STATE.redoStack.pop();
                    STATE.history.push(state);
                    DOM.editorLayer.innerHTML = state;
                    rebindEvents();
                }
            }

            function clearSelection() {
                STATE.selectedObject = null;
                DOM.buttons.delete.disabled = true;
            }

            // ============================================
            // ANNOTATION CREATION
            // ============================================
            function createAnnotation(type, text = '', color = 'blue') {
                if (type === 'comment') {
                    return createEditableComment(text);
                }
                return createDraggableMark(text, color);
            }

            function createDraggableMark(text, color) {
                const mark = document.createElement('div');
                mark.textContent = text;

                const config = CONFIG.colors[text.toLowerCase()] || CONFIG.colors.check;

                Object.assign(mark.style, {
                    position: 'absolute',
                    top: '50px',
                    left: '50px',
                    cursor: 'move',
                    color: color,
                    background: config.bg,
                    padding: '2px 5px',
                    fontSize: '24px',
                    userSelect: 'none',
                    border: '1px solid transparent'
                });

                setupDraggableEvents(mark);
                setupSelectionEvents(mark);

                return mark;
            }

            function createEditableComment(initialText = '') {
                const comment = document.createElement('div');
                comment.contentEditable = true;
                comment.textContent = initialText;

                Object.assign(comment.style, {
                    position: 'absolute',
                    top: '50px',
                    left: '50px',
                    cursor: 'move',
                    color: CONFIG.colors.comment.text,
                    backgroundColor: CONFIG.colors.comment.bg,
                    border: 'none',
                    whiteSpace: 'pre-wrap', // Allow newlines
                    minWidth: '100px',
                    minHeight: '20px',
                    outline: 'none'
                });

                comment.addEventListener('input', saveState);
                setupDraggableEvents(comment);
                setupSelectionEvents(comment);

                return comment;
            }

            // ============================================
            // EVENT HANDLERS
            // ============================================
            function setupDraggableEvents(element) {
                element.setAttribute('draggable', 'true');

                // Disable dragging when editing to allow Enter key
                element.addEventListener('focus', () => {
                    element.removeAttribute('draggable');
                });

                element.addEventListener('blur', () => {
                    element.setAttribute('draggable', 'true');
                });

                // Explicit Enter key handler for newlines
                element.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Insert line break manually
                        document.execCommand('insertLineBreak');
                    }
                });

                element.addEventListener('dragstart', (e) => {
                    element.startX = e.clientX - element.offsetLeft;
                    element.startY = e.clientY - element.offsetTop;
                });

                element.addEventListener('dragend', (e) => {
                    const x = constrainX(e.clientX - element.startX, element.offsetWidth);
                    const y = constrainY(e.clientY - element.startY, element.offsetHeight);

                    element.style.left = x + 'px';
                    element.style.top = y + 'px';
                    saveState();
                });
            }

            function setupSelectionEvents(element) {
                element.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectElement(element);
                });
            }

            function constrainX(x, width) {
                const maxX = DOM.editorLayer.clientWidth - width;
                return Math.max(0, Math.min(x, maxX));
            }

            function constrainY(y, height) {
                const maxY = DOM.editorLayer.clientHeight - height;
                return Math.max(0, Math.min(y, maxY));
            }

            function selectElement(element) {
                if (STATE.selectedObject) {
                    STATE.selectedObject.classList.remove('selected');
                    resetElementBorder(STATE.selectedObject);
                }

                STATE.selectedObject = element;
                element.classList.add('selected');
                element.style.border = '2px dashed red';
                DOM.buttons.delete.disabled = false;
            }

            function resetElementBorder(element) {
                if (element.contentEditable === 'true') {
                    element.style.border = '2px dashed #fff';
                } else {
                    element.style.border = '1px solid transparent';
                }
            }

            function deleteSelected() {
                if (STATE.selectedObject) {
                    DOM.editorLayer.removeChild(STATE.selectedObject);
                    clearSelection();
                    saveState();
                }
            }
            // ============================================
            // TOOLBAR CONTROLS
            // ============================================
            function toggleChecklist(mode) {
                // Reset all buttons to inactive state
                resetAllButtons();

                // Toggle mode
                if (STATE.currentMode === mode) {
                    STATE.checklistMode = false;
                    STATE.currentMode = null;
                } else {
                    STATE.checklistMode = true;
                    STATE.currentMode = mode;
                    activateButton(mode);
                }
            }

            function resetAllButtons() {
                const buttons = ['check', 'ng', 'x', 'comment'];
                buttons.forEach(btn => {
                    DOM.buttons[btn].classList.remove('btn-success');
                    DOM.buttons[btn].classList.add('btn-primary');
                });

                DOM.icons.check.textContent = 'edit_off';
                DOM.icons.ng.textContent = 'block';
                DOM.icons.x.textContent = 'close';
                DOM.icons.comment.textContent = 'text_fields';
            }

            function activateButton(mode) {
                DOM.buttons[mode].classList.add('btn-success');
                DOM.buttons[mode].classList.remove('btn-primary');
                DOM.icons[mode].textContent = 'edit';
            }

            // ============================================
            // EDITOR LAYER INTERACTION
            // ============================================
            DOM.editorLayer.addEventListener('click', function (e) {
                if (!STATE.checklistMode) {
                    handleClickOutsideAnnotation();
                    return;
                }

                const position = getClickPosition(e);
                const annotation = createAnnotationByMode(STATE.currentMode);

                if (annotation) {
                    placeAnnotation(annotation, position);
                    saveState();
                }
            });

            function handleClickOutsideAnnotation() {
                if (STATE.selectedObject) {
                    STATE.selectedObject.classList.remove('selected');
                    resetElementBorder(STATE.selectedObject);
                    STATE.selectedObject = null;
                    DOM.buttons.delete.disabled = true;
                }
            }

            function getClickPosition(e) {
                const rect = DOM.editorLayer.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            }

            function createAnnotationByMode(mode) {
                const annotations = {
                    'check': () => createDraggableMark('V', 'blue'),
                    'ng': () => createDraggableMark('NG', 'blue'),
                    'x': () => createDraggableMark('X', 'blue'),
                    'comment': () => createEditableComment('')
                };

                return annotations[mode] ? annotations[mode]() : null;
            }

            function placeAnnotation(element, position) {
                DOM.editorLayer.appendChild(element);

                const centerX = position.x - (element.offsetWidth / 2);
                const centerY = position.y - (element.offsetHeight / 2);

                element.style.left = Math.max(0, centerX) + 'px';
                element.style.top = Math.max(0, centerY) + 'px';
            }

            // ============================================
            // EVENT REBINDING (for Undo/Redo)
            // ============================================
            function rebindEvents() {
                DOM.editorLayer.querySelectorAll('div').forEach(div => {
                    // Clear old event listeners
                    div.onclick = null;
                    div.ondragstart = null;
                    div.ondragend = null;
                    div.oninput = null;

                    // Re-attach based on element type
                    if (div.contentEditable === 'true') {
                        rebindCommentEvents(div);
                    } else {
                        rebindMarkEvents(div);
                    }
                });
            }

            function rebindCommentEvents(element) {
                setupSelectionEvents(element);
                setupDraggableEvents(element);
                element.addEventListener('input', saveState);
            }

            function rebindMarkEvents(element) {
                setupSelectionEvents(element);
                setupDraggableEvents(element);
            }
            {{--Submit Report with Annotations--}}
            // ============================================
            // UTILITIES
            // ============================================

            function getWIBTimestamp() {
                const nowUTC = new Date();
                const offsetWIB = 7 * 60; // WIB = UTC+7
                const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
                return localWIB.toISOString().slice(0, 19).replace('T', ' ');
            }

            function calculatePageOffsets() {
                const offsets = [0];
                for (let i = 0; i < STATE.pageViewportHeights.length - 1; i++) {
                    offsets.push(offsets[i] + STATE.pageViewportHeights[i]);
                }
                return offsets;
            }

            function findPageIndex(y, offsets) {
                const index = offsets.findIndex((offset, i) =>
                    y < offset + STATE.pageViewportHeights[i]
                );
                return index === -1 ? offsets.length - 1 : index;
            }

            function calculatePDFPosition(x, y, pageIndex, offsets, page) {
                const canvasW = DOM.canvas.width;
                const pageHeight = page.getHeight();
                const pageWidth = page.getWidth();
                const offsetY = y - offsets[pageIndex];
                const scaleX = pageWidth / canvasW;
                const scaleY = pageHeight / STATE.pageViewportHeights[pageIndex];

                return {
                    x: x * scaleX,
                    y: pageHeight - (offsetY * scaleY) - 18
                };
            }

            function drawRectangle(page, x, y, width, height, color, opacity = 1) {
                page.drawRectangle({ x, y, width, height, color, opacity });
            }

            // ============================================
            // PDF ANNOTATION RENDERING
            // ============================================

            function addTimestampToFirstPage(page, font) {
                const fontSize = CONFIG.fontSize.timestamp;
                const timestamp = getWIBTimestamp();
                const lines = [timestamp, "{{ $current_user->Name_User }}"];
                const lineHeight = fontSize + 2;
                let y = page.getHeight() - 10;

                lines.forEach((line, idx) => {
                    page.drawText(line, {
                        x: 500,
                        y: y - (idx * lineHeight),
                        size: fontSize,
                        font,
                        color: PDFLib.rgb(0, 0, 1)
                    });
                });
            }

            function renderCommentToPDF(page, element, x, y, font) {
                const text = element.textContent.trim();
                if (!text || text === 'Tulis komentar...') return;

                const fontSize = CONFIG.fontSize.comment;
                const lineHeight = fontSize + 4;

                // Split text by newlines
                const rawText = element.innerText;
                const lines = rawText.split(/\r?\n/);

                // Calculate dimensions based on longest line
                let maxLineWidth = 0;
                lines.forEach(line => {
                    const width = font.widthOfTextAtSize(line, fontSize);
                    if (width > maxLineWidth) maxLineWidth = width;
                });

                const paddingX = 6;
                const paddingY = 6;
                const outerWidth = maxLineWidth + (2 * paddingX);
                const outerHeight = (lines.length * lineHeight) + (2 * paddingY);

                // Adjust Y calculation (similar to report.blade.php fix)
                // In this file, it references 'y' directly.
                // Previous logic: const boxY = y - fontSize - 4 - padding.y; (Logic seems to place box ABOVE the Y point? or maybe Y is bottom?)
                // Let's check calculatePDFPosition: y = pageHeight - (offsetY * scaleY) - 18;
                // If HTML y increases, PDF y decreases.
                // If y is the TOP in HTML:
                // We want the box to extend DOWN from y in HTML, which means DOWN from y in PDF too.
                // Wait, PDF Y=0 is bottom. So DOWN means decreasing Y.
                // So boxY should be y - outerHeight.

                const boxY = y - outerHeight;
                const boxX = x - paddingX;

                FINAL_STATE.comments.push({
                    'text': text,
                    'position': { 'x': x, 'y': y },
                    'fontSize': fontSize
                })

                // Outer background (pink)
                drawRectangle(page, boxX, boxY, outerWidth, outerHeight,
                    PDFLib.rgb(0.913, 0.117, 0.388)); // #E91E63

                // Text lines
                lines.forEach((line, index) => {
                    const textY = y - paddingY - (index + 1) * lineHeight + 4;
                    page.drawText(line, {
                        x: x,
                        y: textY,
                        size: fontSize,
                        color: PDFLib.rgb(1, 1, 1), // White
                        font
                    });
                });
            }

            function renderMarkToPDF(page, element, x, y, font) {
                const text = element.textContent;
                const size = CONFIG.fontSize.mark;
                const width = 20 * text.length * 0.6;
                const height = 18;
                const padding = 2;

                // Background
                drawRectangle(page, x - padding, y - padding,
                    width + (2 * padding), height + (2 * padding),
                    PDFLib.rgb(1, 1, 1), 0.5
                );

                // Text color
                const isRed = element.style.color === 'red';
                const color = isRed ? PDFLib.rgb(1, 0, 0) : PDFLib.rgb(0, 0, 1);

                page.drawText(text, { x, y, size, color, font });
            }

            function convertAnnotationsToPDF(pages, font) {
                const offsets = calculatePageOffsets();

                DOM.editorLayer.querySelectorAll('div').forEach(div => {
                    const x = parseFloat(div.style.left);
                    const y = parseFloat(div.style.top);
                    const pageIndex = findPageIndex(y, offsets);
                    const page = pages[pageIndex];
                    const position = calculatePDFPosition(x, y, pageIndex, offsets, page);

                    if (div.contentEditable === 'true') {
                        renderCommentToPDF(page, div, position.x, position.y, font);
                    } else {
                        renderMarkToPDF(page, div, position.x, position.y, font);
                    }
                });
            }

            // ============================================
            // SUBMIT REPORT
            // ============================================

            async function submitReport() {
                try {
                    const pdfBytes = await loadAndAnnotatePDF();
                    await uploadToServer(pdfBytes);
                } catch (error) {
                    console.error('Submit error:', error);
                    alert('Failed to submit report: ' + error.message);
                }
            }

            async function loadAndAnnotatePDF() {
                const pdfBuffer = await fetch(CONFIG.pdfUrl).then(r => r.arrayBuffer());
                const pdfDoc = await PDFLib.PDFDocument.load(pdfBuffer);
                const pages = pdfDoc.getPages();
                const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

                addTimestampToFirstPage(pages[0], font);
                convertAnnotationsToPDF(pages, font);

                return await pdfDoc.save();
            }

            async function uploadToServer(pdfBytes) {
                const formData = new FormData();
                formData.append('Id_List_Report', '{{ $listReport->Id_List_Report }}');
                formData.append('Id_Temuan', '{{ $ListTemuanNull['Id_Temuan'] }}');
                formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
                formData.append('timestamp', getWIBTimestamp());
                formData.append('comments', JSON.stringify(FINAL_STATE.comments));

                const response = await fetch(`{{ route('auditor-report.temuan_submit') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });

                console.log(await response.json());
                if (response.ok) {
                    alert('Report submitted successfully!');
                    location.reload();
                } else {
                    throw new Error('Server rejected submission');
                }
            }
        </script>
    @endif



@endsection
