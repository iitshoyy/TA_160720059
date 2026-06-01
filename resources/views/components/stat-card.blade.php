@props([
    'label' => '',
    'value' => '',
    'sub' => null,
    'tone' => 'gold', // gold | success | info | warning | danger
])

<div {{ $attributes->merge(['class' => "stat-card " . ($tone !== 'gold' ? $tone : '')]) }}>
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value">{{ $value }}</div>
    @if($sub)
        <div class="stat-sub">{{ $sub }}</div>
    @endif
</div>
