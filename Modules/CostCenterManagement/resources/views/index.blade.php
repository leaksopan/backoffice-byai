@extends('layouts.module')

@section('title', 'Cost Center Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Cost Center Management</h1>
        <p class="mt-2 text-gray-600">Modul pengelolaan pusat biaya dan pusat pertanggungjawaban</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Cost Centers Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Cost Centers</h2>
            </div>
            <p class="text-gray-600 mb-4">Kelola pusat biaya dan hierarki organisasi</p>
            <a href="{{ route('ccm.cost-centers.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                Lihat Cost Centers
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Dashboard Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Dashboard</h2>
            </div>
            <p class="text-gray-600 mb-4">Monitoring biaya dan analisis variance</p>
            <a href="{{ route('ccm.dashboard.index') }}" class="inline-flex items-center text-green-600 hover:text-green-800">
                Buka Dashboard
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Allocation Process Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Allocation Process</h2>
            </div>
            <p class="text-gray-600 mb-4">Proses alokasi biaya antar cost center</p>
            <a href="{{ route('ccm.allocation-process.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800">
                Kelola Alokasi
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Reports Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Reports</h2>
            </div>
            <p class="text-gray-600 mb-4">Laporan dan analisis cost center</p>
            <a href="{{ route('ccm.reports.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-800">
                Lihat Laporan
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Approval Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Approval</h2>
            </div>
            <p class="text-gray-600 mb-4">Approval allocation rules dan budget revisions</p>
            <a href="{{ route('ccm.approval.allocation-rules') }}" class="inline-flex items-center text-red-600 hover:text-red-800">
                Lihat Approval
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Audit Trail Card -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h2 class="ml-3 text-xl font-semibold text-gray-900">Audit Trail</h2>
            </div>
            <p class="text-gray-600 mb-4">Tracking perubahan dan aktivitas user</p>
            <a href="{{ route('ccm.audit-trail.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
                Lihat Audit Trail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="text-blue-600 text-sm font-medium">Total Cost Centers</div>
            <div class="text-2xl font-bold text-blue-900 mt-1">{{ \Modules\CostCenterManagement\Models\CostCenter::count() }}</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <div class="text-green-600 text-sm font-medium">Active Cost Centers</div>
            <div class="text-2xl font-bold text-green-900 mt-1">{{ \Modules\CostCenterManagement\Models\CostCenter::where('is_active', true)->count() }}</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="text-purple-600 text-sm font-medium">Allocation Rules</div>
            <div class="text-2xl font-bold text-purple-900 mt-1">{{ \Modules\CostCenterManagement\Models\AllocationRule::count() }}</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4">
            <div class="text-orange-600 text-sm font-medium">Service Lines</div>
            <div class="text-2xl font-bold text-orange-900 mt-1">{{ \Modules\CostCenterManagement\Models\ServiceLine::count() }}</div>
        </div>
    </div>
</div>
@endsection
