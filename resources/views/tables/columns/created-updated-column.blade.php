@php
    /** @var \Filament\Tables\Columns\Column|\Filament\Infolists\Components\Entry $component */
    /** @var \Filament\Infolists\Components\Entry|null $entry */

    $component = $column ?? $entry;
    $record = $getRecord();

    // Normalizing variables from relationships
    $createdAt = $record?->created_at ?? null;
    $updatedAt = $record?->updated_at ?? null;

    // Attempt to resolve users from standard or custom relationship names
    $createdByUser = $record?->createdByUser ?? null;
    $updatedByUser = $record?->updatedByUser ?? null;

    $hasBeenUpdated = $updatedAt && $updatedAt->ne($createdAt);

@endphp

<div
    {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['flex flex-col items-start text-[10px] font-normal leading-tight text-gray-500 dark:text-gray-500 gap-1 mt-2']) }}>

    {{-- Creation Row --}}
    <div class="flex items-center gap-2">
        <x-filament::icon icon="heroicon-m-plus-circle" class="w-3.5 h-3.5 opacity-70" />

        @if ($createdByUser)
            <x-project-essentials::user-avatar :user="$createdByUser" size="size-3" class="opacity-70" />
        @else
            <x-heroicon-o-user class="size-3 opacity-70"></x-heroicon-o-user>
        @endif

        <h6 class="whitespace-nowrap">
            {{ $createdAt?->diffForHumans() }}
        </h6>
    </div>

    {{-- Update Row --}}
    @if ($hasBeenUpdated)
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-m-pencil-square" class="w-3.5 h-3.5 opacity-70" />

            @if ($updatedByUser)
                <x-project-essentials::user-avatar :user="$updatedByUser" size="size-3" class="opacity-70" />
            @else
                <x-heroicon-o-user class="size-3 opacity-70"></x-heroicon-o-user>
            @endif

            <h6 class="whitespace-nowrap">
                {{ $updatedAt->diffForHumans() }}
            </h6>
        </div>
    @endif
</div>
