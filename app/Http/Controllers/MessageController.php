<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
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

        return response()->json(['messages' => $messages, 'unread' => $unread]);
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

        return response()->json(['message' => $message->toChatArray()]);
    }

    /* ================= ADMIN ================= */

    public function adminIndex()
    {
        return view('admin.messages');
    }

    // Conversation list, newest activity first.
    public function adminThreads()
    {
        $latest = Message::select('user_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('user_id');

        $threads = Message::joinSub($latest, 'latest', 'messages.id', '=', 'latest.last_id')
            ->join('users', 'users.id', '=', 'messages.user_id')
            ->select(
                'messages.user_id', 'messages.body', 'messages.from_admin', 'messages.created_at',
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
                'preview' => ($t->from_admin ? 'You: ' : '') . \Illuminate\Support\Str::limit($t->body, 60),
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

    // Used by the sidebar badge on every admin page.
    public static function adminUnreadCount(): int
    {
        return Message::where('from_admin', false)->whereNull('read_at')->count();
    }
}
