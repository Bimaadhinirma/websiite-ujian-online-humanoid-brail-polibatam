<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        // Load all participants and perform a natural, case-insensitive sort in PHP.
        // Keep the previous rule: usernames that start with letters (or non-digits) come first,
        // then usernames that start with digits. Within each group use natural sort so
        // values like test1..test10 order as expected.
        $all = User::where('role', 2)->get();

        $alpha = $all->filter(function ($u) {
            return !preg_match('/^[0-9]/', trim((string) $u->username));
        })->sortBy('username', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $digits = $all->filter(function ($u) {
            return preg_match('/^[0-9]/', trim((string) $u->username));
        })->sortBy('username', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $users = $alpha->concat($digits);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'nama' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'username' => $request->username,
            'nama' => $request->nama,
            'password' => bcrypt($request->password),
            'role' => 2
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Participant berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|unique:users,username,' . $id,
            'nama' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        $user->username = $request->username;
        $user->nama = $request->nama;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Participant berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Participant berhasil dihapus');
    }

    /**
     * Export participants as CSV
     */
    public function export()
    {
        $filename = 'participants_' . date('Ymd_His') . '.csv';
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            // header
            fputcsv($handle, ['username', 'nama', 'password']);

            $all = User::where('role', 2)->get();

            $alpha = $all->filter(function ($u) {
                return !preg_match('/^[0-9]/', trim((string) $u->username));
            })->sortBy('username', SORT_NATURAL | SORT_FLAG_CASE)->values();

            $digits = $all->filter(function ($u) {
                return preg_match('/^[0-9]/', trim((string) $u->username));
            })->sortBy('username', SORT_NATURAL | SORT_FLAG_CASE)->values();

            $users = $alpha->concat($digits);
            foreach ($users as $u) {
                // do not export password hash; leave password empty for admins to set
                fputcsv($handle, [$u->username, $u->nama, '']);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Download CSV template for participants
     */
    public function template()
    {
        $filename = 'participants_template.csv';
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['username', 'nama', 'password']);
            // sample row
            fputcsv($handle, ['contoh_user', 'Nama Contoh', 'password123']);
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Import participants from uploaded CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->back()->with('success', 'Gagal membaca file');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            return redirect()->back()->with('success', 'File kosong atau header tidak ditemukan');
        }

        $expected = ['username', 'nama', 'password'];
        $normalized = array_map(function ($h) { return Str::lower(trim($h)); }, $header);

        // try to map columns by name
        $map = [];
        foreach ($expected as $col) {
            $idx = array_search($col, $normalized);
            $map[$col] = $idx !== false ? $idx : null;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row)) === 0) {
                continue; // skip empty rows
            }

            $username = $map['username'] !== null ? ($row[$map['username']] ?? null) : null;
            $nama = $map['nama'] !== null ? ($row[$map['nama']] ?? null) : null;
            $password = $map['password'] !== null ? ($row[$map['password']] ?? null) : null;

            if (!$username || !$nama) {
                $skipped++;
                continue;
            }

            $user = User::where('username', $username)->first();
            if ($user) {
                // update
                $user->nama = $nama;
                if ($password) {
                    $user->password = bcrypt($password);
                }
                $user->role = 2;
                $user->save();
                $updated++;
            } else {
                // create
                User::create([
                    'username' => $username,
                    'nama' => $nama,
                    'password' => $password ? bcrypt($password) : bcrypt(Str::random(10)),
                    'role' => 2
                ]);
                $created++;
            }
        }

        fclose($handle);

        $message = "Import selesai. Dibuat: {$created}, Diperbarui: {$updated}, Dilewati: {$skipped}";
        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
