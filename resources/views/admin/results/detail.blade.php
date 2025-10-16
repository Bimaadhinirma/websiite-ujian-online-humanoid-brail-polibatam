<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Hasil Ujian</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    @include('admin.layouts.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('admin.results.show', $userAnswer->period_id) }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar Hasil
            </a>
        </div>

        <!-- Header Info -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Detail Hasil Ujian</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Participant</p>
                    <p class="font-semibold text-gray-900">{{ $userAnswer->user->nama }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Periode</p>
                    <p class="font-semibold text-gray-900">{{ $userAnswer->period->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Waktu Pengerjaan</p>
                    <p class="font-semibold text-gray-900">{{ $userAnswer->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Nilai</p>
                    @php
                        $earned = $detailedResult['totalScore'] ?? 0;
                        $totalPossible = $detailedResult['totalQuestions'] ?? 0;
                        $percentTotal = $totalPossible > 0 ? ($earned / $totalPossible) * 100 : 0;
                    @endphp
                    <p class="font-semibold text-blue-600 text-lg">
                        {{ number_format($earned, 1) }} / {{ $totalPossible }}
                        <span class="text-sm text-gray-600">({{ number_format($percentTotal, 1) }}%)</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Category Grades -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Nilai Per Kategori</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($detailedResult['categoryGrades'] as $categoryGrade)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">{{ $categoryGrade['category'] }}</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($categoryGrade['score'], 1) }}</p>
                            <p class="text-sm text-gray-500">/ {{ $categoryGrade['total'] }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($categoryGrade['percentage'], 1) }}%</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Detailed Answers -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Jawaban</h3>
            
            @foreach($detailedResult['categoryGrades'] as $categoryGrade)
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b">{{ $categoryGrade['category'] }}</h4>
                    
                    @foreach($userAnswer->period->categories as $category)
                        @if($category->name === $categoryGrade['category'])
                            @foreach($category->questions()->orderBy('order')->get() as $index => $question)
                                @php
                                    $userAnswerItem = $userAnswer->answerItems->where('question_id', $question->id)->first();
                                    $answerKey = $question->answerKey;
                                    // Reuse the model's grading logic so admin view matches participant scoring
                                    $isCorrect = $userAnswerItem ? (bool) $userAnswerItem->isCorrect() : false;
                                @endphp
                                
                                <div class="mb-4 p-3 sm:p-4 rounded-lg {{ $isCorrect ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                    <div class="flex items-start gap-2 mb-2">
                                        <span class="font-semibold text-gray-700">{{ $index + 1 }}.</span>
                                        <div class="flex-1">
                                            <p class="text-gray-800 mb-2">{{ $question->question }}</p>
                                            @if(!empty($question->image))
                                                <div class="mb-3">
                                                    <img src="{{ asset('storage/' . $question->image) }}" alt="Gambar soal {{ $index + 1 }}" class="max-w-full max-h-64 object-contain rounded border" />
                                                </div>
                                            @endif
                                            
                                            @if($question->type === 'options')
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-600">Jawaban Peserta:</p>
                                                    <p class="font-medium {{ $isCorrect ? 'text-green-700' : 'text-red-700' }}">
                                                        {{ $userAnswerItem?->questionOption?->option ?? 'Tidak dijawab' }}
                                                    </p>
                                                </div>
                                                @if($answerKey)
                                                    <div class="mt-2">
                                                        <p class="text-sm text-gray-600">Jawaban Benar:</p>
                                                        <p class="font-medium text-green-700">{{ $answerKey->questionOption->option }}</p>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-600">Jawaban Peserta:</p>
                                                    <p class="font-medium {{ $isCorrect ? 'text-green-700' : 'text-red-700' }}">
                                                        {{ $userAnswerItem?->answer ?? 'Tidak dijawab' }}
                                                    </p>
                                                </div>
                                                @if($answerKey)
                                                    <div class="mt-2">
                                                        <p class="text-sm text-gray-600">Jawaban Benar:</p>
                                                        <p class="font-medium text-green-700">{{ $answerKey->key }}</p>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <span class="flex-shrink-0">
                                            @if($isCorrect)
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
