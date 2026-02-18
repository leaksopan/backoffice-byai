<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Informasi Depresiasi
        </x-slot>

        @if(!$isDepreciable)
            <div class="text-gray-500">
                Aset ini tidak memiliki pengaturan depresiasi.
            </div>
        @else
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Depresiasi Bulanan</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($monthlyDepreciation, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Akumulasi Depresiasi</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($accumulatedDepreciation, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Nilai Buku</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($bookValue, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Jadwal Depresiasi 12 Bulan Ke Depan
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bulan</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Akumulasi Depresiasi</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nilai Buku</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($schedule as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $item['month'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">
                                            Rp {{ number_format($item['accumulated'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">
                                            Rp {{ number_format($item['book_value'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <p>Metode: {{ ucfirst(str_replace('_', ' ', $depreciationMethod)) }}</p>
                    <p>Umur Ekonomis: {{ $usefulLifeYears }} tahun</p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
