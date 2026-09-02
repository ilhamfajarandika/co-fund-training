<!DOCTYPE html>
<html>
<head>
    <title>Campaign Deadline Approaching</title>
</head>
<body>
    <h2>Hello,</h2>
    <p>The campaign you backed, <strong>{{ $campaign->title }}</strong>, is ending in <strong>{{ $daysRemaining }} days</strong>!</p>
    <p>Current Amount: Rp {{ number_format($campaign->current_amount, 0, ',', '.') }}</p>
    <p>Target Amount: Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
    
    <p>Don't forget to share the campaign with your friends to help it reach its goal.</p>
    <br>
    <p>Thank you,</p>
    <p>CoFund Team</p>
</body>
</html>
