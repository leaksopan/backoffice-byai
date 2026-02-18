@extends('layouts.module')

@section('title', 'Jalankan Alokasi Baru')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Jalankan Alokasi Baru</h1>
        <p class="text-gray-600 mt-2">Setup periode untuk menjalankan proses alokasi biaya</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('ccm.allocation-process.execute') }}" method="POST" id="allocationForm">
            @csrf

            <div class="mb-4">
                <label for="period_start" class="block text-gray-700 text-sm font-bold mb-2">
                    Tanggal Mulai Periode
                </label>
                <input type="date" 
                       name="period_start" 
                       id="period_start" 
                       value="{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('period_start') border-red-500 @enderror"
                       required>
                @error('period_start')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="period_end" class="block text-gray-700 text-sm font-bold mb-2">
                    Tanggal Akhir Periode
                </label>
                <input type="date" 
                       name="period_end" 
                       id="period_end" 
                       value="{{ old('period_end', now()->endOfMonth()->format('Y-m-d')) }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('period_end') border-red-500 @enderror"
                       required>
                @error('period_end')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Proses alokasi akan menjalankan semua allocation rules yang aktif dan approved untuk periode yang dipilih.
                            Pastikan semua data biaya sudah lengkap sebelum menjalankan alokasi.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('ccm.allocation-process.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        id="submitBtn">
                    Jalankan Alokasi
                </button>
            </div>
        </form>
    </div>

    <!-- Progress Modal (hidden by default) -->
    <div id="progressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Memproses Alokasi</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="progressMessage">
                        Mohon tunggu, proses alokasi sedang berjalan...
                    </p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('allocationForm').addEventListener('submit', function(e) {
    // Show progress modal
    document.getElementById('progressModal').classList.remove('hidden');
    document.getElementById('submitBtn').disabled = true;
});
</script>
@endpush
@endsection
