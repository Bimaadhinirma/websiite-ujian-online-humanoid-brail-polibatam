<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $period->name }}</title>
    <script src="/js/tailwind.js"></script>
    <style>
        /* Ensure TinyMCE content is responsive and long words wrap */
        .tinymce-content { 
            word-break: break-word;
            overflow-wrap: break-word;
            -webkit-font-smoothing: antialiased;
            white-space: normal;
            max-width: 100%;
        }
        .tinymce-content img, .question-image, .option-image {
            max-width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
            display: block;
        }
        /* Ensure tables and other wide elements inside TinyMCE are scrollable / responsive */
        .tinymce-content table {
            display: block;
            width: 100% !important;
            max-width: 100% !important;
            overflow: auto;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .tinymce-content th, .tinymce-content td {
            word-break: break-word;
            overflow-wrap: break-word;
        }
        /* Prevent horizontal overflow from labels with large images */
        .option-label { 
            word-break: break-word;
            width: 100%;
            flex-wrap: wrap;
        }
        .option-label .flex-1 { min-width: 0; }
        .question-card { overflow: hidden; }
        .question-image { max-width:100% !important; max-height:40vh !important; height:auto !important; display:block; margin:0 auto; }
        .option-image { max-width:100% !important; max-height:30vh !important; height:auto !important; display:block; }
    </style>
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
            <input type="hidden" name="elapsed_seconds" id="elapsed_seconds" value="">

            <div class="flex justify-end mb-4">
                <div class="bg-white px-4 py-2 rounded shadow text-sm">
                    Waktu tersisa: <span id="countdown">--:--:--</span>
                </div>
            </div>

            <!-- Floating countdown (always visible while scrolling) -->
            <div id="floating-countdown" class="fixed top-4 right-4 z-50">
                <div class="bg-white px-3 py-2 rounded shadow text-sm flex items-center space-x-2">
                    <span class="hidden sm:inline text-gray-700">Waktu tersisa:</span>
                    <span id="floatingCountdown" class="font-semibold text-gray-800">--:--:--</span>
                </div>
            </div>

            <!-- Offline banner shown when client cannot reach server -->
            <div id="offline-banner" class="hidden fixed top-16 right-4 z-50">
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 px-4 py-2 rounded shadow text-sm">
                    <strong>Terputus:</strong> Koneksi ke server ujian terputus. Anda tidak dapat mengubah jawaban sampai koneksi pulih.
                </div>
            </div>

            @php
                // Build ordered categories: prefer persisted order from userAnswer, fallback to period settings
                $allCategories = $period->categories->keyBy('id');
                if (!empty($userAnswer) && !empty($userAnswer->category_order)) {
                    $orderedCategories = collect($userAnswer->category_order)->map(function($id) use ($allCategories) { return $allCategories->get($id); })->filter();
                } else {
                    $orderedCategories = $period->is_random_questions ? $period->categories->shuffle() : $period->categories->sortBy('order');
                }
            @endphp

            @foreach($orderedCategories as $category)
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2">{{ $category->name }}</h3>
                    @if($category->descriptions)
                        <p class="text-sm sm:text-base text-gray-600 mb-4">{{ $category->descriptions }}</p>
                    @endif

                    <div class="space-y-6">
                        @php
                            $allQuestions = $category->questions->keyBy('id');
                            if (!empty($userAnswer) && !empty($userAnswer->question_order) && array_key_exists($category->id, $userAnswer->question_order)) {
                                $orderedQuestions = collect($userAnswer->question_order[$category->id])->map(function($id) use ($allQuestions) { return $allQuestions->get($id); })->filter();
                            } else {
                                $orderedQuestions = $period->is_random_questions ? $category->questions->shuffle() : $category->questions->sortBy('order');
                            }
                        @endphp

                        @foreach($orderedQuestions as $question)
                            <div class="border-b border-gray-200 pb-4 question-card">
                                @if($question->image)
                                    <div class="mt-3 overflow-auto">
                                        <img src="{{ asset('storage/'.$question->image) }}" alt="Soal Gambar" class="question-image border rounded" />
                                        <p class="text-xs text-gray-500 mt-1">Gambar soal</p>
                                    </div>
                                @endif
                                <p class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">
                                    {{ $loop->iteration }}.
                                    <span class="text-xs sm:text-sm text-blue-600">(Bobot: {{ $question->grade }})</span>
                                </p>
                                <div class="tinymce-content text-sm sm:text-base mb-3">{!! $question->question !!}</div>

                                @if($question->type === 'options')
                                    <div class="space-y-3">
                                        @php
                                            if (!empty($userAnswer) && !empty($userAnswer->options_order) && array_key_exists($question->id, $userAnswer->options_order)) {
                                                $orderedOptions = collect($userAnswer->options_order[$question->id])->map(function($id) use ($question) { return $question->options->firstWhere('id', $id); })->filter();
                                            } else {
                                                $orderedOptions = $period->is_random_options ? $question->options->shuffle() : $question->options->sortBy('order');
                                            }
                                        @endphp

                                        @foreach($orderedOptions as $option)
                                            <label class="option-label flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                                <div class="flex items-center space-x-2">
                                                    <input type="radio" 
                                                        name="answers[{{ $question->id }}]" 
                                                        value="{{ $option->id }}" 
                                                        class="form-radio text-blue-600"
                                                        required>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-gray-700">{{ $option->option }}</div>
                                                    @if($option->image)
                                                        <img src="{{ asset('storage/'.$option->image) }}" alt="Opsi Gambar" class="option-image border rounded mt-2 sm:mt-1" />
                                                    @endif
                                                </div>
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
    // Use a unique key per user, period, and userAnswer to persist end timestamp across reloads
    const userId = {{ Auth::id() }};
    const periodId = {{ $period->id }};
    const uaId = {{ $userAnswer->id }};
    const endTsKey = `exam_end_ts_${userId}_${periodId}_${uaId}`;
    const storedDurationKey = `exam_duration_minutes_${userId}_${periodId}_${uaId}`;
    const expiredFlagKey = `exam_expired_${userId}_${periodId}_${uaId}`;
    const scrollKey = `exam_scroll_${userId}_${periodId}_${uaId}`;

    console.log('Exam durationMinutes from server:', durationMinutes);

    const countdownEl = document.getElementById('countdown');
    const floatingCountdownEl = document.getElementById('floatingCountdown');
    const form = document.getElementById('examForm');

    // Helper: format seconds to HH:MM:SS
    function formatTime(sec) {
        const h = Math.floor(sec / 3600).toString().padStart(2, '0');
        const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
        const s = Math.floor(sec % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    // Save & restore scroll position across reloads so user doesn't jump to top
    function saveScrollPos() {
        try {
            const y = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
            localStorage.setItem(scrollKey, String(Math.floor(y)));
        } catch (e) {
            // ignore
        }
    }

    function restoreScrollPos() {
        try {
            const v = localStorage.getItem(scrollKey);
            if (v !== null) {
                const pos = parseInt(v, 10);
                if (!isNaN(pos)) {
                    // wait a tick for layout, then scroll
                    requestAnimationFrame(function() {
                        window.scrollTo(0, pos);
                    });
                }
                // remove after restoring
                localStorage.removeItem(scrollKey);
            }
        } catch (e) {
            // ignore
        }
    }

    // Restore on initial load
    restoreScrollPos();

    // Persist before unload (covers manual reloads / navigation)
    window.addEventListener('beforeunload', saveScrollPos);

    // Compute or initialize end timestamp
    function getOrCreateEndTs() {
        if (durationMinutes === null || isNaN(Number(durationMinutes)) || Number(durationMinutes) <= 0) {
            return null; // unlimited
        }

        const storedDuration = parseInt(localStorage.getItem(storedDurationKey));
        let endTs = parseInt(localStorage.getItem(endTsKey));

        // If duration changed (admin updated), reset end timestamp to new duration
        if (isNaN(storedDuration) || storedDuration !== Number(durationMinutes) || isNaN(endTs)) {
            endTs = Date.now() + parseInt(durationMinutes) * 60 * 1000;
            localStorage.setItem(endTsKey, endTs);
            localStorage.setItem(storedDurationKey, parseInt(durationMinutes));
        }

        return endTs;
    }

    // Attempt to submit the form via fetch. If success, clear persistence keys.
    function attemptSubmit() {
        const submitForm = document.getElementById('examForm');
        // ensure elapsed_seconds hidden input is set so server can persist real time spent
        try {
            const elapsedInput = document.getElementById('elapsed_seconds');
            if (elapsedInput) {
                const endTs = parseInt(localStorage.getItem(endTsKey));
                const storedDuration = parseInt(localStorage.getItem(storedDurationKey));
                let elapsed = '';
                if (!isNaN(endTs) && !isNaN(storedDuration)) {
                    const startTs = endTs - (storedDuration * 60 * 1000);
                    elapsed = Math.max(0, Math.floor((Date.now() - startTs) / 1000));
                }
                elapsedInput.value = elapsed;
            }
        } catch (e) {
            // ignore
        }

        const formData = new FormData(submitForm);
        const action = submitForm.action;

        // disable visible/interactive inputs to prevent further changes (best-effort)
        // but DO NOT disable hidden inputs (e.g. CSRF token) so the token remains in FormData
        document.querySelectorAll('#examForm input:not([type="hidden"]):not([name="_token"]), #examForm button, #examForm textarea, #examForm select').forEach(el => el.disabled = true);

        // mark expired so subsequent loads know an expiry happened
        localStorage.setItem(expiredFlagKey, '1');

        return fetch(action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        }).then(resp => {
            if (resp.ok) {
                // clean up local keys on success
                localStorage.removeItem(endTsKey);
                localStorage.removeItem(storedDurationKey);
                localStorage.removeItem(expiredFlagKey);
                // remove saved answers keys as well
                clearSavedAnswers();
                // redirect to response URL if provided
                window.location.href = resp.url || window.location.href;
                return true;
            } else if (resp.status === 419) {
                // CSRF/session expired — redirect to dashboard (or login)
                alert('Sesi Anda telah berakhir. Anda akan dikembalikan ke dashboard. Jawaban mungkin belum tersubmit.');
                window.location.href = '{{ route("participant.exam.index") }}';
                return false;
            } else {
                // fallback to normal submit
                submitForm.submit();
                return false;
            }
        }).catch(err => {
            // network error -> keep endTs so next load will try again
            console.error('Auto-submit failed:', err);
            // fallback to normal submit (this will navigate away and likely fail), but we prefer to keep keys so reload will trigger retry
            // submitForm.submit();
            return false;
        });
    }

    // Clear saved answer keys
    function clearSavedAnswers() {
        const inputs = form.querySelectorAll('input[type="radio"], input[type="text"]');
        inputs.forEach(input => {
            const match = input.name.match(/answers\[(\d+)\]/);
            const questionId = match ? match[1] : 'unknown';
            const key = `answer_${userId}_${periodId}_${questionId}`;
            localStorage.removeItem(key);
        });
    }

    // Start/restore timer
    let timerInterval = null;
    (function initTimer() {
        const endTs = getOrCreateEndTs();

        if (!endTs) {
                if (countdownEl) countdownEl.textContent = '--:--:--';
                if (floatingCountdownEl) floatingCountdownEl.textContent = '--:--:--';
                return;
            }

        function update() {
            const remaining = Math.floor((parseInt(localStorage.getItem(endTsKey)) - Date.now()) / 1000);
            if (remaining <= 0) {
                if (countdownEl) countdownEl.textContent = '00:00:00';
                if (floatingCountdownEl) floatingCountdownEl.textContent = '00:00:00';
                // mark expired (persist) and try to submit; but don't remove endTs here so retries can occur
                localStorage.setItem(expiredFlagKey, '1');
                // attempt submit once; if it fails, leaving endTs allows retry on next load
                attemptSubmit();
                clearInterval(timerInterval);
                return;
            }

            const text = formatTime(remaining);
            if (countdownEl) countdownEl.textContent = text;
            if (floatingCountdownEl) floatingCountdownEl.textContent = text;
        }

        // If the exam already expired while the user was away, immediately attempt submit
        const alreadyRemaining = Math.floor((endTs - Date.now()) / 1000);
        if (alreadyRemaining <= 0) {
            if (countdownEl) countdownEl.textContent = '00:00:00';
            if (floatingCountdownEl) floatingCountdownEl.textContent = '00:00:00';
            localStorage.setItem(expiredFlagKey, '1');
            attemptSubmit();
            return;
        }

        update();
        timerInterval = setInterval(update, 1000);
    })();

    // When the user manually submits, clear persisted timer and saved answers
    form.addEventListener('submit', function() {
        // set elapsed_seconds before clearing keys
        try {
            const elapsedInput = document.getElementById('elapsed_seconds');
            if (elapsedInput) {
                const endTs = parseInt(localStorage.getItem(endTsKey));
                const storedDuration = parseInt(localStorage.getItem(storedDurationKey));
                let elapsed = '';
                if (!isNaN(endTs) && !isNaN(storedDuration)) {
                    const startTs = endTs - (storedDuration * 60 * 1000);
                    elapsed = Math.max(0, Math.floor((Date.now() - startTs) / 1000));
                }
                elapsedInput.value = elapsed;
            }
        } catch (e) {
            // ignore
        }

        localStorage.removeItem(endTsKey);
        localStorage.removeItem(storedDurationKey);
        localStorage.removeItem(expiredFlagKey);
        if (timerInterval) clearInterval(timerInterval);
        clearSavedAnswers();
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

    // --- Heartbeat: inform server of connectivity and detect IP change ---
    const heartbeatUrl = '{{ route("participant.exam.heartbeat", $userAnswer->id) }}';
    let hbInterval = null;
    let missedHeartbeats = 0;
    const maxMissed = 3; // after 3 consecutive failures, consider offline
    const offlineBanner = document.getElementById('offline-banner');
    let isOffline = false;
    let successSinceReload = 0;
    const reloadEvery = 2; // number of successful heartbeats before forcing a reload (6 * 10s = 60s)

    function setOffline(state) {
        if (state) {
            if (offlineBanner) offlineBanner.classList.remove('hidden');
            // disable interactive inputs to prevent further changes while offline
            document.querySelectorAll('#examForm input:not([type="hidden"]):not([name="_token"]), #examForm button, #examForm textarea, #examForm select').forEach(el => el.disabled = true);
        } else {
            if (offlineBanner) offlineBanner.classList.add('hidden');
            document.querySelectorAll('#examForm input:not([type="hidden"]):not([name="_token"]), #examForm button, #examForm textarea, #examForm select').forEach(el => el.disabled = false);
        }
    }

    
    </script>
</body>
</html>
