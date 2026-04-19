@props([
    'access',
])

@php
    $value = $access['state']->value ?? $access['state'];
    $tone = match ($value) {
        'open', 'free', 'owned_via_entitlement' => 'success',
        'included_in_package' => 'warning',
        'buy' => 'neutral',
        default => 'danger',
    };
@endphp

<x-admin.status-badge :label="$access['label']" :tone="$tone" />
