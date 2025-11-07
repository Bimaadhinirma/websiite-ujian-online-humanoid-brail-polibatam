<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian - {{ $period->name }}</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Admin - Ujian Humanoid</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">{{ Auth::user()->nama }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('admin.periods.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali ke Daftar Periode
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Hasil Ujian: {{ $period->name }}</h2>
                    <span class="px-2 py-1 rounded text-xs {{ $period->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $period->status ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
                <div class="space-y-2">
                    <div>
                        <a href="{{ route('admin.results.export', $period->id) }}" 
                           class="block w-full px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-center">
                            Export to Excel
                        </a>
                    </div>
                    <form method="POST" action="{{ route('admin.results.toggle-show-grade', $period->id) }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 rounded {{ $period->show_grade ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500' }} text-white">
                            {{ $period->show_grade ? '✓ Nilai Ditampilkan' : '✗ Nilai Disembunyikan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.results.toggle-show-result', $period->id) }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 rounded {{ $period->show_result ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500' }} text-white">
                            {{ $period->show_result ? '✓ Hasil Dapat Direview' : '✗ Hasil Disembunyikan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Participant dan Nilai Per Kategori</h3>
            
            @if(count($results) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Pengerjaan</th>
                                @foreach($results[0]['by_category'] as $cat)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $cat['category_name'] }}</th>
                                @endforeach
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($results as $result)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $result['user']->nama }}</div>
                                        <div class="text-sm text-gray-500">{{ $result['user']->username }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $result['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $result['status'] ? 'Selesai' : 'Belum Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ $result['overall']['earned'] }}/{{ $result['overall']['total'] }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ number_format($result['overall']['percentage'], 1) }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if(isset($result['elapsed_seconds']) && $result['elapsed_seconds'] !== null && intval($result['elapsed_seconds']) >= 0)
                                            {{ gmdate('H:i:s', intval($result['elapsed_seconds'])) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @foreach($result['by_category'] as $category)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold 
                                                @if($category['percentage'] >= 80) text-green-600
                                                @elseif($category['percentage'] >= 60) text-yellow-600
                                                @else text-red-600
                                                @endif">
                                                {{ $category['earned_grade'] }}/{{ $category['total_grade'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ number_format($category['percentage'], 1) }}%
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.results.detail', $result['user_answer_id']) }}" 
                                            class="text-blue-600 hover:text-blue-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600 text-center py-8">Belum ada participant yang mengerjakan ujian ini.</p>
            @endif
        </div>

        <!-- Statistik Kategori -->
        @if(count($results) > 0)
            <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Statistik Per Kategori</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($results[0]['by_category'] as $index => $cat)
                        @php
                            $categoryScores = array_column(array_column($results, 'by_category'), $index);
                            $avgPercentage = collect($categoryScores)->avg('percentage');
                            $maxPercentage = collect($categoryScores)->max('percentage');
                            $minPercentage = collect($categoryScores)->min('percentage');
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">{{ $cat['category_name'] }}</h4>
                            <div class="space-y-1 text-sm text-gray-600">
                                <p>Rata-rata: <span class="font-semibold text-blue-600">{{ number_format($avgPercentage, 1) }}%</span></p>
                                <p>Tertinggi: <span class="font-semibold text-green-600">{{ number_format($maxPercentage, 1) }}%</span></p>
                                <p>Terendah: <span class="font-semibold text-red-600">{{ number_format($minPercentage, 1) }}%</span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>
