const WASM_BASE = '/mediapipe/wasm';
const MODEL_URL = '/models/blaze_face_short_range.tflite';

export const FACE_QUALITY_DEFAULTS = {
    detectionIntervalMs: 300,
    goodFramesRequired: 3,
    maxErrors: 5,
    minFaceConfidence: 0.5,
    minSuppressionThreshold: 0.3,
    minFaceHeightRatio: 0.20,
    maxFaceHeightRatio: 0.85,
    frameMargin: 0.02,
    centerMinX: 0.15,
    centerMaxX: 0.85,
    centerMinY: 0.10,
    centerMaxY: 0.85,
    maxTiltDeg: 18,
    minBrightness: 60,
    maxBrightness: 235,
    minSharpness: 25,
    faceCropInset: 0.12
};

export const FACE_QUALITY_MESSAGES = {
    loading: 'Menyiapkan pemeriksaan wajah…',
    no_face: 'Arahkan wajah ke kamera',
    multi_face: 'Pastikan hanya satu orang di depan kamera',
    too_far: 'Wajah terlalu jauh',
    too_close: 'Wajah terlalu dekat, mundur sedikit',
    out_of_frame: 'Wajah keluar dari area kamera',
    off_center: 'Arahkan wajah ke tengah kamera',
    tilted: 'Jangan terlalu miring',
    too_dark: 'Pencahayaan wajah terlalu gelap',
    too_bright: 'Pencahayaan wajah terlalu terang',
    blurry: 'Wajah kurang jelas, tahan posisi dan pastikan pencahayaan cukup',
    confirm: 'Memastikan kualitas wajah…',
    ok: '✓ Wajah terlihat jelas',
    unavailable: 'Pemeriksaan wajah tidak tersedia — silakan lanjutkan presensi'
};

function resolveConfig() {
    const override = window.FACE_QUALITY_CONFIG || {};
    const config = Object.assign({}, FACE_QUALITY_DEFAULTS);
    for (const key of Object.keys(override)) {
        if (key !== 'messages' && typeof FACE_QUALITY_DEFAULTS[key] === 'number' && typeof override[key] === 'number') {
            config[key] = override[key];
        }
    }
    return config;
}

function resolveMessages() {
    const override = window.FACE_QUALITY_CONFIG || {};
    return Object.assign({}, FACE_QUALITY_MESSAGES, override.messages || {});
}

let analysisCanvas = null;
let analysisCtx = null;

function getAnalysisContext() {
    if (!analysisCanvas) {
        analysisCanvas = document.createElement('canvas');
        analysisCtx = analysisCanvas.getContext('2d', { willReadFrequently: true });
    }
    return analysisCtx;
}

function analyzeFaceRegion(video, box, config) {
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    const inset = config.faceCropInset;

    let sx = box.originX + box.width * inset;
    let sy = box.originY + box.height * inset;
    let sw = box.width * (1 - inset * 2);
    let sh = box.height * (1 - inset * 2);

    sx = Math.max(0, Math.min(sx, vw - 1));
    sy = Math.max(0, Math.min(sy, vh - 1));
    sw = Math.max(1, Math.min(sw, vw - sx));
    sh = Math.max(1, Math.min(sh, vh - sy));

    const ctx = getAnalysisContext();
    const w = Math.max(1, Math.round(sw));
    const h = Math.max(1, Math.round(sh));
    analysisCanvas.width = w;
    analysisCanvas.height = h;
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, w, h);

    const pixels = ctx.getImageData(0, 0, w, h).data;
    const count = w * h;

    let brightnessSum = 0;
    const gray = new Float32Array(count);

    for (let i = 0; i < count; i++) {
        const offset = i * 4;
        const r = pixels[offset];
        const g = pixels[offset + 1];
        const b = pixels[offset + 2];
        const luminance = 0.299 * r + 0.587 * g + 0.114 * b;
        brightnessSum += luminance;
        gray[i] = luminance;
    }

    const brightness = brightnessSum / count;

    let sharpness = 0;
    if (w >= 8 && h >= 8) {
        let sum = 0;
        let sumSq = 0;
        let samples = 0;

        for (let y = 1; y < h - 1; y++) {
            for (let x = 1; x < w - 1; x++) {
                const i = y * w + x;
                const laplacian = gray[i - w] + gray[i + w] + gray[i - 1] + gray[i + 1] - 4 * gray[i];
                sum += laplacian;
                sumSq += laplacian * laplacian;
                samples++;
            }
        }

        if (samples > 0) {
            const mean = sum / samples;
            sharpness = sumSq / samples - mean * mean;
        }
    }

    return { brightness: brightness, sharpness: sharpness };
}

function detectionScore(detection) {
    const categories = detection.categories;
    if (Array.isArray(categories) && categories.length > 0 && typeof categories[0].score === 'number') {
        return categories[0].score;
    }
    return typeof detection.score === 'number' ? detection.score : 1;
}

