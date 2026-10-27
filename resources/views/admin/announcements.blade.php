@extends('layouts.app-admin')
@section('content')
@php
  $in = 'width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);background:transparent;color:inherit;';
@endphp
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Announcements</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ff6b6b;"><p>{{ $errors->first() }}</p></div>
    @endif

    <div class="stat-card mb-6">
        <h2 class="font-semibold mb-1">Post an announcement</h2>
        <p class="text-muted text-sm mb-3">It shows as a big card on every user's overview page until you turn it off or it expires.</p>
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" style="display:grid;gap:10px;">
            @csrf
            <input name="title" maxlength="80" required placeholder="Title (max 80 characters)" value="{{ old('title') }}" style="{{ $in }}">
            <textarea name="body" rows="3" maxlength="220" placeholder="Message (max 220 characters)" style="{{ $in }}">{{ old('body') }}</textarea>
            <input name="link_url" placeholder="Link when tapped (optional), like /gigs or https://..." value="{{ old('link_url') }}" style="{{ $in }}">
            <select name="color" style="{{ $in }}">
                @foreach(['orange','teal','green','purple','blue','rose'] as $c)
                <option value="{{ $c }}" @selected(old('color') === $c)>{{ ucfirst($c) }} card</option>
                @endforeach
            </select>
            <label class="text-muted text-sm">Picture (optional)
                <input type="file" name="image" accept="image/*" style="display:block;margin-top:4px;">
            </label>
            <label class="text-muted text-sm">Stop showing on (optional)
                <input type="date" name="expires_at" value="{{ old('expires_at') }}" style="{{ $in }}margin-top:4px;">
            </label>
            <button type="submit" class="balance-chip primary" style="justify-self:start;">Post announcement</button>
        </form>
    </div>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Title</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Posted</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $a)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $a->title }}</td>
                    <td class="py-2 pr-4">{{ ! $a->is_active ? 'Off' : (($a->expires_at && $a->expires_at->isPast()) ? 'Expired' : 'Live') }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $a->created_at?->format('M j, Y') }}</td>
                    <td class="py-2 pr-4">
                        <div style="display:flex;gap:8px;">
                            <form method="POST" action="{{ route('admin.announcements.toggle', $a) }}">
                                @csrf
                                <button class="balance-chip" style="font-size:0.75rem;">{{ $a->is_active ? 'Turn off' : 'Turn on' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" onsubmit="return confirm('Delete this announcement?');">
                                @csrf @method('DELETE')
                                <button class="balance-chip" style="font-size:0.75rem;color:#ff6b6b;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-muted">Nothing posted yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
