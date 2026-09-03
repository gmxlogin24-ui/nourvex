@extends('layouts.app-admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Feature Gates</h1>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Feature</th>
                <th class="py-2">Required Tier</th>
                <th class="py-2">Status</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gates as $gate)
                <tr class="border-b">
                    <td class="py-2">
                        <div class="font-medium">{{ $gate->label }}</div>
                        <div class="text-xs text-gray-500">{{ $gate->feature_key }}</div>
                    </td>
                    <td class="py-2">
                        <form method="POST" action="{{ route('admin.feature-gates.update', $gate) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="required_tier" class="border rounded px-2 py-1">
                                @foreach (['free', 'tier1', 'tier2', 'tier3'] as $t)
                                    <option value="{{ $t }}" @selected($gate->required_tier === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="text-sm text-blue-600 underline">Save</button>
                        </form>
                    </td>
                    <td class="py-2">
                        <span class="{{ $gate->enabled ? 'text-green-700' : 'text-red-700' }}">
                            {{ $gate->enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </td>
                    <td class="py-2">
                        <form method="POST" action="{{ route('admin.feature-gates.toggle', $gate) }}">
                            @csrf
                            <button type="submit" class="text-sm underline {{ $gate->enabled ? 'text-red-600' : 'text-green-600' }}">
                                {{ $gate->enabled ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
