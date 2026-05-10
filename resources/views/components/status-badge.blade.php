@props([
    'status' => 'pending',
    'label'  => null,
])

@php
    $known = ['pending','processing','completed','cancelled','available','occupied','reserved','low','confirmed','arrived'];
    $cls   = in_array($status, $known, true) ? "status-{$status}" : 'status-pending';
@endphp

<span {{ $attributes->merge(['class' => "status {$cls}"]) }}>
    {{ $label ?? ucfirst($status) }}
</span>
