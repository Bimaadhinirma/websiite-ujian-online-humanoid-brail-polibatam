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
                    <h1 class="text-sm sm:text-lg md:text-xl font-bold text-gray-800">Ujian Humanoid</h1>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="hidden sm:block text-gray-700 text-sm">{{ Auth::user()->nama }}</span>
                    <a href="{{ route('change-password') }}" class="text-blue-600 hover:text-blue-800" title="Ganti Password">
                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-2 py-1 sm:px-4 sm:py-2 rounded hover:bg-red-700 text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('participant.exam.index') }}" class="text-blue-600 hover:text-blue-800 text-sm sm:text-base">
                ← Kembali ke Daftar Ujian
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Hasil Ujian: {{ $period->name }}</h2>
            <p class="text-sm sm:text-base text-gray-600">Status: <span class="font-semibold">{{ $userAnswer->status ? 'Selesai' : 'Belum Selesai' }}</span></p>
        </div>

        @if($period->show_grade)
            <!-- Nilai Keseluruhan -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 mb-6 text-white">
                <h3 class="text-lg sm:text-xl font-bold mb-4">Nilai Keseluruhan</h3>
                <div class="grid grid-cols-3 gap-2 sm:gap-4">
                    <div class="text-center">
                        <p class="text-xs sm:text-sm opacity-90">Total Bobot</p>
                        <p class="text-xl sm:text-3xl font-bold">{{ $detailedResult['overall']['total'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm opacity-90">Nilai Didapat</p>
                        <p class="text-xl sm:text-3xl font-bold">{{ $detailedResult['overall']['earned'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm opacity-90">Persentase</p>
                        <p class="text-xl sm:text-3xl font-bold">{{ number_format($detailedResult['overall']['percentage'], 2) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Nilai Per Kategori -->
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Nilai Per Kategori</h3>
                <div class="space-y-4">
                    @foreach($detailedResult['by_category'] as $category)
                        <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 gap-2">
                                <h4 class="text-base sm:text-lg font-semibold text-gray-800">{{ $category['category_name'] }}</h4>
                                <span class="text-xl sm:text-2xl font-bold 
                                    @if($category['percentage'] >= 80) text-green-600
                                    @elseif($category['percentage'] >= 60) text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ number_format($category['percentage'], 1) }}%
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <p>Nilai Didapat: <span class="font-semibold text-gray-800">{{ $category['earned_grade'] }}</span></p>
                                </div>
                                <div>
                                    <p>Total Bobot: <span class="font-semibold text-gray-800">{{ $category['total_grade'] }}</span></p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-500
                                    @if($category['percentage'] >= 80) bg-green-600
                                    @elseif($category['percentage'] >= 60) bg-yellow-600
                                    @else bg-red-600
                                    @endif" 
                                    style="width: {{ $category['percentage'] }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p class="font-semibold">Nilai belum dapat ditampilkan.</p>
                <p class="text-sm mt-1">Admin belum mengaktifkan fitur tampilan nilai untuk periode ini.</p>
            </div>
        @endif

        @if($period->show_result)
            <!-- Detail Jawaban -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Detail Jawaban Anda</h3>
                
                @foreach($period->categories->sortBy('order') as $category)
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">{{ $category->name }}</h4>

                        @foreach($category->questions->sortBy('order') as $index => $question)
                            @php
                                $item = $userAnswer->answerItems->where('question_id', $question->id)->first();
                                $answerKey = $question->answerKey;
                                $isCorrect = $item ? $item->isCorrect() : false;
                            @endphp

                            <div class="mb-4 p-4 border rounded-lg {{ $isCorrect ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }}">
                                <p class="font-semibold text-gray-800 mb-2">{{ $index + 1 }}. {{ $question->question }}</p>
                                @if(!empty($question->image))
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $question->image) }}" alt="Gambar soal {{ $index + 1 }}" class="max-w-full max-h-64 object-contain rounded border" />
                                    </div>
                                @endif

                                @if($question->type === 'options')
                                    <p class="text-gray-700">Jawaban Anda: <span class="font-semibold">{{ $item?->questionOption?->option ?? 'Tidak dijawab' }}</span></p>
                                    @if($answerKey)
                                        <p class="text-gray-600 text-sm mt-1">Jawaban Benar: <span class="font-semibold text-green-700">{{ $answerKey->questionOption->option ?? '-' }}</span></p>
                                    @endif
                                @else
                                    <p class="text-gray-700">Jawaban Anda: <span class="font-semibold">{{ $item?->answer ?? 'Tidak dijawab' }}</span></p>
                                    @if($answerKey)
                                        <p class="text-gray-600 text-sm mt-1">Jawaban Benar: <span class="font-semibold text-green-700">{{ $answerKey->key ?? '-' }}</span></p>
                                    @endif
                                @endif

                                <div class="mt-2">
                                    @if($isCorrect)
                                        <span class="inline-block bg-green-600 text-white text-xs px-2 py-1 rounded">✓ Benar (Bobot: {{ $question->grade }})</span>
                                    @else
                                        <span class="inline-block bg-red-600 text-white text-xs px-2 py-1 rounded">✗ Salah (Bobot: {{ $question->grade }})</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mt-6">
                <p class="font-semibold">Detail jawaban belum dapat ditampilkan.</p>
                <p class="text-sm mt-1">Admin belum mengaktifkan fitur review hasil untuk periode ini.</p>
            </div>
        @endif
    </div>
</body>
</html>
