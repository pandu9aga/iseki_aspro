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
            <a class="btn btn-primary mx-3" href="{{ route('list_report_detail_auditor', ['Id_Report' => $listReport->Id_Report, 'Name_Tractor' => $listReport->Name_Tractor]) }}">
                <span style="padding-left: 50px; padding-right: 50px;"><b><-</b> Back</span>
            </a>
            <br><br>

            <h4 class="pt-2">Procedure : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h4>
            <br>

            {{-- <button class="btn btn-sm btn-secondary mt-3" onclick="addText()">Add Text</button> --}}
            
            @if (is_null($listReport->Time_Approved_Auditor))
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

            @if ($listReport->Time_Approved_Auditor)
                <div><b>Check Member : <span class="text-primary">{{ $listReport->Time_List_Report }}</span></b></div>
                <div><b>Leader Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Leader }}</span></b></div>
                <div><b>Auditor Approvement : <span class="text-primary">{{ $listReport->Time_Approved_Auditor }}</span></b></div>
                <br>

                <button class="btn btn-sm btn-primary mt-3" onclick="downloadPdf()">Download PDF</button>
            @endif

            <div id="pdf-container" style="border:1px solid #ccc; height:600px; overflow:auto; position:relative;">
                <canvas id="pdf-canvas"></canvas>
                <div id="editor-layer" style="position:absolute; top:0; left:0;"></div>
            </div>

            <br><br>
            @if (is_null($listReport->Time_Approved_Auditor))
            {{-- <h5>Photos for : <span class="text-primary">{{ $listReport->Name_Procedure }}</span></h5>
            <div class="input-group input-group-outline my-3 is-filled">
                <label class="form-label">Photos</label>
                <input type="file" class="form-control image-input" id="imageInput" multiple accept="image/*" capture="environment">
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
<script src="{{asset('assets/js/pdf.min.js')}}"></script>
<script src="{{asset('assets/js/pdf-lib.min.js')}}"></script>
<script>
const pdfUrl = "{{ asset($pdfPath) }}?t=" + new Date().getTime();
const pdfCanvas = document.getElementById('pdf-canvas');
const editorLayer = document.getElementById('editor-layer');
const pdfScale = 1.5;
let checklistMode = false;
let selectedObject = null;
let history = [], redoStack = [];

let pageViewportHeights = [];

async function renderPDF() {
    const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
    const ctx = pdfCanvas.getContext('2d');

    const viewports = [];
    let totalHeight = 0, maxWidth = 0;

    for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i);
        const vp = page.getViewport({ scale: pdfScale });
        viewports.push({ page, vp });
        totalHeight += vp.height;
        maxWidth = Math.max(maxWidth, vp.width);
        pageViewportHeights.push(vp.height);
    }

    pdfCanvas.width = maxWidth;
    pdfCanvas.height = totalHeight;
    editorLayer.style.width = maxWidth + 'px';
    editorLayer.style.height = totalHeight + 'px';

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
}
renderPDF();

function saveState() {
    history.push(editorLayer.innerHTML);
    redoStack = [];
}

function createDraggableDiv(text, color = 'black') {
    const div = document.createElement('div');
    div.textContent = text;
    Object.assign(div.style, {
        position: 'absolute',
        top: '50px',
        left: '50px',
        cursor: 'move',
        color,
        background: 'rgba(255,255,255,0.5)',
        padding: '2px 5px',
        fontSize: text === 'V' ? '24px' : '14px'
    });
    div.setAttribute('draggable', true);

    div.addEventListener('click', e => {
        e.stopPropagation();
        if (selectedObject) selectedObject.classList.remove('selected');
        selectedObject = div;
        div.classList.add('selected');
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

function toggleChecklist() {
    checklistMode = !checklistMode;
    const btn = document.getElementById('checklist-btn');
    const icon = document.getElementById('checklist-btn-icon');
    btn.classList.toggle('btn-success', checklistMode);
    btn.classList.toggle('btn-primary', !checklistMode);
    icon.textContent = checklistMode ? 'edit' : 'edit_off';
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
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const div = createDraggableDiv('V', 'blue');
    editorLayer.appendChild(div);

    const w = div.offsetWidth / 2, h = div.offsetHeight / 2;
    div.style.left = Math.max(0, x - w) + 'px';
    div.style.top = Math.max(0, y - h) + 'px';

    saveState();
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
    const pdfBytes = await pdfDoc.save();
    const blob = new Blob([pdfBytes], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = '{{ $listReport->report->member->Name_Member }}-{{ $listReport->Name_Procedure }}.pdf';
    link.click();
}

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
            x: 500,
            y: yStart - idx * lineHeight,
            size: fontSize,
            font,
            color: PDFLib.rgb(0, 0, 1)
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

        // Hitung panjang teks (kira-kira, tidak super presisi)
        const textWidth = 20 * div.textContent.length * 0.6; 
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

        page.drawText(div.textContent, {
            x: finalX,
            y: finalY,
            size: div.textContent === 'V' ? 18 : 12,
            color: div.style.color === 'blue' ? PDFLib.rgb(0, 0, 1) : PDFLib.rgb(0, 0, 0),
            font
        });
    });

    const pdfBytes = await pdfDoc.save();
    const formData = new FormData();
    formData.append('pdf', new Blob([pdfBytes], { type: 'application/pdf' }));
    formData.append('timestamp', now);

    fetch(`{{ route('report_auditor.detail.submit', ['Id_List_Report' => $listReport->Id_List_Report]) }}`, {
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
</script>
@endsection
