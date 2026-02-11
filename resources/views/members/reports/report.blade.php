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
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Photos</label>
                        <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*"
                            capture="environment">
                        {{-- <input type="file" class="form-control image-input" name="{{ $part->photo_angle->Id_Photo_Angle }}"
                            data-preview="#preview-{{ $part->photo_angle->Id_Photo_Angle }}" accept="image/*"
                            capture="environment"> --}}
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
        const CONFIG = {
            pdfUrl: (function () {
                let path = "{!! str_replace('\\', '/', $pdfPath) !!}";
                if (!path) return null;
                let baseUrl = "{{ asset('') }}";
                if (baseUrl.endsWith('/')) baseUrl = baseUrl.slice(0, -1);
                let p = path.replace(/\\/g, '/');
                if (!p.startsWith('/') && !p.startsWith('http')) p = '/' + p;
                let url = p.startsWith('http') ? p : baseUrl + p;
                url = url.replace(/ /g, '%20');
                return url + (url.includes('?') ? '&' : '?') + "t=" + new Date().getTime();
            })(),
            pdfScale: 1.5,
            fontSize: {
                timestamp: 8,
                comment: 12,
                mark: 24
            },
            colors: {
                check: { text: 'green', bg: 'rgba(0,255,0,0.3)' },
                ng: { text: 'green', bg: 'rgba(255,0,0,0.3)' },
                x: { text: 'green', bg: 'rgba(255,0,0,0.3)' },
                comment: { text: 'white', bg: '#E91E63', border: 'transparent' }
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
            if (!CONFIG.pdfUrl || !DOM.canvas) return;
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
                console.error('RenderPDF error:', error);
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
            STATE.selectedObject = null;
            if (DOM.buttons.delete) DOM.buttons.delete.disabled = true;
        }

        // ============================================
        // ANNOTATION CREATION
        // ============================================
        function createAnnotation(type, text = '', color = 'green') {
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
                fontSize: CONFIG.fontSize.mark + 'px',
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
                minWidth: '100px',
                minHeight: '20px',
                padding: '5px',
                fontSize: CONFIG.fontSize.comment + 'px',
                whiteSpace: 'pre-wrap', // Allow newlines
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
            if (DOM.buttons.delete) DOM.buttons.delete.disabled = false;
        }

        function resetElementBorder(element) {
            if (element.contentEditable === 'true') {
                element.style.border = '2px dashed #fff'; // White selection for visibility on pink
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
                if (DOM.buttons[btn]) {
                    DOM.buttons[btn].classList.remove('btn-success');
                    DOM.buttons[btn].classList.add('btn-primary');
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
                if (DOM.buttons.delete) DOM.buttons.delete.disabled = true;
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
                'check': () => createDraggableMark('V', 'green'),
                'ng': () => createDraggableMark('NG', 'green'),
                'x': () => createDraggableMark('X', 'green'),
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

        // ============================================
        // PDF DOWNLOAD
        // ============================================
        async function downloadPdf() {
            const pdfBytes = await fetch(CONFIG.pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
            const finalBytes = await pdfDoc.save();

            const blob = new Blob([finalBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = '{{ $listReport->report->member->Name_Member }}-{{ $listReport->Name_Procedure }}.pdf';
            link.click();
        }

        // ============================================
        // PHOTO UPLOAD & MANAGEMENT
        // ============================================
        STATE.images = [];

        if (document.getElementById('imageInput')) {
            document.getElementById('imageInput').addEventListener('change', function (e) {
                for (let file of e.target.files) {
                    STATE.images.push(file);
                    showPreview(file);
                }
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
                delBtn.style.padding = '0';
                delBtn.style.display = 'flex';
                delBtn.style.alignItems = 'center';
                delBtn.style.justifyContent = 'center';
                delBtn.style.fontSize = '12px';

                delBtn.onclick = function () {
                    const index = Array.from(container.parentNode.children).indexOf(container);
                    STATE.images.splice(index, 1);
                    container.remove();
                };

                container.appendChild(img);
                container.appendChild(delBtn);
                const previewDiv = document.getElementById('preview');
                if (previewDiv) previewDiv.appendChild(container);
            };
            reader.readAsDataURL(file);
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
                    canvas.toBlob(blob => resolve(blob), file.type, 0.7); // 0.7 = quality (for jpeg)
                };
                img.src = URL.createObjectURL(file);
            });
        }

        // ============================================
        // SUBMIT REPORT WITH ANNOTATIONS & PHOTOS
        // ============================================
        async function submitReport() {
            // Load existing PDF
            const existingPdf = await fetch(CONFIG.pdfUrl).then(r => r.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdf);
            const pages = pdfDoc.getPages();
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

            // Add timestamp and member name
            const nowUTC = new Date();
            const offsetWIB = 7 * 60; // WIB = UTC+7 in minutes
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const timestamp = localWIB.toISOString().slice(0, 19).replace('T', ' ');
            addTimestampToFirstPage(pages[0], font, timestamp);

            // Convert HTML annotations to PDF
            convertAnnotationsToPDF(pages, font);

            // Create and merge photo PDF
            const photoPdfBytes = await createPhotoPDF();
            if (photoPdfBytes) {
                const imgDoc = await PDFLib.PDFDocument.load(photoPdfBytes);
                const copiedPages = await pdfDoc.copyPages(imgDoc, imgDoc.getPageIndices());
                copiedPages.forEach(p => pdfDoc.addPage(p));
            }

            // Save and submit
            const mergedBytes = await pdfDoc.save();
            await uploadToServer(mergedBytes, timestamp);
        }

        function addTimestampToFirstPage(page, font, timestamp) {
            const fontSize = CONFIG.fontSize.timestamp;
            const lines = [timestamp, "{{ $member->Name_Member }}"];
            let yStart = page.getHeight() - 10;
            const lineHeight = fontSize + 2;

            lines.forEach((line, idx) => {
                const width = font.widthOfTextAtSize(line, fontSize);
                page.drawText(line, {
                    x: 200, // Adjusted X for member position
                    y: yStart - idx * lineHeight,
                    size: fontSize,
                    font,
                    color: PDFLib.rgb(0, 0.6, 0) // Green for member
                });
            });
        }

        function convertAnnotationsToPDF(pages, font) {
            // Calculate page offsets
            const canvasW = DOM.canvas.width;
            let yOffsets = [0];
            for (let i = 0; i < STATE.pageViewportHeights.length - 1; i++) {
                yOffsets.push(yOffsets[i] + STATE.pageViewportHeights[i]);
            }

            // Process each annotation
            DOM.editorLayer.querySelectorAll('div').forEach(div => {
                const x = parseFloat(div.style.left);
                const y = parseFloat(div.style.top);

                // Find which page this annotation belongs to
                let pageIndex = yOffsets.findIndex((offset, i) => y < offset + STATE.pageViewportHeights[i]);
                if (pageIndex === -1) pageIndex = pages.length - 1;

                const page = pages[pageIndex];
                const pageHeight = page.getHeight();
                const pageWidth = page.getWidth();

                // Calculate position on PDF page
                const offsetY = y - yOffsets[pageIndex];
                const scaleX = pageWidth / canvasW;
                const scaleY = pageHeight / STATE.pageViewportHeights[pageIndex];
                const finalX = x * scaleX;
                const finalY = pageHeight - (offsetY * scaleY) - 18;

                // Render annotation to PDF
                if (div.contentEditable === 'true') {
                    renderCommentToPDF(page, div, finalX, finalY, font);
                } else {
                    renderMarkToPDF(page, div, finalX, finalY, font);
                }
            });
        }

        function renderCommentToPDF(page, element, x, y, font) {
            const rawText = element.innerText.replace(/[\u200B-\u200D\uFEFF]/g, '');
            const lines = rawText.split(/\r?\n/).map(l => l.replace(/[^\x00-\xFF]/g, ''));
            const text = lines.join('').trim();
            if (!text || text === 'Tulis komentar...') return;

            const fontSize = CONFIG.fontSize.comment;
            const lineHeight = fontSize + 4;

            // Calculate dimensions based on longest line
            let maxLineWidth = 0;
            lines.forEach(line => {
                try {
                    const width = font.widthOfTextAtSize(line, fontSize);
                    if (width > maxLineWidth) maxLineWidth = width;
                } catch (e) { }
            });

            const paddingX = 6;
            const paddingY = 6;
            const outerWidth = maxLineWidth + (2 * paddingX);
            const outerHeight = (lines.length * lineHeight) + (2 * paddingY);
            const outerX = x - paddingX;
            const rectBottomY = y - outerHeight;

            // Background pink
            page.drawRectangle({
                x: outerX,
                y: rectBottomY,
                width: outerWidth,
                height: outerHeight,
                color: PDFLib.rgb(0.913, 0.117, 0.388),
                opacity: 1
            });

            // Text white
            lines.forEach((line, index) => {
                const textY = y - paddingY - (index + 1) * lineHeight + 4;
                page.drawText(line, {
                    x: x,
                    y: textY,
                    size: fontSize,
                    color: PDFLib.rgb(1, 1, 1),
                    font
                });
            });
        }

        function renderMarkToPDF(page, element, x, y, font) {
            const text = element.textContent;
            const size = (text === 'V' || text === 'X' || text === 'NG') ? 18 : 12;
            const textWidth = 20 * text.length * 0.6;
            const textHeight = 18;

            // Background white 50%
            page.drawRectangle({
                x: x - 2,
                y: y - 2,
                width: textWidth + 4,
                height: textHeight + 4,
                color: PDFLib.rgb(1, 1, 1),
                opacity: 0.5
            });

            // Member marks are always green
            const color = PDFLib.rgb(0, 0.6, 0);

            page.drawText(text, {
                x: x,
                y: y,
                size: size,
                color: color,
                font
            });
        }

        async function createPhotoPDF() {
            if (STATE.images.length === 0) return null;

            const photoDoc = await PDFLib.PDFDocument.create();
            const PAGE_WIDTH = 841.89; // A4 landscape
            const PAGE_HEIGHT = 595.28;
            const MARGIN = 20;
            const SLOT_COLS = 2, SLOT_ROWS = 2;
            const SLOT_W = (PAGE_WIDTH - (MARGIN * 2)) / SLOT_COLS;
            const SLOT_H = (PAGE_HEIGHT - (MARGIN * 2)) / SLOT_ROWS;

            let page = null;
            let slotIndex = 0;

            for (let file of STATE.images) {
                if (slotIndex % 4 === 0) {
                    page = photoDoc.addPage([PAGE_WIDTH, PAGE_HEIGHT]);
                }

                const resizedBlob = await resizeImage(file, 1000, 1000);
                const imgBytes = await resizedBlob.arrayBuffer();
                let imgEmbed;
                try {
                    imgEmbed = file.type.includes('png') ?
                        await photoDoc.embedPng(imgBytes) :
                        await photoDoc.embedJpg(imgBytes);
                } catch (e) {
                    continue;
                }

                const { width, height } = imgEmbed.size();
                const scale = Math.min(SLOT_W / width, SLOT_H / height);
                const col = slotIndex % 2;
                const row = Math.floor((slotIndex % 4) / 2);
                const x = MARGIN + col * SLOT_W + (SLOT_W - width * scale) / 2;
                const y = PAGE_HEIGHT - MARGIN - ((row + 1) * SLOT_H) + (SLOT_H - height * scale) / 2;

                page.drawImage(imgEmbed, {
                    x, y,
                    width: width * scale,
                    height: height * scale
                });
                slotIndex++;
            }

            return await photoDoc.save();
        }

        async function uploadToServer(pdfBytes, timestamp) {
            const formData = new FormData();
            formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
            formData.append('timestamp', timestamp);

            const url = `{{ route('report_list_member.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });

                if (res.ok) {
                    alert('Report submitted successfully!');
                    location.reload();
                } else {
                    alert('Failed to submit report. Server returned ' + res.status);
                }
            } catch (error) {
                alert('An error occurred during submission: ' + error.message);
            }
        }
    </script>
@endsection