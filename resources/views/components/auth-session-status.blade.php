@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif

{{-- Error dari percobaan login/register via Google/GitHub (lihat
     SocialiteController & bootstrap/app.php, yang flash session('oauth_error'))
     - dipakai gaya alert merah yang sama dengan x-admin.flash supaya konsisten
     dengan alert lain di project, tanpa mengubah desain halaman Login/Register. --}}
@if (session('oauth_error'))
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800']) }}>
        {{ session('oauth_error') }}
    </div>
@endif
