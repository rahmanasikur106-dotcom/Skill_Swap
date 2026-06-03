<?php
/**
 * call.php
 * ────────
 * WebRTC peer-to-peer audio/video call page.
 *
 * URL params:
 * user_id  – the other user
 * mode     – "video" (default) | "audio"
 * role     – "caller" | "callee"  (auto-set by chat.php links)
 */

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$my_id       = intval($_SESSION['user_id']);
$other_id    = intval($_GET['user_id'] ?? 0);
$mode        = ($_GET['mode'] ?? 'video') === 'audio' ? 'audio' : 'video';
$role        = ($_GET['role'] ?? 'caller') === 'callee' ? 'callee' : 'caller';

if ($other_id <= 0 || $other_id === $my_id) {
    header("Location: chat.php");
    exit();
}

// FIXED: Concat first_name and last_name as name to match your updated schema structure
$us = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ?");
$us->bind_param("i", $other_id);
$us->execute();
$other = $us->get_result()->fetch_assoc();
$other_name = htmlspecialchars($other['name'] ?? 'User');

// Deterministic room ID (lower id first)
$room_id = 'room_' . min($my_id, $other_id) . '_' . max($my_id, $other_id);

include 'includes/header.php';
?>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
footer, nav { display: none !important; }

body { background: #0a0a0f; font-family: 'Segoe UI', system-ui, sans-serif; }

.call-shell {
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: #0a0a0f;
}

/* Video containers */
#remoteVideo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #111;
}

#localVideo {
    position: absolute;
    bottom: 110px;
    right: 24px;
    width: 160px;
    height: 110px;
    border-radius: 14px;
    object-fit: cover;
    border: 2px solid rgba(99,102,241,0.6);
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    background: #1e1e28;
    z-index: 10;
}

/* Overlay when no remote stream yet */
.call-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, #0f0f1a 0%, #1a0a2e 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    z-index: 5;
    transition: opacity 0.4s;
}
.call-overlay.hidden { opacity: 0; pointer-events: none; }

.call-avatar {
    width: 100px;
    height: 100px;
    border-radius: 28px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 0 0 12px rgba(99,102,241,0.15), 0 0 0 24px rgba(99,102,241,0.07);
    animation: pulse 2s ease infinite;
}
@keyframes pulse {
    0%,100% { box-shadow: 0 0 0 12px rgba(99,102,241,0.15), 0 0 0 24px rgba(99,102,241,0.07); }
    50%      { box-shadow: 0 0 0 16px rgba(99,102,241,0.2),  0 0 0 32px rgba(99,102,241,0.1); }
}

