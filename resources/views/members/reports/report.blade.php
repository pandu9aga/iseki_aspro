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
                <a class="btn btn-primary mx-3"
                    href="{{ route('report_list_member', ['Id_Report' => $listReport->Id_Report]) }}">
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
                    <div><b>Leader Approvement : <span
                                class="text-primary">{{ $listReport->Time_Approved_Leader }}</span></b></div>
                    <div><b>Auditor Approvement : <span
                                class="text-primary">{{ $listReport->Time_Approved_Auditor }}</span></b></div>
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
                        {{-- <input type="file" class="form-control image-input" name="{{ $part->photo_angle->Id_Photo_Angle }}" data-preview="#preview-{{ $part->photo_angle->Id_Photo_Angle }}" accept="image/*" capture="environment"> --}}
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
        let pdfUrl = "{{ asset($pdfPath) }}?t=" + new Date().getTime();
        let pdfCanvas = document.getElementById('pdf-canvas');
        let editorLayer = document.getElementById('editor-layer');
        let checklistMode = false;
        let currentMode = null; // 'check', 'ng', 'x', 'comment'
        let selectedObject = null;
        let history = [];
        let redoStack = [];
        let pdfScale = 1.5;

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

            const pageViewports = [];
            let totalHeight = 0;
            let maxWidth = 0;

            // Hitung ukuran total
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const viewport = page.getViewport({
                    scale: pdfScale
                });
                pageViewports.push({
                    page,
                    viewport
                });
                totalHeight += viewport.height;
                maxWidth = Math.max(maxWidth, viewport.width);
            }

            pdfCanvas.width = maxWidth;
            pdfCanvas.height = totalHeight;
            editorLayer.style.width = maxWidth + 'px';
            editorLayer.style.height = totalHeight + 'px';

            const ctx = pdfCanvas.getContext('2d');

            let currentY = 0;
            for (const {
                    page,
                    viewport
                }
                of pageViewports) {
                // Render ke kanvas kecil
                const tmpCanvas = document.createElement('canvas');
                tmpCanvas.width = viewport.width;
                tmpCanvas.height = viewport.height;

                const tmpCtx = tmpCanvas.getContext('2d');
                await page.render({
                    canvasContext: tmpCtx,
                    viewport: viewport
                }).promise;

                // Gambar kanvas kecil ke kanvas utama
                ctx.drawImage(tmpCanvas, 0, currentY);
                currentY += viewport.height;
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
                color: 'black', // Teks hitam
                background: 'white', // Background putih
                padding: '5px',
                fontSize: '14px',
                border: '2px solid #bd0237', // Outer box warna pink gelap
                minWidth: '100px',
                minHeight: '20px',
                outline: 'none'
            });
            div.setAttribute('draggable', true);

            div.addEventListener('input', () => {
                saveState();
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

        // --- Event Listener Klik pada Editor Layer ---
        editorLayer.addEventListener('click', function(e) {
            if (!checklistMode) {
                if (selectedObject) {
                    selectedObject.classList.remove('selected');
                    // Kembalikan border default
                    if (selectedObject.tagName.toLowerCase() === 'div' && selectedObject.contentEditable !==
                        'true') {
                        selectedObject.style.border = '1px solid transparent';
                    } else if (selectedObject.tagName.toLowerCase() === 'div' && selectedObject.contentEditable ===
                        'true') {
                        selectedObject.style.border = '2px solid #bd0237'; // Border default untuk komentar
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
                newElement = createDraggableDiv('V', 'green');
            } else if (currentMode === 'ng') {
                newElement = createDraggableDiv('NG', 'green');
            } else if (currentMode === 'x') {
                newElement = createDraggableDiv('X', 'green');
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
    </script>
    <script>
        let images = [];

        // Saat user pilih gambar
        document.getElementById('imageInput').addEventListener('change', function(e) {
            for (let file of e.target.files) {
                images.push(file);
                showPreview(file);
            }
        });

        // Tampilkan preview dengan tombol hapus
        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
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

                delBtn.onclick = function() {
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

        // Generate PDF
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

            for (let file of images) {
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
                        // fallback aman
                        try {
                            imgEmbed = await pdfDoc.embedJpg(imgBytes);
                        } catch (e) {
                            imgEmbed = await pdfDoc.embedPng(imgBytes);
                        }
                    }
                } catch (err) {
                    alert(`Gagal embed gambar: ${file.name}`);
                    continue;
                }

                const {
                    width,
                    height
                } = imgEmbed.size();
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
            const blob = new Blob([pdfBytes], {
                type: 'application/pdf'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'combined.pdf';
            link.click();
        }
    </script>
    <script>
        async function resizeImage(file, maxWidth, maxHeight) {
            return new Promise(resolve => {
                const img = new Image();
                img.onload = function() {
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

        async function submitReport() {
            const existingPdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(existingPdfBytes);
            const page = pdfDoc.getPages()[0]; // Asumsikan hanya halaman pertama yang diedit

            // Tambahkan tanggal dan nama user di pojok atas kiri
            const nowUTC = new Date();
            const offsetWIB = 7 * 60; // WIB = UTC+7 dalam menit
            const localWIB = new Date(nowUTC.getTime() + offsetWIB * 60 * 1000);
            const now = localWIB.toISOString().slice(0, 19).replace('T', ' ');
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
            const fontSize = 8;

            // Pisah teks menjadi array baris
            const lines = [now, "{{ $member->Name_Member }}"];
            const lineHeight = fontSize + 2;

            // Mulai dari Y tertentu
            let startY = page.getHeight() - 10;

            // Tulis setiap baris
            lines.forEach((line, i) => {
                const textWidth = font.widthOfTextAtSize(line, fontSize);
                page.drawText(line, {
                    x: 200, // Sesuaikan posisi X jika perlu
                    y: startY - i * lineHeight,
                    size: fontSize,
                    font: font,
                    color: PDFLib.rgb(0, 0.6, 0), // Warna hijau
                });
            });

            // Tambahkan text/checklist dari editor layer ke PDF
            const pageWidth = page.getWidth();
            const pageHeight = page.getHeight();
            const canvasWidth = pdfCanvas.width;
            const canvasHeight = pdfCanvas.height;

            // Hitung offset halaman berdasarkan tinggi viewport per halaman (untuk multi-halaman jika diperlukan)
            // Karena versi Member ini hanya mengedit halaman pertama, kita asumsikan satu halaman.
            // Jika PDF multi-halaman diedit, logika ini perlu disesuaikan seperti di versi Leader.
            // Untuk saat ini, kita gunakan logika dasar untuk halaman pertama.

            editorLayer.querySelectorAll('div').forEach(div => {
                let x = parseFloat(div.style.left);
                let y = parseFloat(div.style.top);

                // Skala posisi dari canvas ke PDF (hanya halaman pertama)
                let scaledX = x * (pageWidth / canvasWidth);
                let scaledY = y * (pageHeight / canvasHeight);
                // PDF Y=0 adalah di bawah, Canvas Y=0 adalah di atas, jadi balik Y
                let finalY = pageHeight - scaledY - 18; // 18 adalah kira-kira tinggi teks

                // --- Periksa apakah elemen ini adalah komentar (menggunakan contentEditable) ---
                if (div.contentEditable === 'true') {
                    const text = div.textContent;
                    // Hindari menyimpan placeholder teks jika kosong
                    if (text && text.trim() !== '' && text.trim() !== 'Tulis komentar...') {
                        const fontSizeComment = 12; // Ukuran teks komentar di PDF
                        const textWidth = font.widthOfTextAtSize(text, fontSizeComment);
                        const textHeight = fontSizeComment + 4; // Tinggi teks + sedikit padding

                        // --- 1. Gambar border pink gelap (kotak luar) ---
                        const borderWidth = 1; // Ketebalan border
                        const paddingX = 6; // Padding total horizontal (border + padding dalam)
                        const paddingY = 6; // Padding total vertikal
                        const outerWidth = textWidth + (2 * paddingX);
                        const outerHeight = textHeight + (2 * paddingY);
                        const outerX = scaledX - paddingX;
                        const outerY = finalY - textHeight -
                            paddingY; // finalY adalah puncak teks, jadi posisi Y kotak dihitung dari sana

                        page.drawRectangle({
                            x: outerX,
                            y: outerY,
                            width: outerWidth,
                            height: outerHeight,
                            color: PDFLib.rgb(0.741, 0.008, 0.216), // Warna pink gelap: #bd0237
                            thickness: borderWidth,
                            opacity: 1
                        });

                        // --- 2. Gambar background putih (kotak dalam, sedikit lebih kecil dari border) ---
                        // Kurangi ukuran dan posisi agar berada di dalam border
                        const innerPadding = 1; // Jarak antara border dan background putih
                        page.drawRectangle({
                            x: outerX + borderWidth + innerPadding,
                            y: outerY + borderWidth + innerPadding,
                            width: outerWidth - 2 * (borderWidth + innerPadding),
                            height: outerHeight - 2 * (borderWidth + innerPadding),
                            color: PDFLib.rgb(1, 1, 1), // Putih
                            opacity: 1
                        });

                        // --- 3. Gambar teks hitam ---
                        // Tempatkan teks di tengah area dalam kotak putih
                        const textX = outerX + borderWidth + innerPadding + 2; // Sedikit jarak dari kiri
                        const textY = outerY + outerHeight - fontSizeComment -
                            4; // Sesuaikan posisi Y agar teks pas di dalam kotak

                        page.drawText(text, {
                            x: textX,
                            y: textY,
                            size: fontSizeComment,
                            color: PDFLib.rgb(0, 0, 0), // Hitam
                            font
                        });
                    }
                } else {
                    // --- Ini adalah elemen V, X, atau NG ---
                    // Gunakan logika serupa dengan versi Leader untuk V, X, NG
                    const textContent = div.textContent;
                    const size = (textContent === 'V' || textContent === 'X' || textContent === 'NG') ? 18 : 12;

                    // Hitung panjang teks (kira-kira, tidak super presisi)
                    const textWidth = 20 * textContent.length * 0.6;
                    const textHeight = 18; // tinggi line kira-kira

                    // Gambar kotak background putih 50%
                    page.drawRectangle({
                        x: scaledX - 2,
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
                        x: scaledX,
                        y: finalY,
                        size: size,
                        color: textColor,
                        font
                    });
                }
            });

            // Embed gambar jadi PDF baru (ini adalah bagian dari submit gambar)
            const imgPdfDoc = await PDFLib.PDFDocument.create();
            const PAGE_WIDTH = 841.89;
            const PAGE_HEIGHT = 595.28;
            const MARGIN = 20;
            const SLOT_COLS = 2,
                SLOT_ROWS = 2;
            const SLOT_W = (PAGE_WIDTH - MARGIN * 2) / SLOT_COLS;
            const SLOT_H = (PAGE_HEIGHT - MARGIN * 2) / SLOT_ROWS;

            let imgPage = null,
                slotIndex = 0;
            for (let file of images) {
                if (slotIndex % 4 === 0) {
                    imgPage = imgPdfDoc.addPage([PAGE_WIDTH, PAGE_HEIGHT]);
                }
                const resizedBlob = await resizeImage(file, 1000, 1000);
                const imgBytes = await resizedBlob.arrayBuffer();
                // const imgBytes = await file.arrayBuffer();
                let imgEmbed = file.type.includes('png') ?
                    await imgPdfDoc.embedPng(imgBytes) :
                    await imgPdfDoc.embedJpg(imgBytes);

                const {
                    width,
                    height
                } = imgEmbed.size();
                const scale = Math.min(SLOT_W / width, SLOT_H / height);
                const col = slotIndex % 2;
                const row = Math.floor((slotIndex % 4) / 2);
                const x = MARGIN + col * SLOT_W + (SLOT_W - width * scale) / 2;
                const y = PAGE_HEIGHT - MARGIN - ((row + 1) * SLOT_H) + (SLOT_H - height * scale) / 2;

                imgPage.drawImage(imgEmbed, {
                    x,
                    y,
                    width: width * scale,
                    height: height * scale
                });
                slotIndex++;
            }

            // Gabung PDF canvas (dengan V, NG, X, Comment) + PDF gambar
            const [imgPdfBytes] = await imgPdfDoc.save().then(b => [b]);
            const imgDoc = await PDFLib.PDFDocument.load(imgPdfBytes);
            const copiedPages = await pdfDoc.copyPages(imgDoc, imgDoc.getPageIndices());
            copiedPages.forEach(p => pdfDoc.addPage(p));

            const mergedBytes = await pdfDoc.save();

            // Kirim ke server
            const formData = new FormData();
            formData.append('pdf', new Blob([mergedBytes], {
                type: 'application/pdf'
            }));
            formData.append('timestamp', now);

            fetch(`{{ route('report_list_member.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
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
