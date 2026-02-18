@extends('layouts.module')

@section('title', 'Cost Centers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Cost Centers</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Kelola cost center dan responsibility center</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ccm.cost-centers.tree') }}" class="btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                Tree View
            </a>
            @can('cost-center-management.create')
                <a href="{{ route('ccm.cost-centers.create') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Cost Center
                </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card border-green-500/50 bg-green-50/50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card border-red-500/50 bg-red-50/50 dark:bg-red-900/20 p-4">
            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Livewire Filament Table Component --}}
    @livewire('cost-center-management::cost-center-table')
</div>
@endsection
