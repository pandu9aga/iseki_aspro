@extends('layouts.user')
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
            <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h4>
            <br>

            <!-- Tombol Back -->
            <button class="btn btn-primary mx-3" onclick="window.history.back()">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </button>
            <br><br>

            {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button>
            <button class="btn btn-sm btn-primary mt-3" onclick="downloadPdf()">Download Edited PDF</button> --}}
            
            @if (is_null($listReport->Time_List_Report))
                <button class="btn btn-primary mt-3" id="checklist-btn" onclick="toggleChecklist()">
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
            @endif

            @if ($listReport->Time_List_Report)
                <h5>Time Report : <span class="text-primary">{{ $listReport->Time_List_Report }}</span></h5>
                <h5>Time Approvement : <span class="text-primary">{{ $listReport->Time_Approvement }}</span></h5>
                <br>
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
                <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*" capture="environment">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
<script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
<script>
    let pdfUrl = "{{ asset($pdfPath) }}";
    let pdfCanvas = document.getElementById('pdf-canvas');
    let editorLayer = document.getElementById('editor-layer');
    let checklistMode = false;
    let selectedObject = null;
    let history = [];
    let redoStack = [];
    let pdfScale = 1.5;

    async function renderPDF() {
        const pdf = await pdfjsLib.getDocument(pdfUrl).promise;

        const pageViewports = [];
        let totalHeight = 0;
        let maxWidth = 0;

        // Hitung ukuran total
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);
            const viewport = page.getViewport({ scale: pdfScale });
            pageViewports.push({ page, viewport });
            totalHeight += viewport.height;
            maxWidth = Math.max(maxWidth, viewport.width);
        }

        pdfCanvas.width = maxWidth;
        pdfCanvas.height = totalHeight;
        editorLayer.style.width = maxWidth + 'px';
        editorLayer.style.height = totalHeight + 'px';

        const ctx = pdfCanvas.getContext('2d');

        let currentY = 0;
        for (const { page, viewport } of pageViewports) {
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

    function rebindEvents() {
        editorLayer.querySelectorAll('div').forEach(div => {
            div.addEventListener('click', function(e) {
                e.stopPropagation();
                if (selectedObject) selectedObject.classList.remove('selected');
                selectedObject = div;
                selectedObject.classList.add('selected');
                document.getElementById('delete-btn').disabled = false;
            });
        });
    }

    function addText() {
        let text = prompt("Enter text:");
        if (!text) return;
        let div = createDraggableDiv(text, 'black');
        editorLayer.appendChild(div);
        saveState();
        rebindEvents();
    }

    function createDraggableDiv(content, color = 'black') {
        let div = document.createElement('div');
        div.textContent = content;
        div.style.position = 'absolute';
        div.style.top = '50px';
        div.style.left = '50px';
        div.style.cursor = 'move';
        div.style.color = color;
        div.style.background = 'rgba(255,255,255,0.5)';
        div.style.padding = '2px 5px';
        div.setAttribute('draggable', true);

        div.addEventListener('click', function(e) {
            e.stopPropagation();
            if (selectedObject) selectedObject.classList.remove('selected');
            selectedObject = div;
            selectedObject.classList.add('selected');
            document.getElementById('delete-btn').disabled = false;
        });

        div.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', '');
            div.startX = e.clientX - div.offsetLeft;
            div.startY = e.clientY - div.offsetTop;
        });

        div.addEventListener('dragend', e => {
            let newX = e.clientX - div.startX;
            let newY = e.clientY - div.startY;
            const maxX = editorLayer.clientWidth - div.offsetWidth;
            const maxY = editorLayer.clientHeight - div.offsetHeight;
            newX = Math.max(0, Math.min(newX, maxX));
            newY = Math.max(0, Math.min(newY, maxY));
            div.style.left = newX + 'px';
            div.style.top = newY + 'px';
            saveState();
        });

        return div;
    }

    function toggleChecklist() {
        checklistMode = !checklistMode;
        const btn = document.getElementById('checklist-btn');
        const icon = document.getElementById('checklist-btn-icon');
        if (checklistMode) {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            icon.textContent = 'edit';
        } else {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            icon.textContent = 'edit_off';
        }
    }

    editorLayer.addEventListener('click', function(e) {
        if (!checklistMode) {
            if (selectedObject) {
                selectedObject.classList.remove('selected');
                selectedObject = null;
                document.getElementById('delete-btn').disabled = true;
            }
            return;
        }

        const rect = editorLayer.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;

        let div = createDraggableDiv('V', 'green');
        div.style.fontSize = '16px';

        // Tempel dulu ke DOM biar bisa ukur ukuran div
        editorLayer.appendChild(div);

        // Ambil ukuran div
        const divWidth = div.offsetWidth;
        const divHeight = div.offsetHeight;

        // Hitung posisi biar tengah
        let newX = x - divWidth / 2;
        let newY = y - divHeight / 2;

        // Batas canvas
        const maxX = editorLayer.clientWidth - divWidth;
        const maxY = editorLayer.clientHeight - divHeight;
        newX = Math.max(0, Math.min(newX, maxX));
        newY = Math.max(0, Math.min(newY, maxY));

        div.style.left = `${newX}px`;
        div.style.top = `${newY}px`;

        saveState();
        rebindEvents();
    });

    function deleteSelected() {
        if (selectedObject) {
            editorLayer.removeChild(selectedObject);
            selectedObject = null;
            document.getElementById('delete-btn').disabled = true;
            saveState();
        }
    }

    function undo() {
        if (history.length > 0) {
            redoStack.push(history.pop());
            editorLayer.innerHTML = history[history.length - 1] || '';
            rebindEvents();
            selectedObject = null;
            document.getElementById('delete-btn').disabled = true;
        }
    }

    function redo() {
        if (redoStack.length > 0) {
            let state = redoStack.pop();
            history.push(state);
            editorLayer.innerHTML = state;
            rebindEvents();
        }
    }
    
    async function downloadPdf() {
        const existingPdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
        const pdfDoc = await PDFLib.PDFDocument.load(existingPdfBytes);
        const page = pdfDoc.getPages()[0];

        const pageWidth = page.getWidth();
        const pageHeight = page.getHeight();
        const canvasWidth = pdfCanvas.width;
        const canvasHeight = pdfCanvas.height;

        editorLayer.querySelectorAll('div').forEach(div => {
            let x = parseFloat(div.style.left);
            let y = parseFloat(div.style.top);
            let scaledX = x * (pageWidth / canvasWidth);
            let scaledY = y * (pageHeight / canvasHeight);
            let finalY = pageHeight - scaledY - 12;

            page.drawText(div.textContent, {
                x: scaledX,
                y: finalY,
                size: 12,
                color: div.style.color === 'green' ? PDFLib.rgb(0, 0.6, 0) : PDFLib.rgb(0, 0, 0)
            });
        });

        const pdfBytes = await pdfDoc.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'Edited-{{ $listReport->Name_Procedure }}.pdf';
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
        const PAGE_WIDTH = 841.89;  // A4 landscape
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
        const page = pdfDoc.getPages()[0];

        // Tambahkan tanggal dan nama user di pojok atas kiri
        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        page.drawText(`${now} - {{ $user->Name_User }}`, {
            x: 30,
            y: page.getHeight() - 20,
            size: 12,
            color: PDFLib.rgb(0, 0.6, 0)
        });

        // Tambahkan text/checklist di editor layer
        const pageWidth = page.getWidth();
        const pageHeight = page.getHeight();
        const canvasWidth = pdfCanvas.width;
        const canvasHeight = pdfCanvas.height;

        editorLayer.querySelectorAll('div').forEach(div => {
            let x = parseFloat(div.style.left);
            let y = parseFloat(div.style.top);
            let scaledX = x * (pageWidth / canvasWidth);
            let scaledY = y * (pageHeight / canvasHeight);
            let finalY = pageHeight - scaledY - 12;

            // Hitung panjang teks (kira-kira, tidak super presisi)
            const textWidth = 12 * div.textContent.length * 0.6; 
            const textHeight = 14; // tinggi line kira-kira

            // Gambar kotak background putih 50%
            page.drawRectangle({
                x: scaledX - 2,
                y: finalY - 2,
                width: textWidth + 4,
                height: textHeight + 4,
                color: PDFLib.rgb(1, 1, 1),
                opacity: 0.5
            });

            // Lalu gambar teksnya
            page.drawText(div.textContent, {
                x: scaledX,
                y: finalY,
                size: 12,
                color: div.style.color === 'green' ? PDFLib.rgb(0, 0.6, 0) : PDFLib.rgb(0, 0, 0)
            });
        });

        // Embed gambar jadi PDF baru
        const imgPdfDoc = await PDFLib.PDFDocument.create();
        const PAGE_WIDTH = 841.89;
        const PAGE_HEIGHT = 595.28;
        const SLOT_COLS = 2, SLOT_ROWS = 2;
        const SLOT_W = PAGE_WIDTH / SLOT_COLS;
        const SLOT_H = PAGE_HEIGHT / SLOT_ROWS;

        let imgPage = null, slotIndex = 0;
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

            const { width, height } = imgEmbed.size();
            const scale = Math.min(SLOT_W / width, SLOT_H / height);
            const col = slotIndex % 2;
            const row = Math.floor((slotIndex % 4) / 2);
            const x = col * SLOT_W + (SLOT_W - width * scale) / 2;
            const y = PAGE_HEIGHT - ((row + 1) * SLOT_H) + (SLOT_H - height * scale) / 2;

            imgPage.drawImage(imgEmbed, { x, y, width: width * scale, height: height * scale });
            slotIndex++;
        }

        // Gabung PDF canvas + PDF gambar
        const [imgPdfBytes] = await imgPdfDoc.save().then(b => [b]);
        const imgDoc = await PDFLib.PDFDocument.load(imgPdfBytes);
        const copiedPages = await pdfDoc.copyPages(imgDoc, imgDoc.getPageIndices());
        copiedPages.forEach(p => pdfDoc.addPage(p));

        const mergedBytes = await pdfDoc.save();

        // Kirim ke server
        const formData = new FormData();
        formData.append('pdf', new Blob([mergedBytes], { type: 'application/pdf' }));
        formData.append('timestamp', now);

        fetch(`{{ route('report_list_user.pdf.editor.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
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