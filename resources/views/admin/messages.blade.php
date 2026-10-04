<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Messages | Rem's Transport</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<style>
    .inbox {
        display: grid; grid-template-columns: 320px 1fr; height: calc(100vh - 140px); min-height: 460px;
        background: #fff; border-radius: 14px; box-shadow: 0 4px 16px rgba(15, 23, 42, .06); overflow: hidden;
    }
    .inbox-list { border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; min-height: 0; }
    .inbox-search { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .inbox-search input {
        width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none;
    }
    .inbox-search input:focus { border-color: #2563eb; }
    .inbox-filter { display: flex; gap: 6px; margin-top: 8px; }
    .inbox-filter button {
        flex: 1; padding: 6px; border: 1px solid #d1d5db; background: #fff; border-radius: 6px;
        font-size: 12px; cursor: pointer; color: #475569;
    }
    .inbox-filter button.active { background: #111827; border-color: #111827; color: #fff; }
    .threads { flex: 1; overflow-y: auto; }
    .thread {
        display: flex; gap: 10px; padding: 12px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9;
        align-items: flex-start;
    }
    .thread:hover { background: #f8fafc; }
    .thread.active { background: #eff6ff; }
    .thread-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0; color: #fff; font-weight: 700; font-size: 14px;
        display: flex; align-items: center; justify-content: center; background: #2563eb;
    }
    .thread-avatar.driver { background: #0d9488; }
    .thread-main { flex: 1; min-width: 0; }
    .thread-top { display: flex; justify-content: space-between; gap: 6px; align-items: baseline; }
    .thread-name { font-weight: 600; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .thread-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
    .thread-preview { font-size: 12.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .thread.unread .thread-preview { color: #0f172a; font-weight: 600; }
    .thread-meta { display: flex; gap: 6px; align-items: center; margin-top: 4px; }
    .role-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 2px 6px; border-radius: 4px; background: #dbeafe; color: #1d4ed8; }
    .role-tag.driver { background: #ccfbf1; color: #0f766e; }
    .unread-dot { margin-left: auto; background: #ef4444; color: #fff; font-size: 10.5px; font-weight: 700; min-width: 18px; height: 18px; line-height: 18px; border-radius: 9px; text-align: center; padding: 0 5px; }
    .threads-empty { padding: 30px 16px; text-align: center; color: #94a3b8; font-size: 13px; }

    .chat { display: flex; flex-direction: column; min-height: 0; }
    .chat-head { padding: 14px 18px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; }
    .chat-back { display: none; background: none; border: none; font-size: 20px; cursor: pointer; color: #334155; }
    .chat-head h3 { font-size: 15px; margin: 0; }
    .chat-head p { font-size: 12px; color: #64748b; margin: 2px 0 0; }
    .chat-body { flex: 1; overflow-y: auto; padding: 18px; background: #f8fafc; display: flex; flex-direction: column; gap: 8px; }
    .chat-placeholder { margin: auto; text-align: center; color: #94a3b8; font-size: 14px; }
    .msg { max-width: 70%; display: flex; flex-direction: column; }
    .msg.mine { align-self: flex-end; align-items: flex-end; }
    .msg.theirs { align-self: flex-start; align-items: flex-start; }
    .bubble { padding: 9px 13px; border-radius: 14px; font-size: 13.5px; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; }
    .msg.mine .bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
    .msg.theirs .bubble { background: #fff; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
    .msg-time { font-size: 10.5px; color: #94a3b8; margin-top: 3px; padding: 0 4px; }
    .chat-form { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; }
    .chat-form textarea {
        flex: 1; resize: none; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px 12px;
        font-size: 13.5px; max-height: 120px; outline: none;
    }
    .chat-form textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .chat-form button { border: none; background: #2563eb; color: #fff; border-radius: 10px; padding: 0 18px; font-weight: 600; cursor: pointer; }
    .chat-form button:disabled { opacity: .5; cursor: default; }
    .chat-error { color: #dc2626; font-size: 12px; padding: 0 14px 8px; }

    @media (max-width: 900px) {
        .inbox { grid-template-columns: 1fr; height: calc(100vh - 120px); }
        .inbox.viewing .inbox-list { display: none; }
        .inbox:not(.viewing) .chat { display: none; }
        .chat-back { display: block; }
        .msg { max-width: 85%; }
    }
</style>
</head>

<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="container">

<!-- SIDEBAR -->
<div class="sidebar" id="adminSidebar">
    <h2 class="logo">Rem's Transport</h2>

    <nav>
        <a href="/admin/dashboard">Dashboard</a>
        <a href="/admin/bookings">Bookings</a>
        <a href="/admin/drivers">Drivers</a>
        <a href="/admin/vans">Vans</a>
        <a href="/admin/tours">Tours</a>
        <a href="/admin/customers">Customers</a>
        <a href="/admin/book-for-customer">New Booking</a>
        <a href="/admin/joiner-trips">Joiner Trips</a>
        <a href="/admin/pricing">Pricing</a>
        <a href="/admin/reports">Reports</a>
        <a href="/admin/live-map">Live Map</a>
        @include('admin.partials.messages-link')
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>

    <form id="logout-form" action="/logout" method="POST" style="display: none;">
        @csrf
    </form>
</nav>
</div>

<!-- MAIN -->
<div class="main">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
        <h1 style="margin:0;">Messages</h1>
    </div>

    <div class="inbox" id="inbox">
        <div class="inbox-list">
            <div class="inbox-search">
                <input type="text" id="threadSearch" placeholder="Search name or email...">
                <div class="inbox-filter">
                    <button type="button" data-filter="all" class="active">All</button>
                    <button type="button" data-filter="customer">Customers</button>
                    <button type="button" data-filter="driver">Drivers</button>
                    <button type="button" data-filter="unread">Unread</button>
                </div>
            </div>
            <div class="threads" id="threads">
                <div class="threads-empty">Loading conversations...</div>
            </div>
        </div>

        <div class="chat">
            <div class="chat-head" id="chatHead" style="display:none;">
                <button type="button" class="chat-back" id="chatBack" aria-label="Back">&larr;</button>
                <div class="thread-avatar" id="chatAvatar"></div>
                <div>
                    <h3 id="chatName"></h3>
                    <p id="chatSub"></p>
                </div>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="chat-placeholder">Select a conversation to view messages.</div>
            </div>
            <div class="chat-error" id="chatError" hidden></div>
            <form class="chat-form" id="chatForm" style="display:none;">
                <textarea id="chatInput" rows="1" maxlength="1000" placeholder="Type a reply... (Enter to send, Shift+Enter for new line)"></textarea>
                <button type="submit" id="chatSend">Send</button>
            </form>
        </div>
    </div>

</div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

(function () {
    const csrf = @json(csrf_token());
    const inbox = document.getElementById('inbox');
    const threadsEl = document.getElementById('threads');
    const search = document.getElementById('threadSearch');
    const chatHead = document.getElementById('chatHead');
    const chatBody = document.getElementById('chatBody');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');
    const chatError = document.getElementById('chatError');
    const sidebarBadge = document.getElementById('adminMsgBadge');

    let threads = [];
    let filter = 'all';
    let activeId = null;
    let lastId = 0;
    let seen = new Set();

    const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

    function initials(name) {
        return name.split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('') || '?';
    }

    function renderThreads() {
        const q = search.value.trim().toLowerCase();
        const list = threads.filter(t => {
            if (filter === 'unread' && !t.unread) return false;
            if ((filter === 'customer' || filter === 'driver') && t.role !== filter) return false;
            return !q || t.name.toLowerCase().includes(q) || t.email.toLowerCase().includes(q);
        });

        threadsEl.innerHTML = '';
        if (!list.length) {
            const p = document.createElement('div');
            p.className = 'threads-empty';
            p.textContent = threads.length ? 'No matching conversations.' : 'No messages yet. Customers and drivers can message you from their pages.';
            threadsEl.appendChild(p);
            return;
        }

        list.forEach(t => {
            const row = document.createElement('div');
            row.className = 'thread' + (t.unread ? ' unread' : '') + (t.user_id === activeId ? ' active' : '');
            row.innerHTML =
                '<div class="thread-avatar"></div>' +
                '<div class="thread-main">' +
                    '<div class="thread-top"><span class="thread-name"></span><span class="thread-time"></span></div>' +
                    '<div class="thread-preview"></div>' +
                    '<div class="thread-meta"><span class="role-tag"></span></div>' +
                '</div>';
            const isDriver = t.role === 'driver';
            const avatar = row.querySelector('.thread-avatar');
            avatar.textContent = initials(t.name);
            if (isDriver) avatar.classList.add('driver');
            row.querySelector('.thread-name').textContent = t.name;
            row.querySelector('.thread-time').textContent = t.time;
            row.querySelector('.thread-preview').textContent = t.preview;
            const tag = row.querySelector('.role-tag');
            tag.textContent = isDriver ? 'Driver' : 'Customer';
            if (isDriver) tag.classList.add('driver');
            if (t.unread) {
                const dot = document.createElement('span');
                dot.className = 'unread-dot';
                dot.textContent = t.unread;
                row.querySelector('.thread-meta').appendChild(dot);
            }
            row.addEventListener('click', () => openThread(t));
            threadsEl.appendChild(row);
        });
    }

    function setSidebarBadge(n) {
        if (!sidebarBadge) return;
        sidebarBadge.textContent = n > 99 ? '99+' : n;
        sidebarBadge.style.display = n ? '' : 'none';
    }

    async function loadThreads() {
        try {
            const res = await fetch('/admin/messages/threads', { headers, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            threads = data.threads;
            // The open conversation is being read live, so don't flag it.
            const active = threads.find(t => t.user_id === activeId);
            const total = data.total_unread - (active ? active.unread : 0);
            if (active) active.unread = 0;
            setSidebarBadge(total);
            renderThreads();
        } catch (e) {}
    }

    function appendMsg(m) {
        if (seen.has(m.id)) return;
        seen.add(m.id);
        lastId = Math.max(lastId, m.id);
        const placeholder = chatBody.querySelector('.chat-placeholder');
        if (placeholder) placeholder.remove();

        const wrap = document.createElement('div');
        wrap.className = 'msg ' + (m.from_admin ? 'mine' : 'theirs');
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.textContent = m.body;
        const time = document.createElement('div');
        time.className = 'msg-time';
        time.textContent = m.time;
        wrap.append(bubble, time);
        chatBody.appendChild(wrap);
    }

    async function loadMessages() {
        if (!activeId) return;
        const id = activeId;
        try {
            const res = await fetch('/admin/messages/' + id + '?after=' + lastId, { headers, credentials: 'same-origin' });
            if (!res.ok || id !== activeId) return;
            const data = await res.json();
            const nearBottom = chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 80;
            data.messages.forEach(appendMsg);
            if (data.messages.length && nearBottom) chatBody.scrollTop = chatBody.scrollHeight;
        } catch (e) {}
    }

    async function openThread(t) {
        activeId = t.user_id;
        lastId = 0;
        seen = new Set();
        chatBody.innerHTML = '';
        chatError.hidden = true;
        chatHead.style.display = '';
        chatForm.style.display = '';
        const avatar = document.getElementById('chatAvatar');
        avatar.textContent = initials(t.name);
        avatar.classList.toggle('driver', t.role === 'driver');
        document.getElementById('chatName').textContent = t.name;
        document.getElementById('chatSub').textContent = (t.role === 'driver' ? 'Driver' : 'Customer') + ' · ' + t.email;
        inbox.classList.add('viewing');
        history.replaceState(null, '', '/admin/messages?user=' + t.user_id);

        await loadMessages();
        chatBody.scrollTop = chatBody.scrollHeight;
        chatInput.focus();
        loadThreads();
    }

    document.getElementById('chatBack').addEventListener('click', () => {
        inbox.classList.remove('viewing');
        activeId = null;
        history.replaceState(null, '', '/admin/messages');
        renderThreads();
    });

    document.querySelectorAll('.inbox-filter button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.inbox-filter button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filter = btn.dataset.filter;
            renderThreads();
        });
    });
    search.addEventListener('input', renderThreads);

    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
    });
    chatInput.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatForm.requestSubmit(); }
    });

    chatForm.addEventListener('submit', async e => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text || !activeId) return;
        chatSend.disabled = true;
        chatError.hidden = true;
        try {
            const res = await fetch('/admin/messages/' + activeId, {
                method: 'POST',
                headers: Object.assign({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, headers),
                credentials: 'same-origin',
                body: JSON.stringify({ body: text })
            });
            if (!res.ok) {
                chatError.textContent = 'Reply not sent. Please try again.';
                chatError.hidden = false;
                return;
            }
            const data = await res.json();
            appendMsg(data.message);
            chatInput.value = '';
            chatInput.style.height = 'auto';
            chatBody.scrollTop = chatBody.scrollHeight;
            loadThreads();
        } catch (err) {
            chatError.textContent = 'Reply not sent. Check your connection.';
            chatError.hidden = false;
        } finally {
            chatSend.disabled = false;
            chatInput.focus();
        }
    });

    // Initial load, reopening ?user=<id> if the page was refreshed mid-chat.
    loadThreads().then(() => {
        const wanted = Number(new URLSearchParams(location.search).get('user'));
        const t = threads.find(x => x.user_id === wanted);
        if (t) openThread(t);
    });

    setInterval(loadThreads, 6000);
    setInterval(loadMessages, 3000);
})();
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
