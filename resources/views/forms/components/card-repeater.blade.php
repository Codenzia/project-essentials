@php
    $rawState = $getRawState() ?? [];
    $gridCols = $getGridColumns();
    $hasCardSchema = $hasCardSchema();
    $isInline = $isInlineMode();
    $cardRecords = $getCardRecords();
    $isDeletable = $isDeletable();
    $isEditable = $isEditable();
    $hasRelationship = $hasRelationship();
    $tableHeaderView = $getTableHeaderView();

    $childSchemas = $getDefaultChildSchemas();

    $addAction = $getAction('addItem');
    $deleteAction = $getAction('deleteItem');
    $editAction = $getAction('editItem');
    $doneAction = $getAction('doneEditing');
    $cancelEditAction = $getAction('cancelEdit');
    $confirmAddAction = $getAction('confirmAdd');
    $cancelNewAction = $getAction('cancelNew');

    $editingKey = null;
    $hasFormOpen = false;
    foreach ($rawState as $key => $data) {
        if (is_array($data) && ! empty($data['__editing__'])) {
            $editingKey = $key;
            $hasFormOpen = true;
            break;
        }
    }
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="space-y-3">
        @if (count($rawState))
            @php $cardIndex = 0; $cardTotal = $cardRecords->count(); @endphp
            @if ($tableHeaderView && $cardTotal > 0)
                @include($tableHeaderView)
            @endif
            <div class="grid gap-3" style="grid-template-columns: repeat({{ $gridCols }}, minmax(0, 1fr))">
                @foreach ($rawState as $itemKey => $itemData)
                    @php
                        $isEditing = ($itemKey === $editingKey);
                        $isSaved = str_starts_with($itemKey, 'record-');
                        $isConfirmed = is_array($itemData) && ! empty($itemData['__confirmed__']);
                        $isNew = ! $isSaved;
                        $record = $cardRecords->get($itemKey);
                    @endphp

                    @php
                        $hasRecord = $record !== null;
                        $isRawData = ! $hasRelationship;
                        $showAsCard = ($isSaved || $isConfirmed || $isRawData) && $hasRecord && ! $isEditing;
                    @endphp

                    @if ($isEditing && isset($childSchemas[$itemKey]))
                        {{-- FORM: render Filament-managed child schema (add/edit) --}}
                        <div class="col-span-full rounded-xl dark:bg-gray-800 bg-gray-50 p-4 space-y-3">
                            {{ $childSchemas[$itemKey] }}

                            <div class="flex items-center gap-2 pt-2">
                                @if ($isNew && ! $isRawData)
                                    {{ $confirmAddAction(['item' => $itemKey]) }}
                                    {{ $cancelNewAction(['item' => $itemKey]) }}
                                @elseif ($isRawData && ! $isConfirmed && empty($itemData['title'] ?? ''))
                                    {{-- Raw data: new item without data yet --}}
                                    {{ $doneAction(['item' => $itemKey]) }}
                                    {{ $cancelNewAction(['item' => $itemKey]) }}
                                @else
                                    {{ $doneAction(['item' => $itemKey]) }}
                                    {{ $cancelEditAction(['item' => $itemKey]) }}
                                @endif
                            </div>
                        </div>

                    @elseif ($isInline && isset($childSchemas[$itemKey]))
                        {{-- INLINE: form fields always visible, side by side --}}
                        <div class="col-span-full rounded-xl px-4 py-2 flex items-end gap-3 card-repeater-inline">
                            <div class="flex-1">
                                {{ $childSchemas[$itemKey] }}
                            </div>
                            @if ($isDeletable)
                                <div class="pb-2 shrink-0">
                                    {{ $deleteAction(['item' => $itemKey]) }}
                                </div>
                            @endif
                        </div>

                    @elseif ($showAsCard && $hasCardSchema)
                        {{-- CARD: saved, confirmed, or raw data item.
                             Delete button is anchored INSIDE the wrapper's top-right corner.
                             Originally it was at `top-1 -right-10` (40px outside the wrapper)
                             which created a hover gap — the cursor crossed the parent's
                             boundary on its way to the button, fired `mouseleave`, and the
                             button vanished mid-reach. Anchoring inside removes the gap.
                             Card schemas that need horizontal space for the button should
                             reserve it themselves (e.g. `pr-10` on the row content). --}}
                        <div class="relative @if($isConfirmed && ! $isRawData) opacity-75 border border-dashed dark:border-gray-600 border-gray-300 rounded-xl @endif" x-data="{ hovered: false }" x-on:mouseenter="hovered = true" x-on:mouseleave="hovered = false">
                            @if (($isSaved || $isConfirmed || $isRawData) && $isEditable)
                                @php $safeKey = str_replace('-', '_', $itemKey); @endphp
                                <div class="absolute inset-0 z-0 cursor-pointer" x-on:click="$refs.editBtn_{{ $safeKey }}.querySelector('button').click()"></div>
                                <div class="sr-only" aria-hidden="true">
                                    <span x-ref="editBtn_{{ $safeKey }}">{{ $editAction(['item' => $itemKey]) }}</span>
                                </div>
                            @endif
                            @if ($isDeletable)
                                <div class="absolute top-1 right-1 z-20" x-show="hovered" x-on:click.stop>
                                    {{ $deleteAction(['item' => $itemKey]) }}
                                </div>
                            @endif
                            @foreach ($getEvaluatedCardSchema($record, $cardIndex, $cardTotal) as $comp)
                                {!! $comp->toHtml() !!}
                            @endforeach
                            @php $cardIndex++; @endphp
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="rounded-xl dark:bg-gray-800 bg-gray-50">
                <p class="text-sm text-center dark:text-gray-500 text-gray-400 p-4">{{ $getEmptyMessage() }}</p>
            </div>
        @endif

        @if (($isInline || ! $hasFormOpen) && $addAction->isVisible())
            <div class="text-{{ $getAddActionAlignment() }}">{{ $addAction }}</div>
        @endif
    </div>
</x-dynamic-component>
