@extends('layouts.leader')
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
                <!-- Tombol Back & Navigation -->
                <div class="d-flex gap-2 mb-4 align-items-center">
                    <a class="btn btn-primary mb-0" href="javascript:void(0)" onclick="window.history.back()">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back
                    </a>

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        @if($prevReportId)
                            <a href="{{ route('training.detail', array_merge(['Id_List_Training' => $prevReportId], request()->query())) }}" class="btn btn-outline-primary mb-0" title="Previous Report">
                                <i class="material-symbols-rounded text-sm">chevron_left</i>
                            </a>
                        @else
                            <button class="btn btn-outline-secondary mb-0" disabled>
                                <i class="material-symbols-rounded text-sm">chevron_left</i>
                            </button>
                        @endif

                        <span class="text-sm text-secondary fw-bold">{{ $currentPos }} / {{ count($siblingReports) }}</span>

                        @if($nextReportId)
                            <a href="{{ route('training.detail', array_merge(['Id_List_Training' => $nextReportId], request()->query())) }}" class="btn btn-outline-primary mb-0" title="Next Report">
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
                    'itemType' => 'training',
                    'itemId' => $listReport->Id_List_Training,
                    'qrCodes' => $listReport->Qr_Codes
                ])

                <div class="mt-2 mb-3">
                    <button class="btn btn-sm btn-primary mb-0" onclick="downloadPdf()">Download PDF</button>
                </div>

                {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button> --}}

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
                </div>

                <div id="pdf-container" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                    <canvas id="pdf-canvas"></canvas>
                    <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
                </div>

                <!-- PDF Navigation Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    @if($prevReportId)
                        <a href="{{ route('training.detail', array_merge(['Id_List_Training' => $prevReportId], request()->query())) }}" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded text-sm align-middle">arrow_back</i> Previous
                        </a>
                    @else
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="material-symbols-rounded text-sm align-middle">arrow_back</i> Previous
                        </button>
                    @endif

                    <span class="text-sm text-secondary fw-bold">{{ $currentPos }} / {{ count($siblingReports) }}</span>

                    @if($nextReportId)
                        <a href="{{ route('training.detail', array_merge(['Id_List_Training' => $nextReportId], request()->query())) }}" class="btn btn-outline-primary">
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
                    'itemType' => 'training',
                    'itemId' => $listReport->Id_List_Training,
                    'photos' => $listReport->Photos,
                    'currentRole' => 'leader'
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
                    {{ $listReport->Time_Approved_Leader ? 'Update Report & Stamp' : 'Submit Report' }}
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
    <script>
        let images = [];

        function triggerPhotoInput(mode) {
            const input = document.getElementById('imageInput');
            if (mode === 'camera') {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('imageInput');
            if (!input) return;
            input.addEventListener('change', async function (e) {
                for (let file of e.target.files) {
                    try {
                        const resizedBlob = await resizeImage(file, 1600, 1600);
                        if (!resizedBlob) throw new Error('resize null');
                        const jpegName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        const blobFile = new File([resizedBlob], jpegName, { type: 'image/jpeg' });
                        images.push(blobFile);
                        showPreview(blobFile);
                    } catch (e) {
                        images.push(file);
                        showPreview(file);
                    }
                }
                // Reset input agar file yang sama bisa dipilih lagi
                e.target.value = '';
            });
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
                    const index = images.indexOf(file);
                    if (index > -1) images.splice(index, 1);
                    container.remove();
                };
                container.appendChild(img);
                container.appendChild(delBtn);
                document.getElementById('preview').appendChild(container);
            };
            reader.readAsDataURL(file);
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
    </script>
    <script src="{{ asset('assets/js/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/js/pdf-lib.min.js') }}"></script>
    <script src="{{ asset('assets/js/aspro-annotations.js') }}"></script>
    <script>
        const CONFIG = {
            pdfUrl: "{{ asset($pdfPath) }}?t=" + new Date().getTime(),
            itemType: 'training',
            itemId: '{{ $listReport->Id_List_Training }}',
            currentRole: 'leader',
            savedAnnotations: @json($listReport->Annotations ?? []),
            timestamps: {
                member: '{{ $listReport->Time_List_Report }}',
                leader: '{{ $listReport->Time_Approved_Leader }}',
                auditor: '{{ $listReport->Time_Approved_Auditor }}'
            },
            names: {
                member: '{{ $listReport->training->member->Name_Member ?? '' }}',
                leader: '{{ $user->Name_User ?? ($listReport->Leader_Name ?? '') }}',
                auditor: '{{ $listReport->Auditor_Name ?? '' }}'
            }
        };
        const pdfUrl = CONFIG.pdfUrl;
        const pdfCanvas = document.getElementById('pdf-canvas');
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/js/pdf.worker.min.js') }}";
        const editorLayer = document.getElementById('editor-layer');
        const pdfScale = 1.5;
        let checklistMode = false;
        let currentMode = null; // 'check', 'ng', 'x', 'comment'
        let selectedObject = null;
        let history = [],
            redoStack = [];

        let pageViewportHeights = [];

        // --- Referensi Tombol dan Ikon ---
        const checklistBtn = document.getElementById('checklist-btn');
        const ngBtn = document.getElementById('ng-btn');
        const xBtn = document.getElementById('x-btn');
        const commentBtn = document.getElementById('comment-btn');
        const checklistBtnIcon = document.getElementById('checklist-btn-icon');
        const ngBtnIcon = document.getElementById('ng-btn-icon');
        const xBtnIcon = document.getElementById('x-btn-icon');
        const commentBtnIcon = document.getElementById('comment-btn-icon');

        async function renderPDF() {
            const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
            const ctx = pdfCanvas.getContext('2d');

            const viewports = [];
            let totalHeight = 0,
                maxWidth = 0;

            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const vp = page.getViewport({
                    scale: pdfScale
                });
                viewports.push({
                    page,
                    vp
                });
                totalHeight += vp.height;
                maxWidth = Math.max(maxWidth, vp.width);
                pageViewportHeights.push(vp.height);
            }

            pdfCanvas.width = maxWidth;
            pdfCanvas.height = totalHeight;
            editorLayer.style.width = maxWidth + 'px';
            editorLayer.style.height = totalHeight + 'px';

            let currentY = 0;
            for (const {
                page,
                vp
            }
                of viewports) {
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = vp.width;
                tempCanvas.height = vp.height;
                const tempCtx = tempCanvas.getContext('2d');

                await page.render({
                    canvasContext: tempCtx,
                    viewport: vp
                }).promise;
                ctx.drawImage(tempCanvas, 0, currentY);
                currentY += vp.height;
            }

            // Render saved annotations from JSON onto editor layer
            if (window.AsproAnnotationEngine && CONFIG.savedAnnotations) {
                AsproAnnotationEngine.renderSavedAnnotations(
                    editorLayer,
                    CONFIG.savedAnnotations,
                    'leader',
                    setupLeaderElement,
                    CONFIG.timestamps,
                    CONFIG.names
                );
            }
        }
        renderPDF();

        function saveState() {
            history.push(editorLayer.innerHTML);
            redoStack = [];
        }

        // --- Fungsi untuk membuat div yang bisa digeser (V, NG, X) ---
        function createDraggableDiv(text, color = 'black') {
            const div = document.createElement('div');
            div.textContent = text;
            let bgColor = 'rgba(255,255,255,0.5)'; // Default background
            if (text === 'V') {
                bgColor = 'rgba(0,255,0,0.3)'; // Hijau muda untuk V
            } else if (text === 'X' || text === 'NG') {
                bgColor = 'rgba(255,0,0,0.3)'; // Merah muda untuk X dan NG
            }

            Object.assign(div.style, {
                position: 'absolute',
                top: '50px',
                left: '50px',
                cursor: 'move',
                color,
                background: bgColor,
                padding: '2px 5px',
                fontSize: (text === 'V' || text === 'X' || text === 'NG') ? '24px' : '14px',
                userSelect: 'none',
                border: '1px solid transparent'
            });
            div.setAttribute('draggable', true);

            div.addEventListener('click', e => {
                e.stopPropagation();
                if (selectedObject) selectedObject.classList.remove('selected');
                selectedObject = div;
                div.classList.add('selected');
                div.style.border = '2px dashed red';
                document.getElementById('delete-btn').disabled = false;
            });

            div.addEventListener('dragstart', e => {
                div.startX = e.clientX - div.offsetLeft;
                div.startY = e.clientY - div.offsetTop;
            });

            div.addEventListener('dragend', e => {
                let x = e.clientX - div.startX;
                let y = e.clientY - div.startY;
                const maxX = editorLayer.clientWidth - div.offsetWidth;
                const maxY = editorLayer.clientHeight - div.offsetHeight;
                x = Math.max(0, Math.min(x, maxX));
                y = Math.max(0, Math.min(y, maxY));
                div.style.left = x + 'px';
                div.style.top = y + 'px';
                saveState();
            });

            return div;
        }

        // --- Fungsi untuk membuat div teks yang bisa diedit (Comment) ---
        function createEditableTextDiv(initialText = '', color = 'black') {
            const div = document.createElement('div');
            div.contentEditable = true;
            div.textContent = initialText || '';
            Object.assign(div.style, {
                position: 'absolute',
                top: '50px',
                left: '50px',
                cursor: 'move',
                color: 'white', // White text
                backgroundColor: '#8B4513', // Brown background
                whiteSpace: 'pre-wrap', // Allow newlines
                padding: '5px',
                fontSize: '14px',
                border: 'none',
                minWidth: '100px',
                minHeight: '20px',
                outline: 'none'
            });
            div.setAttribute('draggable', true);

            div.addEventListener('input', () => {
                saveState();
            });

            // Disable dragging when editing to allow Enter key
            div.addEventListener('focus', () => {
                div.removeAttribute('draggable');
            });

            div.addEventListener('blur', () => {
                div.setAttribute('draggable', 'true');
            });

            // Explicit Enter key handler for newlines
            div.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Insert line break manually
                    document.execCommand('insertLineBreak');
                }
            });

            div.addEventListener('click', e => {
                e.stopPropagation();
                if (selectedObject) selectedObject.classList.remove('selected');
                selectedObject = div;
                div.classList.add('selected');
                div.style.border = '2px dashed red'; // Border saat dipilih
                document.getElementById('delete-btn').disabled = false;
            });

            div.addEventListener('dragstart', e => {
                div.startX = e.clientX - div.offsetLeft;
                div.startY = e.clientY - div.offsetTop;
            });

            div.addEventListener('dragend', e => {
                let x = e.clientX - div.startX;
                let y = e.clientY - div.startY;
                const maxX = editorLayer.clientWidth - div.offsetWidth;
                const maxY = editorLayer.clientHeight - div.offsetHeight;
                x = Math.max(0, Math.min(x, maxX));
                y = Math.max(0, Math.min(y, maxY));
                div.style.left = x + 'px';
                div.style.top = y + 'px';
                saveState();
            });

            return div;
        }

        // --- Fungsi Toggle Checklist ---
        function toggleChecklist(mode) {
            // Reset semua tombol ke keadaan tidak aktif
            checklistBtn.classList.remove('btn-success');
            checklistBtn.classList.add('btn-primary');
            ngBtn.classList.remove('btn-success');
            ngBtn.classList.add('btn-primary');
            xBtn.classList.remove('btn-success');
            xBtn.classList.add('btn-primary');
            commentBtn.classList.remove('btn-success');
            commentBtn.classList.add('btn-primary');
            checklistBtnIcon.textContent = 'edit_off';
            ngBtnIcon.textContent = 'block';
            xBtnIcon.textContent = 'close';
            commentBtnIcon.textContent = 'text_fields';

            // Jika mode yang diklik sama dengan mode aktif saat ini, matikan semua mode
            if (currentMode === mode) {
                checklistMode = false;
                currentMode = null;
            } else {
                // Aktifkan mode yang diklik
                checklistMode = true;
                currentMode = mode;

                // Perbarui tampilan tombol yang aktif
                if (mode === 'check') {
                    checklistBtn.classList.add('btn-success');
                    checklistBtn.classList.remove('btn-primary');
                    checklistBtnIcon.textContent = 'edit';
                } else if (mode === 'ng') {
                    ngBtn.classList.add('btn-success');
                    ngBtn.classList.remove('btn-primary');
                    ngBtnIcon.textContent = 'edit';
                } else if (mode === 'x') {
                    xBtn.classList.add('btn-success');
                    xBtn.classList.remove('btn-primary');
                    xBtnIcon.textContent = 'edit';
                } else if (mode === 'comment') {
                    commentBtn.classList.add('btn-success');
                    commentBtn.classList.remove('btn-primary');
                    commentBtnIcon.textContent = 'edit';
                }
            }
        }

        // --- Event Listener Klik pada Editor Layer ---
        editorLayer.addEventListener('click', function (e) {
            if (!checklistMode) {
                if (selectedObject) {
                    selectedObject.classList.remove('selected');
                    // Kembalikan border default
                    if (selectedObject.tagName.toLowerCase() === 'div' && selectedObject.contentEditable !==
                        'true') {
                        selectedObject.style.border = '1px solid transparent';
                    } else if (selectedObject.tagName.toLowerCase() === 'div' && selectedObject.contentEditable ===
                        'true') {
                        selectedObject.style.border = '2px dashed #fff'; // White selection for visibility on pink
                    }
                    selectedObject = null;
                    document.getElementById('delete-btn').disabled = true;
                }
                return;
            }

            const rect = editorLayer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            let newElement;
            if (currentMode === 'check') {
                newElement = createDraggableDiv('V', 'red');
            } else if (currentMode === 'ng') {
                newElement = createDraggableDiv('NG', 'red');
            } else if (currentMode === 'x') {
                newElement = createDraggableDiv('X', 'red');
            } else if (currentMode === 'comment') {
                newElement = createEditableTextDiv('', 'black');
            }

            if (newElement) {
                newElement.setAttribute('data-role', CONFIG.currentRole || 'leader');
                editorLayer.appendChild(newElement);

                const w = newElement.offsetWidth / 2;
                const h = newElement.offsetHeight / 2;
                newElement.style.left = Math.max(0, x - w) + 'px';
                newElement.style.top = Math.max(0, y - h) + 'px';

                saveState();
            }
        });

        // --- Fungsi Delete ---
        function deleteSelected() {
            if (selectedObject) {
                editorLayer.removeChild(selectedObject);
                selectedObject = null;
                document.getElementById('delete-btn').disabled = true;
                saveState();
            }
        }

        // --- Fungsi Undo ---
        function undo() {
            if (history.length > 0) {
                redoStack.push(history.pop());
                editorLayer.innerHTML = history[history.length - 1] || '';
                rebindEvents();
                selectedObject = null;
                document.getElementById('delete-btn').disabled = true;
            }
        }

        // --- Fungsi Redo ---
        function redo() {
            if (redoStack.length > 0) {
                let state = redoStack.pop();
                history.push(state);
                editorLayer.innerHTML = state;
                rebindEvents();
            }
        }

        // --- Fungsi Rebind Events (penting untuk undo/redo) ---
        function rebindEvents() {
            editorLayer.querySelectorAll('div').forEach(div => {
                // Hapus event listener lama
                div.onclick = null;
                div.ondragstart = null;
                div.ondragend = null;
                div.oninput = null; // Untuk komentar

                // Tambahkan kembali event listener berdasarkan tipe div
                if (div.contentEditable === 'true') {
                    // Untuk div komentar
                    div.addEventListener('click', e => {
                        e.stopPropagation();
                        if (selectedObject) selectedObject.classList.remove('selected');
                        selectedObject = div;
                        div.classList.add('selected');
                        div.style.border = '2px dashed red';
                        document.getElementById('delete-btn').disabled = false;
                    });
                    div.addEventListener('dragstart', e => {
                        div.startX = e.clientX - div.offsetLeft;
                        div.startY = e.clientY - div.offsetTop;
                    });
                    div.addEventListener('dragend', e => {
                        let x = e.clientX - div.startX;
                        let y = e.clientY - div.startY;
                        const maxX = editorLayer.clientWidth - div.offsetWidth;
                        const maxY = editorLayer.clientHeight - div.offsetHeight;
                        x = Math.max(0, Math.min(x, maxX));
                        y = Math.max(0, Math.min(y, maxY));
                        div.style.left = x + 'px';
                        div.style.top = y + 'px';
                        saveState();
                    });
                    div.addEventListener('input', () => {
                        saveState();
                    });
                } else {
                    // Untuk div V, NG, dan X
                    div.addEventListener('click', e => {
                        e.stopPropagation();
                        if (selectedObject) selectedObject.classList.remove('selected');
                        selectedObject = div;
                        div.classList.add('selected');
                        div.style.border = '2px dashed red';
                        document.getElementById('delete-btn').disabled = false;
                    });
                    div.addEventListener('dragstart', e => {
                        div.startX = e.clientX - div.offsetLeft;
                        div.startY = e.clientY - div.offsetTop;
                    });
                    div.addEventListener('dragend', e => {
                        let x = e.clientX - div.startX;
                        let y = e.clientY - div.startY;
                        const maxX = editorLayer.clientWidth - div.offsetWidth;
                        const maxY = editorLayer.clientHeight - div.offsetHeight;
                        x = Math.max(0, Math.min(x, maxX));
                        y = Math.max(0, Math.min(y, maxY));
                        div.style.left = x + 'px';
                        div.style.top = y + 'px';
                        saveState();
                    });
                }
            });
        }

        function setupLeaderElement(div) {
            if (div.contentEditable === 'true') {
                div.addEventListener('input', () => saveState());
                div.addEventListener('focus', () => div.removeAttribute('draggable'));
                div.addEventListener('blur', () => div.setAttribute('draggable', 'true'));
                div.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        e.stopPropagation();
                        document.execCommand('insertLineBreak');
                    }
                });
            }
            div.addEventListener('click', e => {
                e.stopPropagation();
                if (selectedObject) selectedObject.classList.remove('selected');
                selectedObject = div;
                div.classList.add('selected');
                div.style.border = '2px dashed red';
                document.getElementById('delete-btn').disabled = false;
            });
            div.addEventListener('dragstart', e => {
                div.startX = e.clientX - div.offsetLeft;
                div.startY = e.clientY - div.offsetTop;
            });
            div.addEventListener('dragend', e => {
                let x = e.clientX - div.startX;
                let y = e.clientY - div.startY;
                const maxX = editorLayer.clientWidth - div.offsetWidth;
                const maxY = editorLayer.clientHeight - div.offsetHeight;
                x = Math.max(0, Math.min(x, maxX));
                y = Math.max(0, Math.min(y, maxY));
                div.style.left = x + 'px';
                div.style.top = y + 'px';
                saveState();
            });
        }

        // --- Fungsi Download PDF ---
        async function downloadPdf() {
            try {
                const res = await fetch(`{{ route('item.annotations.get', ['type' => 'training', 'id' => $listReport->Id_List_Training]) }}`);
                const data = await res.json();
                const masterUrl = data.master_pdf_url || CONFIG.pdfUrl;

                await AsproAnnotationEngine.downloadAnnotatedPdf({
                    masterPdfUrl: masterUrl,
                    downloadFilename: '{{ $listReport->training->member->Name_Member }}-{{ $listReport->display_name }}.pdf',
                    canvasWidth: pdfCanvas.width,
                    pageViewportHeights: pageViewportHeights,
                    annotations: data.annotations || CONFIG.savedAnnotations,
                    timestamps: data.timestamps || CONFIG.timestamps,
                    names: data.names || CONFIG.names
                });
            } catch (err) {
                console.error('Download error:', err);
                window.open(CONFIG.pdfUrl, '_blank');
            }
        }

        // --- Fungsi Submit Report ---
        async function submitReport() {
            const currentAnnotations = AsproAnnotationEngine.serializeLayer(editorLayer, CONFIG.currentRole || 'leader');

            const nowUTC = new Date();
            const offsetWIB = 7 * 60;
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const now = localWIB.toISOString().slice(0, 19).replace('T', ' ');

            openQrApprovalScanner(async function(scannedQrs) {
                if (images && images.length > 0) {
                    let uploadErrors = 0;
                    for (let file of images) {
                        const photoForm = new FormData();
                        photoForm.append('role', 'leader');
                        photoForm.append('photo', file, file.name);
                        try {
                            const photoRes = await fetch(`{{ route('item.photo.upload', ['type' => 'training', 'id' => $listReport->Id_List_Training]) }}`, {
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

                fetch(`{{ route('training.detail.submit', ['Id_List_Training' => $listReport->Id_List_Training]) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                }).then(res => {
                    if (res.ok) {
                        alert('Training submitted successfully!');
                        location.reload();
                    } else {
                        alert('Failed to submit training');
                    }
                }).catch(err => {
                    alert('Error: ' + err.message);
                });
            });
        }
    </script>
@endsection
