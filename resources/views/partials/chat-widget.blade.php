{{--
    Floating "Message Admin" button + chat panel for customers and drivers.
    Self-contained (own CSS/JS) because every page here is a standalone HTML
    file. Admins get the inbox at /admin/messages instead, so nothing renders
    for them. Guests see the button, but it sends them to the login page.
--}}
@if(!auth()->check() || auth()->user()->role !== 'admin')
<style>
    .rt-chat-fab {
        position: fixed; right: 24px; bottom: 24px; z-index: 1050;
        width: 58px; height: 58px; border-radius: 50%; border: none; cursor: pointer;
        background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.45);
        display: flex; align-items: center; justify-content: center;
        transition: transform .2s ease, box-shadow .2s ease; text-decoration: none;
    }
    .rt-chat-fab:hover { transform: translateY(-2px) scale(1.04); box-shadow: 0 12px 28px rgba(37, 99, 235, 0.55); }
    .rt-chat-fab svg { width: 26px; height: 26px; }
    .rt-chat-badge {
        position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px; padding: 0 5px;
        border-radius: 10px; background: #ef4444; color: #fff; font: 700 11px/20px system-ui, sans-serif;
        text-align: center; border: 2px solid #fff;
    }
    .rt-chat-badge[hidden] { display: none; }

    .rt-chat-panel {
        position: fixed; right: 24px; bottom: 96px; z-index: 1051;
        width: 360px; max-width: calc(100vw - 32px); height: 500px; max-height: calc(100vh - 130px);
        background: #fff; border-radius: 16px; overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
        display: flex; flex-direction: column;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif; color: #1e293b;
    }
    .rt-chat-panel[hidden] { display: none; }
    .rt-chat-head {
        background: #0f172a; color: #fff; padding: 14px 16px;
        display: flex; align-items: center; gap: 10px;
    }
    .rt-chat-avatar {
        width: 36px; height: 36px; border-radius: 50%; background: #2563eb;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
    }
    .rt-chat-head h4 { margin: 0; font-size: 15px; font-weight: 600; color: #fff; }
    .rt-chat-head p { margin: 2px 0 0; font-size: 11.5px; color: #94a3b8; }
    .rt-chat-close {
        margin-left: auto; background: none; border: none; color: #cbd5e1;
        font-size: 22px; line-height: 1; cursor: pointer; padding: 4px;
    }
    .rt-chat-close:hover { color: #fff; }
    .rt-chat-body {
        flex: 1; overflow-y: auto; padding: 16px; background: #f1f5f9;
        display: flex; flex-direction: column; gap: 8px;
    }
    .rt-chat-empty { margin: auto; text-align: center; color: #64748b; font-size: 13px; line-height: 1.5; padding: 0 12px; }
    .rt-chat-msg { max-width: 80%; display: flex; flex-direction: column; }
    .rt-chat-msg.mine { align-self: flex-end; align-items: flex-end; }
    .rt-chat-msg.theirs { align-self: flex-start; align-items: flex-start; }
    .rt-chat-bubble {
        padding: 9px 13px; border-radius: 14px; font-size: 13.5px; line-height: 1.45;
        white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere;
    }
    .rt-chat-msg.mine .rt-chat-bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
    .rt-chat-msg.theirs .rt-chat-bubble { background: #fff; color: #1e293b; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
    .rt-chat-time { font-size: 10.5px; color: #94a3b8; margin-top: 3px; padding: 0 4px; }
    .rt-chat-form { display: flex; gap: 8px; padding: 10px; border-top: 1px solid #e2e8f0; background: #fff; }
    .rt-chat-input {
        flex: 1; resize: none; border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px;
        font: inherit; font-size: 13.5px; max-height: 100px; outline: none; color: #1e293b; background: #fff;
    }
    .rt-chat-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
    .rt-chat-send {
        border: none; background: #2563eb; color: #fff; border-radius: 10px; width: 44px;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
    }
    .rt-chat-send:disabled { opacity: .5; cursor: default; }
    .rt-chat-send svg { width: 18px; height: 18px; }
    .rt-chat-error { color: #dc2626; font-size: 12px; padding: 0 12px 8px; background: #fff; }
    .rt-chat-error[hidden] { display: none; }

    @media print { .rt-chat-fab, .rt-chat-panel { display: none !important; } }

    @media (max-width: 480px) {
        .rt-chat-fab { right: 16px; bottom: 16px; width: 54px; height: 54px; }
        .rt-chat-panel { right: 8px; left: 8px; width: auto; max-width: none; bottom: 80px; height: calc(100vh - 100px); max-height: none; }
    }
</style>

@guest
    <a href="{{ route('login') }}" class="rt-chat-fab" title="Log in to message us" aria-label="Log in to message us">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </a>
@else
    <button type="button" class="rt-chat-fab" id="rtChatFab" title="Message Admin" aria-label="Message Admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span class="rt-chat-badge" id="rtChatBadge" hidden>0</span>
    </button>

    <div class="rt-chat-panel" id="rtChatPanel" hidden role="dialog" aria-label="Chat with Rem's Transport admin">
        <div class="rt-chat-head">
            <div class="rt-chat-avatar">RT</div>
            <div>
                <h4>Rem's Transport Admin</h4>
                <p>{{ auth()->user()->role === 'driver' ? 'Questions about your trips? Message the office.' : 'Ask us anything about bookings, vans, or tours.' }}</p>
            </div>
            <button type="button" class="rt-chat-close" id="rtChatClose" aria-label="Close chat">&times;</button>
        </div>
        <div class="rt-chat-body" id="rtChatBody">
            <div class="rt-chat-empty" id="rtChatEmpty">
                Hi {{ auth()->user()->first_name }}! 👋<br>
                Send us a message and our admin will reply here as soon as possible.
            </div>
        </div>
        <div class="rt-chat-error" id="rtChatError" hidden></div>
        <form class="rt-chat-form" id="rtChatForm">
            <textarea class="rt-chat-input" id="rtChatInput" rows="1" maxlength="1000" placeholder="Type your message..." required></textarea>
            <button type="submit" class="rt-chat-send" id="rtChatSend" aria-label="Send">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </div>

    <script>
    (function () {
        const csrf = @json(csrf_token());
        const fab = document.getElementById('rtChatFab');
        const panel = document.getElementById('rtChatPanel');
        const body = document.getElementById('rtChatBody');
        const empty = document.getElementById('rtChatEmpty');
        const badge = document.getElementById('rtChatBadge');
        const form = document.getElementById('rtChatForm');
        const input = document.getElementById('rtChatInput');
        const sendBtn = document.getElementById('rtChatSend');
        const errorBox = document.getElementById('rtChatError');

        let lastId = 0;
        let timer = null;
        const seen = new Set();

        function isOpen() { return !panel.hidden; }

        function setBadge(n) {
            badge.textContent = n > 99 ? '99+' : n;
            badge.hidden = !n || isOpen();
        }

        function append(msg) {
            if (seen.has(msg.id)) return;
            seen.add(msg.id);
            lastId = Math.max(lastId, msg.id);
            empty.style.display = 'none';

            const wrap = document.createElement('div');
            wrap.className = 'rt-chat-msg ' + (msg.from_admin ? 'theirs' : 'mine');
            const bubble = document.createElement('div');
            bubble.className = 'rt-chat-bubble';
            bubble.textContent = msg.body;
            const time = document.createElement('div');
            time.className = 'rt-chat-time';
            time.textContent = (msg.from_admin ? 'Admin · ' : '') + msg.time;
            wrap.append(bubble, time);
            body.appendChild(wrap);
        }

        function scrollDown() { body.scrollTop = body.scrollHeight; }

        async function poll() {
            try {
                const res = await fetch('/messages/fetch?after=' + lastId + (isOpen() ? '&open=1' : ''), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const data = await res.json();
                const nearBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 60;
                data.messages.forEach(append);
                if (data.messages.length && (nearBottom || !isOpen())) scrollDown();
                setBadge(data.unread);
            } catch (e) { /* offline — try again next tick */ }
        }

        function schedule() {
            clearTimeout(timer);
            timer = setTimeout(async function () { await poll(); schedule(); }, isOpen() ? 4000 : 20000);
        }

        async function open() {
            panel.hidden = false;
            badge.hidden = true;
            await poll();
            scrollDown();
            input.focus();
            schedule();
        }

        function close() {
            panel.hidden = true;
            schedule();
        }

        fab.addEventListener('click', function () { isOpen() ? close() : open(); });
        document.getElementById('rtChatClose').addEventListener('click', close);

        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 100) + 'px';
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            sendBtn.disabled = true;
            errorBox.hidden = true;
            try {
                const res = await fetch('/messages/send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ body: text })
                });
                if (!res.ok) {
                    errorBox.textContent = res.status === 429
                        ? 'You are sending messages too quickly. Please wait a moment.'
                        : 'Message not sent. Please try again.';
                    errorBox.hidden = false;
                    return;
                }
                const data = await res.json();
                append(data.message);
                input.value = '';
                input.style.height = 'auto';
                scrollDown();
            } catch (err) {
                errorBox.textContent = 'Message not sent. Check your connection.';
                errorBox.hidden = false;
            } finally {
                sendBtn.disabled = false;
                input.focus();
            }
        });

        poll();
        schedule();
    })();
    </script>
@endguest
@endif
