@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Platform Revenue</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-muted text-xs">All-Time</p>
            <p class="text-2xl font-bold mt-1">{!! \App\Support\Money::coin($totalAllTime) !!}</p>
        </div>
        <div class="stat-card">
            <p class="text-muted text-xs">This Month</p>
            <p class="text-2xl font-bold mt-1">{!! \App\Support\Money::coin($totalThisMonth) !!}</p>
        </div>
        <div class="stat-card">
            <p class="text-muted text-xs">This Week</p>
            <p class="text-2xl font-bold mt-1">{!! \App\Support\Money::coin($totalThisWeek) !!}</p>
        </div>
        <div class="stat-card">
            <p class="text-muted text-xs">Today</p>
            <p class="text-2xl font-bold mt-1">{!! \App\Support\Money::coin($totalToday) !!}</p>
        </div>
    </div>

    <div class="stat-card mb-6 overflow-x-auto">
        <h2 class="font-semibold mb-3">By Source</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Source</th>
                    <th class="py-2 pr-4">Total Earned</th>
                    <th class="py-2 pr-4">Transactions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bySource as $row)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ ucfirst($row->source) }}</td>
                    <td class="py-2 pr-4">{!! \App\Support\Money::coin($row->total) !!}</td>
                    <td class="py-2 pr-4">{{ $row->count }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-muted">No revenue recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="stat-card overflow-x-auto">
        <h2 class="font-semibold mb-3">Recent Entries</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Source</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Description</th>
                    <th class="py-2 pr-4">When</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $entry)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ ucfirst($entry->source) }}</td>
                    <td class="py-2 pr-4">{!! \App\Support\Money::coin($entry->amount) !!}</td>
                    <td class="py-2 pr-4 text-muted">{{ $entry->description }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $entry->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-muted">Nothing yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
