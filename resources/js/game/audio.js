// ---------------------------------------------------------------
// Audio Manager (BGM & SFX)
// ---------------------------------------------------------------
const soundCache = {};
let bgmAudio = null;
let isBgmPlaying = false;
let isMuted = false;

function getAudio(filename) {
    if (!soundCache[filename]) {
        const audio = new Audio(`/sounds/${filename}`);
        soundCache[filename] = audio;
    }
    return soundCache[filename];
}

export function playSound(name) {
    if (isMuted) return;
    try {
        const audio = getAudio(`${name}.mp3`);
        audio.currentTime = 0;
        audio.play().catch(() => {});
    } catch (e) {
        console.warn('Audio play error:', e);
    }
}

export function playRollSound() {
    playSound('roll_dice');
}

export function playHitSound(vol = 0.2) {
    // Reuse roll_dice or play roll_dice
    playSound('roll_dice');
}

export function startBGM() {
    if (isBgmPlaying || isMuted) return;
    try {
        if (!bgmAudio) {
            bgmAudio = getAudio('bgm.mp3');
            bgmAudio.loop = true;
            bgmAudio.volume = 0.25;
        }
        bgmAudio.play().then(() => {
            isBgmPlaying = true;
        }).catch(() => {
            // Autoplay policy: will start on first user click
        });
    } catch (e) {}
}

export function stopBGM() {
    if (bgmAudio) {
        bgmAudio.pause();
        isBgmPlaying = false;
    }
}

export function toggleAudio() {
    isMuted = !isMuted;
    if (isMuted) {
        stopBGM();
    } else {
        startBGM();
    }
    return !isMuted;
}

export function initAudioAutoStart() {
    const handler = () => {
        startBGM();
    };
    document.addEventListener('click', handler, { once: true });
    document.addEventListener('keydown', handler, { once: true });
    document.addEventListener('touchstart', handler, { once: true });
}

