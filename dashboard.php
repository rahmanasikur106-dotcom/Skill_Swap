<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// Fetch user name
$u = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$name = $user['name'] ?? 'User';

// Unread messages count
$unread_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id = ? AND IsRead = 0");
$unread_stmt->bind_param("i", $user_id);
$unread_stmt->execute();
$unread = $unread_stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// Pending swaps count
$swap_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM swaps WHERE receiver_id = ? AND status = 'pending'");
$swap_stmt->bind_param("i", $user_id);
$swap_stmt->execute();
$pending_swaps = $swap_stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// Pending sessions count (sessions where I am invited and haven't responded)
$sess_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM sessions WHERE user2_id = ? AND status = 'Pending'");
$sess_stmt->bind_param("i", $user_id);
$sess_stmt->execute();
$pending_sessions = $sess_stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// Fetch all upcoming events with join status and participant count
$events_stmt = $conn->prepare("
    SELECT 
        e.event_id,
        e.location,
        e.date_time,
        e.creator_id,
        CONCAT(u.first_name, ' ', u.last_name) AS creator_name,
        COUNT(DISTINCT ep.id) AS participant_count,
        MAX(CASE WHEN ep.user_id = ? THEN 1 ELSE 0 END) AS has_joined
    FROM events e
    JOIN users u ON u.user_id = e.creator_id
    LEFT JOIN event_participant ep ON ep.event_id = e.event_id
    WHERE e.date_time >= NOW()
    GROUP BY e.event_id
    ORDER BY e.date_time ASC
");
$events_stmt->bind_param("i", $user_id);
$events_stmt->execute();
$events = $events_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<style>
.db-wrap {
    min-height: calc(100vh - 70px);
    background: #0d0d12;
    padding: 40px 5%;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.db-greeting { margin-bottom: 36px; }
.db-greeting h1 { font-size: 1.8rem; font-weight: 800; color: #e0e0f0; margin-bottom: 4px; }
.db-greeting p { color: #505060; font-size: 0.9rem; }
.db-greeting span { background: linear-gradient(135deg,#6366f1,#8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

/* Top 4 cards */
.db-grid-top {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 18px;
}

/* Bottom 3 cards */
.db-grid-bottom {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 18px;
    margin-bottom: 40px;
}

.db-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 20px;
    padding: 28px 24px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.db-card:hover { border-color: #6366f1; transform: translateY(-3px); box-shadow: 0 12px 30px rgba(99,102,241,0.12); }
.db-card.highlight { background: linear-gradient(135deg,#6366f1,#8b5cf6); border-color: transparent; }
.db-card.highlight:hover { box-shadow: 0 12px 30px rgba(99,102,241,0.35); }

.db-card-icon { font-size: 2rem; line-height: 1; }
.db-card-title { font-size: 1.1rem; font-weight: 800; color: #e0e0f0; line-height: 1.2; }
.db-card.highlight .db-card-title { color: #fff; }
.db-card-desc { font-size: 0.82rem; color: #505060; line-height: 1.55; flex: 1; }
.db-card.highlight .db-card-desc { color: rgba(255,255,255,0.75); }

.db-card-btn {
    display: inline-block; padding: 9px 20px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 700; text-decoration: none;
    margin-top: 4px; width: fit-content; transition: opacity 0.15s;
}
.db-card-btn:hover { opacity: 0.85; }
.btn-violet  { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
.btn-teal    { background: linear-gradient(135deg,#06b6d4,#0891b2); color: #fff; }
.btn-orange  { background: linear-gradient(135deg,#f59e0b,#ea580c); color: #fff; }
.btn-dark    { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; }
.btn-white   { background: #fff; color: #6366f1; }

.db-badge {
    position: absolute; top: 18px; right: 18px;
    background: #ec4899; color: #fff;
    font-size: 0.7rem; font-weight: 700;
    padding: 3px 9px; border-radius: 99px;
}

/* ── Events Section ─────────────────────────── */
.events-section { margin-top: 10px; }
.events-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 18px;
}
.events-title { font-size: 1.2rem; font-weight: 800; color: #e0e0f0; }
.btn-create-event {
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: #fff; padding: 9px 20px; border-radius: 10px;
    text-decoration: none; font-size: 0.82rem; font-weight: 700;
    transition: opacity 0.2s;
}
.btn-create-event:hover { opacity: 0.85; }

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.event-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 16px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color 0.2s, transform 0.15s;
}
.event-card:hover { border-color: #6366f1; transform: translateY(-2px); }

.event-card-location {
    font-size: 1rem; font-weight: 700; color: #e0e0f0;
    display: flex; align-items: center; gap: 6px;
}
.event-card-time { font-size: 0.8rem; color: #6366f1; font-weight: 600; }
.event-card-creator { font-size: 0.78rem; color: #505060; }
.event-card-participants {
    font-size: 0.78rem; color: #a0a0b0;
    display: flex; align-items: center; gap: 5px;
}

.event-card-actions { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
.ev-btn {
    padding: 7px 14px; border-radius: 8px; font-size: 0.78rem;
    font-weight: 700; text-decoration: none; border: none; cursor: pointer;
    transition: opacity 0.2s; display: inline-block; text-align: center;
}
.ev-btn:hover { opacity: 0.85; }
.ev-btn-join    { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
.ev-btn-leave   { background: #1e1e28; color: #ef4444; border: 1px solid #3f1010; }
.ev-btn-parts   { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; }
.ev-btn-delete  { background: #1e1e28; color: #ef4444; border: 1px solid #3f1010; }

.events-empty {
    grid-column: 1/-1; text-align: center;
    padding: 50px 20px; color: #404050;
}
.events-empty .icon { font-size: 2.5rem; margin-bottom: 12px; }

@media (max-width: 900px) {
    .db-grid-top    { grid-template-columns: repeat(2,1fr); }
    .db-grid-bottom { grid-template-columns: 1fr; }
}
@media (max-width: 500px) {
    .db-grid-top { grid-template-columns: 1fr; }
}
</style>

<div class="db-wrap">
    <div class="db-greeting">
        <h1>Welcome back, <span><?= htmlspecialchars($name) ?></span> 👋</h1>
        <p>Here's everything in one place — manage your skills, swaps, and conversations.</p>
    </div>

    <!-- Top 4 cards -->
    <div class="db-grid-top">
        <a href="profile.php" class="db-card">
            <div class="db-card-icon">🧑‍💼</div>
            <div class="db-card-title">Manage Skills</div>
            <div class="db-card-desc">List what you can teach and what you want to learn from others.</div>
            <span class="db-card-btn btn-violet">Edit Profile →</span>
        </a>

        <a href="discovery.php" class="db-card">
            <div class="db-card-icon">🔍</div>
            <div class="db-card-title">Discover People</div>
            <div class="db-card-desc">Find community members who have the exact skills you need.</div>
            <span class="db-card-btn btn-teal">Explore →</span>
        </a>

        <a href="chat.php" class="db-card">
            <?php if ($unread > 0): ?>
                <div class="db-badge"><?= $unread ?></div>
            <?php endif; ?>
            <div class="db-card-icon">💬</div>
            <div class="db-card-title">Messages</div>
            <div class="db-card-desc">Chat with your swap partners and share files or start a call.</div>
            <span class="db-card-btn btn-orange">Open Chat →</span>
        </a>

        <a href="swaps.php" class="db-card">
            <?php if ($pending_swaps > 0): ?>
                <div class="db-badge"><?= $pending_swaps ?></div>
            <?php endif; ?>
            <div class="db-card-icon">🤝</div>
            <div class="db-card-title">Swap Requests</div>
            <div class="db-card-desc">View and manage all incoming and outgoing swap requests.</div>
            <span class="db-card-btn btn-dark">View Swaps →</span>
        </a>
    </div>

    <!-- Bottom 3 cards -->
    <div class="db-grid-bottom">
        <a href="filter_messages.php" class="db-card">
            <div class="db-card-icon">📋</div>
            <div class="db-card-title">Filter Messages</div>
            <div class="db-card-desc">Quickly find unread or read messages across all conversations.</div>
            <span class="db-card-btn btn-dark">Filter →</span>
        </a>

        <a href="session_list.php" class="db-card">
            <?php if ($pending_sessions > 0): ?>
                <div class="db-badge"><?= $pending_sessions ?></div>
            <?php endif; ?>
            <div class="db-card-icon">📅</div>
            <div class="db-card-title">My Sessions</div>
            <div class="db-card-desc">View, accept or reject scheduled skill-swap sessions with your partners.</div>
            <span class="db-card-btn btn-teal">View Sessions →</span>
        </a>

        <a href="discovery.php" class="db-card highlight">
            <div class="db-card-icon">⚡</div>
            <div class="db-card-title">Quick Swap</div>
            <div class="db-card-desc">Jump straight into the community and find your next match.</div>
            <span class="db-card-btn btn-white">Find a Match →</span>
        </a>
    </div>

    <!-- Events Section -->
    <div class="events-section">
        <div class="events-header">
            <div class="events-title">📅 Upcoming Events</div>
            <a href="create_event.php" class="btn-create-event">+ Create Event</a>
        </div>

        <div class="events-grid">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $ev): 
                    $is_creator  = ($ev['creator_id'] == $user_id);
                    $has_joined  = (bool)$ev['has_joined'];
                    $event_date  = date('D d M Y, H:i', strtotime($ev['date_time']));
                ?>
                <div class="event-card">
                    <div class="event-card-location">📍 <?= htmlspecialchars($ev['location']) ?></div>
                    <div class="event-card-time">⏰ <?= $event_date ?></div>
                    <div class="event-card-creator">
                        Hosted by <strong style="color:#a0a0b0;"><?= htmlspecialchars($ev['creator_name']) ?></strong>
                        <?= $is_creator ? ' <span style="color:#6366f1;">(You)</span>' : '' ?>
                    </div>
                    <div class="event-card-participants">
                        👥 <?= $ev['participant_count'] ?> participant<?= $ev['participant_count'] != 1 ? 's' : '' ?>
                    </div>
                    <div class="event-card-actions">
                        <?php if (!$has_joined): ?>
                            <a href="join_event.php?event_id=<?= $ev['event_id'] ?>" class="ev-btn ev-btn-join">Join</a>
                        <?php else: ?>
                            <a href="leave_event.php?event_id=<?= $ev['event_id'] ?>" class="ev-btn ev-btn-leave">Leave</a>
                        <?php endif; ?>

                        <a href="participants.php?event_id=<?= $ev['event_id'] ?>" class="ev-btn ev-btn-parts">👥 Participants</a>

                        <?php if ($is_creator): ?>
                            <a href="delete_event.php?event_id=<?= $ev['event_id'] ?>"
                               class="ev-btn ev-btn-delete"
                               onclick="return confirm('Delete this event?')">🗑 Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="events-empty">
                    <div class="icon">📅</div>
                    <p>No upcoming events yet. <a href="create_event.php" style="color:#6366f1;font-weight:700;">Create one!</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>