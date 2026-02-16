@extends('layouts.member')
@section('content')
    <header class="header-2">
        <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg.jpg') }}')">
            <span class="mask bg-gradient-dark opacity-4"></span>
            <div class="container">
                <div class="row">
                    <div class="col-12 mx-auto">
                        <h3 class="text-white pt-3 mt-n2">Training</h3>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

        <section class="pt-3 pb-4" id="count-stats">
            <div class="container">
                <!-- Tombol Back -->
                <a class="btn btn-primary mx-3" href="javascript:void(0)" onclick="window.history.back()">
                    <span style="padding-left: 50px; padding-right: 50px;">Back</span>
                </a>
                <br><br>

                <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h4>
                <br>

                {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button> --}}

                @if (is_null($listReport->Time_List_Report))
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
                    <!-- Tombol NG -->
                    <button class="btn btn-primary mt-3" id="ng-btn" onclick="toggleChecklist('ng')">
                        <i class="material-symbols-rounded" id="ng-btn-icon">block</i> <!-- Ganti ikon sesuai kebutuhan -->
                    </button>
                    <!-- Tombol X -->
                    <button class="btn btn-primary mt-3" id="x-btn" onclick="toggleChecklist('x')">
                        <!-- Ganti onclick ke 'x' -->
                        <i class="material-symbols-rounded" id="x-btn-icon">edit_off</i>
                        <!-- Ganti ikon sesuai kebutuhan -->
                    </button>
                    <!-- Tombol Comment -->
                    <button class="btn btn-primary mt-3" id="comment-btn" onclick="toggleChecklist('comment')">
                        <i class="material-symbols-rounded" id="comment-btn-icon">text_fields</i>
                        <!-- Ganti ikon sesuai kebutuhan -->
                    </button>
                @endif

                @if ($listReport->Time_List_Report)
                    <div><b>Check Member : <span class="text-primary">{{ $listReport->Time_List_Report }}</span></b></div>
                    <div><b>Leader Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Leader }}</span></b>
                    </div>
                    <div><b>Auditor Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Auditor }}</span></b>
                    </div>
                    <br>

                    <button class="btn btn-sm btn-primary mt-3" onclick="downloadPdf()">Download PDF</button>
                @endif

                <div id="pdf-container" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                    <canvas id="pdf-canvas"></canvas>
                    <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
                </div>

                <br><br>
                @if (is_null($listReport->Time_List_Report))
                    <h5>Photos for : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h5>
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
                    <button onclick="submitReport()" class="btn btn-primary mt-3">Submit Report</button>
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
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/js/pdf.worker.min.js') }}";

        // ============================================
        // GLOBAL VARIABLES & CONFIGURATION
        // ============================================
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
            fontSize: {
                timestamp: 8,
                comment: 12,
                mark: 18
            },
            colors: {
                check: { text: 'green', bg: 'rgba(0,255,0,0.3)' }, // Member stamp V is green
                ng: { text: 'red', bg: 'rgba(255,0,0,0.3)' },
                x: { text: 'red', bg: 'rgba(255,0,0,0.3)' },
                comment: { text: 'white', bg: '#800080', border: 'transparent' } // Member comment is purple
            }
        };

        // DOM Elements
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

        // ============================================
        // PDF RENDERING
        // ============================================
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
            } catch (error) {
                console.error('RenderPDF error:', error);
            }
        }
        renderPDF();

        // ============================================
        // STATE MANAGEMENT
        // ============================================
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

        // ============================================
        // EVENT BINDING (for Undo/Redo)
        // ============================================
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
                        // Insert line break manually
                        document.execCommand('insertLineBreak');
                    }
                });
                element.addEventListener('input', saveState);
            }
        }

        function rebindEvents() {
            DOM.editorLayer.querySelectorAll('div').forEach(div => {
                // Remove old listeners implicitly by replacing element or just re-adding (standard in this pattern)
                // For simplicity in this logic, we call setupEvents again (event listeners stack, so ideally we'd use onclick = etc., 
                // but the Leader/Auditor code uses addEventListener. I'll stick to clear assignments if possible or just ensure it's robust).
                setupEvents(div);
            });
        }

        // ============================================
        // TOOLBAR CONTROLS
        // ============================================
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

        // ============================================
        // EDITOR LAYER INTERACTION
        // ============================================
        DOM.editorLayer.addEventListener('click', e => {
            if (!STATE.checklistMode) {
                clearSelection();
                return;
            }
            const rect = DOM.editorLayer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            let annotation;
            if (STATE.currentMode === 'check') annotation = createDraggableMark('V', 'green');
            else if (STATE.currentMode === 'ng') annotation = createDraggableMark('NG', 'red');
            else if (STATE.currentMode === 'x') annotation = createDraggableMark('X', 'red');
            else if (STATE.currentMode === 'comment') annotation = createEditableComment('');

            if (annotation) {
                DOM.editorLayer.appendChild(annotation);
                // Center annotation on click
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
            const existingPdfBytes = await fetch(CONFIG.pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdfBytes);
            const pdfBytes = await pdfDoc.save();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = '{{ $listReport->report->member->Name_Member }}-{{ $listReport->Name_Procedure }}.pdf';
            link.click();
        }

        async function submitReport() {
            const existingPdfBytes = await fetch(CONFIG.pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdfBytes);
            const pages = pdfDoc.getPages();
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

            // Add timestamp
            const nowUTC = new Date();
            const offsetWIB = 7 * 60;
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const now = localWIB.toISOString().slice(0, 19).replace('T', ' ');
            const lines = [now, "{{ $member->Name_Member }}"];
            let startY = pages[0].getHeight() - 10;
            const timestampFontSize = CONFIG.fontSize.timestamp;
            const lineHeight = timestampFontSize + 2;

            lines.forEach((line, i) => {
                pages[0].drawText(line, {
                    x: 200,
                    y: startY - i * lineHeight,
                    size: timestampFontSize,
                    font: font,
                    color: PDFLib.rgb(0, 0.6, 0), // Member timestamp is green
                });
            });

            // Convert HTML annotations to PDF
            const canvasW = DOM.canvas.width;
            let yOffsets = [0];
            for (let i = 0; i < STATE.pageViewportHeights.length - 1; i++) {
                yOffsets.push(yOffsets[i] + STATE.pageViewportHeights[i]);
            }

            DOM.editorLayer.querySelectorAll('div').forEach(div => {
                let x = parseFloat(div.style.left);
                let y = parseFloat(div.style.top);

                let pageIndex = yOffsets.findIndex((offset, i) => y < offset + STATE.pageViewportHeights[i]);
                if (pageIndex === -1) pageIndex = pages.length - 1;

                const page = pages[pageIndex];
                const pageHeight = page.getHeight();
                const pageWidth = page.getWidth();

                const offsetY = y - yOffsets[pageIndex];
                const scaleX = pageWidth / canvasW;
                const scaleY = pageHeight / STATE.pageViewportHeights[pageIndex];

                const finalX = x * scaleX;
                const finalY = pageHeight - (offsetY * scaleY) - 18;

                if (div.contentEditable === 'true') {
                    renderCommentToPDF(page, div, finalX, finalY, font);
                } else {
                    renderMarkToPDF(page, div, finalX, finalY, font);
                }
            });

            const pdfBytes = await pdfDoc.save();
            const formData = new FormData();
            formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
            formData.append('timestamp', now);

            fetch(`{{ route('report.detail.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            }).then(res => {
                if (res.ok) {
                    alert('Report submitted successfully!');
                    location.reload();
                } else {
                    alert('Failed to submit report');
                }
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
                color: PDFLib.rgb(0.502, 0, 0.502), // Purple
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
            if (div.style.color === 'green') color = PDFLib.rgb(0, 0.6, 0);
            else if (div.style.color === 'red') color = PDFLib.rgb(1, 0, 0);

            page.drawText(text, { x, y, size, color, font });
        }

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
                    canvas.toBlob(blob => resolve(blob), file.type, 0.7);
                };
                img.src = URL.createObjectURL(file);
            });
        }

        function triggerPhotoInput(mode) {
            const input = document.getElementById('imageInput');
            if (mode === 'camera') {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        }

        // ============================================
        // IMAGE UPLOAD & PREVIEW
        // ============================================
        document.getElementById('imageInput').addEventListener('change', function (e) {
            for (let file of e.target.files) {
                STATE.images.push(file);
                showPreview(file);
            }
        });

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