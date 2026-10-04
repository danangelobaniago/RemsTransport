<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // An admin counts as online if any admin page pinged within this window.
    const ONLINE_WINDOW_MINUTES = 2;

    // At most one automatic reply per conversation within this window.
    const AUTO_REPLY_COOLDOWN_MINUTES = 60;

    const AUTO_REPLY_ONLINE = "Hi! Thanks for messaging Rem's Transport. 😊\n\n"
        . "We've received your message and an admin will reply to you shortly. Please stay on this chat.";

    const AUTO_REPLY_OFFLINE = "Hi! Thanks for messaging Rem's Transport. 😊\n\n"
        . "Our admin is offline right now, but we've received your message and will reply as soon as we're back online.\n\n"
        . "For urgent concerns, please call or text us at +63 999 883 4375.";

    public static function adminOnline(): bool
    {
        return User::where('role', 'admin')
            ->where('last_seen_at', '>=', now()->subMinutes(self::ONLINE_WINDOW_MINUTES))
            ->exists();
    }

    // Records that this admin is active; throttled to one write per 30s.
    private static function touchAdmin(): void
    {
        $admin = Auth::user();
        if (!$admin->last_seen_at || \Carbon\Carbon::parse($admin->last_seen_at)->lt(now()->subSeconds(30))) {
            DB::table('users')->where('id', $admin->id)->update(['last_seen_at' => now()]);
        }
    }

    // Instant acknowledgement for the first message of a conversation burst;
    // the wording depends on whether an admin is online.
    private function maybeAutoReply(User $user): ?Message
    {
        // Skip if the admin (or an auto-reply) already answered recently.
        $recentAdminReply = Message::where('user_id', $user->id)
            ->where('from_admin', true)
            ->where('created_at', '>=', now()->subMinutes(self::AUTO_REPLY_COOLDOWN_MINUTES))
            ->exists();

        if ($recentAdminReply) {
            return null;
        }

        return Message::create([
            'user_id' => $user->id,
            'sender_id' => null,
            'from_admin' => true,
            'is_auto' => true,
            'body' => self::adminOnline() ? self::AUTO_REPLY_ONLINE : self::AUTO_REPLY_OFFLINE,
        ]);
    }

    /* ================= CUSTOMER / DRIVER ================= */

    // Polled by the chat widget. ?after=<id> returns only newer messages;
    // ?open=1 means the panel is open, so admin replies count as read.
    public function fetch(Request $request)
    {
        $user = Auth::user();
        abort_if($user->role === 'admin', 403);

        if ($request->boolean('open')) {
            Message::where('user_id', $user->id)
                ->where('from_admin', true)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $messages = Message::where('user_id', $user->id)
            ->where('id', '>', (int) $request->query('after', 0))
            ->orderBy('id')
            ->get()
            ->map->toChatArray();

        $unread = Message::where('user_id', $user->id)
            ->where('from_admin', true)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'messages' => $messages,
            'unread' => $unread,
            'admin_online' => self::adminOnline(),
        ]);
    }

    public function send(Request $request)
    {
        $user = Auth::user();
        abort_if($user->role === 'admin', 403);

        $data = $request->validate(['body' => 'required|string|max:1000']);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'from_admin' => false,
            'body' => trim($data['body']),
        ]);

        $auto = $this->maybeAutoReply($user);

        return response()->json([
            'message' => $message->toChatArray(),
            'auto_reply' => $auto?->toChatArray(),
        ]);
    }

    /* ================= ADMIN ================= */

    public function adminIndex()
    {
        return view('admin.messages');
    }

    // Conversation list, newest activity first.
    public function adminThreads()
    {
        self::touchAdmin();

        $latest = Message::select('user_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('user_id');

        $threads = Message::joinSub($latest, 'latest', 'messages.id', '=', 'latest.last_id')
            ->join('users', 'users.id', '=', 'messages.user_id')
            ->select(
                'messages.user_id', 'messages.body', 'messages.from_admin', 'messages.is_auto', 'messages.created_at',
                'users.first_name', 'users.last_name', 'users.email', 'users.role'
            )
            ->orderByDesc('messages.id')
            ->get();

        $unread = Message::where('from_admin', false)
            ->whereNull('read_at')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        return response()->json([
            'threads' => $threads->map(fn ($t) => [
                'user_id' => $t->user_id,
                'name' => trim($t->first_name . ' ' . $t->last_name) ?: $t->email,
                'email' => $t->email,
                'role' => $t->role,
                'preview' => ($t->is_auto ? 'Auto-reply: ' : ($t->from_admin ? 'You: ' : '')) . \Illuminate\Support\Str::limit($t->body, 60),
                'time' => \Carbon\Carbon::parse($t->created_at)->timezone('Asia/Manila')->format('M j, g:i A'),
                'unread' => (int) ($unread[$t->user_id] ?? 0),
            ]),
            'total_unread' => (int) $unread->sum(),
        ]);
    }

    public function adminThread(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        Message::where('user_id', $user->id)
            ->where('from_admin', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where('user_id', $user->id)
            ->where('id', '>', (int) $request->query('after', 0))
            ->orderBy('id')
            ->get()
            ->map->toChatArray();

        return response()->json(['messages' => $messages]);
    }

    public function adminSend(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        abort_if($user->role === 'admin', 422);

        $data = $request->validate(['body' => 'required|string|max:1000']);

        $message = Message::create([
            'user_id' => $user->id,
            'sender_id' => Auth::id(),
            'from_admin' => true,
            'body' => trim($data['body']),
        ]);

        return response()->json(['message' => $message->toChatArray()]);
    }

    // Pinged by every admin page: keeps the admin "online" and refreshes the badge.
    public function adminHeartbeat()
    {
        self::touchAdmin();

        return response()->json(['unread' => self::adminUnreadCount()]);
    }

    // Used by the sidebar badge on every admin page.
    public static function adminUnreadCount(): int
    {
        return Message::where('from_admin', false)->whereNull('read_at')->count();
    }
}
