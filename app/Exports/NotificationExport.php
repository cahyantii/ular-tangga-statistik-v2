<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NotificationExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $notifications)
    {
    }

    public function collection(): Collection
    {
        return $this->notifications;
    }

    public function headings(): array
    {
        return ['tanggal', 'kategori', 'judul', 'pesan', 'status'];
    }

    public function map($notification): array
    {
        return [
            $notification->created_at->format('Y-m-d H:i'),
            $notification->data['category'] ?? '',
            $notification->data['title'] ?? '',
            $notification->data['message'] ?? '',
            $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca',
        ];
    }
}
