<x-mail::message>
# Campaign Kamu Perlu Diperbaiki

Halo {{ $creator->name }},

Maaf, campaign kamu belum bisa disetujui dan perlu beberapa perbaikan.

## Detail Campaign

**Judul:** {{ $campaign->title }}

## Catatan dari Admin

{!! nl2br(e($rejectionNote ?? 'Tidak ada catatan tambahan. Silakan perbaiki campaign kamu dan ajukan kembali.')) !!}

Silakan perbaiki campaign kamu dan ajukan kembali untuk ditinjau kembali.

<x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
Lihat Campaign
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
