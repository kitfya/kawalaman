/**
 * KawalAman - Frontend JavaScript Interaction
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Modal Pelaporan Handler
    const reportModal = document.getElementById('reportModal');
    const openReportBtns = document.querySelectorAll('.btn-open-report');
    const closeReportBtns = document.querySelectorAll('.btn-close-report');

    function openModal() {
        if (!reportModal) return;
        reportModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => {
            const backdrop = reportModal.querySelector('.modal-backdrop');
            const content = reportModal.querySelector('.modal-content');
            if (backdrop) backdrop.classList.remove('opacity-0');
            if (content) {
                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }
        }, 10);
    }

    function closeModal() {
        if (!reportModal) return;
        const backdrop = reportModal.querySelector('.modal-backdrop');
        const content = reportModal.querySelector('.modal-content');
        if (backdrop) backdrop.classList.add('opacity-0');
        if (content) {
            content.classList.add('opacity-0', 'scale-95');
            content.classList.remove('opacity-100', 'scale-100');
        }
        setTimeout(() => {
            reportModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 250);
    }

    openReportBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    });

    closeReportBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    // Close on backdrop click
    if (reportModal) {
        reportModal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                closeModal();
            }
        });
    }

    // Keyboard ESC to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && reportModal && !reportModal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // 2. Anonymous Checkbox Handler
    const anonCheckbox = document.getElementById('is_anonymous');
    const nameInput = document.getElementById('reporter_name');
    let previousName = '';

    if (anonCheckbox && nameInput) {
        anonCheckbox.addEventListener('change', () => {
            if (anonCheckbox.checked) {
                previousName = nameInput.value;
                nameInput.value = 'Hamba Allah (Anonim)';
                nameInput.readOnly = true;
                nameInput.classList.add('bg-gray-100', 'text-gray-500');
            } else {
                nameInput.value = previousName;
                nameInput.readOnly = false;
                nameInput.classList.remove('bg-gray-100', 'text-gray-500');
                nameInput.focus();
            }
        });
    }

    // 3. File Upload Preview & Drag-and-Drop
    const fileInput = document.getElementById('evidence_file');
    const dropzone = document.getElementById('evidence_dropzone');
    const previewContainer = document.getElementById('file_preview_container');
    const previewImage = document.getElementById('preview_image');
    const previewVideo = document.getElementById('preview_video');
    const previewFileName = document.getElementById('preview_filename');
    const previewFileSize = document.getElementById('preview_filesize');
    const removeFileBtn = document.getElementById('btn_remove_file');

    function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }

    function handleFile(file) {
        if (!file) return;

        if (file.size > 30 * 1024 * 1024) {
            alert('Ukuran berkas melebihi batas maksimal 30MB!');
            fileInput.value = '';
            return;
        }

        const isImg = file.type.startsWith('image/');
        const isVid = file.type.startsWith('video/');

        if (!isImg && !isVid) {
            alert('Hanya mendukung format foto (JPG, PNG, WEBP) atau video (MP4, WEBM, MOV)!');
            fileInput.value = '';
            return;
        }

        if (previewFileName) previewFileName.textContent = file.name;
        if (previewFileSize) previewFileSize.textContent = formatBytes(file.size);

        if (isImg && previewImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
                if (previewVideo) previewVideo.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else if (isVid && previewVideo) {
            const videoUrl = URL.createObjectURL(file);
            previewVideo.src = videoUrl;
            previewVideo.classList.remove('hidden');
            if (previewImage) previewImage.classList.add('hidden');
        }

        if (dropzone) dropzone.classList.add('hidden');
        if (previewContainer) previewContainer.classList.remove('hidden');
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleFile(e.target.files[0]);
            }
        });
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (fileInput) fileInput.value = '';
            if (previewContainer) previewContainer.classList.add('hidden');
            if (dropzone) dropzone.classList.remove('hidden');
            if (previewImage) { previewImage.src = ''; previewImage.classList.add('hidden'); }
            if (previewVideo) { previewVideo.src = ''; previewVideo.classList.add('hidden'); }
        });
    }

    // Drag-and-drop events
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('border-indigo-500', 'bg-indigo-50/50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/50');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
    }

    // 4. Salin Kode Tiket ke Clipboard
    window.copyTicketCode = function(ticketCode, buttonElement) {
        if (!ticketCode) return;
        navigator.clipboard.writeText(ticketCode).then(() => {
            const originalHtml = buttonElement.innerHTML;
            buttonElement.innerHTML = `
                <svg class="w-4 h-4 text-emerald-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-emerald-700 font-semibold">Tersalin!</span>
            `;
            setTimeout(() => {
                buttonElement.innerHTML = originalHtml;
            }, 2500);
        }).catch(err => {
            console.error('Gagal menyalin:', err);
        });
    };

    // 5. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
