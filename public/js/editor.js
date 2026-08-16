// The editor page: webcam preview, overlay selection, and capture.

const stage = document.querySelector('[data-stage]');
const video = document.querySelector('[data-video]');
const preview = document.querySelector('[data-overlay-preview]');
const shutter = document.querySelector('[data-shutter]');
const panel = document.querySelector('[data-panel]');
const emptyNotice = document.querySelector('[data-panel-empty]');
const noCamera = document.querySelector('[data-no-camera]');
const radios = document.querySelectorAll('[name="overlay"]');
const uploadPreview = document.querySelector('[data-upload-preview]');
const fileInput = document.querySelector('[name="photo"]');
const useCamera = document.querySelector('[data-use-camera]');
const captureDialog = document.querySelector('[data-capture-dialog]');
const capturePreview = document.querySelector('[data-capture-preview]');
const captureOverlay = document.querySelector('[data-capture-overlay]');

const width = Number(stage.dataset.width);
const height = Number(stage.dataset.height);

let cameraReady = false;
let captured = null;
let chosenFile = null;

const chosen = () => document.querySelector('[name="overlay"]:checked');

const refreshShutter = () => {
    shutter.disabled = !cameraReady || chosenFile !== null || chosen() === null;
};

const notify = (message) => {
    const alert = document.createElement('div');

    alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
    alert.setAttribute('role', 'alert');
    alert.textContent = message;

    const close = document.createElement('button');

    close.type = 'button';
    close.className = 'btn-close';
    close.setAttribute('aria-label', 'Close');

    // layout.js already closes any .alert .btn-close, including this one.
    alert.append(close);
    document.querySelector('main').prepend(alert);
};

// The video goes, but the stage stays: it is where an uploaded picture gets
// previewed, and someone with no camera is exactly who needs that.
const stopCamera = () => {
    video.hidden = true;
    shutter.hidden = true;
    noCamera.hidden = false;
    cameraReady = false;
};

const startCamera = async () => {
    if (navigator.mediaDevices === undefined) {
        stopCamera();

        return;
    }

    try {
        video.srcObject = await navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: width }, height: { ideal: height }, facingMode: 'user' },
            audio: false,
        });

        await video.play();
        cameraReady = true;
    } catch {
        stopCamera();
    }

    refreshShutter();
};

const frame = () => {
    const canvas = document.createElement('canvas');

    canvas.width = width;
    canvas.height = height;

    const ratio = width / height;
    const source = { width: video.videoWidth, height: video.videoHeight };
    const wide = source.width / source.height > ratio;

    const cropWidth = wide ? Math.round(source.height * ratio) : source.width;
    const cropHeight = wide ? source.height : Math.round(source.width / ratio);

    canvas.getContext('2d').drawImage(
        video,
        Math.floor((source.width - cropWidth) / 2),
        Math.floor((source.height - cropHeight) / 2),
        cropWidth,
        cropHeight,
        0,
        0,
        width,
        height
    );

    return canvas.toDataURL('image/jpeg', 0.9);
};

const publish = async (body) => {
    body.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

    const response = await fetch('/editor', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body,
    });

    const data = await response.json();

    if (!response.ok) {
        notify(data.error ?? 'Your picture could not be saved.');

        return;
    }

    panel.insertAdjacentHTML('afterbegin', data.html);
    emptyNotice.classList.add('d-none');
};

radios.forEach((radio) => {
    radio.addEventListener('change', () => {
        preview.src = radio.dataset.overlaySrc;
        preview.hidden = false;

        refreshShutter();
    });
});

// Nothing is sent yet: the frame is frozen and shown back for approval first.
shutter.addEventListener('click', () => {
    const radio = chosen();

    if (radio === null || !cameraReady) {
        return;
    }

    captured = frame();
    capturePreview.src = captured;
    captureOverlay.src = radio.dataset.overlaySrc;

    video.pause();
    captureDialog.showModal();
});

document.querySelector('[data-retake]').addEventListener('click', () => {
    captureDialog.close();
});

// Escape and "Take another" land here alike, so the camera resumes either way.
captureDialog.addEventListener('close', () => {
    captured = null;

    if (cameraReady && chosenFile === null) {
        video.play().catch(() => {});
    }
});

document.querySelector('[data-use-capture]').addEventListener('click', async () => {
    const radio = chosen();
    const data = captured;

    captureDialog.close();

    if (radio === null || data === null) {
        return;
    }

    const body = new FormData();

    body.append('overlay', radio.value);
    body.append('capture', data);

    shutter.disabled = true;

    try {
        await publish(body);
    } catch {
        notify('Your picture could not be sent. Check your connection and try again.');
    } finally {
        refreshShutter();
    }
});

const showUpload = (file) => {
    if (chosenFile !== null) {
        URL.revokeObjectURL(uploadPreview.src);
    }

    chosenFile = file;
    uploadPreview.src = URL.createObjectURL(file);
    uploadPreview.hidden = false;

    video.hidden = true;
    video.pause();

    useCamera.hidden = !cameraReady;

    refreshShutter();
};

const showCamera = () => {
    if (chosenFile !== null) {
        URL.revokeObjectURL(uploadPreview.src);
    }

    chosenFile = null;
    uploadPreview.hidden = true;
    uploadPreview.removeAttribute('src');

    useCamera.hidden = true;

    if (cameraReady) {
        video.hidden = false;
        video.play().catch(() => {});
    }

    refreshShutter();
};

fileInput.addEventListener('change', () => {
    const file = fileInput.files.item(0);

    if (file === null) {
        showCamera();

        return;
    }

    if (!file.type.startsWith('image/')) {
        notify('Choose a JPEG or a PNG.');
        fileInput.value = '';
        showCamera();

        return;
    }

    showUpload(file);
});

useCamera.addEventListener('click', () => {
    fileInput.value = '';
    showCamera();
});

startCamera();
