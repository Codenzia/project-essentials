<?php

namespace Codenzia\ProjectEssentials\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Standard locale/language switcher dropdown.
 *
 * Self-contained: ships its own scoped CSS (no flag-icons library, no host
 * Tailwind compilation required) so it renders identically inside a Filament
 * panel AND on any public Blade page. Uses Alpine for the dropdown toggle
 * (always present in Filament/Livewire environments) and a globe glyph +
 * native language names instead of flag sprites.
 *
 * Usage:
 *   <x-project-essentials::locale-switcher />
 *   <x-project-essentials::locale-switcher :locales="['en' => [...], 'ar' => [...]]" align="start" />
 */
class LocaleSwitcher extends Component
{
    /** @var array<string, array{code:string, native:string, dir:string}> */
    public array $locales;

    public string $currentLocale;

    public string $switchRoute;

    public string $align;

    /**
     * Native language names for the most common locales, used when the caller
     * doesn't supply one. Falls back to the uppercased code for anything else.
     *
     * @var array<string, string>
     */
    protected array $nativeNames = [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
        'tr' => 'Türkçe',
        'ur' => 'اردو',
        'fa' => 'فارسی',
        'hi' => 'हिन्दी',
        'zh' => '中文',
        'ru' => 'Русский',
        'pt' => 'Português',
        'it' => 'Italiano',
        'id' => 'Bahasa Indonesia',
        'ms' => 'Bahasa Melayu',
        'he' => 'עברית',
        'nl' => 'Nederlands',
    ];

    /**
     * @param  array<string, mixed>|null  $locales  Map of code => meta (string|array). Defaults
     *                                              to filament-panel-base's active locales, then
     *                                              config('app.available_locales'), then ['en'].
     */
    public function __construct(
        ?array $locales = null,
        ?string $currentLocale = null,
        string $switchRoute = 'locale.switch',
        string $align = 'end',
    ) {
        $this->locales = $this->normalize($locales ?? $this->resolveLocales());
        $this->currentLocale = $currentLocale ?? app()->getLocale();
        $this->switchRoute = $switchRoute;
        $this->align = $align === 'start' ? 'start' : 'end';
    }

    /**
     * Resolve the available locales from the environment without hard-depending
     * on any one package.
     *
     * @return array<string, mixed>
     */
    protected function resolveLocales(): array
    {
        $providerMiddleware = 'Codenzia\\FilamentPanelBase\\Middleware\\SetLocale';
        if (class_exists($providerMiddleware) && method_exists($providerMiddleware, 'getLocales')) {
            $locales = $providerMiddleware::getLocales();
            if (! empty($locales)) {
                return $locales;
            }
        }

        $available = config('app.available_locales');
        if (is_array($available) && ! empty($available)) {
            return $available;
        }

        return [app()->getLocale() => []];
    }

    /**
     * Normalize mixed input into a uniform shape: code => [code, native, dir].
     *
     * Accepts entries as a string code, or an array carrying any of
     * native/name/dir (filament-panel-base's shape is supported as-is).
     *
     * @param  array<int|string, mixed>  $locales
     * @return array<string, array{code:string, native:string, dir:string}>
     */
    protected function normalize(array $locales): array
    {
        $out = [];

        foreach ($locales as $key => $meta) {
            // Support both ['en' => [...]] and list-style ['en', 'ar'].
            $code = is_int($key) ? (string) $meta : (string) $key;

            $native = null;
            $dir = null;

            if (is_array($meta)) {
                $native = $meta['native'] ?? ($meta['name'] ?? null);
                $dir = $meta['dir'] ?? null;
            }

            // A native that just echoes the code isn't useful — prefer our map.
            if (! $native || $native === $code) {
                $native = $this->nativeNames[$code] ?? strtoupper($code);
            }

            $dir ??= in_array($code, ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr';

            $out[$code] = ['code' => $code, 'native' => $native, 'dir' => $dir];
        }

        return $out;
    }

    public function shouldRender(): bool
    {
        return count($this->locales) > 1;
    }

    public function render(): View
    {
        return view('project-essentials::components.locale-switcher');
    }
}
