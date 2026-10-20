{{-- Admin notification bell: 10 newest in a scrollable dropdown, unread count, "See all" link. --}}
@php
    $adminUnreadNotifs = auth()->user()->unreadNotifications()->count();
    $adminNotifs = auth()->user()->notifications()->take(10)->get();
@endphp

<style>
    .anotif { position: relative; margin-left: auto; }
    .anotif-btn { position: relative; background: #fff; border: 1px solid #e2e8f0; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; font-size: 17px; color: #334155; display: flex; align-items: center; justify-content: center; }
    .anotif-btn:hover { background: #f1f5f9; }
    .anotif-count { position: absolute; top: -4px; right: -4px; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 10px; background: #ef4444; color: #fff; font-size: 10.5px; font-weight: 700; line-height: 19px; text-align: center; border: 2px solid #fff; }
    .anotif-drop { display: none; position: absolute; right: 0; top: 50px; width: 360px; max-width: calc(100vw - 24px); background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,.18); z-index: 1200; overflow: hidden; }
    .anotif-drop.show { display: block; }
    .anotif-head { display: flex; justify-content: space-between; align-items: center; padding: 13px 15px; font-weight: 600; font-size: 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #0f172a; }
    .anotif-new { font-size: 11px; font-weight: 600; color: #fff; background: #ef4444; padding: 2px 8px; border-radius: 10px; }
    .anotif-list { max-height: 380px; overflow-y: auto; overscroll-behavior: contain; }
    .anotif-item { display: flex; flex-direction: column; gap: 3px; padding: 11px 15px; color: #374151; text-decoration: none; font-size: 13px; line-height: 1.45; border-bottom: 1px solid #f1f5f9; border-left: 3px solid transparent; }
    .anotif-item:hover { background: #f8fafc; }
    .anotif-item.unread { background: #eff6ff; border-left-color: #2563eb; color: #1e293b; font-weight: 500; }
    .anotif-time { font-size: 11px; color: #94a3b8; font-weight: 400; }
    .anotif-empty { padding: 22px 15px; text-align: center; color: #94a3b8; font-size: 13px; margin: 0; }
    .anotif-foot { display: block; text-align: center; padding: 11px; font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .anotif-foot:hover { background: #eef2ff; }
    @media (max-width: 600px) { .anotif-drop { position: fixed; top: 70px; right: 12px; } .anotif-list { max-height: calc(100vh - 200px); } }
</style>

<div class="anotif">
    <button type="button" class="anotif-btn" id="anotifBtn" aria-label="Notifications" title="Notifications">
        <i class="fa fa-bell"></i>
        @if($adminUnreadNotifs)
            <span class="anotif-count">{{ $adminUnreadNotifs > 99 ? '99+' : $adminUnreadNotifs }}</span>
        @endif
    </button>
    <div class="anotif-drop" id="anotifDrop">
        <div class="anotif-head">
            <span>Notifications</span>
            @if($adminUnreadNotifs)
                <span class="anotif-new">{{ $adminUnreadNotifs }} new</span>
            @endif
        </div>
        <div class="anotif-list">
            @forelse($adminNotifs as $notif)
                <a href="/notifications/read" class="anotif-item {{ $notif->read_at ? '' : 'unread' }}">
                    <span>{{ $notif->data['message'] ?? 'Notification' }}</span>
                    <span class="anotif-time">{{ $notif->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <p class="anotif-empty">No notifications</p>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="anotif-foot">See all notifications</a>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('anotifBtn');
    const drop = document.getElementById('anotifDrop');
    btn.addEventListener('click', function (e) { e.stopPropagation(); drop.classList.toggle('show'); });
    drop.addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('click', function () { drop.classList.remove('show'); });
})();
</script>
