<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Pengguna</h1>

    <form method="POST" action="{{ route('admin.management.users.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        @csrf

        <x-admin.input label="Nama" name="name" required />
        <x-admin.input label="Email" name="email" type="email" required />
        <x-admin.input label="Password" name="password" type="password" required />
        <x-admin.input label="Konfirmasi Password" name="password_confirmation" type="password" required />
        <x-admin.select label="Role" name="role" required :options="['player' => 'Player', 'admin' => 'Admin']" placeholder="Pilih role" />

        <p class="text-xs text-slate-500 dark:text-slate-400">
            Jika role diatur ke Admin, tautan verifikasi email akan otomatis dikirim ke alamat email tersebut.
        </p>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.users.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
