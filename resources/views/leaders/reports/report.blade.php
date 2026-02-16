@extends('layouts.leader')
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
                    <span style="padding-left: 50px; padding-right: 50px;"><b> </b> Back</span>
                </a>
                <br><br>

                <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h4>
                <br>

                {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button> --}}

                @if (is_null($listReport->Time_Approved_Leader))
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

                @if ($listReport->Time_Approved_Leader)
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
                @if (is_null($listReport->Time_Approved_Leader))
                    {{-- <h5>Photos for : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h5>
                    <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Photos</label>
                        <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*"
                            capture="environment">
                    </div>
                    <div id="preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>
                    <br> --}}
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
        const pdfUrl = "{{ asset($pdfPath) }}?t=" + new Date().getTime();
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

        // --- Fungsi Download PDF ---
        async function downloadPdf() {
            const existingPdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdfBytes);
            const pdfBytes = await pdfDoc.save();
            const blob = new Blob([pdfBytes], {
                type: 'application/pdf'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = '{{ $listReport->report->member->Name_Member }}-{{ $listReport->Name_Procedure }}.pdf';
            link.click();
        }

        // --- Fungsi Submit Report ---
        async function submitReport() {
            const existingPdf = await fetch(pdfUrl).then(r => r.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdf);
            const pages = pdfDoc.getPages();
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
            const fontSize = 8;
            const nowUTC = new Date();
            const offsetWIB = 7 * 60; // WIB = UTC+7 dalam menit
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const now = localWIB.toISOString().slice(0, 19).replace('T', ' ');
            const lines = [now, "{{ $user->Name_User }}"];
            let yStart = pages[0].getHeight() - 10;
            const lineHeight = fontSize + 2;

            lines.forEach((line, idx) => {
                const width = font.widthOfTextAtSize(line, fontSize);
                pages[0].drawText(line, {
                    x: (pages[0].getWidth() - width) / 2,
                    y: yStart - idx * lineHeight,
                    size: fontSize,
                    font,
                    color: PDFLib.rgb(1, 0, 0)
                });
            });

            // Hitung offset halaman berdasarkan tinggi viewport per halaman
            const canvasW = pdfCanvas.width;
            let yOffsets = [0];
            for (let i = 0; i < pageViewportHeights.length - 1; i++) {
                yOffsets.push(yOffsets[i] + pageViewportHeights[i]);
            }

            editorLayer.querySelectorAll('div').forEach(div => {
                let x = parseFloat(div.style.left);
                let y = parseFloat(div.style.top);

                let pageIndex = yOffsets.findIndex((offset, i) => y < offset + pageViewportHeights[i]);
                if (pageIndex === -1) pageIndex = pages.length - 1;

                const page = pages[pageIndex];
                const pageHeight = page.getHeight();
                const pageWidth = page.getWidth();

                const offsetY = y - yOffsets[pageIndex];
                const scaleX = pageWidth / canvasW;
                const scaleY = pageHeight / pageViewportHeights[pageIndex];

                const finalX = x * scaleX;
                const finalY = pageHeight - (offsetY * scaleY) - 18;

                // --- Periksa apakah elemen ini adalah komentar (menggunakan contentEditable) ---
                if (div.contentEditable === 'true') {
                    // --- Ini adalah elemen komentar ---
                    const text = div.textContent;
                    // Hindari menyimpan placeholder teks jika kosong
                    if (text && text.trim() !== '' && text.trim() !== 'Tulis komentar...') {
                        const fontSizeComment = 12; // Ukuran teks komentar di PDF
                        const lineHeight = fontSizeComment + 4;

                        // Split text by newlines (handle contentEditable behavior)
                        // Sanitize: Remove zero-width chars and ensure WinAnsi compatibility
                        const rawText = div.innerText.replace(/[\u200B-\u200D\uFEFF]/g, '');
                        // Split and map to ensure we check characters
                        const lines = rawText.split(/\r?\n/).map(l => l.replace(/[^\x00-\xFF]/g, '')); // Basic Latin-1 filter

                        // Calculate max width
                        let maxLineWidth = 0;
                        lines.forEach(line => {
                            try {
                                const width = font.widthOfTextAtSize(line, fontSizeComment);
                                if (width > maxLineWidth) maxLineWidth = width;
                            } catch (e) {
                                console.warn('Skipping unsupported character line:', line);
                            }
                        });


                        const paddingX = 6;
                        const paddingY = 6;
                        const outerWidth = maxLineWidth + (2 * paddingX);
                        const outerHeight = (lines.length * lineHeight) + (2 * paddingY);

                        // Adjust Y position (similarly to previous fixes)
                        // finalY comes from: pageHeight - (offsetY * scaleY) - 18;
                        // Assuming finalY is the visual TOP of the element in PDF coords relative to bottom-left origin?
                        // If y (HTML) is top-left, increasing y means going down.
                        // Increasing HTML y increases offsetY, which decreases finalY.
                        // So finalY is indeed the top Y coordinate in PDF space (higher value).
                        // To draw a box extending DOWN from finalY, we need to start at finalY - outerHeight.

                        const outerX = finalX - paddingX;
                        const rectBottomY = finalY - outerHeight;

                        // --- 1. Gambar background cokelat ---
                        page.drawRectangle({
                            x: outerX,
                            y: rectBottomY,
                            width: outerWidth,
                            height: outerHeight,
                            color: PDFLib.rgb(0.545, 0.271, 0.075), // #8B4513 (Brown)
                            opacity: 1
                        });

                        // --- 2. Gambar teks putih ---
                        lines.forEach((line, index) => {
                            // finalY is top, go down by padding and lines
                            // PDF Y increases upwards, so we subtract from finalY
                            const textY = finalY - paddingY - (index + 1) * lineHeight + 4; // +4 baseline adjust

                            page.drawText(line, {
                                x: finalX, // Align left
                                y: textY,
                                size: fontSizeComment,
                                color: PDFLib.rgb(1, 1, 1), // Putih
                                font
                            });
                        });
                    }
                } else {
                    // --- Ini adalah elemen V, X, atau NG ---
                    // Gunakan logika lama seperti sebelumnya
                    const textContent = div.textContent;
                    const size = (textContent === 'V' || textContent === 'X' || textContent === 'NG') ? 18 : 12;

                    // Hitung panjang teks (kira-kira, tidak super presisi)
                    const textWidth = 20 * textContent.length * 0.6;
                    const textHeight = 18; // tinggi line kira-kira

                    // Gambar kotak background putih 50%
                    page.drawRectangle({
                        x: finalX - 2,
                        y: finalY - 2,
                        width: textWidth + 4,
                        height: textHeight + 4,
                        color: PDFLib.rgb(1, 1, 1),
                        opacity: 0.5
                    });

                    // Warna teks disesuaikan berdasarkan warna yang ditetapkan di style elemen HTML
                    let textColor = PDFLib.rgb(0, 0, 0); // Default hitam
                    if (div.style.color === 'red') {
                        textColor = PDFLib.rgb(1, 0, 0); // Merah
                    } else if (div.style.color === 'green') {
                        textColor = PDFLib.rgb(0, 1, 0); // Hijau
                    }

                    page.drawText(textContent, {
                        x: finalX,
                        y: finalY,
                        size: size,
                        color: textColor,
                        font
                    });
                }
            });

            const pdfBytes = await pdfDoc.save();
            const formData = new FormData();
            formData.append('pdf', new Blob([pdfBytes], {
                type: 'application/pdf'
            }));
            formData.append('timestamp', now);

            fetch(`{{ route('report.detail.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
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
    </script>
@endsection