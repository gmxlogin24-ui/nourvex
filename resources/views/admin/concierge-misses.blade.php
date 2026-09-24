@extends('layouts.app-admin')
@section('content')
<div class="p-6">
    <h1 class="text-xl font-bold mb-4">LEVI — things it didn't understand</h1>
    <p class="text-sm text-gray-500 mb-6">Sorted by how often each message came up. Add a keyword rule in KeywordConciergeService.php to cover the common ones, then mark handled.</p>
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Message</th>
                <th class="py-2">Times</th>
                <th class="py-2">Last seen</th>
                <th class="py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($misses as $miss)
            <tr class="border-b">
                <td class="py-2">{{ $miss->message }}</td>
                <td class="py-2">{{ $miss->occurrences }}</td>
                <td class="py-2">{{ $miss->updated_at->diffForHumans() }}</td>
                <td class="py-2">
                    <form method="POST" action="{{ route('admin.concierge-misses.resolve', $miss) }}">
                        @csrf
                        <button class="text-blue-600">Mark handled</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-6 text-center text-gray-400">Nothing unresolved right now.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $misses->links() }}
</div>
@endsection