.call-name   { color: #fff;    font-size: 1.6rem; font-weight: 700; }
.call-status { color: #818cf8; font-size: 0.9rem; letter-spacing: 0.05em; }

/* Controls bar */
.call-controls {
    position: absolute;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 16px;
    align-items: center;
    z-index: 20;
    background: rgba(15,15,20,0.7);
    border: 1px solid #2a2a35;
    border-radius: 999px;
    padding: 10px 22px;
    backdrop-filter: blur(12px);
}

.ctrl-btn {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s, opacity 0.15s;
}
.ctrl-btn:hover { transform: scale(1.08); }
.ctrl-btn:active { transform: scale(0.96); }

.btn-mute   { background: #1e1e28; color: #d0d0e8; }
.btn-camera { background: #1e1e28; color: #d0d0e8; }
.btn-hang   { background: #ef4444; color: #fff; width: 60px; height: 60px; font-size: 1.5rem; }
.btn-muted   { background: #3f0f0f; color: #f87171; }
.btn-nocam   { background: #3f0f0f; color: #f87171; }

/* Status badge top-left */
.call-badge {
    position: absolute;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15,15,20,0.7);
    border: 1px solid #2a2a35;
    border-radius: 999px;
    padding: 6px 18px;
    font-size: 0.8rem;
    color: #a0a0b0;
    z-index: 20;
    backdrop-filter: blur(8px);
}
.call-badge span { color: #22c55e; font-weight: 700; }

#errBanner {
    position: absolute;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    background: #2a1010;
    border: 1px solid #7f1d1d;
    color: #fca5a5;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 0.85rem;
    z-index: 30;
    display: none;
}
</style>

<div class="call-shell">

    <video id="remoteVideo" autoplay playsinline></video>

    <video id="localVideo"  autoplay playsinline muted></video>

    <div class="call-overlay" id="overlay">
        <div class="call-avatar"><?= strtoupper(substr($other['name'] ?? 'U', 0, 1)) ?></div>
        <div class="call-name"><?= $other_name ?></div>
        <div class="call-status" id="overlayStatus">
            <?= $role === 'caller' ? '📞 Calling…' : '📲 Connecting…' ?>
        </div>
    </div>

    <div id="errBanner"></div>

    <div class="call-badge">
        <?= $mode === 'video' ? '📹 Video Call' : '🎙️ Voice Call' ?> &nbsp;·&nbsp;
        <span id="callTimer">00:00</span>
    </div>

    <div class="call-controls">
        <button class="ctrl-btn btn-mute"   id="btnMute"   title="Mute">🎤</button>
        <?php if ($mode === 'video'): ?>
        <button class="ctrl-btn btn-camera" id="btnCamera" title="Camera">📷</button>
        <?php endif; ?>
        <button class="ctrl-btn btn-hang"   id="btnHang"   title="End call">📵</button>
    </div>

</div>

<script>
// ── Config ─────────────────────────────────────────────────
const MY_ID      = <?= $my_id ?>;
const OTHER_ID   = <?= $other_id ?>;
const ROOM_ID    = <?= json_encode($room_id) ?>;
const ROLE       = <?= json_encode($role) ?>;
const MODE       = <?= json_encode($mode) ?>;

const ICE_SERVERS = { iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' }
]};

let pc          = null;
let localStream = null;
let muted       = false;
let camOff      = false;
let callStarted = false;
let timerHandle = null;
let callSecs    = 0;
let lastSigId   = 0;
let pollHandle  = null;

// ── DOM refs ───────────────────────────────────────────────
const remoteVideo  = document.getElementById('remoteVideo');
const localVideo   = document.getElementById('localVideo');
const overlay      = document.getElementById('overlay');
const overlayStatus= document.getElementById('overlayStatus');
const errBanner    = document.getElementById('errBanner');
const btnMute      = document.getElementById('btnMute');
const btnCamera    = document.getElementById('btnCamera');
const timerEl      = document.getElementById('callTimer');

// ── Helpers ────────────────────────────────────────────────
function showErr(msg) {
    errBanner.textContent = '⚠️ ' + msg;
    errBanner.style.display = 'block';
    setTimeout(() => { errBanner.style.display = 'none'; }, 5000);
}

async function sendSignal(type, payload) {
    try {
        await fetch('signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_id: ROOM_ID, to_user: OTHER_ID, type, payload })
        });
    } catch(e) { console.error('Signal send failed', e); }
}

async function pollSignals() {
    try {
        const res  = await fetch(`signal.php?room_id=${ROOM_ID}&since=${lastSigId}`, { cache: 'no-store' });
        const data = await res.json();
        for (const sig of (data.signals || [])) {
            lastSigId = Math.max(lastSigId, sig.id);
            await handleSignal(sig);
        }
    } catch(e) { /* network blip, try again */ }
}

async function handleSignal(sig) {
    if (!pc) return;
    const p = sig.payload;
    if (sig.type === 'offer') {
        overlayStatus.textContent = 'Connecting…';
        await pc.setRemoteDescription(new RTCSessionDescription(p));
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        sendSignal('answer', answer);
    } else if (sig.type === 'answer') {
        overlayStatus.textContent = 'Connected!';
        await pc.setRemoteDescription(new RTCSessionDescription(p));
    } else if (sig.type === 'ice') {
        try { await pc.addIceCandidate(new RTCIceCandidate(p)); } catch(e) {}
    } else if (sig.type === 'hangup') {
        endCall(false);
    }
}

// ── Timer ──────────────────────────────────────────────────
function startTimer() {
    timerHandle = setInterval(() => {
        callSecs++;
        const m = String(Math.floor(callSecs/60)).padStart(2,'0');
        const s = String(callSecs % 60).padStart(2,'0');
        timerEl.textContent = `${m}:${s}`;
    }, 1000);
}

// ── Start call ─────────────────────────────────────────────
async function startCall() {
    try {
        const constraints = {
            audio: true,
            video: MODE === 'video' ? { width:1280, height:720, facingMode:'user' } : false
        };
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        localVideo.srcObject = localStream;

        pc = new RTCPeerConnection(ICE_SERVERS);

        localStream.getTracks().forEach(t => pc.addTrack(t, localStream));

        pc.ontrack = (e) => {
            if (e.streams[0]) {
                remoteVideo.srcObject = e.streams[0];
                overlay.classList.add('hidden');
                if (!callStarted) { callStarted = true; startTimer(); }
            }
        };

        pc.onicecandidate = (e) => {
            if (e.candidate) sendSignal('ice', e.candidate.toJSON());
        };

        pc.onconnectionstatechange = () => {
            if (['failed','disconnected','closed'].includes(pc.connectionState)) {
                showErr('Connection lost.');
            }
        };

        if (ROLE === 'caller') {
            overlayStatus.textContent = '📞 Calling…';
            sendSignal('ring', {});
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            sendSignal('offer', offer);
        }

        // Start polling for signals
        pollHandle = setInterval(pollSignals, 1000);

    } catch(err) {
        showErr('Could not access camera/mic: ' + err.message);
        console.error(err);
    }
}

// ── End call ───────────────────────────────────────────────
function endCall(notify = true) {
    if (notify) sendSignal('hangup', {});
    clearInterval(timerHandle);
    clearInterval(pollHandle);
    localStream?.getTracks().forEach(t => t.stop());
    pc?.close();
    window.location.href = 'chat.php?user_id=' + OTHER_ID;
}

// ── Controls ───────────────────────────────────────────────
btnMute?.addEventListener('click', () => {
    muted = !muted;
    localStream?.getAudioTracks().forEach(t => t.enabled = !muted);
    btnMute.textContent  = muted ? '🔇' : '🎤';
    btnMute.className    = 'ctrl-btn ' + (muted ? 'btn-muted' : 'btn-mute');
});

btnCamera?.addEventListener('click', () => {
    camOff = !camOff;
    localStream?.getVideoTracks().forEach(t => t.enabled = !camOff);
    btnCamera.textContent = camOff ? '🚫' : '📷';
    btnCamera.className   = 'ctrl-btn ' + (camOff ? 'btn-nocam' : 'btn-camera');
});

document.getElementById('btnHang').addEventListener('click', () => endCall(true));

// ── Boot ───────────────────────────────────────────────────
startCall();
</script>

<?php include 'includes/footer.php'; ?>