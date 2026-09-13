<?php

declare(strict_types=1);

use Codenzia\ProjectEssentials\Filament\Actions\SplitButtonDropdownAction;
use Codenzia\ProjectEssentials\Forms\Components\CardRepeater;
use Codenzia\ProjectEssentials\Forms\Components\CounterInput;
use Codenzia\ProjectEssentials\Forms\Components\DatePickerWithHint;
use Codenzia\ProjectEssentials\Forms\Components\DateRangePicker;
use Codenzia\ProjectEssentials\Forms\Components\DateTimePickerWithHint;
use Codenzia\ProjectEssentials\Forms\Components\IconColoredEnumSelect;
use Codenzia\ProjectEssentials\Forms\Components\IconPicker;
use Codenzia\ProjectEssentials\Forms\Components\PercentageSlider;
use Codenzia\ProjectEssentials\Forms\Components\UserSelectWithAvatar;
use Codenzia\ProjectEssentials\Infolists\Components\ColoredEnumEntry;
use Codenzia\ProjectEssentials\Infolists\Components\ColoredPillsEntry;
use Codenzia\ProjectEssentials\Infolists\Components\CreatedUpdatedEntry;
use Codenzia\ProjectEssentials\Infolists\Components\PriorityEntry;
use Codenzia\ProjectEssentials\Infolists\Components\ProgressBarEntry;
use Codenzia\ProjectEssentials\Infolists\Components\TagEntry;
use Codenzia\ProjectEssentials\Infolists\Components\UserAvatarEntry;
use Codenzia\ProjectEssentials\ProjectEssentialsServiceProvider;
use Codenzia\ProjectEssentials\Tables\Columns\CircularProgressBar;
use Codenzia\ProjectEssentials\Tables\Columns\ColoredEnumColumn;
use Codenzia\ProjectEssentials\Tables\Columns\ColoredPillsColumn;
use Codenzia\ProjectEssentials\Tables\Columns\CreatedUpdatedColumn;
use Codenzia\ProjectEssentials\Tables\Columns\HtmlColumn;
use Codenzia\ProjectEssentials\Tables\Columns\PriorityColumn;
use Codenzia\ProjectEssentials\Tables\Columns\ProgressBarColumn;
use Codenzia\ProjectEssentials\Tables\Columns\StateSwitcher;
use Codenzia\ProjectEssentials\Tables\Columns\TagColumn;
use Codenzia\ProjectEssentials\Tables\Columns\UserAvatarColumn;
use Codenzia\ProjectEssentials\Tables\Filters\DateRangeFilter;
use Codenzia\ProjectEssentials\View\Components\LoadingSpinner;
use Codenzia\ProjectEssentials\View\Components\LocaleSwitcher;
use Codenzia\ProjectEssentials\View\Components\Progress;
use Filament\Actions\ActionGroup as FilamentActionGroup;
use Filament\Forms\Components\Field as FilamentField;
use Filament\Infolists\Components\Entry as FilamentEntry;
use Filament\Tables\Columns\Column as FilamentColumn;
use Filament\Tables\Filters\Filter as FilamentFilter;
use Illuminate\View\Component as BladeComponent;

/**
 * BVT — Build Verification Test (existence net).
 *
 * project-essentials is a component library, so its BVT is a construction net:
 * every shipped Filament form field, infolist entry, table column, filter and
 * action must construct via ::make() (no fatal in setUp), and every shipped
 * Blade view/component must resolve. Deep behaviour lives in the surface tests
 * (PaymentTimelineTest, StateSwitcherTest, CardRepeaterTest, DateRangeFilterTest,
 * DateRange*Test, FilamentTableHelperTest, CanToggleColumnsTest, …). BVT only
 * guarantees each surface still exists and boots.
 */
it('every shipped form component constructs and is a Filament field', function (string $class): void {
    $instance = $class::make('bvt');

    expect($instance)->toBeInstanceOf(FilamentField::class);
})->with([
    CardRepeater::class,
    CounterInput::class,
    DatePickerWithHint::class,
    DateRangePicker::class,
    DateTimePickerWithHint::class,
    IconColoredEnumSelect::class,
    IconPicker::class,
    PercentageSlider::class,
    UserSelectWithAvatar::class,
]);

it('every shipped infolist component constructs and is a Filament entry', function (string $class): void {
    $instance = $class::make('bvt');

    expect($instance)->toBeInstanceOf(FilamentEntry::class);
})->with([
    ColoredEnumEntry::class,
    ColoredPillsEntry::class,
    CreatedUpdatedEntry::class,
    PriorityEntry::class,
    ProgressBarEntry::class,
    TagEntry::class,
    UserAvatarEntry::class,
]);

it('every shipped table column constructs and is a Filament column', function (string $class): void {
    $instance = $class::make('bvt');

    expect($instance)->toBeInstanceOf(FilamentColumn::class);
})->with([
    CircularProgressBar::class,
    ColoredEnumColumn::class,
    ColoredPillsColumn::class,
    CreatedUpdatedColumn::class,
    HtmlColumn::class,
    PriorityColumn::class,
    ProgressBarColumn::class,
    StateSwitcher::class,
    TagColumn::class,
    UserAvatarColumn::class,
]);

it('the date-range filter constructs and is a Filament filter', function (): void {
    $instance = DateRangeFilter::make('bvt');

    expect($instance)->toBeInstanceOf(FilamentFilter::class);
});

it('the split-button dropdown action constructs and is a Filament action group', function (): void {
    $instance = SplitButtonDropdownAction::make([]);

    expect($instance)->toBeInstanceOf(FilamentActionGroup::class);
});

it('every shipped Blade view component class loads and is a view component', function (string $class): void {
    expect(class_exists($class))->toBeTrue()
        ->and(is_subclass_of($class, BladeComponent::class))->toBeTrue();
})->with([
    LoadingSpinner::class,
    LocaleSwitcher::class,
    Progress::class,
]);

it('every shipped Blade view resolves under the package namespace', function (string $view): void {
    expect(view()->exists($view))->toBeTrue();
})->with(shipped_project_essentials_views());

/**
 * Build the view-namespace dataset by scanning the package's shipped Blade
 * files, so newly-added views are covered without editing this test.
 *
 * @return array<string, array{string}>
 */
function shipped_project_essentials_views(): array
{
    $root = __DIR__ . '/../../resources/views';
    $namespace = ProjectEssentialsServiceProvider::$viewNamespace;

    $dataset = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $key = substr($relative, 0, -strlen('.blade.php'));
        $dotted = str_replace('/', '.', $key);

        $dataset[$dotted] = ["{$namespace}::{$dotted}"];
    }

    ksort($dataset);

    return $dataset;
}
