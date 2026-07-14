<?php
include '../backend/db.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$me = $_SESSION['user_id'];
$to = isset($_GET['to']) ? intval($_GET['to']) : 0;

// Send message
if(isset($_POST['send_msg']) && $to > 0 && !empty(trim($_POST['body']))) {
    $body = mysqli_real_escape_string($conn, trim($_POST['body']));
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, body, is_read, created_at) VALUES ($me, $to, '$body', 0, NOW())");
    header("Location: messages.php?to=$to"); exit();
}

// Mark messages as read
if($to > 0) {
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE sender_id = $to AND receiver_id = $me AND is_read = 0");
}

// Get all conversations
$convos = mysqli_query($conn, "
    SELECT u.user_id, u.full_name, u.profile_pic, u.role_id,
           (SELECT body FROM messages WHERE (sender_id=u.user_id AND receiver_id=$me) OR (sender_id=$me AND receiver_id=u.user_id) ORDER BY created_at DESC LIMIT 1) as last_msg,
           (SELECT created_at FROM messages WHERE (sender_id=u.user_id AND receiver_id=$me) OR (sender_id=$me AND receiver_id=u.user_id) ORDER BY created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM messages WHERE sender_id=u.user_id AND receiver_id=$me AND is_read=0) as unread
    FROM users u
    WHERE u.user_id IN (
        SELECT DISTINCT CASE WHEN sender_id=$me THEN receiver_id ELSE sender_id END
        FROM messages WHERE sender_id=$me OR receiver_id=$me
    )
    ORDER BY last_time DESC
");

$chat_user = null;
if($to > 0) {
    $cu = mysqli_query($conn, "SELECT user_id, full_name, profile_pic, role_id FROM users WHERE user_id=$to");
    $chat_user = mysqli_fetch_assoc($cu);
}

$messages = [];
if($to > 0) {
    $msg_res = mysqli_query($conn, "SELECT * FROM messages WHERE (sender_id=$me AND receiver_id=$to) OR (sender_id=$to AND receiver_id=$me) ORDER BY created_at ASC");
    while($m = mysqli_fetch_assoc($msg_res)) $messages[] = $m;
}

// First include header.php to define __() function
include 'header.php';
$pageTitle = __('messages') . ' — ' . __('site_title');

// Role names array - use after __() is defined
$role_names = [1=>__('admin'), 2=>__('donor'), 3=>__('ngos'), 4=>__('riders')];
$role_colors = [1=>'#7c3aed', 2=>'#2e9458', 3=>'#ea580c', 4=>'#2563eb'];
?>

<style>
:root {
    --green-50: #f0faf4;
    --green-100: #d6f0e0;
    --green-500: #2e9458;
    --green-600: #226e42;
    --blue-50: #eff6ff;
    --blue-100: #dbeafe;
    --blue-600: #2563eb;
    --gray-50: #f8f9fa;
    --gray-100: #f1f3f5;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #adb5bd;
    --gray-500: #6c757d;
    --gray-700: #343a40;
    --gray-900: #212529;
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.1);
}

.msg-page {
    background: linear-gradient(135deg, #f0faf4 0%, #e8f5e9 100%);
    min-height: 100vh;
    padding: 2rem 0 3rem;
}

.msg-shell {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    display: flex;
    height: calc(100vh - 140px);
    min-height: 560px;
    transition: all 0.3s ease;
}

/* SIDEBAR - GREEN HEADER */
.msg-sidebar {
    width: 340px;
    flex-shrink: 0;
    border-right: 1px solid var(--gray-200);
    display: flex;
    flex-direction: column;
    background: #fff;
}

.msg-sidebar-header {
    padding: 1.25rem 1.25rem;
    border-bottom: none;
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
}

.msg-sidebar-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
}

.msg-sidebar-header h5 i {
    color: rgba(255,255,255,0.9);
}

.total-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 2px 10px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 600;
}

.msg-convo-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem 0;
}

.msg-convo-list::-webkit-scrollbar { width: 4px; }
.msg-convo-list::-webkit-scrollbar-track { background: var(--gray-100); }
.msg-convo-list::-webkit-scrollbar-thumb { background: var(--gray-400); border-radius: 10px; }

.convo-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    border-bottom: 1px solid var(--gray-100);
    margin: 0 0.5rem;
    border-radius: var(--radius-md);
}

.convo-item:hover {
    background: var(--gray-50);
    transform: translateX(2px);
}

.convo-item.active {
    background: linear-gradient(135deg, var(--green-50), #fff);
    border-left: 3px solid var(--green-500);
    box-shadow: var(--shadow-card);
}

.convo-av {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--green-100), var(--green-50));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    color: var(--green-600);
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.convo-av img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.convo-info {
    flex: 1;
    min-width: 0;
}

