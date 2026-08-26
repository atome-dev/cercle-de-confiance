@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1 text-center">
    <flux:heading size="xl" class="font-display text-text">{{ $title }}</flux:heading>
    <flux:subheading class="text-text-muted">{{ $description }}</flux:subheading>
</div>
