<x-mail::message>
# Refund Campaign

Halo {{ $backer->name ?? 'Backer' }},

Campaign {{ $campaign->title }} telah berakhir dan dana telah dikembalikan ke saldo kamu.

## Detail Refund

**Campaign:** {{ $campaign->title }}  
**Nominal Refund:** Rp {{ number_format($amount, 0, ',', '.') }}  

Dana sudah ditambahkan ke saldo kamu. Terima kasih telah menggunakan CoFund!

<x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
Lihat Campaign
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
