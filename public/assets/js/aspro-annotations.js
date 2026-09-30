/**
 * aspro-annotations.js
 * Decoupled annotation engine for iseki_aspro.
 */
window.AsproAnnotationEngine = (function () {
    const ROLE_CONFIG = {
        member: {
            timestampColor: [0, 0.6, 0],
            timestampX: 200,
            markColors: {
                'V': { text: 'green', bg: 'rgba(0,255,0,0.3)', pdfColor: [0, 0.6, 0] },
                'NG': { text: 'red', bg: 'rgba(255,0,0,0.3)', pdfColor: [1, 0, 0] },
                'X': { text: 'red', bg: 'rgba(255,0,0,0.3)', pdfColor: [1, 0, 0] }
            },
            comment: {
                text: 'white',
                bg: '#800080',
                pdfBg: [0.502, 0, 0.502]
            }
        },
        leader: {
            timestampColor: [1, 0, 0],
            timestampX: null,
            markColors: {
                'V': { text: 'red', bg: 'rgba(255,0,0,0.3)', pdfColor: [1, 0, 0] },
                'NG': { text: 'red', bg: 'rgba(255,0,0,0.3)', pdfColor: [1, 0, 0] },
                'X': { text: 'red', bg: 'rgba(255,0,0,0.3)', pdfColor: [1, 0, 0] }
            },
            comment: {
                text: 'white',
                bg: '#8B4513',
                pdfBg: [0.545, 0.271, 0.075]
            }
        },
        auditor: {
            timestampColor: [0, 0, 1],
            timestampX: null,
            markColors: {
                'V': { text: 'blue', bg: 'rgba(0,0,255,0.3)', pdfColor: [0, 0, 1] },
                'NG': { text: 'blue', bg: 'rgba(0,0,255,0.3)', pdfColor: [0, 0, 1] },
                'X': { text: 'blue', bg: 'rgba(0,0,255,0.3)', pdfColor: [0, 0, 1] }
            },
            comment: {
                text: 'white',
                bg: '#E91E63',
                pdfBg: [0.914, 0.118, 0.388]
            }
        }
    };

    function serializeLayer(editorLayer, targetRole) {
        if (!editorLayer) return [];
        const items = [];
        editorLayer.querySelectorAll('div').forEach(div => {
            // Abaikan bar stamp header atau elemen stamp header
            if (div.classList.contains('aspro-header-stamp-bar') || div.closest('.aspro-header-stamp-bar')) return;
            if (div.classList.contains('aspro-header-stamp') || div.closest('.aspro-header-stamp')) return;

            // Jika div berasal dari role lain yang sudah tersimpan sebelumnya, jangan dimasukkan ke serialize role ini!
            const elementRole = div.getAttribute('data-role');
            if (elementRole && targetRole && elementRole !== targetRole) {
                return;
            }

            const isComment = div.contentEditable === 'true';
            const left = parseFloat(div.style.left) || 0;
            const top = parseFloat(div.style.top) || 0;
            const text = ((isComment ? div.innerText : div.textContent) || '').trim();

            if (isComment && text === '') return;

            // Filter out legacy/accidental stamp timestamp texts (e.g. "2026-09-29 13:34:58saiful" or empty marks)
            if (/^\d{4}-\d{2}-\d{2}/.test(text)) return;
            if (!isComment && text === '') return;

            items.push({
                type: isComment ? 'comment' : 'mark',
                text: text,
                left: left,
                top: top,
                color: div.style.color || '',
                backgroundColor: div.style.backgroundColor || ''
            });
        });
        return items;
    }

    function renderSavedAnnotations(editorLayer, annotationsData, currentRole, onSetupElement, timestamps, names) {
        if (!editorLayer) return;
        editorLayer.innerHTML = '';

        const roles = ['member', 'leader', 'auditor'];
        roles.forEach(role => {
            const roleAnnotations = annotationsData[role];
            if (!Array.isArray(roleAnnotations)) return;

            const isEditable = (role === currentRole);
            const roleCfg = ROLE_CONFIG[role] || ROLE_CONFIG.member;

            roleAnnotations.forEach(item => {
                const rawText = (item.text || '').trim();
                // Skip legacy/invalid items like accidental timestamp strings in saved annotations
                if (/^\d{4}-\d{2}-\d{2}/.test(rawText)) return;
                if (!rawText && item.type !== 'comment') return;

                let el;
                if (item.type === 'comment') {
                    el = document.createElement('div');
                    el.setAttribute('data-role', role);
                    el.setAttribute('data-saved', 'true');
                    el.contentEditable = isEditable ? 'true' : 'false';
                    el.innerText = item.text || '';
                    Object.assign(el.style, {
                        position: 'absolute',
                        left: (item.left || 0) + 'px',
                        top: (item.top || 0) + 'px',
                        cursor: isEditable ? 'move' : 'default',
                        color: roleCfg.comment.text,
                        backgroundColor: roleCfg.comment.bg,
                        border: 'none',
                        minWidth: '80px',
                        minHeight: '20px',
                        whiteSpace: 'pre-wrap',
                        padding: '4px 6px',
                        fontSize: '13px',
                        outline: 'none',
                        borderRadius: '3px',
                        boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                    });
                } else {
                    el = document.createElement('div');
                    el.setAttribute('data-role', role);
                    el.setAttribute('data-saved', 'true');
                    const markText = rawText || 'V';
                    el.textContent = markText;
                    const markCfg = roleCfg.markColors[markText] || roleCfg.markColors['V'];
                    Object.assign(el.style, {
                        position: 'absolute',
                        left: (item.left || 0) + 'px',
                        top: (item.top || 0) + 'px',
                        cursor: isEditable ? 'move' : 'default',
                        color: markCfg.text,
                        backgroundColor: markCfg.bg,
                        padding: '2px 5px',
                        fontSize: (markText === 'V' || markText === 'X' || markText === 'NG') ? '22px' : '14px',
                        fontWeight: 'bold',
                        userSelect: 'none',
                        border: '1px solid transparent',
                        borderRadius: '2px'
                    });
                }

                if (isEditable && typeof onSetupElement === 'function') {
                    onSetupElement(el);
                }
                editorLayer.appendChild(el);
            });
        });

        // Tampilkan stamp nama & timestamp approval di atas canvas (Header)
        // Hanya render jika ada timestamps yang tidak null/kosong
        const hasApprovalTimestamp = timestamps && Object.values(timestamps).some(t => t && t.trim && t.trim() !== '');
        if (hasApprovalTimestamp) {
            renderHeaderStamps(editorLayer, timestamps, names);
        }
    }

    function renderHeaderStamps(editorLayer, timestamps, names) {
        if (!editorLayer) return;

        // Bersihkan header stamp lama jika ada
        editorLayer.querySelectorAll('.aspro-header-stamp-bar').forEach(el => el.remove());
        editorLayer.querySelectorAll('.aspro-header-stamp').forEach(el => el.remove());

        const roles = ['member', 'leader', 'auditor'];
        const activeRoles = roles.filter(role => {
            const time = timestamps ? timestamps[role] : null;
            return time && typeof time === 'string' && time.trim() !== '';
        });

        if (activeRoles.length === 0) return;

        // Buat container bar di bagian paling atas canvas dengan lebar 100%
        const bar = document.createElement('div');
        bar.className = 'aspro-header-stamp-bar';
        Object.assign(bar.style, {
            position: 'absolute',
            top: '4px',
            left: '0px',
            width: '100%',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'flex-start',
            padding: '0 40px',
            boxSizing: 'border-box',
            pointerEvents: 'none',
            zIndex: '20'
        });

        // 3 Slot: Kiri (Member), Tengah (Leader), Kanan (Auditor)
        ['member', 'leader', 'auditor'].forEach(role => {
            const slot = document.createElement('div');
            slot.style.flex = '1';
            slot.style.display = 'flex';
            slot.style.flexDirection = 'column';

            if (role === 'member') {
                slot.style.alignItems = 'flex-start';
                slot.style.textAlign = 'left';
            } else if (role === 'leader') {
                slot.style.alignItems = 'center';
                slot.style.textAlign = 'center';
            } else {
                slot.style.alignItems = 'flex-end';
                slot.style.textAlign = 'right';
            }

            const time = timestamps ? timestamps[role] : null;
            const name = names ? names[role] : '';

            if (time && typeof time === 'string' && time.trim() !== '') {
                let textColor = '#2e7d32'; // Hijau gelap
                if (role === 'leader') textColor = '#c62828'; // Merah
                else if (role === 'auditor') textColor = '#1565c0'; // Biru

                const stampBox = document.createElement('div');
                stampBox.className = 'aspro-header-stamp';
                Object.assign(stampBox.style, {
                    fontSize: '11px',
                    fontWeight: 'bold',
                    lineHeight: '1.25',
                    color: textColor,
                    background: 'rgba(255, 255, 255, 0.75)',
                    padding: '2px 6px',
                    borderRadius: '3px',
                    whiteSpace: 'nowrap'
                });

                const timeDiv = document.createElement('div');
                timeDiv.textContent = time;
                stampBox.appendChild(timeDiv);

                if (name && name.trim() !== '') {
                    const nameDiv = document.createElement('div');
                    nameDiv.textContent = name;
                    stampBox.appendChild(nameDiv);
                }

                slot.appendChild(stampBox);
            }

            bar.appendChild(slot);
        });

        editorLayer.appendChild(bar);
    }

    async function downloadAnnotatedPdf({
        masterPdfUrl,
        downloadFilename,
        canvasWidth,
        pageViewportHeights,
        annotations,
        timestamps,
        names
    }) {
        if (!window.PDFLib) {
            alert('PDF-Lib tidak tersedia');
            return;
        }

        try {
            const masterBytes = await fetch(masterPdfUrl).then(res => {
                if (!res.ok) throw new Error('Gagal mengambil file PDF master.');
                return res.arrayBuffer();
            });

            const pdfDoc = await PDFLib.PDFDocument.load(masterBytes);
            const pages = pdfDoc.getPages();
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

            if (pages.length > 0) {
                const firstPage = pages[0];
                const pageWidth = firstPage.getWidth();
                const pageHeight = firstPage.getHeight();
                const fontSize = 8;
                const lineHeight = fontSize + 2;

                const roleOrder = ['member', 'leader', 'auditor'];
                roleOrder.forEach(role => {
                    const time = timestamps ? timestamps[role] : null;
                    const name = names ? names[role] : '';
                    if (!time) return;

                    const cfg = ROLE_CONFIG[role];
                    const rgbColor = PDFLib.rgb(...cfg.timestampColor);
                    const lines = [time, name].filter(Boolean);

                    lines.forEach((lineText, idx) => {
                        let xPos;
                        const textW = font.widthOfTextAtSize(lineText, fontSize);
                        if (role === 'member') {
                            xPos = 200;
                        } else if (role === 'leader') {
                            xPos = (pageWidth - textW) / 2;
                        } else {
                            xPos = pageWidth - textW - 40;
                        }

                        firstPage.drawText(lineText, {
                            x: xPos,
                            y: pageHeight - 10 - (idx * lineHeight),
                            size: fontSize,
                            font: font,
                            color: rgbColor
                        });
                    });
                });
            }

            const canvasW = canvasWidth || 800;
            let yOffsets = [0];
            for (let i = 0; i < pageViewportHeights.length - 1; i++) {
                yOffsets.push(yOffsets[i] + pageViewportHeights[i]);
            }

            const roles = ['member', 'leader', 'auditor'];
            roles.forEach(role => {
                const roleItems = annotations ? annotations[role] : null;
                if (!Array.isArray(roleItems)) return;

                const roleCfg = ROLE_CONFIG[role] || ROLE_CONFIG.member;

                roleItems.forEach(item => {
                    const x = parseFloat(item.left) || 0;
                    const y = parseFloat(item.top) || 0;

                    let pageIndex = yOffsets.findIndex((offset, i) => y < offset + pageViewportHeights[i]);
                    if (pageIndex === -1) pageIndex = pages.length - 1;

                    const targetPage = pages[pageIndex];
                    if (!targetPage) return;

                    const pHeight = targetPage.getHeight();
                    const pWidth = targetPage.getWidth();

                    const offsetY = y - yOffsets[pageIndex];
                    const scaleX = pWidth / canvasW;
                    const scaleY = pHeight / (pageViewportHeights[pageIndex] || pHeight);

                    const finalX = x * scaleX;
                    const finalY = pHeight - (offsetY * scaleY) - 18;

                    if (item.type === 'comment') {
                        drawCommentOnPdf(targetPage, item.text, finalX, finalY, font, roleCfg);
                    } else {
                        drawMarkOnPdf(targetPage, item.text, finalX, finalY, font, roleCfg);
                    }
                });
            });

            const pdfBytes = await pdfDoc.save();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = downloadFilename || 'Procedure-Annotated.pdf';
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 10000);
        } catch (err) {
            console.error('Error generating PDF:', err);
            alert('Gagal membuat PDF: ' + err.message);
        }
    }

    function drawCommentOnPdf(page, text, x, y, font, roleCfg) {
        const rawText = (text || '').replace(/[\u200B-\u200D\uFEFF]/g, '');
        const textLines = rawText.split(/\r?\n/).map(l => l.replace(/[^\x00-\xFF]/g, ''));
        if (textLines.join('').trim() === '') return;

        const fontSize = 11;
        const lineHeight = fontSize + 3;
        let maxWidth = 0;
        textLines.forEach(l => {
            try { maxWidth = Math.max(maxWidth, font.widthOfTextAtSize(l, fontSize)); } catch (e) {}
        });

        const padding = 5;
        const boxWidth = maxWidth + (2 * padding);
        const boxHeight = (textLines.length * lineHeight) + (2 * padding);

        const pdfBg = roleCfg.comment.pdfBg || [0.5, 0, 0.5];

        page.drawRectangle({
            x: x - padding,
            y: y - boxHeight,
            width: boxWidth,
            height: boxHeight,
            color: PDFLib.rgb(...pdfBg),
            opacity: 0.95
        });

        textLines.forEach((line, i) => {
            page.drawText(line, {
                x: x,
                y: y - padding - ((i + 1) * lineHeight) + 3,
                size: fontSize,
                color: PDFLib.rgb(1, 1, 1),
                font: font
            });
        });
    }

    function drawMarkOnPdf(page, text, x, y, font, roleCfg) {
        const markText = (text || 'V').trim();
        const size = (markText === 'V' || markText === 'X' || markText === 'NG') ? 18 : 12;
        const textWidth = 20 * markText.length * 0.6;
        const textHeight = 18;

        page.drawRectangle({
            x: x - 2,
            y: y - 2,
            width: textWidth + 4,
            height: textHeight + 4,
            color: PDFLib.rgb(1, 1, 1),
            opacity: 0.5
        });

        const markCfg = roleCfg.markColors[markText] || roleCfg.markColors['V'];
        const colorRgb = markCfg.pdfColor || [0, 0, 0];

        page.drawText(markText, {
            x: x,
            y: y,
            size: size,
            color: PDFLib.rgb(...colorRgb),
            font: font
        });
    }

    return {
        ROLE_CONFIG,
        serializeLayer,
        renderSavedAnnotations,
        renderHeaderStamps,
        downloadAnnotatedPdf
    };
})();
