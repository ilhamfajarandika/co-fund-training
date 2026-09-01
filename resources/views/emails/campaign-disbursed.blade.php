<x-mail::message>
# Dana Campaign Sudah Dicairkan!

Halo {{ $creator->name }},

Bagus! Dana dari campaign kamu sudah berhasil dicairkan.

## Detail Pencairan

**Campaign:** {{ $campaign->title }}  
**Total Terkumpul:** Rp {{ number_format($collectedAmount, 0, ',', '.') }}  
**Platform Fee (5%):** - Rp {{ number_format($platformFee, 0, ',', '.') }}  
**Dana Diterima:** Rp {{ number_format($creatorReceive, 0, ',', '.') }}  

Dana sudah ditambahkan ke saldo kamu. Terima kasih telah menggunakan CoFund!

<x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
Lihat Campaign
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
