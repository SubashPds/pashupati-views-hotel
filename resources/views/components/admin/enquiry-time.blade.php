@props(['date'])
@php
    $localDate = $date->copy()->timezone('Asia/Kathmandu')->locale('en');
    $age = $localDate->diffForHumans([
        'short' => true,
        'parts' => 2,
        'join' => ' ',
        'minimumUnit' => 'minute',
        'options' => \Carbon\CarbonInterface::JUST_NOW,
    ]);
    $age = preg_replace('/(\d+)m\b/', '$1 min', $age);
@endphp
<time datetime="{{ $localDate->toIso8601String() }}" title="{{ $localDate->format('d M Y, g:i:s A') }} (Nepal time)" {{ $attributes->class(['block text-xs']) }}>
    <span class="block text-gray-400">{{ $localDate->format('d M Y, g:i A') }}</span>
    <span class="mt-0.5 block text-gray-500">{{ $age }}</span>
</time>
