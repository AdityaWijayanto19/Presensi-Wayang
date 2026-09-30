import { cpSync, existsSync, mkdirSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const wasmSrc = join(root, 'node_modules', '@mediapipe', 'tasks-vision', 'wasm');
const wasmDest = join(root, 'public', 'mediapipe', 'wasm');
const modelDest = join(root, 'public', 'models', 'blaze_face_short_range.tflite');
const modelUrl = 'https://storage.googleapis.com/mediapipe-models/face_detector/blaze_face_short_range/float16/1/blaze_face_short_range.tflite';
const modelSize = 229746;

const log = (msg) => console.log('[prepare-mediapipe] ' + msg);

function dirSize(dir) {
    if (!existsSync(dir)) return -1;
    let total = 0;
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
        const path = join(dir, entry.name);
        total += entry.isDirectory() ? dirSize(path) : statSync(path).size;
    }
    return total;
}

try {
    if (!existsSync(wasmSrc)) {
        throw new Error('folder wasm tidak ditemukan di node_modules/@mediapipe/tasks-vision');
    }
    if (dirSize(wasmSrc) !== dirSize(wasmDest)) {
        cpSync(wasmSrc, wasmDest, { recursive: true });
        log('wasm disalin -> public/mediapipe/wasm (' + Math.round(dirSize(wasmDest) / 1048576) + ' MB)');
    } else {
        log('wasm sudah up to date');
    }
} catch (error) {
    log('WARN: gagal menyalin wasm: ' + error.message);
}

try {
    if (existsSync(modelDest) && statSync(modelDest).size === modelSize) {
        log('model sudah ada (' + modelSize + ' byte)');
    } else {
        const res = await fetch(modelUrl);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const buf = Buffer.from(await res.arrayBuffer());
        if (buf.length !== modelSize) throw new Error('ukuran model tidak sesuai: ' + buf.length);
        mkdirSync(dirname(modelDest), { recursive: true });
        writeFileSync(modelDest, buf);
        log('model diunduh -> public/models/blaze_face_short_range.tflite (' + buf.length + ' byte)');
    }
} catch (error) {
    log('WARN: gagal mengunduh model: ' + error.message);
    log('WARN: jalankan ulang "npm run prepare:mediapipe" saat koneksi tersedia.');
}