export function evaluateDetection(detector, video, config) {
    const result = detector.detectForVideo(video, performance.now());
    const detections = (result && result.detections) || [];

    if (detections.length === 0) return { status: 'checking', code: 'no_face' };
    if (detections.length > 1) return { status: 'checking', code: 'multi_face' };

    const detection = detections[0];
    const box = detection.boundingBox;

    if (!box || !box.width || !box.height) return { status: 'checking', code: 'no_face' };
    if (detectionScore(detection) < config.minFaceConfidence) return { status: 'checking', code: 'no_face' };

    const vw = video.videoWidth;
    const vh = video.videoHeight;
    const heightRatio = box.height / vh;

    if (heightRatio < config.minFaceHeightRatio) return { status: 'checking', code: 'too_far' };
    if (heightRatio > config.maxFaceHeightRatio) return { status: 'checking', code: 'too_close' };

    const marginX = vw * config.frameMargin;
    const marginY = vh * config.frameMargin;

    if (
        box.originX < marginX ||
        box.originY < marginY ||
        box.originX + box.width > vw - marginX ||
        box.originY + box.height > vh - marginY
    ) {
        return { status: 'checking', code: 'out_of_frame' };
    }

    const centerX = (box.originX + box.width / 2) / vw;
    const centerY = (box.originY + box.height / 2) / vh;

    if (
        centerX < config.centerMinX ||
        centerX > config.centerMaxX ||
        centerY < config.centerMinY ||
        centerY > config.centerMaxY
    ) {
        return { status: 'checking', code: 'off_center' };
    }

    const keypoints = detection.keypoints || [];
    if (keypoints.length >= 2 && keypoints[0] && keypoints[1]) {
        const dx = (keypoints[1].x - keypoints[0].x) * vw;
        const dy = (keypoints[1].y - keypoints[0].y) * vh;
        const tilt = Math.abs(Math.atan2(dy, dx) * 180 / Math.PI);

        if (tilt > config.maxTiltDeg) return { status: 'checking', code: 'tilted' };
    }

    const analysis = analyzeFaceRegion(video, box, config);

    if (analysis.brightness < config.minBrightness) return { status: 'checking', code: 'too_dark' };
    if (analysis.brightness > config.maxBrightness) return { status: 'checking', code: 'too_bright' };
    if (analysis.sharpness < config.minSharpness) return { status: 'checking', code: 'blurry' };

    return { status: 'ok', code: 'ok' };
}

export async function initFaceQualityCheck(options) {
    const video = options.video;
    const onResult = options.onResult;

    let stopped = false;
    let timer = null;
    let detector = null;
    let consecutiveErrors = 0;
    let goodStreak = 0;
    let lastVideoTime = -1;
    let lastEmitted = null;

    const emit = (status, code) => {
        const key = status + '|' + code;
        if (key === lastEmitted) return;
        lastEmitted = key;
        const messages = resolveMessages();
        onResult({ status: status, code: code, text: messages[code] || code });
    };

    emit('checking', 'loading');

    const initialConfig = resolveConfig();

    try {
        const vision = await import('@mediapipe/tasks-vision');
        const fileset = await vision.FilesetResolver.forVisionTasks(WASM_BASE);
        detector = await vision.FaceDetector.createFromOptions(fileset, {
            baseOptions: { modelAssetPath: MODEL_URL },
            runningMode: 'VIDEO',
            minDetectionConfidence: initialConfig.minFaceConfidence,
            minSuppressionThreshold: initialConfig.minSuppressionThreshold
        });
    } catch (error) {
        console.warn('[face-quality] gagal inisialisasi detector:', error);
        emit('unavailable', 'unavailable');
        return { stop: function () {} };
    }

    const stop = () => {
        if (stopped) return;
        stopped = true;
        if (timer !== null) clearTimeout(timer);
        timer = null;
        if (detector && typeof detector.close === 'function') {
            try { detector.close(); } catch (error) { /* noop */ }
        }
        detector = null;
    };

    const tick = () => {
        if (stopped) return;

        const config = resolveConfig();
        timer = setTimeout(tick, config.detectionIntervalMs);

        if (document.hidden) return;
        if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) return;
        if (video.currentTime === lastVideoTime) return;
        lastVideoTime = video.currentTime;

        try {
            const outcome = evaluateDetection(detector, video, config);
            consecutiveErrors = 0;

            if (outcome.status === 'ok') {
                goodStreak++;
                if (goodStreak >= config.goodFramesRequired) {
                    emit('ok', 'ok');
                } else {
                    emit('checking', 'confirm');
                }
            } else {
                goodStreak = 0;
                emit(outcome.status, outcome.code);
            }
        } catch (error) {
            consecutiveErrors++;
            if (consecutiveErrors >= config.maxErrors) {
                console.warn('[face-quality] deteksi gagal berulang, fitur dinonaktifkan:', error);
                emit('unavailable', 'unavailable');
                stop();
            }
        }
    };

    tick();

    return { stop: stop };
}
