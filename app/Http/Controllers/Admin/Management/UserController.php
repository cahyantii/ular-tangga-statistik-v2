<?php

namespace App\Http\Controllers\Admin\Management;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $users = User::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalPengguna = User::query()->count();
        $totalAdmin = User::query()->where('role', UserRole::Admin)->count();
        $totalPemain = User::query()->where('role', UserRole::Player)->count();
        $adminBelumVerifikasi = User::query()->where('role', UserRole::Admin)->whereNull('email_verified_at')->count();

        return view('admin.management.users.index', [
            'users' => $users,
            'filter' => $filter,
            'stats' => [
                'total' => $totalPengguna,
                'aktif' => $totalPengguna - $adminBelumVerifikasi,
                'admin' => $totalAdmin,
                'pemain' => $totalPemain,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.management.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // "role" sengaja tidak ada di $fillable (lihat App\Models\User) agar tidak
        // bisa di-mass-assign dari input publik; di sini di-set eksplisit karena
        // controller ini hanya bisa diakses admin dan sudah tervalidasi via enum.
        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->role = UserRole::from($data['role']);
        $user->save();

        if ($user->role === UserRole::Admin) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        Cache::forget(AdminDashboardService::STATS_PLAYERS_CACHE_KEY);

        return redirect()->route('admin.management.users.index')
            ->with('status', "Pengguna \"{$user->name}\" berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        return view('admin.management.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $wasPlayer = $user->role === UserRole::Player;
        $newRole = UserRole::from($data['role']);
        $promotedToAdmin = $wasPlayer && $newRole === UserRole::Admin;

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $newRole;

        // Sama seperti ProfileController::update() di sisi player: email yang
        // berubah selalu meng-unverified-kan akun, supaya status "terverifikasi"
        // tidak pernah menempel pada email yang belum pernah dikonfirmasi
        // pemiliknya — terlepas dari apakah perubahan ini juga promosi ke Admin.
        if ($user->isDirty('email') || $promotedToAdmin) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($promotedToAdmin) {
            $user->sendEmailVerificationNotification();
        }

        Cache::forget(AdminDashboardService::STATS_PLAYERS_CACHE_KEY);

        return redirect()->route('admin.management.users.index')
            ->with('status', "Pengguna \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak bisa menghapus akun Anda sendiri.',
            ]);
        }

        if ($user->role === UserRole::Admin) {
            $totalAdminAktif = User::query()->where('role', UserRole::Admin)->count();

            if ($totalAdminAktif <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'Tidak bisa menghapus admin terakhir yang tersisa.',
                ]);
            }
        }

        $user->delete();

        Cache::forget(AdminDashboardService::STATS_PLAYERS_CACHE_KEY);

        return redirect()->route('admin.management.users.index')
            ->with('status', "Pengguna \"{$user->name}\" berhasil dihapus.");
    }

    public function restore(int $user): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($user);
        $user->restore();

        Cache::forget(AdminDashboardService::STATS_PLAYERS_CACHE_KEY);

        return redirect()->route('admin.management.users.index')
            ->with('status', "Pengguna \"{$user->name}\" berhasil dipulihkan.");
    }
}
