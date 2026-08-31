<x-mail::message>
# Campaign Kamu Sudah Disetujui!

Halo {{ $creator->name }},

Bagus! Campaign kamu sudah disetujui dan kini aktif.

## Detail Campaign

**Judul:** {{ $campaign->title }}  
**Target Dana:** Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}  
**Deadline:** {{ $campaign->deadline->format('d M Y') }}

Sekarang campaign kamu sudah bisa menerima donasi dari backer.

<x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
Lihat Campaign
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
