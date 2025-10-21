<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Participants</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    @include('admin.layouts.navbar')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Kelola Participants</h2>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.users.template') }}" class="bg-gray-200 text-gray-800 px-3 py-2 rounded hover:bg-gray-300 text-center text-sm">Download Template</a>
                <a href="{{ route('admin.users.export') }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-center text-sm">Export CSV</a>

                <form id="importForm" action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <label class="block">
                        <input type="file" name="csv_file" accept=".csv" class="hidden" id="csv_file_input">
                        <a href="#" id="chooseCsv" onclick="document.getElementById('csv_file_input').click(); return false;" class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-center text-sm">Pilih CSV</a>
                    </label>
                    <!-- Import will auto-submit when a file is selected -->
                    <span id="importStatus" class="text-sm text-gray-600"></span>
                </form>

                <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto text-center">
                    + Tambah Participant
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->username }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-sm text-gray-500">{{ $user->nama }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 sm:px-6 py-4 text-center text-gray-500 text-sm">Belum ada participant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
<script>
    (function(){
        const input = document.getElementById('csv_file_input');
        const form = document.getElementById('importForm');
        const status = document.getElementById('importStatus');
        const choose = document.getElementById('chooseCsv');

        if (!input || !form) return;

        input.addEventListener('change', function(){
            if (!this.files || this.files.length === 0) return;
            // optional: confirm before uploading
            const fileName = this.files[0].name || '';
            const proceed = confirm('Upload file: ' + fileName + '\nLanjutkan import sekarang?');
            if (!proceed) {
                // reset input
                this.value = '';
                return;
            }

            // show status and disable choose button to prevent re-clicks
            if (status) status.textContent = 'Uploading...';
            if (choose) choose.classList.add('opacity-50', 'pointer-events-none');

            form.submit();
        });
    })();
</script>
</html>
