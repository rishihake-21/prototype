@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage MSBTE Schemes</h1>
            <p class="text-sm text-gray-500 mt-1">Define structural blueprints (K-Scheme, I-Scheme) that dictate curriculum levels.</p>
        </div>
        <a href="{{ route('cdc.schemes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Scheme
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($schemes as $scheme)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition">
            <div class="p-6 flex-1">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">{{ $scheme->name }}</h3>
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $scheme->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                        {{ $scheme->is_active ? 'Active' : 'Legacy' }}
                    </span>
                </div>
                
                <div class="space-y-2 mb-6">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Implemented Year: <strong class="ml-1">{{ $scheme->implemented_year ?? 'N/A' }}</strong>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Programmes Linked: <strong class="ml-1">{{ $scheme->programmes_count }}</strong>
                    </div>
                </div>

                @if($scheme->description)
                <p class="text-xs text-gray-500 line-clamp-2">{{ $scheme->description }}</p>
                @endif
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('cdc.schemes.edit', $scheme) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    Edit Configuration
                </a>
                
                <form action="{{ route('cdc.schemes.destroy', $scheme) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this scheme?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700" {{ $scheme->programmes_count > 0 ? 'disabled title="Cannot delete scheme with attached programmes"' : '' }}>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center bg-white rounded-xl border border-gray-100 border-dashed">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <h3 class="text-sm font-medium text-gray-900">No schemes defined</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new curriculum scheme (e.g., K-Scheme).</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
