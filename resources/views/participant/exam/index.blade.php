<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ujian</title>
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
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('info') }}
            </div>
        @endif

        <!-- Active Periods -->
        <div class="mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Ujian Aktif</h2>
            
            @if($activePeriods->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($activePeriods as $period)
                        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-2">{{ $period->name }}</h3>

                            @php
                                $userAnswer = $userAnswers->where('period_id', $period->id)->first();
                            @endphp

                            @if($userAnswer && $userAnswer->status)
                                <div class="mb-4">
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                        ✓ Sudah Dikerjakan
                                    </span>
                                </div>
                                <a href="{{ route('participant.exam.result', $userAnswer->id) }}" 
                                    class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm sm:text-base">
                                    Lihat Hasil
                                </a>
                                @else
                                <a href="{{ route('participant.exam.start', $period->id) }}" 
                                    class="start-exam-btn block w-full text-center bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm sm:text-base"
                                    data-period-id="{{ $period->id }}"
                                    data-has-password="{{ $period->exam_password ? '1' : '0' }}">
                                    {{ $userAnswer ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8 text-center">
                    <p class="text-gray-600 text-sm sm:text-base">Tidak ada ujian aktif saat ini.</p>
                </div>
            @endif
        </div>

        <!-- History -->
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Riwayat Ujian</h2>
            
            @if($userAnswers->count() > 0)
                <div class="bg-white rounded-lg shadow-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($userAnswers as $answer)
                                <tr>
                                    <td class="px-3 sm:px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $answer->period->name }}</div>
                                        <div class="text-xs text-gray-500 sm:hidden">{{ $answer->created_at->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                                        <div class="text-sm text-gray-500">{{ $answer->created_at->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $answer->status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $answer->status ? 'Selesai' : 'Belum' }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 text-sm">
                                        @if($answer->status)
                                            <a href="{{ route('participant.exam.result', $answer->id) }}" 
                                                class="text-blue-600 hover:text-blue-900">
                                                Lihat
                                            </a>
                                        @else
                                            <a href="{{ route('participant.exam.start', $answer->period_id) }}" 
                                                class="text-green-600 hover:text-green-900">
                                                Lanjutkan
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8 text-center">
                    <p class="text-gray-600 text-sm sm:text-base">Belum ada riwayat ujian.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Password Modal -->
    <div id="exam-password-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">Masukkan Password Ujian</h3>
            <div id="modal-error" class="hidden bg-red-100 text-red-700 px-3 py-2 rounded mb-3 text-sm"></div>
            <div class="mb-4">
                <input id="exam-password-input" type="password" class="w-full border rounded px-3 py-2" placeholder="Password Ujian">
            </div>
            <div class="flex justify-end space-x-2">
                <button id="modal-cancel" class="px-4 py-2 rounded border">Batal</button>
                <button id="modal-submit" class="px-4 py-2 rounded bg-blue-600 text-white">Masuk</button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // CSRF token
            const csrfToken = '{{ csrf_token() }}';

            let currentPeriodId = null;
            // attach to start buttons
            document.querySelectorAll('.start-exam-btn').forEach(btn => {
                btn.addEventListener('click', function(ev){
                    const hasPassword = btn.getAttribute('data-has-password') === '1';
                    if (!hasPassword) return; // allow normal navigation
                    ev.preventDefault();
                    currentPeriodId = btn.getAttribute('data-period-id');
                    // show modal
                    document.getElementById('modal-error').classList.add('hidden');
                    document.getElementById('exam-password-input').value = '';
                    document.getElementById('exam-password-modal').classList.remove('hidden');
                    document.getElementById('exam-password-modal').classList.add('flex');
                });
            });

            document.getElementById('modal-cancel').addEventListener('click', function(){
                document.getElementById('exam-password-modal').classList.add('hidden');
                document.getElementById('exam-password-modal').classList.remove('flex');
            });

            document.getElementById('modal-submit').addEventListener('click', function(){
                const pwd = document.getElementById('exam-password-input').value.trim();
                const errDiv = document.getElementById('modal-error');
                if (!pwd) {
                    errDiv.textContent = 'Masukkan password';
                    errDiv.classList.remove('hidden');
                    return;
                }

                // POST to verify route
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('exam_password', pwd);

                fetch(`/participant/exam/verify-password/${currentPeriodId}`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r => r.json().then(j => ({status: r.status, body: j}))).then(({status, body}) => {
                    if (status >= 200 && status < 300 && body.ok) {
                        // redirect to provided URL
                        window.location.href = body.redirect;
                    } else {
                        errDiv.textContent = (body && body.message) ? body.message : 'Password salah';
                        errDiv.classList.remove('hidden');
                    }
                }).catch(err => {
                    errDiv.textContent = 'Terjadi kesalahan. Coba lagi.';
                    errDiv.classList.remove('hidden');
                });
            });
        })();
    </script>
</body>
</html>
