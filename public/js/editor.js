// The editor page: webcam preview, overlay selection, and capture.

const stage = document.querySelector('[data-stage]');
const video = document.querySelector('[data-video]');
const overlayStack = document.querySelector('[data-overlay-stack]');
const captureStack = document.querySelector('[data-capture-stack]');
const stackList = document.querySelector('[data-stack]');
const stackSummary = document.querySelector('[data-stack-summary]');
const addOverlay = document.querySelector('[data-add-overlay]');
const clearOverlays = document.querySelector('[data-clear-overlays]');
const upload = document.querySelector('[data-upload]');
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
const maxOverlays = Number(document.querySelector('[data-max-overlays]').dataset.maxOverlays);

const width = Number(stage.dataset.width);
const height = Number(stage.dataset.height);

let cameraReady = false;
let captured = null;
let chosenFile = null;

// The overlays already added, bottom layer first.
let stack = [];

const chosen = () => document.querySelector('[name="overlay"]:checked');

const asLayer = (radio) => ({
    key: radio.value,
    src: radio.dataset.overlaySrc,
    label: radio.dataset.overlayLabel,
});

const layers = () => {
    const radio = chosen();

    return radio === null ? stack : stack.concat([asLayer(radio)]);
};

const drawLayers = (into, entries) => {
    into.replaceChildren(...entries.map((entry) => {
        const layer = document.createElement('img');

        layer.src = entry.src;
        layer.alt = '';

        return layer;
    }));
};

const drawStack = () => {
    stackList.replaceChildren(...stack.map((entry) => {
        const item = document.createElement('li');
        const thumb = document.createElement('img');

        thumb.src = entry.src;
        thumb.width = 48;
        thumb.height = 36;
        thumb.alt = entry.label;
        thumb.title = entry.label;

        item.append(thumb);

        return item;
    }));
};

const refresh = () => {
    const chosenLayers = layers();
    const full = stack.length >= maxOverlays;

    drawLayers(overlayStack, chosenLayers);
    drawStack();

    addOverlay.hidden = chosen() === null || full;
    clearOverlays.hidden = chosenLayers.length === 0;

    stackSummary.textContent = full
        ? 'That is as many as one picture can take.'
        : chosenLayers.length + (chosenLayers.length === 1 ? ' overlay' : ' overlays') + ' on the picture.';

    shutter.disabled = !cameraReady || chosenFile !== null || chosenLayers.length === 0;
    upload.disabled = chosenLayers.length === 0;
};

// The video goes, but the stage stays: it is where an uploaded picture gets
// previewed, and someone with no camera is exactly who needs that.
const stopCamera = (reason) => {
    video.hidden = true;
    shutter.hidden = true;
    noCamera.textContent = reason;
    noCamera.hidden = false;
    cameraReady = false;
};

// Firefox and Chrome do not always throw the same name for the same situation,
// and "no camera" needs a different fix from "you blocked it" or "it is busy".
const cameraProblem = (error) => {
    switch (error.name) {
        case 'NotAllowedError':
        case 'SecurityError':
            return 'Camagru is not allowed to use your camera. Allow it from the icon in the address bar, then reload the page.';
        case 'NotFoundError':
        case 'OverconstrainedError':
            return 'No camera was found on this computer, so upload a picture instead.';
        case 'NotReadableError':
        case 'AbortError':
            return 'Your camera is already in use by another tab or program. Close it, then reload the page.';
        default:
            return 'Your camera could not be started (' + error.name + '), so upload a picture instead.';
    }
};

const startCamera = async () => {
    if (navigator.mediaDevices === undefined) {
        stopCamera('Your browser only shares the camera over a secure connection. Open the site on localhost, or upload a picture instead.');

        return;
    }

    try {
        video.srcObject = await navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: width }, height: { ideal: height }, facingMode: 'user' },
            audio: false,
        });

        cameraReady = true;

        video.play().catch(() => {});
    } catch (error) {
        stopCamera(cameraProblem(error));
    }

    refresh();
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

        return false;
    }

    panel.insertAdjacentHTML('afterbegin', data.html);
    emptyNotice.classList.add('d-none');

    return true;
};

radios.forEach((radio) => {
    radio.addEventListener('change', refresh);
});

// Keeps the pending layer and clears the picker, so the next choice is a new
// layer rather than a replacement — and the same sticker can go on twice.
addOverlay.addEventListener('click', () => {
    const radio = chosen();

    if (radio === null || stack.length >= maxOverlays) {
        return;
    }

    stack.push(asLayer(radio));
    radio.checked = false;

    refresh();
});

clearOverlays.addEventListener('click', () => {
    const radio = chosen();

    stack = [];

    if (radio !== null) {
        radio.checked = false;
    }

    refresh();
});

// Nothing is sent yet: the frame is frozen and shown back for approval first.
shutter.addEventListener('click', () => {
    const chosenLayers = layers();

    if (chosenLayers.length === 0 || !cameraReady) {
        return;
    }

    captured = frame();
    capturePreview.src = captured;
    drawLayers(captureStack, chosenLayers);

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
    const chosenLayers = layers();
    const data = captured;

    captureDialog.close();

    if (chosenLayers.length === 0 || data === null) {
        return;
    }

    const body = new FormData();

    chosenLayers.forEach((entry) => body.append('overlay[]', entry.key));
    body.append('capture', data);

    shutter.disabled = true;

    try {
        await publish(body);
    } catch {
        notify('Your picture could not be sent. Check your connection and try again.');
    } finally {
        refresh();
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

    refresh();
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

    refresh();
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

fileInput.form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const chosenLayers = layers();

    if (chosenLayers.length === 0 || chosenFile === null) {
        return;
    }

    const body = new FormData();

    chosenLayers.forEach((entry) => body.append('overlay[]', entry.key));
    body.append('photo', chosenFile);

    const button = event.target.querySelector('button[type="submit"]');

    button.disabled = true;

    try {
        if (await publish(body)) {
            fileInput.value = '';
            showCamera();
        }
    } catch {
        notify('Your picture could not be sent. Check your connection and try again.');
    } finally {
        button.disabled = false;
    }
});

// Paints the initial state, including disabling what has nothing to act on.
// startCamera() refreshes again once the camera has answered either way.
refresh();
startCamera();
