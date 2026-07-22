// ============================================================
// LinguaMax — Core JavaScript
// ============================================================

const SITE_URL = window.location.origin + '/English_web';

// ── Toast Notifications ──────────────────────────────────────
function showToast(message, icon = '✅', type = '') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span style="font-size:1.3rem;">${icon}</span> ${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function showBadgeToast(icon, name) {
    showToast(`เหรียญใหม่! ${name}`, icon, 'toast-badge');
    launchConfetti();
}

// ── Confetti Effect ──────────────────────────────────────────
function launchConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);

    const colors = ['#6C63FF', '#FF6B9D', '#FFB347', '#2ED573', '#45B7D1', '#FFD700'];
    for (let i = 0; i < 50; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = Math.random() * 100 + '%';
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDelay = Math.random() * 2 + 's';
        piece.style.animationDuration = (2 + Math.random() * 2) + 's';
        piece.style.width = (5 + Math.random() * 10) + 'px';
        piece.style.height = (5 + Math.random() * 10) + 'px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        piece.style.transform = `rotate(${Math.random() * 360}deg)`;
        container.appendChild(piece);
    }

    setTimeout(() => container.remove(), 4000);
}

// ── AJAX Helper ──────────────────────────────────────────────
async function apiCall(endpoint, data = {}) {
    try {
        const response = await fetch(`${SITE_URL}/api/${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return { error: 'Network error' };
    }
}

// ── Format Time ──────────────────────────────────────────────
function formatTime(seconds) {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

// ── Shuffle Array ────────────────────────────────────────────
function shuffleArray(arr) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

// ── Animate Number ───────────────────────────────────────────
function animateNumber(element, target, duration = 1000) {
    const start = parseInt(element.textContent) || 0;
    const diff = target - start;
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = Math.round(start + diff * eased);
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

// ── Add ripple effect ────────────────────────────────────────
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn');
    if (!btn) return;
    const ripple = document.createElement('span');
    ripple.style.cssText = `
        position:absolute;border-radius:50%;background:rgba(255,255,255,0.3);
        pointer-events:none;transform:scale(0);animation:ripple 0.6s ease-out;
        width:100px;height:100px;left:${e.offsetX - 50}px;top:${e.offsetY - 50}px;
    `;
    btn.style.position = 'relative';
    btn.style.overflow = 'hidden';
    btn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
});

// Add ripple keyframes
const style = document.createElement('style');
style.textContent = '@keyframes ripple{to{transform:scale(4);opacity:0;}}';
document.head.appendChild(style);
