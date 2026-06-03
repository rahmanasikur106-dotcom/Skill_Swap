<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// ── Sidebar: accepted swap partners ──────────────────────────
$conversation_sql = "
    SELECT
        u.user_id AS id,
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        latest.MessageText,
        latest.file_name,
        latest.Timestamp,
        latest.sender_id,
        COALESCE(unread.unread_count, 0) AS unread_count
    FROM swaps s
    JOIN users u ON u.user_id = CASE WHEN s.sender_id = ? THEN s.receiver_id ELSE s.sender_id END
    LEFT JOIN messages latest ON latest.MessageID = (
        SELECT MessageID FROM messages
        WHERE (sender_id = ? AND receiver_id = u.user_id)
           OR (sender_id = u.user_id AND receiver_id = ?)
        ORDER BY Timestamp DESC LIMIT 1
    )
    LEFT JOIN (
        SELECT sender_id, COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = ? AND IsRead = 0
        GROUP BY sender_id
    ) unread ON unread.sender_id = u.user_id
    WHERE (s.sender_id = ? OR s.receiver_id = ?) AND s.status = 'accepted'
    ORDER BY COALESCE(latest.Timestamp, s.created_at) DESC
";
$stmt = $conn->prepare($conversation_sql);
if (!$stmt) die("Sidebar Error: " . $conn->error);
$stmt->bind_param("iiiiii", $sender_id, $sender_id, $sender_id, $sender_id, $sender_id, $sender_id);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Active chat data ─────────────────────────────────────────
$receiver_name = "";
$messages      = [];
if ($receiver_id > 0) {
    $u = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE user_id=?");
    $u->bind_param("i", $receiver_id);
    $u->execute();
    if ($row = $u->get_result()->fetch_assoc()) $receiver_name = $row['name'];
    $u->close();

    $mr = $conn->prepare("UPDATE messages SET IsRead=1 WHERE sender_id=? AND receiver_id=? AND IsRead=0");
    $mr->bind_param("ii", $receiver_id, $sender_id);
    $mr->execute();
    $mr->close();

    $h = $conn->prepare("
        SELECT MessageID, sender_id, receiver_id, MessageText,
               file_path, file_name, file_type, IsEdited, Timestamp
        FROM messages
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY Timestamp ASC
    ");
    $h->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $h->execute();
    $messages = $h->get_result()->fetch_all(MYSQLI_ASSOC);
    $h->close();
}

$room_id = ($receiver_id > 0)
    ? ($sender_id < $receiver_id ? "room_{$sender_id}_{$receiver_id}" : "room_{$receiver_id}_{$sender_id}")
    : "";

// ── Swap gate ────────────────────────────────────────────────
$is_connected = false;
$swap_status  = null;
if ($receiver_id > 0) {
    $g = $conn->prepare("
        SELECT status FROM swaps
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at DESC LIMIT 1
    ");
    $g->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $g->execute();
    $swap_row     = $g->get_result()->fetch_assoc();
    $swap_status  = $swap_row['status'] ?? null;
    $is_connected = ($swap_status === 'accepted');
}

$last_msg_id = !empty($messages) ? (int)(end($messages)['MessageID'] ?? 0) : 0;

include 'includes/header.php';
?>
<style>
:root{--bg-main:#0f0f13;--bg-card:#16161d;--border-color:#2a2a35;--text-primary:#e0e0f0;--text-muted:#606070;--violet:#6366f1;--violet-grad:linear-gradient(135deg,#6366f1,#8b5cf6);}
.chat-layout{display:flex;height:calc(100vh - 70px);background:var(--bg-main);overflow:hidden;font-family:'Inter',sans-serif;}
.chat-sidebar{width:320px;border-right:1px solid var(--border-color);background:var(--bg-card);display:flex;flex-direction:column;}
.sidebar-header{padding:20px;border-bottom:1px solid var(--border-color);}
.sidebar-title{font-size:1.25rem;font-weight:800;color:var(--text-primary);display:flex;justify-content:space-between;align-items:center;}
.convo-list{flex:1;overflow-y:auto;}
.convo-item{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid rgba(42,42,53,0.5);text-decoration:none;transition:background 0.2s;position:relative;}
.convo-item:hover{background:rgba(99,102,241,0.04);}
.convo-item.active{background:rgba(99,102,241,0.08);border-left:3px solid var(--violet);}
.convo-avatar{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.1rem;flex-shrink:0;}
.convo-details{flex:1;min-width:0;}
.convo-name-row{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;}
.convo-name{font-weight:700;color:var(--text-primary);font-size:0.92rem;}
.convo-time{font-size:0.75rem;color:var(--text-muted);}
.convo-preview{font-size:0.82rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.unread-badge{background:#ec4899;color:white;font-size:0.72rem;font-weight:700;padding:2px 7px;border-radius:99px;position:absolute;right:20px;bottom:16px;}
.chat-main{flex:1;display:flex;flex-direction:column;background:#0c0c0f;}
.chat-main-header{padding:16px 24px;background:var(--bg-card);border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;}
.active-user-info{display:flex;align-items:center;gap:12px;}
.active-user-name{font-weight:800;color:var(--text-primary);font-size:1.05rem;}
.active-user-status{font-size:0.78rem;color:#10b981;display:flex;align-items:center;gap:4px;}
.header-actions{display:flex;gap:10px;}
.chat-body{flex:1;padding:24px;overflow-y:auto;display:flex;flex-direction:column;gap:16px;}
.msg-row{display:flex;width:100%;}
.msg-row.sent{justify-content:flex-end;}
.msg-row.received{justify-content:flex-start;}
.msg-bubble{max-width:60%;padding:12px 16px;border-radius:16px;font-size:0.92rem;line-height:1.45;word-wrap:break-word;}
.msg-row.sent .msg-bubble{background:var(--violet);color:white;border-bottom-right-radius:4px;}
.msg-row.received .msg-bubble{background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-bottom-left-radius:4px;}
.msg-meta{font-size:0.7rem;color:rgba(255,255,255,0.5);text-align:right;margin-top:4px;display:block;}
.msg-row.received .msg-meta{color:var(--text-muted);text-align:left;}
.msg-hint{font-size:0.62rem;color:rgba(255,255,255,0.3);text-align:right;margin-bottom:2px;}
.file-attachment{display:flex;align-items:center;gap:8px;background:rgba(0,0,0,0.15);padding:8px 12px;border-radius:8px;margin-top:6px;text-decoration:none;color:#cbd5e1;font-size:0.85rem;}
.file-attachment:hover{background:rgba(0,0,0,0.25);}
.chat-footer{padding:16px 24px;background:var(--bg-card);border-top:1px solid var(--border-color);}
.chat-form{display:flex;gap:12px;align-items:center;}
.input-wrap{flex:1;background:var(--bg-main);border:1px solid var(--border-color);border-radius:12px;display:flex;align-items:center;padding:4px 12px;}
.chat-input{flex:1;background:transparent;border:none;padding:10px 4px;color:var(--text-primary);font-size:0.92rem;outline:none;}
.chat-submit{background:var(--violet-grad);color:white;border:none;padding:11px 22px;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;transition:opacity 0.2s;}
.chat-submit:hover{opacity:0.9;}
.chat-submit:disabled{opacity:0.5;cursor:not-allowed;}
.empty-chat{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);text-align:center;padding:40px;}
.empty-icon{font-size:3.5rem;margin-bottom:16px;}
.call-banner{position:fixed;top:20px;left:50%;transform:translateX(-50%) translateY(-120px);background:#1e1e2f;border:2px solid #6366f1;box-shadow:0 10px 30px rgba(0,0,0,0.5);padding:16px 24px;border-radius:16px;display:flex;align-items:center;gap:20px;z-index:9999;transition:transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275);opacity:0;pointer-events:none;}
.call-banner.show{transform:translateX(-50%) translateY(0);opacity:1;pointer-events:auto;}
.call-banner-text{color:white;font-weight:600;font-size:0.95rem;}
.call-banner-btns{display:flex;gap:8px;}
.c-btn{border:none;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;font-size:0.85rem;}
.c-btn-accept{background:#10b981;color:white;}
.c-btn-decline{background:#ef4444;color:white;}
.msg-context-menu{position:fixed;z-index:8000;display:none;background:#1e1e2f;border:1px solid #2a2a35;border-radius:10px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.5);min-width:140px;}
.msg-context-menu button{display:flex;align-items:center;gap:8px;width:100%;padding:10px 16px;background:none;border:none;color:#e0e0f0;font-size:0.85rem;font-weight:600;cursor:pointer;text-align:left;transition:background 0.15s;}
.msg-context-menu button:hover{background:#2a2a40;}
.msg-context-menu button.danger{color:#ef4444;}
.msg-context-menu button.danger:hover{background:#2a1010;}
.edit-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9000;display:none;align-items:center;justify-content:center;}
.edit-modal-overlay.show{display:flex;}
.edit-modal{background:#16161d;border:1px solid #2a2a35;border-radius:16px;padding:28px;width:100%;max-width:440px;margin:20px;}
.edit-modal h3{color:#e0e0f0;font-size:1rem;font-weight:700;margin-bottom:14px;}
.edit-modal textarea{width:100%;background:#0f0f13;border:1px solid #2a2a35;border-radius:10px;padding:12px;color:#e0e0f0;font-size:0.92rem;resize:vertical;min-height:80px;outline:none;font-family:inherit;box-sizing:border-box;}
.edit-modal textarea:focus{border-color:#6366f1;}
.edit-modal-actions{display:flex;gap:10px;margin-top:14px;}
.edit-modal-actions button{flex:1;padding:10px;border-radius:9px;font-weight:700;font-size:0.88rem;cursor:pointer;border:none;}
.btn-save{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;}
.btn-cancel-edit{background:#1e1e28;color:#a0a0b0;border:1px solid #2a2a35 !important;}
</style>

<div class="msg-context-menu" id="msgContextMenu">
    <button id="ctxEdit">✏️ Edit</button>
    <button id="ctxDelete" class="danger">🗑️ Delete</button>
</div>
<div class="edit-modal-overlay" id="editModalOverlay">
    <div class="edit-modal">
        <h3>✏️ Edit Message</h3>
        <textarea id="editTextarea"></textarea>
        <div class="edit-modal-actions">
            <button class="btn-cancel-edit" id="cancelEdit">Cancel</button>
            <button class="btn-save" id="saveEdit">Save</button>
        </div>
    </div>
</div>
<div class="call-banner" id="incomingCallBanner">
    <div class="call-banner-text" id="incCallType">Incoming Call...</div>
    <div class="call-banner-btns">
        <button class="c-btn c-btn-accept" id="incAccept">Answer</button>
        <button class="c-btn c-btn-decline" id="incDecline">Decline</button>
    </div>
</div>

<div class="chat-layout">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">
                <span>Conversations</span>
                <span style="font-size:0.85rem;font-weight:400;color:var(--text-muted);"><?= count($conversations) ?> active</span>
            </div>
        </div>
        <div class="convo-list">
            <?php foreach ($conversations as $c):
                $initial    = strtoupper(substr($c['name'] ?? 'U', 0, 1));
                $isActive   = ($c['id'] == $receiver_id) ? 'active' : '';
                $timeStr    = !empty($c['Timestamp']) ? date('H:i', strtotime($c['Timestamp'])) : '';
                $prefix     = ($c['sender_id'] == $sender_id) ? 'You: ' : '';
                $displayMsg = htmlspecialchars($c['MessageText'] ?? '');
                if (!empty($c['file_name']) && empty($c['MessageText'])) $displayMsg = '📄 Attachment';
                if (empty($displayMsg)) $displayMsg = 'Say hello 👋';
            ?>
            <a href="chat.php?user_id=<?= $c['id'] ?>" class="convo-item <?= $isActive ?>">
                <div class="convo-avatar"><?= $initial ?></div>
                <div class="convo-details">
                    <div class="convo-name-row">
                        <div class="convo-name"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="convo-time"><?= $timeStr ?></div>
                    </div>
                    <div class="convo-preview"><?= $prefix . $displayMsg ?></div>
                </div>
                <?php if ($c['unread_count'] > 0): ?>
                    <div class="unread-badge"><?= $c['unread_count'] ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php if (empty($conversations)): ?>
                <div style="padding:40px 16px;text-align:center;color:#404050;font-size:0.82rem;line-height:1.8;">
                    No conversations yet.<br>
                    <a href="matchmaking.php" style="color:#6366f1;font-weight:600;text-decoration:none;">Find matches →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-main">
        <?php if ($receiver_id > 0 && !$is_connected): ?>
            <div class="empty-chat">
                <?php if ($swap_status === 'pending'): ?>
                    <div class="empty-icon">⏳</div>
                    <h3 style="color:#e0e0f0;margin-bottom:10px;">Request Pending</h3>
                    <p style="max-width:320px;line-height:1.6;">Messaging unlocks once they accept your swap request.</p>
                    <a href="swaps.php" style="margin-top:20px;background:#1e1e28;color:#f59e0b;border:1px solid rgba(245,158,11,0.3);padding:11px 26px;border-radius:10px;text-decoration:none;font-weight:700;">View Requests</a>
                <?php else: ?>
                    <div class="empty-icon">🔒</div>
                    <h3 style="color:#e0e0f0;margin-bottom:10px;">Not Connected</h3>
                    <p style="max-width:320px;line-height:1.6;">Send a swap request and have it accepted before messaging.</p>
                    <a href="matchmaking.php" style="margin-top:20px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:11px 26px;border-radius:10px;text-decoration:none;font-weight:700;">Find Matches →</a>
                <?php endif; ?>
            </div>

        <?php elseif ($receiver_id > 0): ?>
            <div class="chat-main-header">
                <div class="active-user-info">
                    <div class="convo-avatar" style="width:38px;height:38px;font-size:1rem;"><?= strtoupper(substr($receiver_name ?? 'U', 0, 1)) ?></div>
                    <div>
                        <div class="active-user-name"><?= htmlspecialchars($receiver_name) ?></div>
                        <div class="active-user-status" id="wsStatus">● Connected</div>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="call.php?user_id=<?= $receiver_id ?>&mode=audio&role=caller" target="_blank" class="btn btn-secondary btn-sm" style="font-size:1.1rem;padding:6px 12px;">📞</a>
                    <a href="call.php?user_id=<?= $receiver_id ?>&mode=video&role=caller" target="_blank" class="btn btn-primary btn-sm" style="font-size:1.1rem;padding:6px 12px;">📹</a>
                </div>
            </div>

            <div class="chat-body" id="chatBody">
                <?php
                $imgExts = ['jpg','jpeg','png','gif','webp','bmp'];
                foreach ($messages as $m):
                    $isMe    = ($m['sender_id'] == $sender_id);
                    $msgTime = date('H:i', strtotime($m['Timestamp']));
                    $ext     = strtolower(pathinfo($m['file_name'] ?? '', PATHINFO_EXTENSION));
                    $fp      = htmlspecialchars($m['file_path'] ?? '');
                    $fn      = htmlspecialchars($m['file_name'] ?? 'Download');
                ?>
                <div class="msg-row <?= $isMe ? 'sent' : 'received' ?>"
                     <?php if ($isMe): ?>
                     data-msg-id="<?= intval($m['MessageID']) ?>"
                     data-msg-text="<?= htmlspecialchars($m['MessageText'] ?? '', ENT_QUOTES) ?>"
                     data-receiver="<?= $receiver_id ?>"
                     <?php endif; ?>>
                    <div class="msg-bubble">
                        <?php if ($isMe && empty($m['file_path'])): ?>
                            <div class="msg-hint">hold to edit</div>
                        <?php endif; ?>
                        <?php if (!empty($m['MessageText'])): ?>
                            <div><?= nl2br(htmlspecialchars($m['MessageText'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($m['IsEdited'])): ?>
                            <span style="font-size:0.65rem;color:rgba(255,255,255,0.3);"> · edited</span>
                        <?php endif; ?>
                        <?php if (!empty($m['file_path'])): ?>
                            <?php if (in_array($ext, $imgExts)): ?>
                                <a href="<?= $fp ?>" target="_blank">
                                    <img src="<?= $fp ?>" alt="<?= $fn ?>"
                                         style="max-width:220px;max-height:180px;border-radius:8px;margin-top:6px;display:block;cursor:pointer;">
                                </a>
                            <?php else: ?>
                                <a href="<?= $fp ?>" download="<?= $fn ?>" target="_blank" class="file-attachment">📁 <?= $fn ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="msg-meta"><?= $msgTime ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-footer">
                <form class="chat-form" id="chatForm" action="send_message.php" method="POST">
                    <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                    <div class="input-wrap">
                        <input type="text" name="message" id="msgInput" class="chat-input" placeholder="Type your message here..." autocomplete="off">
                        <label style="cursor:pointer;padding:4px 8px;color:var(--text-muted);" title="Attach a file">
                            📎
                            <input type="file" name="chat_file" id="fileInput" style="display:none;"
                                   accept="image/*,application/pdf,.doc,.docx,.txt,.zip,.rar">
                        </label>
                    </div>
                    <button type="submit" class="chat-submit" id="sendBtn">Send</button>
                </form>
                <div id="filePreview" style="font-size:0.8rem;color:var(--violet);margin-top:6px;font-weight:600;"></div>
            </div>

        <?php else: ?>
            <div class="empty-chat">
                <div class="empty-icon">💬</div>
                <h3 style="color:#e0e0f0;margin-bottom:8px;">Your Conversations Hub</h3>
                <p>Select a conversation or find new skill matches.</p>
                <a href="matchmaking.php" style="margin-top:16px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 22px;border-radius:10px;text-decoration:none;font-weight:700;">Find Matches →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const SENDER_ID   = <?= $sender_id ?>;
const RECEIVER_ID = <?= $receiver_id ?>;
const ROOM_ID     = "<?= $room_id ?>";
const RNAME       = "<?= htmlspecialchars($receiver_name, ENT_QUOTES) ?>";
const imgExts     = ['jpg','jpeg','png','gif','webp','bmp'];

const cb          = document.getElementById('chatBody');
const fileInput   = document.getElementById('fileInput');
const filePreview = document.getElementById('filePreview');
const msgInput    = document.getElementById('msgInput');
const sendBtn     = document.getElementById('sendBtn');

if (cb) cb.scrollTop = cb.scrollHeight;

// File preview
fileInput?.addEventListener('change', function() {
    filePreview.textContent = this.files[0]?.name ? `📎 Staged: ${this.files[0].name}` : '';
});

// Form submit — route files to send_file.php
document.getElementById('chatForm')?.addEventListener('submit', function(e) {
    if (!fileInput?.files?.length) return; // no file: normal POST
    e.preventDefault();
    const fd = new FormData();
    fd.append('receiver_id', RECEIVER_ID);
    fd.append('chat_file',   fileInput.files[0]);
    if (msgInput.value.trim()) fd.append('caption', msgInput.value.trim());
    sendBtn.disabled = true; sendBtn.textContent = 'Sending…';
    fetch('send_file.php', { method:'POST', body:fd })
        .then(() => { fileInput.value=''; msgInput.value=''; filePreview.textContent=''; location.reload(); })
        .catch(() => { sendBtn.disabled=false; sendBtn.textContent='Send'; alert('Upload failed.'); });
});

// Polling
let pollActive = true;
let lastMsgId  = <?= $last_msg_id ?>;

function buildMsgHTML(m) {
    const isMe = (m.sender_id == SENDER_ID);
    const ext  = (m.file_name||'').split('.').pop().toLowerCase();
    const t    = new Date((m.Timestamp||'').replace(' ','T'));
    const time = String(t.getHours()).padStart(2,'0')+':'+String(t.getMinutes()).padStart(2,'0');
    let inner  = '';
    if (m.MessageText) inner += `<div>${m.MessageText.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/\n/g,'<br>')}</div>`;
    if (m.file_path) {
        if (imgExts.includes(ext)) {
            inner += `<a href="${m.file_path}" target="_blank"><img src="${m.file_path}" style="max-width:220px;max-height:180px;border-radius:8px;margin-top:6px;display:block;"></a>`;
        } else {
            inner += `<a href="${m.file_path}" download target="_blank" class="file-attachment">📁 ${m.file_name||'Download'}</a>`;
        }
    }
    inner += `<span class="msg-meta">${time}</span>`;
    const attrs = isMe ? ` data-msg-id="${m.MessageID}" data-msg-text="${(m.MessageText||'').replace(/"/g,'&quot;')}" data-receiver="${RECEIVER_ID}"` : '';
    return `<div class="msg-row ${isMe?'sent':'received'}"${attrs}><div class="msg-bubble">${inner}</div></div>`;
}

async function pollMessages() {
    if (!RECEIVER_ID || !pollActive || !cb) return;
    try {
        const res  = await fetch(`poll_messages.php?receiver_id=${RECEIVER_ID}&last_id=${lastMsgId}`, {cache:'no-store'});
        const data = await res.json();
        if (data.messages?.length) {
            data.messages.forEach(m => { lastMsgId = Math.max(lastMsgId, parseInt(m.MessageID)); cb.insertAdjacentHTML('beforeend', buildMsgHTML(m)); });
            cb.scrollTop = cb.scrollHeight;
        }
        const ws = document.getElementById('wsStatus');
        if (ws) { ws.textContent='● Connected'; ws.style.color='#10b981'; }
    } catch(e) {
        const ws = document.getElementById('wsStatus');
        if (ws) { ws.textContent='● Reconnecting…'; ws.style.color='#ef4444'; }
    }
}
if (pollActive && RECEIVER_ID) setInterval(pollMessages, 3000);

// CRUD context menu
const ctxMenu     = document.getElementById('msgContextMenu');
const editOverlay = document.getElementById('editModalOverlay');
const editTA      = document.getElementById('editTextarea');
let activeRow = null;

function showCtx(x,y,row){activeRow=row;ctxMenu.style.cssText=`left:${x}px;top:${y}px;display:block;`;}
function hideCtx(){ctxMenu.style.display='none';}

cb?.addEventListener('contextmenu', e=>{const r=e.target.closest('.msg-row[data-msg-id]');if(!r)return;e.preventDefault();showCtx(e.clientX,e.clientY,r);});
let pt;
cb?.addEventListener('touchstart',e=>{const r=e.target.closest('.msg-row[data-msg-id]');if(!r)return;pt=setTimeout(()=>{const t=e.touches[0];showCtx(t.clientX,t.clientY,r);},500);});
cb?.addEventListener('touchend',()=>clearTimeout(pt));
document.addEventListener('click', hideCtx);

document.getElementById('ctxEdit')?.addEventListener('click',()=>{
    if(!activeRow)return;
    editTA.value=activeRow.dataset.msgText||'';
    editOverlay.classList.add('show');
    editTA.focus(); hideCtx();
});
document.getElementById('cancelEdit')?.addEventListener('click',()=>{editOverlay.classList.remove('show');activeRow=null;});
editOverlay?.addEventListener('click',e=>{if(e.target===editOverlay){editOverlay.classList.remove('show');activeRow=null;}});
document.getElementById('saveEdit')?.addEventListener('click',()=>{
    if(!activeRow)return;
    const t=editTA.value.trim();if(!t)return;
    const fd=new FormData();fd.append('message_id',activeRow.dataset.msgId);fd.append('receiver_id',activeRow.dataset.receiver);fd.append('new_message',t);
    fetch('edit_message.php',{method:'POST',body:fd}).then(()=>{
        const d=activeRow.querySelector('.msg-bubble > div');if(d)d.innerHTML=t.replace(/\n/g,'<br>');
        activeRow.dataset.msgText=t;editOverlay.classList.remove('show');activeRow=null;
    }).catch(console.error);
});
document.getElementById('ctxDelete')?.addEventListener('click',()=>{
    if(!activeRow||!confirm('Delete this message?')){hideCtx();return;}
    const fd=new FormData();fd.append('message_id',activeRow.dataset.msgId);fd.append('receiver_id',activeRow.dataset.receiver);
    fetch('delete_message.php',{method:'POST',body:fd}).then(()=>{activeRow.remove();hideCtx();}).catch(console.error);
});

// Call signals
let lastSigId=0;
const callBanner=document.getElementById('incomingCallBanner');
const incCallType=document.getElementById('incCallType');
async function pollCallSignals(){
    if(!RECEIVER_ID)return;
    try{
        const res=await fetch(`signal.php?room_id=${ROOM_ID}&since=${lastSigId}`,{cache:'no-store'});
        const data=await res.json();
        for(const sig of(data.signals||[])){
            lastSigId=Math.max(lastSigId,sig.id);
            if(sig.type==='ring'&&sig.from_user==RECEIVER_ID){incCallType.textContent=`Incoming call from ${RNAME}…`;callBanner.classList.add('show');}
            if(sig.type==='hangup')callBanner.classList.remove('show');
        }
    }catch(e){}
}
if(RECEIVER_ID>0)setInterval(pollCallSignals,2000);
document.getElementById('incAccept')?.addEventListener('click',()=>{callBanner.classList.remove('show');window.open(`call.php?user_id=${RECEIVER_ID}&mode=video&role=callee`,'_blank');});
document.getElementById('incDecline')?.addEventListener('click',async()=>{
    callBanner.classList.remove('show');
    try{await fetch('signal.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`room_id=${ROOM_ID}&type=hangup&payload={}`});}catch(e){}
});
</script>

<?php include 'includes/footer.php'; ?>