.convo-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.role-badge {
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 20px;
    font-weight: 600;
    background: var(--gray-100);
    color: var(--gray-500);
}

.convo-preview {
    font-size: 12px;
    color: var(--gray-400);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.convo-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
    flex-shrink: 0;
}

.convo-time {
    font-size: 10px;
    color: var(--gray-400);
}

.unread-badge {
    background: #e85a30;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 30px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(233,88,48,0.3);
}

.no-convos {
    padding: 3rem 1rem;
    text-align: center;
    color: var(--gray-400);
}

.no-convos i {
    font-size: 48px;
    margin-bottom: 1rem;
    color: var(--gray-300);
}

/* CHAT AREA - GREEN HEADER */
.msg-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: #fff;
}

.msg-chat-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
    color: white;
}

.chat-header-av {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
    overflow: hidden;
}

.chat-header-av img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-header-info {
    flex: 1;
}

.chat-header-name {
    font-size: 1rem;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
}

.online-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    box-shadow: 0 0 0 2px #fff;
}

.chat-header-role {
    font-size: 11px;
    color: rgba(255,255,255,0.8);
    margin-top: 2px;
}

.chat-actions {
    display: flex;
    gap: 8px;
}

.chat-action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.chat-action-btn:hover {
    background: rgba(255,255,255,0.35);
    transform: scale(1.05);
}

/* Messages Body */
.msg-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: linear-gradient(135deg, var(--gray-50), #fff);
}

.msg-body::-webkit-scrollbar { width: 5px; }
.msg-body::-webkit-scrollbar-track { background: var(--gray-100); }
.msg-body::-webkit-scrollbar-thumb { background: var(--gray-400); border-radius: 10px; }

.msg-bubble-wrap {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    max-width: 75%;
    animation: fadeInMsg 0.3s ease;
}

@keyframes fadeInMsg {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.msg-bubble-wrap.sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.msg-bubble-wrap.recv {
    align-self: flex-start;
}

.bubble-av {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gray-200), var(--gray-100));
    color: var(--gray-500);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.bubble {
    padding: 10px 16px;
    border-radius: 20px;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
    max-width: 100%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.bubble.sent {
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    color: #fff;
    border-bottom-right-radius: 4px;
}

.bubble.recv {
    background: #fff;
    color: var(--gray-700);
    border: 1px solid var(--gray-200);
    border-bottom-left-radius: 4px;
}

.bubble-time {
    font-size: 9px;
    margin-top: 4px;
    opacity: 0.7;
}

.sent .bubble-time {
    text-align: right;
    color: rgba(255,255,255,0.6);
}

.recv .bubble-time {
    text-align: left;
    color: var(--gray-400);
}

.date-sep {
    text-align: center;
    font-size: 10px;
    color: var(--gray-400);
    background: var(--gray-200);
    display: inline-block;
    padding: 4px 14px;
    border-radius: 30px;
    margin: 8px auto;
    align-self: center;
}

.msg-input-bar {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--gray-200);
    background: #fff;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-shrink: 0;
}

.msg-input {
    flex: 1;
    border: 1.5px solid var(--gray-300);
    border-radius: 30px;
    padding: 12px 18px;
    font-size: 13.5px;
    color: var(--gray-900);
    font-family: inherit;
    outline: none;
    resize: none;
    max-height: 100px;
    transition: all 0.2s;
    background: var(--gray-50);
}

.msg-input:focus {
    border-color: var(--green-400);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(46,148,88,0.1);
}

.msg-send-btn {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(46,148,88,0.3);
}

.msg-send-btn:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, var(--green-600), #1b5e20);
}

.no-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    gap: 15px;
    background: linear-gradient(135deg, var(--gray-50), #fff);
}

.no-chat i {
    font-size: 64px;
    color: var(--gray-300);
}

.no-chat h4 {
    font-size: 1.2rem;
    color: var(--gray-500);
    margin: 0;
}

/* ========== DARK MODE STYLES ========== */
body.dark-mode .msg-page {
    background: #0a0a0f;
}

