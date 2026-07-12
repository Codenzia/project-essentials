<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Forms\Components;

use Filament\Forms\Components\Select;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Model;

/**
 * UserSelectWithAvatar
 *
 * A Filament Select that renders user options with avatar + name.
 *
 * Works with any model implementing Filament's HasAvatar contract
 * (i.e. has getFilamentAvatarUrl(): ?string).
 *
 * Usage:
 *   UserSelectWithAvatar::make('assigned_to_user_id')
 *       ->relationship('assignee', 'name')
 *
 *   UserSelectWithAvatar::make('owner_user_id')
 *       ->relationship('owner', 'name')
 *       ->avatarSize('h-8 w-8')
 */
class UserSelectWithAvatar extends Select
{
    protected string $avatarSize = 'h-6 w-6';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->allowHtml()
            ->native(false)
            ->searchable()
            ->preload()
            ->getOptionLabelFromRecordUsing(function (Model $record): string {
                $name = e($record->name ?? $record->getAttributeValue($this->getTitleAttribute() ?? 'name'));

                $avatarUrl = '';
                if ($record instanceof HasAvatar) {
                    $avatarUrl = e($record->getFilamentAvatarUrl() ?? '');
                }

                if ($avatarUrl) {
                    return '<div class="flex items-center gap-2">'
                        . '<img src="' . $avatarUrl . '" class="' . $this->avatarSize . ' rounded-full object-cover" alt="" />'
                        . '<span>' . $name . '</span>'
                        . '</div>';
                }

                return $name;
            });
    }

    /**
     * Set the avatar image size (Tailwind classes).
     */
    public function avatarSize(string $size): static
    {
        $this->avatarSize = $size;

        return $this;
    }

    /**
     * Get the title attribute from the relationship configuration.
     */
    protected function getTitleAttribute(): ?string
    {
        return $this->getRelationshipTitleAttribute();
    }
}
