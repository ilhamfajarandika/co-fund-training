<x-mail::message>
    # Donasi Baru Diterima!

    Halo {{ $campaign->user->name }},

    Kamu baru saja menerima donasi baru untuk campaign kamu.

    ## Detail Donasi

    **Campaign:** {{ $campaign->title }}
    **Backer:** {{ $backer->name }}
    **Jumlah Donasi:** Rp {{ number_format($backing->amount, 0, ',', '.') }}
    **Status:** Pending

    ## Detail Campaign

    - **Target:** Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}
    - **Terkumpul:** Rp {{ number_format($campaign->current_amount, 0, ',', '.') }}
    - **Deadline:** {{ $campaign->deadline->format('d M Y') }}

    Terima kasih telah menggunakan CoFund!

    <x-mail::button :url="url('/api/v1/campaigns/' . $campaign->id)">
        Lihat Campaign
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
