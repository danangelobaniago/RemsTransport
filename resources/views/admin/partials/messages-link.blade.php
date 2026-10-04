@php($adminUnreadMessages = \App\Http\Controllers\MessageController::adminUnreadCount())
<a href="/admin/messages" class="{{ request()->is('admin/messages') ? 'active' : '' }}" style="display:flex;align-items:center;justify-content:space-between;">
    <span>Messages</span>
    <span id="adminMsgBadge" style="background:#ef4444;color:#fff;font-size:11px;font-weight:700;min-width:20px;height:20px;line-height:20px;padding:0 6px;border-radius:10px;text-align:center;{{ $adminUnreadMessages ? '' : 'display:none;' }}">{{ $adminUnreadMessages > 99 ? '99+' : $adminUnreadMessages }}</span>
</a>
