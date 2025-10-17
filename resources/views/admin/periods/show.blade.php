<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Period - {{ $period->name }}</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    @include('admin.layouts.navbar')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.periods.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali ke Daftar Periods
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.results.show', $period->id) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Lihat Hasil
                </a>
                <a href="{{ route('admin.questions.export', $period->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Export Soal (Excel)
                </a>
                <a href="{{ route('admin.questions.template', $period->id) }}" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                    Download Import Template
                </a>
                <form action="{{ route('admin.questions.import', $period->id) }}" method="POST" enctype="multipart/form-data" class="inline-flex items-center">
                    @csrf
                    <label class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 cursor-pointer">
                        Import Soal
                        <input type="file" name="file" accept=".xlsx,.xls" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
                <a href="{{ route('admin.periods.edit', $period->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Edit Period
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Period Info -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $period->name }}</h2>
                    <div class="mt-2 space-x-2">
                        <span class="px-2 py-1 rounded text-xs {{ $period->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $period->status ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs {{ $period->show_grade ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            Nilai: {{ $period->show_grade ? 'Tampil' : 'Sembunyi' }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs {{ $period->show_result ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            Hasil: {{ $period->show_result ? 'Tampil' : 'Sembunyi' }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                            Durasi: {{ $period->duration_minutes ? $period->duration_minutes . ' menit' : 'Tidak terbatas' }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800">
                            Password: {{ $period->exam_password ? $period->exam_password : 'Tidak ada' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Kategori dan Soal</h3>
                <button onclick="openAddCategoryModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    + Tambah Kategori
                </button>
            </div>

            @if($period->categories->count() > 0)
                <div class="space-y-6">
                    @foreach($period->categories->sortBy('order') as $category)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <!-- Category Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-lg font-semibold text-gray-800">{{ $category->order }}. {{ $category->name }}</h4>
                                        <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->order }}, '{{ $category->descriptions }}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Yakin ingin menghapus kategori ini? Semua soal dalam kategori akan terhapus.')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    @if($category->descriptions)
                                        <p class="text-sm text-gray-600 mt-1">{{ $category->descriptions }}</p>
                                    @endif
                                </div>
                                <button onclick="openAddQuestionModal({{ $category->id }}, '{{ $category->name }}')" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    + Tambah Soal
                                </button>
                            </div>

                            <!-- Questions List -->
                            @if($category->questions->count() > 0)
                                <div class="space-y-3 ml-4">
                                    @foreach($category->questions->sortBy('order') as $question)
                                        <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    @if($question->image)
                                                        <div class="mt-2 ml-4">
                                                            <img src="{{ asset('storage/'.$question->image) }}" alt="Soal Gambar" class="max-w-xs border rounded">
                                                        </div>
                                                    @endif
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <p class="font-medium text-gray-800">{{ $question->order }}. {{ $question->question }}</p>
                                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Bobot: {{ $question->grade }}</span>
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">{{ ucfirst($question->type) }}</span>
                                                    </div>

                                                    @if($question->type === 'options' && $question->options->count() > 0)
                                                        <div class="ml-4 space-y-1">
                                                            @foreach($question->options->sortBy('order') as $option)
                                                                <div class="flex items-center space-x-2 text-sm">
                                                                    @if($question->answerKey && $question->answerKey->question_option_id == $option->id)
                                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                        <span class="text-green-600 font-medium">{{ $option->option }}</span>
                                                                    @else
                                                                        <span class="text-gray-600">○ {{ $option->option }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($question->type === 'input' && $question->answerKey)
                                                        <div class="ml-4 text-sm">
                                                            <span class="text-gray-600">Jawaban: </span>
                                                            <span class="text-green-600 font-medium">{{ $question->answerKey->key }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                </div>

                                                <div class="flex space-x-2">
                                                    <button onclick='editQuestion(@json($question->load("options", "answerKey")), {{ $category->id }}, "{{ $category->name }}")' class="text-blue-600 hover:text-blue-800 text-sm">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Yakin ingin menghapus soal ini?')">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm ml-4">Belum ada soal dalam kategori ini.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Belum ada kategori. Silakan tambah kategori terlebih dahulu.</p>
            @endif
        </div>
    </div>

    <!-- Modal Add Category -->
    <div id="addCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Kategori</h3>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Kategori</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Urutan</label>
                    <input type="number" name="order" value="{{ $period->categories->count() + 1 }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi (Opsional)</label>
                    <textarea name="descriptions" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" rows="3"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeAddCategoryModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Category -->
    <div id="editCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Kategori</h3>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Kategori</label>
                    <input type="text" id="edit_category_name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Urutan</label>
                    <input type="number" id="edit_category_order" name="order" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi (Opsional)</label>
                    <textarea id="edit_category_descriptions" name="descriptions" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" rows="3"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditCategoryModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add/Edit Question -->
    <div id="questionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <h3 id="questionModalTitle" class="text-lg font-bold text-gray-800 mb-4">Tambah Soal</h3>
            <form id="questionForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="question_method" name="_method" value="POST">
                <input type="hidden" id="question_category_id" name="category_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Kategori</label>
                    <input type="text" id="question_category_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Urutan</label>
                        <input type="number" id="question_order" name="order" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Bobot Nilai</label>
                        <input type="number" id="question_grade" name="grade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required min="1">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Pertanyaan</label>
                    <textarea id="question_text" name="question" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" rows="3" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Tipe Soal</label>
                    <select id="question_type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" onchange="toggleQuestionType()" required>
                        <option value="options">Multiple Choice (Options)</option>
                        <option value="input">Input Text</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Gambar (Opsional)</label>
                    <input type="file" id="question_image" name="image" accept="image/*" class="w-full">
                    <div id="imagePreview" class="mt-2"></div>
                    <div id="existingImageContainer" class="mt-2"></div>
                    <label id="removeImageLabel" class="text-sm text-red-600 hidden">
                        <input type="checkbox" id="remove_image" name="remove_image" value="1"> Hapus gambar yang ada
                    </label>
                </div>

                <!-- Options Section -->
                <div id="optionsSection" class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Pilihan Jawaban</label>
                    <div id="optionsList" class="space-y-2 mb-2">
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="correct_answer" value="0" checked>
                            <input type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi 1">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="correct_answer" value="1">
                            <input type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi 2">
                        </div>
                    </div>
                    <button type="button" onclick="addOption()" class="text-blue-600 hover:text-blue-800 text-sm">+ Tambah Opsi</button>
                    <p class="text-xs text-gray-500 mt-1">Pilih radio button untuk menandai jawaban yang benar</p>
                </div>

                <!-- Input Answer Section -->
                <div id="inputSection" class="mb-4 hidden">
                    <label class="block text-gray-700 font-semibold mb-2">Jawaban yang Benar</label>
                    <input type="text" id="input_answer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Masukkan jawaban yang benar">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeQuestionModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Category Modal Functions
        function openAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
        }

        function editCategory(id, name, order, descriptions) {
            document.getElementById('editCategoryForm').action = `/admin/categories/${id}`;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_order').value = order;
            document.getElementById('edit_category_descriptions').value = descriptions || '';
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }

        // Question Modal Functions
        function openAddQuestionModal(categoryId, categoryName) {
            document.getElementById('questionModalTitle').textContent = 'Tambah Soal';
            document.getElementById('questionForm').action = '{{ route("admin.questions.store") }}';
            document.getElementById('question_method').value = 'POST';
            document.getElementById('question_category_id').value = categoryId;
            document.getElementById('question_category_name').value = categoryName;
            document.getElementById('question_order').value = '';
            document.getElementById('question_grade').value = '10';
            document.getElementById('question_text').value = '';
            document.getElementById('question_type').value = 'options';
            // Clear image fields
            document.getElementById('question_image').value = '';
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('existingImageContainer').innerHTML = '';
            document.getElementById('removeImageLabel').classList.add('hidden');
            
            // Reset options
            const optionsList = document.getElementById('optionsList');
            optionsList.innerHTML = `
                <div class="flex items-center space-x-2">
                    <input type="radio" name="correct_answer" value="0" checked>
                    <input type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi 1">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="radio" name="correct_answer" value="1">
                    <input type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi 2">
                </div>
            `;
            
            toggleQuestionType();
            document.getElementById('questionModal').classList.remove('hidden');
        }

        function editQuestion(question, categoryId, categoryName) {
            console.log('Edit Question Data:', question);
            document.getElementById('questionModalTitle').textContent = 'Edit Soal';
            document.getElementById('questionForm').action = `/admin/questions/${question.id}`;
            document.getElementById('question_method').value = 'PUT';
            document.getElementById('question_category_id').value = categoryId;
            document.getElementById('question_category_name').value = categoryName;
            document.getElementById('question_order').value = question.order;
            document.getElementById('question_grade').value = question.grade;
            document.getElementById('question_text').value = question.question;
            document.getElementById('question_type').value = question.type;
            
            // Handle existing image
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('existingImageContainer').innerHTML = '';
            document.getElementById('removeImageLabel').classList.add('hidden');
            if (question.image) {
                const imgUrl = '/storage/' + question.image;
                document.getElementById('existingImageContainer').innerHTML = `<img src="${imgUrl}" style="width:50px;height:50px;object-fit:contain;background:#f8fafc;" class="border rounded mb-2" />`;
                document.getElementById('removeImageLabel').classList.remove('hidden');
            }

            if (question.type === 'options') {
                const optionsList = document.getElementById('optionsList');
                optionsList.innerHTML = '';
                question.options.forEach((option, index) => {
                    // Check both answer_key and answerKey for compatibility
                    const answerKey = question.answer_key || question.answerKey;
                    const isCorrect = answerKey && answerKey.question_option_id == option.id;
                    optionsList.innerHTML += `
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="correct_answer" value="${index}" ${isCorrect ? 'checked' : ''}>
                            <input type="text" value="${option.option}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi ${index + 1}">
                        </div>
                    `;
                });
            } else {
                const answerKey = question.answer_key || question.answerKey;
                document.getElementById('input_answer').value = answerKey ? answerKey.key : '';
            }
            
            toggleQuestionType();
            document.getElementById('questionModal').classList.remove('hidden');
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').classList.add('hidden');
        }

        function toggleQuestionType() {
            const type = document.getElementById('question_type').value;
            const optionsSection = document.getElementById('optionsSection');
            const inputSection = document.getElementById('inputSection');
            const inputAnswer = document.getElementById('input_answer');
            
            if (type === 'options') {
                optionsSection.classList.remove('hidden');
                inputSection.classList.add('hidden');
                // Set required for options
                inputAnswer.removeAttribute('required');
                inputAnswer.removeAttribute('name');
                document.querySelectorAll('#optionsList input[type="text"]').forEach(input => {
                    input.setAttribute('required', 'required');
                    input.setAttribute('name', 'options[]');
                });
                document.querySelectorAll('#optionsList input[type="radio"]').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                optionsSection.classList.add('hidden');
                inputSection.classList.remove('hidden');
                // Set required for input
                inputAnswer.setAttribute('required', 'required');
                inputAnswer.setAttribute('name', 'correct_answer');
                document.querySelectorAll('#optionsList input[type="text"]').forEach(input => {
                    input.removeAttribute('required');
                    input.removeAttribute('name');
                });
                document.querySelectorAll('#optionsList input[type="radio"]').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        }

        function addOption() {
            const optionsList = document.getElementById('optionsList');
            const optionCount = optionsList.querySelectorAll('input[type="text"]').length;
            const newOption = document.createElement('div');
            newOption.className = 'flex items-center space-x-2';
            newOption.innerHTML = `
                <input type="radio" name="correct_answer" value="${optionCount}">
                <input type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Opsi ${optionCount + 1}">
                <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            optionsList.appendChild(newOption);
            
            // Re-apply required attributes based on current type
            toggleQuestionType();
        }

        // Image preview when selecting a new file
        document.getElementById('question_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const existing = document.getElementById('existingImageContainer');
            const removeLabel = document.getElementById('removeImageLabel');
            preview.innerHTML = '';
            existing.innerHTML = '';
            removeLabel.classList.add('hidden');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width:50px;height:50px;object-fit:contain;background:#f8fafc;" class="border rounded" />`;
                }
                reader.readAsDataURL(file);
            }
        });

        function removeOption(button) {
            button.parentElement.remove();
            // Update radio button values
            document.querySelectorAll('#optionsList input[type="radio"]').forEach((radio, index) => {
                radio.value = index;
            });
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addCategoryModal = document.getElementById('addCategoryModal');
            const editCategoryModal = document.getElementById('editCategoryModal');
            const questionModal = document.getElementById('questionModal');
            
            if (event.target == addCategoryModal) {
                closeAddCategoryModal();
            }
            if (event.target == editCategoryModal) {
                closeEditCategoryModal();
            }
            if (event.target == questionModal) {
                closeQuestionModal();
            }
        }
    </script>
</body>
</html>
