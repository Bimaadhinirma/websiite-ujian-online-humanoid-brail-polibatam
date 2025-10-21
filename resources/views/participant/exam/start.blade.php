<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $period->name }}</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-sm sm:text-lg md:text-xl font-bold text-gray-800">{{ $period->name }}</h1>
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
        <form method="POST" action="{{ route('participant.exam.submit', $userAnswer->id) }}" id="examForm">
            @csrf

            <div class="flex justify-end mb-4">
                <div class="bg-white px-4 py-2 rounded shadow text-sm">
                    Waktu tersisa: <span id="countdown">--:--:--</span>
                </div>
            </div>

            @foreach($period->categories->sortBy('order') as $category)
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2">{{ $category->name }}</h3>
                    @if($category->descriptions)
                        <p class="text-sm sm:text-base text-gray-600 mb-4">{{ $category->descriptions }}</p>
                    @endif

                    <div class="space-y-6">
                        @foreach($category->questions->sortBy('order') as $index => $question)
                            <div class="border-b border-gray-200 pb-4">
                                @if($question->image)
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/'.$question->image) }}" alt="Soal Gambar" style="max-width:100%;max-height:500px;object-fit:contain;background:#f8fafc;" class="border rounded">
                                        <p class="text-xs text-gray-500 mt-1">Gambar soal</p>
                                    </div>
                                @endif
                                <p class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">
                                    {{ $index + 1 }}. {!! $question->question !!}
                                    <span class="text-xs sm:text-sm text-blue-600">(Bobot: {{ $question->grade }})</span>
                                </p>

                                @if($question->type === 'options')
                                    <div class="space-y-2">
                                        @foreach($question->options->sortBy('order') as $option)
                                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                                <input type="radio" 
                                                    name="answers[{{ $question->id }}]" 
                                                    value="{{ $option->id }}" 
                                                    class="form-radio text-blue-600"
                                                    required>
                                                <span class="text-gray-700">{{ $option->option }}</span>
                                                @if($option->image)
                                                    <img src="{{ asset('storage/'.$option->image) }}" alt="Opsi Gambar" style="width:400px;height:400px;object-fit:contain;background:#f8fafc;" class="border rounded ml-2" />
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <input type="text" 
                                        name="answers[{{ $question->id }}]" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                                        placeholder="Masukkan jawaban Anda..."
                                        required>
                                @endif
                                
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    <a href="{{ route('participant.exam.index') }}" 
                        class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 text-center">
                        Kembali
                    </a>
                    <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
                        onclick="return confirm('Apakah Anda yakin ingin mengirim jawaban? Anda tidak bisa mengubah jawaban setelah dikirim.')">
                        Kirim Jawaban
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    // Exam duration in minutes provided by server (null means unlimited)
    const durationMinutes = {{ json_encode($period->duration_minutes) }};
    // Use a unique key per user, period, and userAnswer to persist remaining time across reloads
    const userId = {{ Auth::id() }};
    const periodId = {{ $period->id }};
    const storageKey = `exam_remaining_seconds_${userId}_${periodId}_{{ $userAnswer->id }}`;
    const storedDurationKey = `exam_duration_minutes_${userId}_${periodId}_{{ $userAnswer->id }}`;
    console.log('Exam durationMinutes from server:', durationMinutes);
    let remaining = parseInt(localStorage.getItem(storageKey));
    const storedDuration = parseInt(localStorage.getItem(storedDurationKey));

        // If admin changed the duration (or no stored duration), update it
        if (isNaN(storedDuration) || storedDuration !== Number(durationMinutes)) {
            if (durationMinutes !== null && !isNaN(durationMinutes) && parseInt(durationMinutes) > 0) {
                localStorage.setItem(storedDurationKey, parseInt(durationMinutes));
                remaining = parseInt(durationMinutes) * 60;
                localStorage.setItem(storageKey, remaining);
            } else {
                document.getElementById('countdown').textContent = '--:--:--';
            }
        } else {
            // storedDuration matches current duration; if remaining is missing or <=0, init it
            if (isNaN(remaining) || remaining === null || remaining <= 0) {
                if (durationMinutes !== null && !isNaN(durationMinutes) && parseInt(durationMinutes) > 0) {
                    remaining = parseInt(durationMinutes) * 60;
                    localStorage.setItem(storageKey, remaining);
                } else {
                    document.getElementById('countdown').textContent = '--:--:--';
                }
            }
        }

        const countdownEl = document.getElementById('countdown');

        function formatTime(sec) {
            const h = Math.floor(sec / 3600).toString().padStart(2, '0');
            const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(sec % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function tick() {
            if (remaining <= 0) {
                countdownEl.textContent = '00:00:00';
                // Auto-submit if time's up
                localStorage.removeItem(storageKey);
                // submit form via fetch with FormData to ensure CSRF token is included and to handle errors
                const submitForm = document.getElementById('examForm');
                const formData = new FormData(submitForm);
                const action = submitForm.action;

                // disable visible/interactive inputs to prevent further changes (best-effort)
                // but DO NOT disable hidden inputs (e.g. CSRF token) so the token remains in FormData
                document.querySelectorAll('#examForm input:not([type="hidden"]):not([name="_token"]), #examForm button, #examForm textarea, #examForm select').forEach(el => el.disabled = true);

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                }).then(resp => {
                    if (resp.ok) {
                        // on success redirect to results or reload
                        window.location.href = resp.url || window.location.href;
                    } else if (resp.status === 419) {
                        // CSRF/session expired — redirect to dashboard (or login)
                        alert('Sesi Anda telah berakhir. Anda akan dikembalikan ke dashboard. Jawaban mungkin belum tersubmit.');
                        window.location.href = '{{ route("participant.exam.index") }}';
                    } else {
                        // fallback to normal submit
                        submitForm.submit();
                    }
                }).catch(err => {
                    // network error -> fallback
                    submitForm.submit();
                });
                return;
            }

            countdownEl.textContent = formatTime(remaining);
            remaining -= 1;
            localStorage.setItem(storageKey, remaining);
        }

        // Start countdown immediately and then every second
        tick();
        const timerInterval = setInterval(tick, 1000);

        // Clear storage on manual submit
        const form = document.getElementById('examForm');
        form.addEventListener('submit', function() {
            localStorage.removeItem(storageKey);
            clearInterval(timerInterval);
        });

    // Auto save to localStorage (optional)
    const inputs = form.querySelectorAll('input[type="radio"], input[type="text"]');
    // Helper to get unique key for each answer
    function getAnswerKey(input) {
        // Extract question id from input name: answers[question_id]
        const match = input.name.match(/answers\[(\d+)\]/);
        const questionId = match ? match[1] : 'unknown';
        return `answer_${userId}_${periodId}_${questionId}`;
    }

    // Load saved answers
    inputs.forEach(input => {
        const key = getAnswerKey(input);
        const savedValue = localStorage.getItem(key);
        if (savedValue) {
            if (input.type === 'radio' && input.value === savedValue) {
                input.checked = true;
            } else if (input.type === 'text') {
                input.value = savedValue;
            }
        }
    });

    // Save on change
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const key = getAnswerKey(this);
            localStorage.setItem(key, this.value);
        });
    });

    // Clear localStorage on submit
    form.addEventListener('submit', function() {
        inputs.forEach(input => {
            const key = getAnswerKey(input);
            localStorage.removeItem(key);
        });
    });
    </script>
</body>
</html>
