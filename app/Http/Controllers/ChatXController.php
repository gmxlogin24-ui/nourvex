<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatXController extends Controller
{
    private function part($cid)
    {
        $p = DB::table('conversation_participants')->where('conversation_id', $cid)->where('user_id', Auth::id())->first();
        abort_unless($p, 403);
        return $p;
    }

    private function seen(): void
    {
        DB::table('users')->where('id', Auth::id())->update(['last_seen_at' => now()]);
    }

    private function statusOf($u): string
    {
        if (!$u || !$u->last_seen_at) return '';
        $c = Carbon::parse($u->last_seen_at);
        if ($c->gt(now()->subSeconds(45))) return 'online';
        return 'last seen ' . ($c->isToday() ? 'today at ' : ($c->isYesterday() ? 'yesterday at ' : $c->format('d M') . ' at ')) . $c->format('H:i');
    }

    private function peer($cid, $me): array
    {
        $ps = DB::table('conversation_participants')->where('conversation_id', $cid)
            ->where(function ($q) use ($me) { $q->where('user_id', '!=', $me)->orWhereNull('user_id'); })->get();
        $us = DB::table('users')->whereIn('id', $ps->pluck('user_id')->filter()->all())->get()->keyBy('id');
        if ($ps->count() > 1) {
            $c = DB::table('conversations')->where('id', $cid)->first();
            $n = ($c && $c->title) ? $c->title : $us->map(fn ($u) => $u->name ?: $u->username)->take(3)->implode(', ');
            return ['name' => $n ?: 'Group', 'avatar' => null, 'status' => ($ps->count() + 1) . ' members', 'online' => false, 'id' => 0, 'group' => true];
        }
        $p = $ps->first();
        $u = ($p && $p->user_id) ? ($us[$p->user_id] ?? null) : null;
        if (!$u) return ['name' => 'Assistant', 'avatar' => null, 'status' => '', 'online' => false, 'id' => 0, 'group' => false];
        $st = $this->statusOf($u);
        return ['name' => $u->name ?: $u->username, 'avatar' => $u->avatar_url, 'status' => $st, 'online' => $st === 'online', 'id' => $u->id, 'group' => false];
    }

    private function preview($m, $me): string
    {
        if (!$m) return 'No messages yet';
        if (!empty($m->deleted_at)) $p = 'This message was deleted';
        elseif ($m->message_type === 'image') $p = '📷 Photo';
        elseif ($m->message_type === 'voice') $p = '🎤 Voice note';
        elseif ($m->message_type === 'file') $p = '📎 File';
        else $p = (string) $m->body;
        return ($m->sender_id == $me ? 'You: ' : '') . Str::limit($p, 60);
    }

    private function fmt($t): string
    {
        $c = Carbon::parse($t);
        return $c->isToday() ? $c->format('H:i') : ($c->isYesterday() ? 'Yesterday' : $c->format('d/m/y'));
    }

    public function index()
    {
        $me = Auth::id();
        $this->seen();
        $reads = DB::table('conversation_participants')->where('user_id', $me)->pluck('last_read_at', 'conversation_id');
        $rows = [];
        foreach (DB::table('conversations')->whereIn('id', $reads->keys()->all())->orderByDesc('last_message_at')->get() as $c) {
            $last = DB::table('conversation_messages')->where('conversation_id', $c->id)->orderByDesc('id')->first();
            $uq = DB::table('conversation_messages')->where('conversation_id', $c->id)
                ->where(function ($q) use ($me) { $q->where('sender_id', '!=', $me)->orWhereNull('sender_id'); });
            if ($reads[$c->id]) $uq->where('created_at', '>', $reads[$c->id]);
            $pe = $this->peer($c->id, $me);
            $rows[] = ['id' => $c->id, 'peer' => $pe, 'preview' => $this->preview($last, $me), 'time' => $last ? $this->fmt($last->created_at) : '', 'unread' => (int) $uq->count()];
        }
        return view('cx.index', ['rows' => $rows]);
    }

    public function thread($id)
    {
        $this->part($id);
        $this->seen();
        return view('cx.thread', ['cid' => (int) $id, 'peer' => $this->peer($id, Auth::id())]);
    }

    public function messages(Request $r, $id)
    {
        $p = $this->part($id);
        $me = Auth::id();
        $this->seen();
        DB::table('conversation_participants')->where('id', $p->id)->update(['last_read_at' => now()]);

        $q = DB::table('conversation_messages')->where('conversation_id', $id);
        $s = trim((string) $r->query('q'));
        if ($s !== '') $q->where('body', 'like', '%' . $s . '%')->whereNull('deleted_at');
        $rows = $q->orderByDesc('id')->limit(100)->get()->reverse()->values();

        $others = DB::table('conversation_participants')->where('conversation_id', $id)->where('user_id', '!=', $me)->whereNotNull('user_id')->get();
        $readMax = $others->max('last_read_at') ? strtotime($others->max('last_read_at')) : 0;
        $ou = DB::table('users')->whereIn('id', $others->pluck('user_id')->all())->get();
        $seenMax = $ou->max('last_seen_at') ? strtotime($ou->max('last_seen_at')) : 0;
        $typing = $others->contains(fn ($o) => $o->typing_at && strtotime($o->typing_at) > time() - 4);

        $rep = DB::table('conversation_messages')->whereIn('id', $rows->pluck('reply_to_id')->filter()->unique()->all())->get()->keyBy('id');
        $sids = $rows->pluck('sender_id')->merge($rep->pluck('sender_id'))->filter()->unique()->all();
        $names = DB::table('users')->whereIn('id', $sids)->get()->keyBy('id');
        $nm = fn ($uid) => $uid == $me ? 'You' : (($names[$uid]->name ?? null) ?: 'Assistant');
        $rx = DB::table('message_reactions')->whereIn('message_id', $rows->pluck('id')->all())->get()->groupBy('message_id');

        $out = [];
        foreach ($rows as $m) {
            $meta = is_string($m->meta) ? (json_decode($m->meta, true) ?: null) : (is_array($m->meta) ? $m->meta : null);
            $ts = strtotime($m->created_at);
            $mine = $m->sender_id == $me;
            $reply = null;
            if ($m->reply_to_id && isset($rep[$m->reply_to_id])) {
                $rm = $rep[$m->reply_to_id];
                $reply = ['id' => $rm->id, 'name' => $nm($rm->sender_id), 'text' => $this->preview((object) array_merge((array) $rm, ['sender_id' => 0]), 0)];
            }
            $rl = [];
            foreach (($rx[$m->id] ?? collect())->groupBy('emoji') as $e => $g) $rl[] = ['e' => $e, 'n' => $g->count(), 'me' => $g->contains('user_id', $me)];
            $c = Carbon::parse($m->created_at);
            $out[] = [
                'id' => $m->id, 'mine' => $mine, 'sender' => $nm($m->sender_id),
                'type' => $m->message_type ?: 'text', 'body' => empty($m->deleted_at) ? $m->body : '',
                'meta' => $meta, 'deleted' => !empty($m->deleted_at), 'edited' => !empty($m->edited_at),
                'reply' => $reply, 'rx' => $rl, 'time' => $c->format('H:i'),
                'day' => $c->isToday() ? 'Today' : ($c->isYesterday() ? 'Yesterday' : $c->format('d M Y')),
                'status' => $mine ? ($ts <= $readMax ? 'read' : ($ts <= $seenMax ? 'delivered' : 'sent')) : '',
            ];
        }
        return response()->json(['messages' => $out, 'peer' => $this->peer($id, $me), 'typing' => $typing]);
    }

    public function send(Request $r, $id)
    {
        $this->part($id);
        $r->validate(['body' => 'nullable|string|max:4000', 'file' => 'nullable|file|max:20480', 'reply_to' => 'nullable|integer', 'dur' => 'nullable|integer', 'kind' => 'nullable|string|max:10']);
        $body = trim((string) $r->input('body', ''));
        $type = 'text'; $meta = null;
        if ($r->hasFile('file')) {
            $f = $r->file('file');
            $mime = (string) $f->getMimeType();
            $ext = strtolower($f->guessExtension() ?: 'bin');
            if (in_array($ext, ['php', 'phtml', 'phar', 'html', 'htm', 'js', 'svg', 'xml', 'sh', 'exe'])) $ext = 'bin';
            $name = time() . '_' . Str::random(8) . '.' . $ext;
            $size = $f->getSize();
            $orig = $f->getClientOriginalName();
            $f->move(public_path('uploads/chat'), $name);
            $type = $r->input('kind') === 'voice' ? 'voice' : (str_starts_with($mime, 'image/') && $ext !== 'bin' ? 'image' : 'file');
            $meta = json_encode(['url' => '/uploads/chat/' . $name, 'name' => $orig, 'size' => $size, 'dur' => (int) $r->input('dur', 0)]);
        }
        if ($body === '' && !$meta) return response()->json(['ok' => false], 422);
        $reply = $r->input('reply_to');
        if ($reply && !DB::table('conversation_messages')->where('id', $reply)->where('conversation_id', $id)->exists()) $reply = null;
        DB::table('conversation_messages')->insert([
            'conversation_id' => $id, 'sender_id' => Auth::id(), 'is_ai' => 0, 'is_system' => 0,
            'message_type' => $type, 'body' => $body, 'meta' => $meta, 'reply_to_id' => $reply,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('conversations')->where('id', $id)->update(['last_message_at' => now()]);
        DB::table('conversation_participants')->where('conversation_id', $id)->where('user_id', Auth::id())->update(['typing_at' => null, 'last_read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function typing($id)
    {
        $p = $this->part($id);
        DB::table('conversation_participants')->where('id', $p->id)->update(['typing_at' => now()]);
        return response()->json(['ok' => true]);
    }

    private function mine($mid)
    {
        $m = DB::table('conversation_messages')->where('id', $mid)->first();
        abort_unless($m, 404);
        $this->part($m->conversation_id);
        return $m;
    }

    public function edit(Request $r, $mid)
    {
        $m = $this->mine($mid);
        abort_unless($m->sender_id == Auth::id() && empty($m->deleted_at) && ($m->message_type ?: 'text') === 'text', 403);
        $b = trim((string) $r->validate(['body' => 'required|string|max:4000'])['body']);
        DB::table('conversation_messages')->where('id', $mid)->update(['body' => $b, 'edited_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function delete($mid)
    {
        $m = $this->mine($mid);
        abort_unless($m->sender_id == Auth::id(), 403);
        DB::table('conversation_messages')->where('id', $mid)->update(['deleted_at' => now(), 'body' => '', 'meta' => null]);
        return response()->json(['ok' => true]);
    }

    public function react(Request $r, $mid)
    {
        $this->mine($mid);
        $e = (string) $r->input('emoji');
        abort_unless(in_array($e, ['👍', '❤️', '😂', '😮', '😢', '🙏']), 422);
        $q = DB::table('message_reactions')->where('message_id', $mid)->where('user_id', Auth::id());
        $cur = $q->first();
        if ($cur && $cur->emoji === $e) $q->delete();
        elseif ($cur) $q->update(['emoji' => $e, 'updated_at' => now()]);
        else DB::table('message_reactions')->insert(['message_id' => $mid, 'user_id' => Auth::id(), 'emoji' => $e, 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function people(Request $r)
    {
        $s = trim((string) $r->query('q'));
        $q = DB::table('users')->where('id', '!=', Auth::id())->where(function ($w) {
            $w->whereNull('is_banned')->orWhere('is_banned', 0);
        });
        if ($s !== '') $q->where(function ($w) use ($s) { $w->where('name', 'like', "%$s%")->orWhere('username', 'like', "%$s%"); });
        return response()->json($q->orderByDesc('id')->limit(15)->get(['id', 'name', 'username', 'avatar_url'])->map(fn ($u) => ['id' => $u->id, 'name' => $u->name ?: $u->username, 'sub' => $u->username ? '@' . $u->username : '', 'avatar' => $u->avatar_url])->values());
    }

    public function start(Request $r)
    {
        $me = Auth::id();
        $uid = (int) $r->input('user_id');
        abort_unless($uid && $uid !== $me && DB::table('users')->where('id', $uid)->exists(), 422);
        $cid = DB::table('conversation_participants as a')
            ->join('conversation_participants as b', 'a.conversation_id', '=', 'b.conversation_id')
            ->join('conversations as c', 'c.id', '=', 'a.conversation_id')
            ->where('a.user_id', $me)->where('b.user_id', $uid)->whereNull('c.subject_type')->value('a.conversation_id');
        if (!$cid) {
            $row = ['title' => null, 'last_message_at' => now(), 'created_at' => now(), 'updated_at' => now()];
            try { $cid = DB::table('conversations')->insertGetId($row + ['type' => 'direct']); }
            catch (\Throwable $e) { $cid = DB::table('conversations')->insertGetId($row + ['type' => DB::table('conversations')->whereNull('subject_type')->value('type') ?: (DB::table('conversations')->value('type') ?: 'direct')]); }
            foreach ([$me, $uid] as $u) DB::table('conversation_participants')->insert(['conversation_id' => $cid, 'user_id' => $u, 'is_ai' => 0, 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }
        return response()->json(['ok' => true, 'url' => route('cx.thread', $cid)]);
    }
}