body.dark-mode .msg-shell {
    background: #2a2a3a;
    border-color: #3a3a4a;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

body.dark-mode .msg-sidebar,
body.dark-mode .msg-chat {
    background: #2a2a3a;
}

body.dark-mode .msg-sidebar-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

body.dark-mode .msg-sidebar-header h5,
body.dark-mode .msg-sidebar-header .total-badge {
    color: white;
}

body.dark-mode .msg-convo-list {
    background: #2a2a3a;
}

body.dark-mode .convo-item {
    border-color: #3a3a4a;
}

body.dark-mode .convo-item:hover {
    background: #1e1e2e;
}

body.dark-mode .convo-item.active {
    background: #1e1e2e;
    border-left-color: #3b82f6;
}

body.dark-mode .convo-name {
    color: #e6edf3;
}

body.dark-mode .convo-preview {
    color: #8b949e;
}

body.dark-mode .convo-time {
    color: #8b949e;
}

body.dark-mode .no-convos {
    color: #8b949e;
}

body.dark-mode .no-convos i {
    color: #3a3a4a;
}

body.dark-mode .msg-chat-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

body.dark-mode .msg-body {
    background: #1e1e2e;
}

body.dark-mode .bubble.recv {
    background: #3a3a4a;
    border-color: #4a4a5a;
    color: #e6edf3;
}

body.dark-mode .bubble.sent {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .recv .bubble-time {
    color: #8b949e;
}

body.dark-mode .date-sep {
    background: #3a3a4a;
    color: #8b949e;
}

body.dark-mode .msg-input-bar {
    background: #2a2a3a;
    border-top-color: #3a3a4a;
}

body.dark-mode .msg-input {
    background: #1e1e2e;
    border-color: #3a3a4a;
    color: #e6edf3;
}

body.dark-mode .msg-input:focus {
    border-color: #3b82f6;
    background: #2a2a3a;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

body.dark-mode .msg-input::placeholder {
    color: #8b949e;
}

body.dark-mode .msg-send-btn {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

body.dark-mode .msg-send-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

body.dark-mode .no-chat {
    background: #1e1e2e;
}

body.dark-mode .no-chat i {
    color: #3a3a4a;
}

body.dark-mode .no-chat h4 {
    color: #8b949e;
}

body.dark-mode .chat-action-btn {
    background: rgba(255,255,255,0.15);
}

body.dark-mode .chat-action-btn:hover {
    background: rgba(255,255,255,0.3);
}

body.dark-mode .role-badge {
    background: #3a3a4a;
    color: #8b949e;
}

@media (max-width: 768px) {
    .msg-shell {
        flex-direction: column;
        height: auto;
    }
    .msg-sidebar {
        width: 100%;
        max-height: 300px;
        border-right: none;
        border-bottom: 1px solid var(--gray-200);
    }
    .msg-bubble-wrap {
        max-width: 85%;
    }
    body.dark-mode .msg-sidebar {
        border-bottom-color: #3a3a4a;
    }
}
</style>

<div class="msg-page">
<div class="container" style="max-width: 1200px;">

<div class="msg-shell">

    <!-- Conversations Sidebar -->
    <div class="msg-sidebar">
        <div class="msg-sidebar-header">
            <h5>
                <i class="fas fa-comment-dots"></i> 
                <?= __('messages') ?>
            </h5>
            <?php
            $total_unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM messages WHERE receiver_id=$me AND is_read=0"))['c'] ?? 0;
            if($total_unread > 0): 
            ?>
            <span class="total-badge"><?= $total_unread ?> <?= __('new') ?></span>
            <?php endif; ?>
        </div>
        <div class="msg-convo-list">
        <?php if($convos && mysqli_num_rows($convos) > 0):
            while($c = mysqli_fetch_assoc($convos)):
                $ci = strtoupper(substr($c['full_name'],0,1));
                $active = ($to == $c['user_id']) ? 'active' : '';
                $t_fmt = $c['last_time'] ? date('h:i A', strtotime($c['last_time'])) : '';
                $role_color = $role_colors[$c['role_id']] ?? '#6c757d';
        ?>
        <a href="messages.php?to=<?= $c['user_id'] ?>" class="convo-item <?= $active ?>">
            <div class="convo-av">
                <?php if(!empty($c['profile_pic']) && file_exists('../'.$c['profile_pic'])): ?>
                    <img src="../<?= $c['profile_pic'] ?>" alt="">
                <?php else: ?>
                    <?= $ci ?>
                <?php endif; ?>
            </div>
            <div class="convo-info">
                <div class="convo-name">
                    <?= htmlspecialchars($c['full_name']) ?>
                    <span class="role-badge" style="background: <?= $role_color ?>20; color: <?= $role_color ?>;">
                        <?= $role_names[$c['role_id']] ?? __('user') ?>
                    </span>
                </div>
                <div class="convo-preview">
                    <?= htmlspecialchars(mb_strimwidth($c['last_msg'] ?? __('start_conversation'), 0, 45, '...')) ?>
                </div>
            </div>
            <div class="convo-meta">
                <?php if($t_fmt): ?>
                <span class="convo-time"><?= $t_fmt ?></span>
                <?php endif; ?>
                <?php if($c['unread'] > 0): ?>
                    <span class="unread-badge"><?= $c['unread'] ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php endwhile;
        elseif($chat_user && $to > 0):
            $ci = strtoupper(substr($chat_user['full_name'],0,1));
        ?>
        <a href="messages.php?to=<?= $chat_user['user_id'] ?>" class="convo-item active">
            <div class="convo-av"><?= $ci ?></div>
            <div class="convo-info">
                <div class="convo-name"><?= htmlspecialchars($chat_user['full_name']) ?></div>
                <div class="convo-preview"><?= __('new_conversation') ?></div>
            </div>
        </a>
        <?php else: ?>
        <div class="no-convos">
            <i class="fas fa-inbox"></i>
            <p><?= __('no_conversations_yet') ?></p>
            <small><?= __('start_chat_from_dashboard') ?></small>
        </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Chat Panel -->
    <?php if($to > 0 && $chat_user): ?>
    <div class="msg-chat">
        <div class="msg-chat-header">
            <div class="chat-header-av">
                <?php if(!empty($chat_user['profile_pic']) && file_exists('../'.$chat_user['profile_pic'])): ?>
                    <img src="../<?= $chat_user['profile_pic'] ?>" alt="">
                <?php else: ?>
                    <?= strtoupper(substr($chat_user['full_name'],0,1)) ?>
                <?php endif; ?>
            </div>
            <div class="chat-header-info">
                <div class="chat-header-name">
                    <?= htmlspecialchars($chat_user['full_name']) ?>
                    <span class="online-dot"></span>
                </div>
                <div class="chat-header-role">
                    <?= $role_names[$chat_user['role_id']] ?? __('user') ?>
                </div>
            </div>
            <div class="chat-actions">
                <button class="chat-action-btn" onclick="window.location.href='profile.php?user=<?= $to ?>'" title="<?= __('view_profile') ?>">
                    <i class="fas fa-user"></i>
                </button>
                <button class="chat-action-btn" onclick="window.location.href='dashboard.php'" title="<?= __('back_to_dashboard') ?>">
                    <i class="fas fa-tachometer-alt"></i>
                </button>
            </div>
        </div>

        <div class="msg-body" id="msgBody">
        <?php
        $prev_date = '';
        foreach($messages as $m):
            $is_sent = ($m['sender_id'] == $me);
            $wrap_cls = $is_sent ? 'sent' : 'recv';
            $bub_cls  = $is_sent ? 'sent' : 'recv';
            $d = date('d M Y', strtotime($m['created_at']));
            $t = date('h:i A', strtotime($m['created_at']));
            if($d !== $prev_date) {
                echo "<div class='date-sep'>$d</div>";
                $prev_date = $d;
            }
            $initial = $is_sent ? strtoupper(substr($_SESSION['full_name'] ?? 'Y', 0, 1)) : strtoupper(substr($chat_user['full_name'],0,1));
        ?>
        <div class="msg-bubble-wrap <?= $wrap_cls ?>">
            <div class="bubble-av"><?= $initial ?></div>
            <div>
                <div class="bubble <?= $bub_cls ?>"><?= nl2br(htmlspecialchars($m['body'])) ?></div>
                <div class="bubble-time"><?= $t ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($messages)): ?>
        <div class="no-chat" style="background: transparent; min-height: 300px;">
            <i class="fas fa-comment-dots"></i>
            <h4><?= __('no_messages_yet') ?></h4>
            <p><?= __('say_hello_to_start') ?></p>
        </div>
        <?php endif; ?>
        </div>

        <form method="POST" class="msg-input-bar" id="msgForm">
            <textarea name="body" class="msg-input" id="msgInput" placeholder="<?= __('type_message') ?>" rows="1" required></textarea>
            <button type="submit" name="send_msg" class="msg-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <?php else: ?>
    <div class="no-chat">
        <i class="fas fa-comments"></i>
        <h4><?= __('welcome_to_messages') ?></h4>
        <p><?= __('select_conversation_to_chat') ?></p>
        <small class="text-muted"><?= __('or_click_chat_from_dashboard') ?></small>
    </div>
    <?php endif; ?>

</div>
</div>
</div>

<script>
const msgBody = document.getElementById('msgBody');
if(msgBody) msgBody.scrollTop = msgBody.scrollHeight;

const msgInput = document.getElementById('msgInput');
const msgForm  = document.getElementById('msgForm');
if(msgInput && msgForm) {
    msgInput.addEventListener('keydown', function(e) {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if(this.value.trim()) {
                const sendBtn = document.querySelector('.msg-send-btn');
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                sendBtn.disabled = true;
                msgForm.submit();
            }
        }
    });
    
    msgInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
}

<?php if($to > 0): ?>
let lastMessageCount = <?= count($messages) ?>;
setInterval(function() {
    fetch('messages.php?to=<?= $to ?>&ajax=1&check_new=1')
        .then(r => r.json())
        .then(data => {
            if(data.new_count > lastMessageCount) {
                location.reload();
            }
        })
        .catch(() => {});
}, 10000);
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>