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
                <!-- Tombol Back -->
                <a class="btn btn-primary mx-3" href="javascript:void(0)" onclick="window.history.back()">
                    <span style="padding-left: 50px; padding-right: 50px;">Back</span>
                </a>

                <br><br>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->Name_Procedure }}</span>
                    </h4>

                    {{-- @if(is_null($listReport->Time_Approved_Auditor)) --}}
{{--                    <button class="btn btn-warning mt-3" style="white-space:nowrap;"--}}
{{--                        onclick="window.location.href = '{{ route('auditor-report.temuan_report', ['Id_List_Report' => $listReport->Id_List_Report]) }}'">--}}
{{--                        Tambahkan Temuan--}}
{{--                    </button>--}}
                    @if($listReport->temuans && count($listReport->temuans) > 0)
                        <button class="btn btn-warning mt-3" style="white-space:nowrap;"
                                onclick="window.location.href = '{{ route('auditor-report.temuan_show',['Id_Temuan' => $listReport->temuans[0]->Id_Temuan]) }}'">
                            Lihat Temuan
                        </button>
                    @endif
                    {{-- @endif --}}
                </div>
                <br>

                {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button> --}}

                @if (is_null($listReport->Time_Approved_Auditor))
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

                @if ($listReport->Time_Approved_Auditor)
                    <div><b>Check Member : <span class="text-primary">{{ $listReport->Time_List_Report }}</span></b>
                    </div>
                    <div><b>Leader Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Leader }}</span></b>
                    </div>
                    <div><b>Auditor Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Auditor }}</span></b>
                    </div>
                    <br>
                    <button class="btn btn-sm btn-primary mt-3" onclick="downloadPdf()">Download PDF</button>
                @endif

                <div id="pdf-container" style="border:1px solid #ccc; height:100%; overflow:auto; position:relative; width:100%; max-width:100%; left:50%; transform:translateX(-50%);">
                    <canvas id="pdf-canvas"></canvas>
                    <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
                </div>

                <br><br>
                @if (is_null($listReport->Time_Approved_Auditor))
                    <h5>Photos for : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h5>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Photos</label>
                        <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*"
                            capture="environment">
                    </div>
                    <div id="preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>
                    <br>
                    <button onclick="submitReport('submit')" class="btn btn-primary mt-3">Submit Report</button>
                    <button onclick="submitReport('temuan')" class="btn btn-warning mt-3 ms-9">Submit Temuan</button>
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
            pdfUrl: "{{ asset($pdfPath) }}?t=" + new Date().getTime(),
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
            const pdf = await pdfjsLib.getDocument(CONFIG.pdfUrl).promise;
            const ctx = DOM.canvas.getContext('2d');

            // Calculate total dimensions
            const pageData = await calculatePageDimensions(pdf);
            setupCanvasSize(pageData.totalHeight, pageData.maxWidth);

            // Render all pages
            await renderAllPages(pdf, pageData.viewports, ctx);
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
                minWidth: '100px',
                minHeight: '20px',
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
            DOM.buttons.delete.disabled = false;
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
    </script>

    <!-- ============================================ -->
    <!-- PHOTO UPLOAD & MANAGEMENT -->
    <!-- ============================================ -->
    <script>
        // Photo state
        STATE.images = [];

        // Handle image selection
        document.getElementById('imageInput').addEventListener('change', function (e) {
            for (let file of e.target.files) {
                STATE.images.push(file);
                showPreview(file);
            }
        });

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
                    STATE.images.splice(index, 1);
                    container.remove();
                };

                container.appendChild(img);
                container.appendChild(delBtn);
                document.getElementById('preview').appendChild(container);
            };
            reader.readAsDataURL(file);
        }

        // Generate standalone photo PDF (for download only, not used in submit)
        async function generatePDF() {
            const pdfDoc = await PDFLib.PDFDocument.create();
            const PAGE_WIDTH = 841.89; // A4 landscape
            const PAGE_HEIGHT = 595.28;

            const SLOT_COLS = 2;
            const SLOT_ROWS = 2;
            const SLOT_W = PAGE_WIDTH / SLOT_COLS;
            const SLOT_H = PAGE_HEIGHT / SLOT_ROWS;

            let page = null;
            let slotIndex = 0;

            for (let file of STATE.images) {
                if (slotIndex % 4 === 0) {
                    page = pdfDoc.addPage([PAGE_WIDTH, PAGE_HEIGHT]);
                }

                const imgBytes = await file.arrayBuffer();
                let imgEmbed = null;
                try {
                    if (file.type === 'image/png') {
                        imgEmbed = await pdfDoc.embedPng(imgBytes);
                    } else if (file.type === 'image/jpeg' || file.type === 'image/jpg') {
                        imgEmbed = await pdfDoc.embedJpg(imgBytes);
                    } else {
                        try {
                            imgEmbed = await pdfDoc.embedJpg(imgBytes);
                        } catch (e) {
                            imgEmbed = await pdfDoc.embedPng(imgBytes);
                        }
                    }
                } catch (err) {
                    alert(`Failed to embed image: ${file.name}`);
                    continue;
                }

                const { width, height } = imgEmbed.size();
                const scale = Math.min(SLOT_W / width, SLOT_H / height);

                const col = slotIndex % 2;
                const row = Math.floor((slotIndex % 4) / 2);

                const x = col * SLOT_W + (SLOT_W - width * scale) / 2;
                const y = PAGE_HEIGHT - ((row + 1) * SLOT_H) + (SLOT_H - height * scale) / 2;

                page.drawImage(imgEmbed, {
                    x: x,
                    y: y,
                    width: width * scale,
                    height: height * scale
                });

                slotIndex++;
            }

            const pdfBytes = await pdfDoc.save();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'photos.pdf';
            link.click();
        }
    </script>
    <script>

        const FINAL_STATE = {
            comments:[]
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
        async function submitReport(type) {
            // Load existing PDF
            const existingPdf = await fetch(CONFIG.pdfUrl).then(r => r.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdf);
            const pages = pdfDoc.getPages();
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

            // Add timestamp and auditor name
            addTimestampToFirstPage(pages[0], font);

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
            if (type === 'submit') {
                console.log('submitReporting');
                await uploadToServerReport(mergedBytes);
            } else if (type === 'temuan') {
                console.log('submitTemuan');
                await uploadToServerTemuan(mergedBytes);
            }
        }

        function addTimestampToFirstPage(page, font) {
            const fontSize = CONFIG.fontSize.timestamp;
            const nowUTC = new Date();
            const offsetWIB = 7 * 60; // WIB = UTC+7 in minutes
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const timestamp = localWIB.toISOString().slice(0, 19).replace('T', ' ');
            const lines = [timestamp, "{{ $user->Name_User }}"];
            let yStart = page.getHeight() - 10;
            const lineHeight = fontSize + 2;

            lines.forEach((line, idx) => {
                page.drawText(line, {
                    x: 500,
                    y: yStart - idx * lineHeight,
                    size: fontSize,
                    font,
                    color: PDFLib.rgb(0, 0, 1)
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
            const text = element.textContent.trim(); // Gunakan textContent untuk mengambil semua teks termasuk newline
            if (!text || text === 'Tulis komentar...') return;

            const fontSize = CONFIG.fontSize.comment;
            const lineHeight = fontSize + 4;

            // Split text by newlines
            // Note: contentEditable usually inserts <div> or <br> for newlines.
            // We need to handle how the browser represents newlines in contentEditable.
            // A safer approach for contentEditable that might contain HTML:
            // Split text by newlines
            // Sanitize: Remove zero-width chars and ensure WinAnsi compatibility
            const rawText = element.innerText.replace(/[\u200B-\u200D\uFEFF]/g, '');
            // Split and map to ensure we check characters
            const lines = rawText.split(/\r?\n/).map(l => l.replace(/[^\x00-\xFF]/g, '')); // Basic Latin-1 filter

            // Calculate dimensions based on longest line
            let maxLineWidth = 0;
            lines.forEach(line => {
                try {
                    const width = font.widthOfTextAtSize(line, fontSize);
                    if (width > maxLineWidth) maxLineWidth = width;
                } catch (e) {
                    console.warn('Skipping unsupported character line:', line);
                }
            });

            const paddingX = 6;
            const paddingY = 6;
            const outerWidth = maxLineWidth + (2 * paddingX);
            const outerHeight = (lines.length * lineHeight) + (2 * paddingY);

            // Adjust Y because PDF coordinates are bottom-up and we want the box to start at 'y' (top-left visual)
            // If 'y' passed here is bottom-left of the element in HTML, we need to correct it.
            // The previous code calculated: const finalY = pageHeight - (offsetY * scaleY) - 18;
            // accepted x,y as bottom-left ish? Let's look at previous implementation.
            // Previous: const outerY = y - textHeight - paddingY;
            // It seems 'y' was treated as the baseline or bottom of the first line?
            // Actually, HTML 'top' is top.
            // In convertAnnotationsToPDF: finalY = pageHeight - (offsetY * scaleY) - 18;
            // This 'finalY' seems to be the TOP of the element in PDF coordinates (since PDF Y=0 is bottom).
            // Wait, PDF Y=0 is bottom. pageHeight - y is often Top.
            // Let's stick to the previous logic's coordinate system but expand height.

            // Previous logic:
            // const outerY = y - textHeight - paddingY;
            // page.drawRectangle({ y: outerY ... })
            // textY = outerY + outerHeight - fontSize - 4;

            // If 'y' is the visual TOP of the element from HTML mapped to PDF:
            // The previous logic seems to shift it down?
            // Let's assume 'y' is the top-left corner of where we want the box.
            // In previous code: outerY = y - textHeight - paddingY. This puts the box BELOW y? No, if y is top, y - height is even lower.
            // Wait, if 'y' is top in PDF (high value), then y - height draws a box from (y-height) extending upwards by height? No, drawRectangle starts at x,y and goes width,height.
            // So if we draw at y - height, the box goes from y-height to y. Correct.

            // So for multiline:
            // We want the box to extend downwards from 'y'.
            // So the bottom of the box will be: y - outerHeight.
            const rectBottomY = y - outerHeight;

            FINAL_STATE.comments.push({
                'text': text,
                'position': { 'x': x, 'y': y },
                'fontSize': fontSize
            })

            // Draw background (Pink)
            page.drawRectangle({
                x: x - paddingX,
                y: rectBottomY,
                width: outerWidth,
                height: outerHeight,
                color: PDFLib.rgb(0.913, 0.117, 0.388), // #E91E63
                opacity: 1
            });

            // Draw text lines
            lines.forEach((line, index) => {
                const textY = y - paddingY - (index + 1) * lineHeight + 4; // +4 adjustment to align baseline
                page.drawText(line, {
                    x: x, // Aligned to left padding
                    y: textY,
                    size: fontSize,
                    color: PDFLib.rgb(1, 1, 1), // White
                    font
                });
            });
        }

        function renderMarkToPDF(page, element, x, y, font) {
            const textContent = element.textContent;
            const size = CONFIG.fontSize.mark;
            const textWidth = 20 * textContent.length * 0.6;
            const textHeight = 18;

            // Draw background
            page.drawRectangle({
                x: x - 2,
                y: y - 2,
                width: textWidth + 4,
                height: textHeight + 4,
                color: PDFLib.rgb(1, 1, 1),
                opacity: 0.5
            });

            // Determine text color
            let textColor = PDFLib.rgb(0, 0, 1); // Default blue
            if (element.style.color === 'red') {
                textColor = PDFLib.rgb(1, 0, 0);
            }

            // Draw text
            page.drawText(textContent, {
                x: x,
                y: y,
                size: size,
                color: textColor,
                font
            });
        }

        async function createPhotoPDF() {
            if (STATE.images.length === 0) return null;

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

            for (let file of STATE.images) {
                if (slotIndex % 4 === 0) {
                    page = pdfDoc.addPage([PAGE_WIDTH, PAGE_HEIGHT]);
                }

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
            }

            return await pdfDoc.save();
        }

        async function uploadToServerReport(pdfBytes) {
            const nowUTC = new Date();
            const offsetWIB = 7 * 60;
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const timestamp = localWIB.toISOString().slice(0, 19).replace('T', ' ');

            const formData = new FormData();
            formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
            formData.append('timestamp', timestamp);

            const response = await fetch(`{{ route('report_auditor.detail.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            if (response.ok) {
                alert('Report submitted successfully!');
                location.reload();
            } else {
                alert('Failed to submit report');
            }
        }
        async function uploadToServerTemuan(pdfBytes) {
            const nowUTC = new Date();
            const offsetWIB = 7 * 60;
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const timestamp = localWIB.toISOString().slice(0, 19).replace('T', ' ');

            const formData = new FormData();
            formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
            formData.append('comments',JSON.stringify(FINAL_STATE.comments))
            formData.append('Id_List_Report', '{{ $listReport->Id_List_Report }}');
            formData.append('timestamp', timestamp);

            const response = await fetch(`{{ route('auditor-report.temuan_submit') }}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            if (response.ok) {
                alert('Report submitted successfully!');
                location.reload();
            } else {
                alert('Failed to submit report');
            }
        }
    </script>
@endsection
