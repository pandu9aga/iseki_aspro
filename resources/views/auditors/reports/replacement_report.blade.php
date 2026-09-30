@extends('layouts.auditor')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Prosedur Pengganti</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                <!-- Tombol Back & Navigation -->
                <div class="d-flex gap-2 mb-4 align-items-center">
                    <a class="btn btn-primary mb-0" href="{{ route('list_report_replacement_auditor', ['Id_Report_Replacement' => $listReport->Id_Report_Replacement]) }}">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        @if($prevReportId)
                            <a href="{{ route('report_auditor.replacement_detail', ['Id_List_Report_Replacement' => $prevReportId]) }}" class="btn btn-outline-primary mb-0" title="Previous Report">
                                <i class="material-symbols-rounded text-sm">chevron_left</i>
                            </a>
                        @else
                            <button class="btn btn-outline-secondary mb-0" disabled>
                                <i class="material-symbols-rounded text-sm">chevron_left</i>
                            </button>
                        @endif

                        <span class="text-sm text-secondary fw-bold">{{ $currentPos }} / {{ count($siblingReports) }}</span>

                        @if($nextReportId)
                            <a href="{{ route('report_auditor.replacement_detail', ['Id_List_Report_Replacement' => $nextReportId]) }}" class="btn btn-outline-primary mb-0" title="Next Report">
                                <i class="material-symbols-rounded text-sm">chevron_right</i>
                            </a>
                        @else
                            <button class="btn btn-outline-secondary mb-0" disabled>
                                <i class="material-symbols-rounded text-sm">chevron_right</i>
                            </button>
                        @endif
                    </div>
                </div>

                <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->display_name }}</span></h4>
                <br>

                <div><b>Check Member : <span class="text-primary">{{ $listReport->Time_List_Report ?? '-' }}</span></b></div>
                <div><b>Leader Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Leader ?? '-' }}</span></b></div>
                <div><b>Auditor Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Auditor ?? '-' }}</span></b></div>

                @include('components.qr-approval-display', [
                    'itemType' => 'replacement',
                    'itemId' => $listReport->Id_List_Report_Replacement,
                    'qrCodes' => $listReport->Qr_Codes
                ])

                <div class="mt-2 mb-3">
                    <button class="btn btn-sm btn-primary mb-0" onclick="downloadPdf()">Download PDF</button>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary mt-3" id="checklist-btn" onclick="toggleChecklist('check')">
                        <i class="material-symbols-rounded" id="checklist-btn-icon">edit_off</i>
                    </button>
                    <button class="btn btn-warning mt-3" onclick="undo()">
                        <i class="material-symbols-rounded">undo</i>
                    </button>
                    <button class="btn btn-info mt-3" onclick="redo()">
                        <i class="material-symbols-rounded">redo</i>
                    </button>
                    <button class="btn btn-danger mt-3" id="delete-btn" onclick="deleteSelected()" disabled>
                        <i class="material-symbols-rounded">delete</i>
                    </button>
                    <button class="btn btn-primary mt-3" id="ng-btn" onclick="toggleChecklist('ng')">
                        <i class="material-symbols-rounded" id="ng-btn-icon">block</i>
                    </button>
                    <button class="btn btn-primary mt-3" id="x-btn" onclick="toggleChecklist('x')">
                        <i class="material-symbols-rounded" id="x-btn-icon">edit_off</i>
                    </button>
                    <button class="btn btn-primary mt-3" id="comment-btn" onclick="toggleChecklist('comment')">
                        <i class="material-symbols-rounded" id="comment-btn-icon">text_fields</i>
                    </button>
                </div>

                <div id="pdf-container" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                    <canvas id="pdf-canvas"></canvas>
                    <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
                </div>

                <!-- PDF Navigation Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    @if($prevReportId)
                        <a href="{{ route('report_auditor.replacement_detail', ['Id_List_Report_Replacement' => $prevReportId]) }}" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded text-sm align-middle">arrow_back</i> Previous
                        </a>
                    @else
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="material-symbols-rounded text-sm align-middle">arrow_back</i> Previous
                        </button>
                    @endif

                    <span class="text-sm text-secondary fw-bold">{{ $currentPos }} / {{ count($siblingReports) }}</span>

                    @if($nextReportId)
                        <a href="{{ route('report_auditor.replacement_detail', ['Id_List_Report_Replacement' => $nextReportId]) }}" class="btn btn-outline-primary">
                            Next <i class="material-symbols-rounded text-sm align-middle">arrow_forward</i>
                        </a>
                    @else
                        <button class="btn btn-outline-secondary" disabled>
                            Next <i class="material-symbols-rounded text-sm align-middle">arrow_forward</i>
                        </button>
                    @endif
                </div>

                <!-- Dokumentasi Foto Per User di bawah PDF -->
                @include('components.photo-gallery-display', [
                    'itemType' => 'replacement',
                    'itemId' => $listReport->Id_List_Report_Replacement,
                    'photos' => $listReport->Photos,
                    'currentRole' => 'auditor'
                ])

                <br>
                <h5>Photos for : <span class="text-primary">{{ $listReport->display_name }}</span></h5>
                <div class="my-3">
                    <label class="form-label d-block">Upload Photos</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary mb-0" onclick="triggerPhotoInput('camera')">
                            <i class="material-symbols-rounded text-sm">photo_camera</i> Camera
                        </button>
                        <button type="button" class="btn btn-outline-info mb-0" onclick="triggerPhotoInput('gallery')">
                            <i class="material-symbols-rounded text-sm">collections</i> Gallery
                        </button>
                    </div>
                    <input type="file" class="form-control d-none" id="imageInput" multiple accept="image/*">
                </div>
                <div id="preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>
                <br>
                <button onclick="submitReport()" class="btn btn-primary mt-3">
                    {{ $listReport->Time_Approved_Auditor ? 'Update Auditor Approval & Stamp' : 'Submit Auditor Approval' }}
                </button>
            </div>
        </section>
    </div>
    @include('components.qr-approval-modal')
    <style>
        .selected {
            outline: 2px dashed red;
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('assets/js/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/js/pdf-lib.min.js') }}"></script>
    <script src="{{ asset('assets/js/aspro-annotations.js') }}"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/js/pdf.worker.min.js') }}";

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

        const CONFIG = {
            pdfUrl: getPdfUrl("{!! str_replace('\\', '/', $pdfPath) !!}"),
            pdfScale: 1.5,
            itemType: 'replacement',
            itemId: '{{ $listReport->Id_List_Report_Replacement }}',
            currentRole: 'auditor',
            savedAnnotations: @json($listReport->Annotations ?? []),
            timestamps: {
                member: '{{ $listReport->Time_List_Report }}',
                leader: '{{ $listReport->Time_Approved_Leader }}',
                auditor: '{{ $listReport->Time_Approved_Auditor }}'
            },
            names: {
                member: '{{ $listReport->replacement->member->Name_Member ?? '' }}',
                leader: '{{ $listReport->Leader_Name ?? '' }}',
                auditor: '{{ $user->Name_User ?? ($listReport->Auditor_Name ?? '') }}'
            },
            fontSize: { timestamp: 8, comment: 12, mark: 18 },
            colors: {
                check: { text: 'blue', bg: 'rgba(0,255,0,0.3)' },
                ng: { text: 'blue', bg: 'rgba(255,0,0,0.3)' },
                x: { text: 'blue', bg: 'rgba(255,0,0,0.3)' },
                comment: { text: 'white', bg: '#E91E63', border: 'transparent' }
            }
        };

        const DOM = {
            canvas: document.getElementById('pdf-canvas'),
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

        const STATE = {
            checklistMode: false,
            currentMode: null,
            selectedObject: null,
            history: [],
            redoStack: [],
            pageViewportHeights: [],
            images: []
        };

        async function renderPDF() {
            if (!CONFIG.pdfUrl) return;
            try {
                const pdf = await pdfjsLib.getDocument(CONFIG.pdfUrl).promise;
                const ctx = DOM.canvas.getContext('2d');
                const viewports = [];
                let totalHeight = 0, maxWidth = 0;

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const vp = page.getViewport({ scale: CONFIG.pdfScale });
                    viewports.push({ page, vp });
                    totalHeight += vp.height;
                    maxWidth = Math.max(maxWidth, vp.width);
                    STATE.pageViewportHeights.push(vp.height);
                }

                DOM.canvas.width = maxWidth;
                DOM.canvas.height = totalHeight;
                DOM.editorLayer.style.width = maxWidth + 'px';
                DOM.editorLayer.style.height = totalHeight + 'px';

                let currentY = 0;
                for (const { page, vp } of viewports) {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = vp.width;
                    tempCanvas.height = vp.height;
                    const tempCtx = tempCanvas.getContext('2d');
                    await page.render({ canvasContext: tempCtx, viewport: vp }).promise;
                    ctx.drawImage(tempCanvas, 0, currentY);
                    currentY += vp.height;
                }

                // Render saved annotations from JSON onto editor layer
                if (window.AsproAnnotationEngine && CONFIG.savedAnnotations) {
                    AsproAnnotationEngine.renderSavedAnnotations(
                        DOM.editorLayer,
                        CONFIG.savedAnnotations,
                        'auditor',
                        (div) => {
                            setupEvents(div);
                            if (div.contentEditable === 'true') {
                                div.addEventListener('input', saveState);
                            }
                        },
                        CONFIG.timestamps,
                        CONFIG.names
                    );
                }
            } catch (error) {
                console.error('RenderPDF error:', error);
            }
        }
        renderPDF();

        function saveState() {
            STATE.history.push(DOM.editorLayer.innerHTML);
            STATE.redoStack = [];
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
            if (STATE.selectedObject) {
                STATE.selectedObject.classList.remove('selected');
                resetElementBorder(STATE.selectedObject);
            }
            STATE.selectedObject = null;
            if (DOM.buttons.delete) DOM.buttons.delete.disabled = true;
        }

        function resetElementBorder(element) {
            if (element.contentEditable === 'true') {
                element.style.border = 'none';
            } else {
                element.style.border = '1px solid transparent';
            }
        }

        function setupEvents(element) {
            element.setAttribute('draggable', 'true');

            element.addEventListener('click', e => {
                e.stopPropagation();
                if (STATE.selectedObject) {
                    STATE.selectedObject.classList.remove('selected');
                    resetElementBorder(STATE.selectedObject);
                }
                STATE.selectedObject = element;
                element.classList.add('selected');
                element.style.border = '2px dashed red';
                if (DOM.buttons.delete) DOM.buttons.delete.disabled = false;
            });

            element.addEventListener('dragstart', e => {
                element.startX = e.clientX - element.offsetLeft;
                element.startY = e.clientY - element.offsetTop;
            });

            element.addEventListener('dragend', e => {
                let x = e.clientX - element.startX;
                let y = e.clientY - element.startY;
                const maxX = DOM.editorLayer.clientWidth - element.offsetWidth;
                const maxY = DOM.editorLayer.clientHeight - element.offsetHeight;
                x = Math.max(0, Math.min(x, maxX));
                y = Math.max(0, Math.min(y, maxY));
                element.style.left = x + 'px';
                element.style.top = y + 'px';
                saveState();
            });

            if (element.contentEditable === 'true') {
                element.addEventListener('focus', () => element.removeAttribute('draggable'));
                element.addEventListener('blur', () => element.setAttribute('draggable', 'true'));
                element.addEventListener('keydown', e => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        e.stopPropagation();
                        document.execCommand('insertLineBreak');
                    }
                });
                element.addEventListener('input', saveState);
            }
        }

        function rebindEvents() {
            DOM.editorLayer.querySelectorAll('div').forEach(div => setupEvents(div));
        }

        function toggleChecklist(mode) {
            resetAllButtons();
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
            const modes = ['check', 'ng', 'x', 'comment'];
            modes.forEach(m => {
                if (DOM.buttons[m]) {
                    DOM.buttons[m].classList.remove('btn-success');
                    DOM.buttons[m].classList.add('btn-primary');
                }
            });
            if (DOM.icons.check) DOM.icons.check.textContent = 'edit_off';
            if (DOM.icons.ng) DOM.icons.ng.textContent = 'block';
            if (DOM.icons.x) DOM.icons.x.textContent = 'close';
            if (DOM.icons.comment) DOM.icons.comment.textContent = 'text_fields';
        }

        function activateButton(mode) {
            if (DOM.buttons[mode]) {
                DOM.buttons[mode].classList.add('btn-success');
                DOM.buttons[mode].classList.remove('btn-primary');
            }
            if (DOM.icons[mode]) DOM.icons[mode].textContent = 'edit';
        }

        function deleteSelected() {
            if (STATE.selectedObject) {
                DOM.editorLayer.removeChild(STATE.selectedObject);
                clearSelection();
                saveState();
            }
        }

        DOM.editorLayer.addEventListener('click', e => {
            if (!STATE.checklistMode) {
                clearSelection();
                return;
            }
            const rect = DOM.editorLayer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            let annotation;
            if (STATE.currentMode === 'check') annotation = createDraggableMark('V', 'blue');
            else if (STATE.currentMode === 'ng') annotation = createDraggableMark('NG', 'red');
            else if (STATE.currentMode === 'x') annotation = createDraggableMark('X', 'red');
            else if (STATE.currentMode === 'comment') annotation = createEditableComment('');

            if (annotation) {
                annotation.setAttribute('data-role', CONFIG.currentRole || 'auditor');
                DOM.editorLayer.appendChild(annotation);
                annotation.style.left = Math.max(0, x - 10) + 'px';
                annotation.style.top = Math.max(0, y - 10) + 'px';
                saveState();
            }
        });

        function createDraggableMark(text, color) {
            const mark = document.createElement('div');
            mark.textContent = text;
            const config = CONFIG.colors[text.toLowerCase()] || CONFIG.colors.check;

            Object.assign(mark.style, {
                position: 'absolute',
                top: '50px',
                left: '50px',
                cursor: 'move',
                color: config.text,
                background: config.bg,
                padding: '2px 5px',
                fontSize: (text === 'V' || text === 'X' || text === 'NG') ? '24px' : '14px',
                userSelect: 'none',
                border: '1px solid transparent'
            });

            setupEvents(mark);
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
                minWidth: '100px',
                minHeight: '20px',
                whiteSpace: 'pre-wrap',
                padding: '5px',
                fontSize: '14px',
                outline: 'none'
            });

            setupEvents(comment);
            comment.addEventListener('input', saveState);
            return comment;
        }

        // ============================================
        // PDF SUBMISSION & DOWNLOAD
        // ============================================
        async function downloadPdf() {
            try {
                const res = await fetch(`{{ route('item.annotations.get', ['type' => 'replacement', 'id' => $listReport->Id_List_Report_Replacement]) }}`);
                const data = await res.json();
                const masterUrl = data.master_pdf_url || CONFIG.pdfUrl;

                await AsproAnnotationEngine.downloadAnnotatedPdf({
                    masterPdfUrl: masterUrl,
                    downloadFilename: 'Replacement-{{ $listReport->display_name }}.pdf',
                    canvasWidth: DOM.canvas.width,
                    pageViewportHeights: STATE.pageViewportHeights,
                    annotations: data.annotations || CONFIG.savedAnnotations,
                    timestamps: data.timestamps || CONFIG.timestamps,
                    names: data.names || CONFIG.names
                });
            } catch (err) {
                console.error('Download error:', err);
                window.open(CONFIG.pdfUrl, '_blank');
            }
        }

        async function submitReport() {
            const currentAnnotations = AsproAnnotationEngine.serializeLayer(DOM.editorLayer, CONFIG.currentRole || 'auditor');

            const nowUTC = new Date();
            const offsetWIB = 7 * 60;
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const now = localWIB.toISOString().slice(0, 19).replace('T', ' ');

            openQrApprovalScanner(async function(scannedQrs) {
                if (STATE.images && STATE.images.length > 0) {
                    let uploadErrors = 0;
                    for (let file of STATE.images) {
                        const photoForm = new FormData();
                        photoForm.append('role', 'auditor');
                        photoForm.append('photo', file, file.name);
                        try {
                            const photoRes = await fetch(`{{ route('item.photo.upload', ['type' => 'replacement', 'id' => $listReport->Id_List_Report_Replacement]) }}`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: photoForm
                            });
                            if (!photoRes.ok) {
                                uploadErrors++;
                                const errData = await photoRes.json().catch(() => ({}));
                                console.warn('Photo upload failed:', photoRes.status, errData);
                            }
                        } catch (e) {
                            uploadErrors++;
                            console.warn('Failed to upload photo:', e);
                        }
                    }
                    if (uploadErrors > 0) {
                        alert(`${uploadErrors} foto gagal diupload. Pastikan ukuran foto tidak terlalu besar.`);
                    }
                }

                const formData = new FormData();
                formData.append('timestamp', now);
                formData.append('annotations', JSON.stringify(currentAnnotations));
                formData.append('qr_codes', JSON.stringify(scannedQrs));

                fetch(`{{ route('report_auditor.replacement_submit', ['Id_List_Report_Replacement' => $listReport->Id_List_Report_Replacement]) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                }).then(res => {
                    if (res.ok) {
                        alert('Approval auditor berhasil disubmit!');
                        location.reload();
                    } else {
                        alert('Gagal menyubmit approval auditor');
                    }
                }).catch(err => {
                    alert('Error: ' + err.message);
                });
            });
        }

        function renderCommentToPDF(page, div, x, y, font) {
            const rawText = div.innerText.replace(/[\u200B-\u200D\uFEFF]/g, '');
            const textLines = rawText.split(/\r?\n/).map(l => l.replace(/[^\x00-\xFF]/g, ''));
            if (textLines.join('').trim() === '') return;

            const fontSize = CONFIG.fontSize.comment;
            const lineHeight = fontSize + 4;
            let maxWidth = 0;
            textLines.forEach(l => {
                try { maxWidth = Math.max(maxWidth, font.widthOfTextAtSize(l, fontSize)); } catch (e) { }
            });

            const padding = 6;
            const boxWidth = maxWidth + 2 * padding;
            const boxHeight = textLines.length * lineHeight + 2 * padding;

            page.drawRectangle({
                x: x - padding,
                y: y - boxHeight,
                width: boxWidth,
                height: boxHeight,
                color: PDFLib.rgb(0.914, 0.118, 0.388),
                opacity: 1
            });

            textLines.forEach((line, i) => {
                page.drawText(line, {
                    x: x,
                    y: y - padding - (i + 1) * lineHeight + 4,
                    size: fontSize,
                    color: PDFLib.rgb(1, 1, 1),
                    font
                });
            });
        }

        function renderMarkToPDF(page, div, x, y, font) {
            const text = div.textContent;
            const size = (text === 'V' || text === 'X' || text === 'NG') ? 18 : 12;
            const textWidth = 20 * text.length * 0.6;
            const textHeight = 18;

            page.drawRectangle({
                x: x - 2,
                y: y - 2,
                width: textWidth + 4,
                height: textHeight + 4,
                color: PDFLib.rgb(1, 1, 1),
                opacity: 0.5
            });

            let color = PDFLib.rgb(0, 0, 0);
            if (div.style.color === 'blue') color = PDFLib.rgb(0, 0, 1);
            else if (div.style.color === 'red') color = PDFLib.rgb(1, 0, 0);

            page.drawText(text, { x, y, size, color, font });
        }

        async function resizeImage(file, maxWidth, maxHeight) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onerror = function() { reject(new Error('img load failed')); };
                img.onload = function () {
                    let width = img.width;
                    let height = img.height;
                    if (width <= maxWidth && height <= maxHeight) {
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(img, 0, 0);
                        canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('toBlob null')), 'image/jpeg', 0.82);
                        return;
                    }
                    const scale = Math.min(maxWidth / width, maxHeight / height);
                    width = Math.round(width * scale);
                    height = Math.round(height * scale);
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('toBlob null')), 'image/jpeg', 0.82);
                };
                img.src = URL.createObjectURL(file);
            });
        }

        function triggerPhotoInput(mode) {
            const input = document.getElementById('imageInput');
            if (!input) return;
            if (mode === 'camera') {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        }

        const imgInput = document.getElementById('imageInput');
        if (imgInput) {
            imgInput.addEventListener('change', async function (e) {
                for (let file of e.target.files) {
                    try {
                        const resizedBlob = await resizeImage(file, 1600, 1600);
                        if (!resizedBlob) throw new Error('resize null');
                        const jpegName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        const blobFile = new File([resizedBlob], jpegName, { type: 'image/jpeg' });
                        STATE.images.push(blobFile);
                        showPreview(blobFile);
                    } catch (err) {
                        STATE.images.push(file);
                        showPreview(file);
                    }
                }
                e.target.value = '';
            });
        }

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
                delBtn.onclick = function () {
                    const index = STATE.images.indexOf(file);
                    if (index > -1) STATE.images.splice(index, 1);
                    container.remove();
                };
                container.appendChild(img);
                container.appendChild(delBtn);
                document.getElementById('preview').appendChild(container);
            };
            reader.readAsDataURL(file);
        }
    </script>
@endsection
