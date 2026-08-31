<x-mail::message>
# Update Campaign Baru!

Halo {{ $creator->name }},

Ada update baru untuk campaign kamu yang sedang berjalan.

## Detail Update

**Campaign:** {{ $campaign->title }}  
**Judul Update:** {{ $update->title }}

## Konten Update

{!! nl2br(e($update->content)) !!}

<x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
Lihat Campaign
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
