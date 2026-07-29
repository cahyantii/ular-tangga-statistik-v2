import { state, dom, config } from './state.js';
import { playRollSound, playHitSound } from './audio.js';
import { spawnParticles } from './utils.js';

    // ---------------------------------------------------------------
    // Dadu 3D
    // ---------------------------------------------------------------
    const FACE_ROTATIONS = {
        1: { x: 0, y: 0 },
        2: { x: 0, y: -90 },
        3: { x: 90, y: 0 },
        4: { x: -90, y: 0 },
        5: { x: 0, y: 90 },
        6: { x: 0, y: 180 },
    };

    function initDiceSize() {
        const diceCubes = document.querySelectorAll('.dice-3d');
        diceCubes.forEach((cube) => {
            if (cube && cube.parentElement) {
                const half = cube.parentElement.offsetWidth / 2;
                cube.style.setProperty('--dice-half', `${half}px`);
            }
        });
    }

    async function animateDiceRoll(finalValue, doubleDiceActive = false) {
        if (!dom.diceCube) return;
        
        const diceWrap = document.getElementById('dice-3d-wrap');
        const board = document.getElementById('game-board');
        if (!diceWrap || !board) return;

        dom.diceGlowWrap?.classList.add('ring-4', 'ring-accent-300', 'animate-pulse');
        
        // Sembunyikan dadu asli selama animasi (seolah terlempar)
        dom.diceCube.style.opacity = '0';

        // Buat clone dadu untuk dianimasikan terbang
        const clone = diceWrap.cloneNode(true);
        clone.id = 'dice-clone';
        clone.classList.add('dice-clone-wrap');
        
        // Hapus disabled overlay di clone jika ada
        const overlay = clone.querySelector('#dice-3d-disabled-overlay');
        if (overlay) overlay.remove();

        const innerCube = clone.querySelector('.dice-3d');
        innerCube.style.transition = 'none';
        innerCube.style.opacity = '1'; // Pastikan clone tidak tersembunyi

        // Tambah dynamic shadow
        const shadow = document.createElement('div');
        shadow.className = 'dice-dynamic-shadow';
        clone.appendChild(shadow);
        
        document.body.appendChild(clone);
        
        const startRect = diceWrap.getBoundingClientRect();
        const boardRect = board.getBoundingClientRect();
        
        const startX = startRect.left + startRect.width / 2 - 40;
        const startY = startRect.top + startRect.height / 2 - 40;
        const endX = boardRect.left + boardRect.width / 2 - 40;
        const endY = boardRect.top + boardRect.height / 2 - 40;
        
        const dx = endX - startX;
        const dy = endY - startY;

        const duration = 1400; // 1.4 detik total sesuai permintaan
        const startTime = performance.now();
        playRollSound();

        const target = FACE_ROTATIONS[finalValue] ?? FACE_ROTATIONS[1];
        const spins = 2; // putaran ekstra penuh supaya terasa "dilempar"
        
        const targetX = state.diceRotation.x + spins * 360 + (target.x - (state.diceRotation.x % 360));
        const targetY = state.diceRotation.y + spins * 360 + (target.y - (state.diceRotation.y % 360));
        const targetZ = Math.floor(2 + Math.random() * 2) * 360;
        
        const bounces = [
            { t: 0.60, height: -180 }, // Terbang awal (puncak)
            { t: 0.80, height: -50 },  // Pantulan 1
            { t: 0.92, height: -15 },  // Pantulan 2
            { t: 1.00, height: 0 }     // Berhenti
        ];

        return new Promise((resolve) => {
            let lastBounceIdx = 0;
            
            function frame(now) {
                const elapsed = now - startTime;
                let p = Math.min(elapsed / duration, 1);
                const easeOutQuart = 1 - Math.pow(1 - p, 4);
                let currentX = startX + dx * easeOutQuart;
                let currentY = startY + dy * easeOutQuart;
                
                // Skala dasar dadu mengecil dari 100% ke 50% selama terbang
                // memberi efek 3D "menjauh" masuk ke dalam papan
                const baseScale = 1.0 - 0.5 * easeOutQuart;
                
                let yOffset = 0;
                let scaleY = baseScale;
                let scaleX = baseScale;

                let segStartT = 0;
                let segEndT = bounces[0].t;
                let segHeight = bounces[0].height;
                let bIdx = 0;
                
                for (let i = 0; i < bounces.length; i++) {
                    if (p <= bounces[i].t) {
                        segEndT = bounces[i].t;
                        segHeight = bounces[i].height;
                        bIdx = i;
                        break;
                    }
                    segStartT = bounces[i].t;
                }
                
                const segP = (p - segStartT) / (segEndT - segStartT);
                // Rumus Parabola 4 * h * t * (1 - t)
                yOffset = 4 * segHeight * segP * (1 - segP);
                
                // Sound dan particle debu saat menyentuh papan
                if (bIdx > lastBounceIdx) {
                    playHitSound(0.4 / bIdx); // Volume berkurang ditiap pantulan
                    spawnParticles(clone, 'dust', Math.max(3, 8 - bIdx * 2));
                    lastBounceIdx = bIdx;
                }
                
                // Efek Squash & Stretch (kamera feel)
                if (segP > 0.85 && bIdx > 0) {
                    const squashAmt = (segP - 0.85) * (1 / 0.15) * (1 / bIdx);
                    scaleY = baseScale * (1 - 0.25 * squashAmt);
                    scaleX = baseScale * (1 + 0.15 * squashAmt);
                } else if (segP < 0.15 && bIdx > 0) {
                    const stretchAmt = (0.15 - segP) * (1 / 0.15) * (1 / bIdx);
                    scaleY = baseScale * (1 + 0.15 * stretchAmt);
                    scaleX = baseScale * (1 - 0.1 * stretchAmt);
                }
                
                currentY += yOffset;
                
                // Dinamika Bayangan
                const shadowScale = 1 - (yOffset / -250);
                const shadowBlur = 4 + (yOffset / -15);
                shadow.style.transform = `scale(${Math.max(0.3, shadowScale)})`;
                shadow.style.filter = `blur(${Math.max(2, shadowBlur)}px)`;
                shadow.style.opacity = Math.max(0.1, shadowScale);
                
                clone.style.left = `${currentX}px`;
                clone.style.top = `${currentY}px`;
                clone.style.transform = `scale(${scaleX}, ${scaleY})`;
                
                // Rotasi melambat (ease-out cubic)
                const rotP = 1 - Math.pow(1 - p, 3);
                
                // Interpolasi mulus dari rotasi sebelumnya ke target akhir
                const rotCurrentX = state.diceRotation.x + (targetX - state.diceRotation.x) * rotP;
                const rotCurrentY = state.diceRotation.y + (targetY - state.diceRotation.y) * rotP;
                
                // Putaran Z liar di udara, tapi memudar menjadi 0 saat mendarat agar tidak merusak orientasi wajah dadu
                const currentZ = targetZ * Math.max(0, 1 - Math.pow(p, 1.5));

                innerCube.style.transform = `rotateX(${rotCurrentX}deg) rotateY(${rotCurrentY}deg) rotateZ(${currentZ}deg)`;
                
                if (p < 1) {
                    requestAnimationFrame(frame);
                } else {
                    // Beri efek glow pada wrapper clone agar tidak merusak preserve-3d innerCube
                    clone.classList.add('tile-glow-bonus');
                    
                    state.diceRotation.x = targetX;
                    state.diceRotation.y = targetY;
                    
                    document.querySelectorAll('.dice-3d').forEach((cube) => {
                        cube.style.transition = 'none';
                        cube.style.transform = `rotateX(${state.diceRotation.x}deg) rotateY(${state.diceRotation.y}deg)`;
                    });

                    // Efek ×2 untuk double dice
                    if (doubleDiceActive) {
                        const multiplierBadge = document.createElement('div');
                        multiplierBadge.textContent = '×2';
                        multiplierBadge.className = 'absolute font-black text-4xl text-primary-500 drop-shadow-md z-50 animate-bounce';
                        multiplierBadge.style.left = `${endX + 20}px`;
                        multiplierBadge.style.top = `${endY - 40}px`;
                        multiplierBadge.style.textShadow = '0 0 10px white, 0 0 20px white';
                        
                        document.body.appendChild(multiplierBadge);
                        
                        // Hapus badge setelah selesai tampil
                        setTimeout(() => multiplierBadge.remove(), 2000);
                    }
                    
                    // Tunggu 2000ms supaya hasil terlihat lebih lama sebelum memindahkan pion
                    setTimeout(() => {
                        clone.remove();
                        dom.diceCube.style.opacity = '1';
                        dom.diceGlowWrap?.classList.remove('ring-4', 'ring-accent-300', 'animate-pulse');
                        resolve();
                    }, 2000);
                }
            }
            requestAnimationFrame(frame);
        });
    }
