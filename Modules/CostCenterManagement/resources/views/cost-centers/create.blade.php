@extends('layouts.module')

@section('title', 'Create Cost Center')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Create Cost Center</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Tambah cost center baru</p>
        </div>
        <a href="{{ route('ccm.cost-centers.index') }}" class="btn-ghost">
            Back to List
        </a>
    </div>

    @livewire('cost-center-management::cost-center-form')
</div>
@endsection
