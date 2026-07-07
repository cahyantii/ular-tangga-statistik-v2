<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Pengguna</h1>

    <form method="POST" action="{{ route('admin.management.users.update', $user) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <x-admin.input label="Nama" name="name" :value="$user->name" required />
        <x-admin.input label="Email" name="email" type="email" :value="$user->email" required />
        <x-admin.select label="Role" name="role" required :value="$user->role->value" :options="['player' => 'Player', 'admin' => 'Admin']" />

        <p class="text-xs text-slate-500">
            Mengubah role dari Player menjadi Admin akan mengirim ulang tautan verifikasi email.
        </p>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.users.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
