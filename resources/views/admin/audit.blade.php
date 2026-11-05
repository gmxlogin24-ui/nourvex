@extends('layouts.app-admin')
@section('title', 'Audit log')
@section('content')
<div class="ax-card">
  <h2 class="ax-h">Audit log <small>last 300 admin actions</small></h2>
  <div class="ax-scroll"><table class="ax-tbl">
    <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>IP</th><th>Data</th></tr></thead>
    <tbody>
    @forelse($rows as $r)
      <tr><td>{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d M H:i') }}</td>
      <td>{{ $r->name ?: ($r->username ?: '#'.$r->user_id) }}</td>
      <td><b>{{ $r->method }}</b> /{{ $r->path }}</td><td>{{ $r->ip }}</td>
      <td class="ax-dim">{{ \Illuminate\Support\Str::limit($r->payload, 90) }}</td></tr>
    @empty
      <tr><td colspan="5" class="ax-dim">Nothing logged yet.</td></tr>
    @endforelse
    </tbody></table></div>
</div>
@endsection
