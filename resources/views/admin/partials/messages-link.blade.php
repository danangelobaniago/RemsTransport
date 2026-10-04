@php($adminUnreadMessages = \App\Http\Controllers\MessageController::adminUnreadCount())
<a href="/admin/messages" class="{{ request()->is('admin/messages') ? 'active' : '' }}" style="display:flex;align-items:center;justify-content:space-between;">
    <span>Messages</span>
    <span id="adminMsgBadge" style="background:#ef4444;color:#fff;font-size:11px;font-weight:700;min-width:20px;height:20px;line-height:20px;padding:0 6px;border-radius:10px;text-align:center;{{ $adminUnreadMessages ? '' : 'display:none;' }}">{{ $adminUnreadMessages > 99 ? '99+' : $adminUnreadMessages }}</span>
</a>
<script>
// Keeps this admin marked "online" (customers get an auto-reply when no admin
// is) and keeps the Messages badge current on every admin page.
(function () {
    if (window.__adminMsgHeartbeat) return;
    window.__adminMsgHeartbeat = true;
    function beat() {
        fetch('/admin/messages/heartbeat', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                var b = document.getElementById('adminMsgBadge');
                if (!d || !b || location.pathname === '/admin/messages') return;
                b.textContent = d.unread > 99 ? '99+' : d.unread;
                b.style.display = d.unread ? '' : 'none';
            })
            .catch(function () {});
    }
    beat();
    setInterval(beat, 30000);
})();
</script>
