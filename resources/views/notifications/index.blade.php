<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', system-ui, sans-serif; background: #f1f5f9; color: #0f172a; min-height: 100vh; }
        .topbar { background: #0f172a; color: #fff; padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
        .topbar a { color: #cbd5e1; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; }
        .topbar a:hover { color: #fff; }
        .topbar .brand { margin-left: auto; font-weight: 700; color: #fff; }
        .wrap { max-width: 720px; margin: 28px auto; padding: 0 16px; }
        .head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .head h1 { font-size: 22px; }
        .mark-all { background: #2563eb; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 8px; }
        .mark-all:hover { background: #1d4ed8; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 16px rgba(15, 23, 42, .06); overflow: hidden; }
        .item { display: flex; gap: 14px; padding: 16px 18px; border-bottom: 1px solid #f1f5f9; border-left: 3px solid transparent; }
        .item:last-child { border-bottom: none; }
        .item.unread { background: #eff6ff; border-left-color: #2563eb; }
        .icon { width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
        .msg { font-size: 14px; line-height: 1.5; }
        .item.unread .msg { font-weight: 500; }
        .time { font-size: 12px; color: #64748b; margin-top: 4px; }
        .empty { padding: 50px 20px; text-align: center; color: #94a3b8; }
        .pager { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 13px; color: #64748b; }
        .pager a, .pager span.btn { padding: 8px 14px; border-radius: 8px; background: #fff; color: #0f172a; text-decoration: none; font-weight: 600; border: 1px solid #e2e8f0; }
        .pager span.btn { color: #cbd5e1; }
    </style>
</head>
<body>

<div class="topbar">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"><i class="fas fa-arrow-left"></i> Back</a>
    <span class="brand">Rem's Transport</span>
</div>

<div class="wrap">
    <div class="head">
        <h1>Notifications</h1>
        @if(auth()->user()->unreadNotifications->count())
            <a href="/notifications/read" class="mark-all"><i class="fas fa-check-double"></i> Mark all as read</a>
        @endif
    </div>

    <div class="card">
        @forelse($notifications as $notif)
            <div class="item {{ $notif->read_at ? '' : 'unread' }}">
                <div class="icon"><i class="fas fa-bell"></i></div>
                <div>
                    <div class="msg">{{ $notif->data['message'] ?? 'Notification' }}</div>
                    <div class="time">{{ $notif->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }} · {{ $notif->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <div class="empty"><i class="fas fa-bell-slash" style="font-size:28px;margin-bottom:10px;display:block;"></i>You have no notifications yet.</div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="pager">
            @if($notifications->onFirstPage())
                <span class="btn">&larr; Newer</span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}">&larr; Newer</a>
            @endif
            <span>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>
            @if($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}">Older &rarr;</a>
            @else
                <span class="btn">Older &rarr;</span>
            @endif
        </div>
    @endif
</div>

@include('partials.chat-widget')
</body>
</html>
