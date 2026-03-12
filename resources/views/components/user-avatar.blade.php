@props([
    'user' => null,
    'alt' => null,
    'size' => 'size-8',
    'avatarMethod' => 'getFilamentAvatarUrl',
])

@php
    $component = null;
    // Scenario : If used in a Column/Entry and no User prop was passed
    if (!$user) {
        $component = $column ?? ($entry ?? null);
        if ($component) {
            // Get the actual Model instance (Project, Task, etc.) from Filament
            $record = $component->getRecord();
            // If the record itself is a User, use it.
            // Usually, Filament components are called on the model that HAS the photo.
            // we may get a relation though
            $statePath = $component->getName();

            // If the path contains a dot, the user is the relationship
            if (str_contains($statePath, '.')) {
                $relationshipName = str($statePath)->beforeLast('.')->toString();
                // data_get allows us to reach nested relationships like 'team.manager'
                $user = data_get($record, $relationshipName);
            } else {
                // If no dot, the record itself is likely the user
                $user = $record;
            }

            //get extra attributes too
            $attributes = $component->getExtraAttributeBag();

            // Handle Size from Trait
            if (method_exists($component, 'getSize') && $attributes->get('size') === null) {
                $size = $component->getSize();
            }
        }
    }

    // get photo url
    $avatarUrl = ($user && method_exists($user, $avatarMethod)) ? $user->$avatarMethod() : null;
    $altText = $user?->name ?? ($alt ?? ($component?->getTooltip() ?? ''));
@endphp

<div class="flex items-center user-avatar">
    @if ($avatarUrl)
        <x-filament::avatar :src="$avatarUrl" :alt="$altText" :title="$altText" :size="$size"
            {{ $attributes->merge(['border border-gray-700 shrink-0']) }} />
    @else
        {{-- Centered fallback for missing users --}}
        <div alt="{{ $altText }}" title="{{ $altText }}"
            {{ $attributes->merge(['class' => "{$size} rounded-full bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-inner flex items-center justify-center text-[10px] font-bold text-gray-400"]) }}>
            {{ __('N/A') }}
        </div>
    @endif
</div>
