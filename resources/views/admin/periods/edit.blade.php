<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Period</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    @include('admin.layouts.navbar')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Period</h2>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="POST" action="{{ route('admin.periods.update', $period->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Period</label>
                    <input type="text" name="name" value="{{ old('name', $period->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                        <option value="1" {{ old('status', $period->status) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $period->status) == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="show_grade" value="0">
                        <input type="checkbox" name="show_grade" value="1" class="form-checkbox text-blue-600" {{ old('show_grade', $period->show_grade) ? 'checked' : '' }}>
                        <span class="text-gray-700">Tampilkan Nilai ke Participant</span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="show_result" value="0">
                        <input type="checkbox" name="show_result" value="1" class="form-checkbox text-blue-600" {{ old('show_result', $period->show_result) ? 'checked' : '' }}>
                        <span class="text-gray-700">Izinkan Participant Review Hasil</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="is_random_questions" value="0">
                        <input type="checkbox" name="is_random_questions" value="1" class="form-checkbox text-blue-600" {{ old('is_random_questions', $period->is_random_questions) ? 'checked' : '' }}>
                        <span class="text-gray-700">Acak Soal (Acak Pertanyaan untuk Peserta)</span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="is_random_options" value="0">
                        <input type="checkbox" name="is_random_options" value="1" class="form-checkbox text-blue-600" {{ old('is_random_options', $period->is_random_options) ? 'checked' : '' }}>
                        <span class="text-gray-700">Acak Opsi (Acak Urutan Pilihan Jawaban)</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Durasi (menit) <span class="text-xs text-gray-500">(kosong = tidak terbatas)</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $period->duration_minutes) }}" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password Ujian (opsional)</label>
                    <input type="text" name="exam_password" value="{{ old('exam_password', $period->exam_password) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Kosongkan jika tidak ingin menggunakan password">
                    <p class="text-xs text-gray-500 mt-1">Jika diisi, peserta harus memasukkan password untuk memulai ujian.</p>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('admin.periods.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
