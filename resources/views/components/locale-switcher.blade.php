{{-- Standard locale switcher. Scoped, self-contained styles (emitted once) so
     it looks correct in a Filament panel and on any public Blade page without
     relying on the host's Tailwind build or the flag-icons library. --}}

@once
    <style>
        [x-cloak] { display: none !important; }
        .pe-ls { position: relative; display: inline-block; }
        .pe-ls__trigger {
            display: inline-flex; align-items: center; gap: 0.375rem;
            height: 2.25rem; padding: 0 0.625rem;
            border: 1px solid rgb(226 232 240); border-radius: 0.5rem;
            background: transparent; color: rgb(71 85 105); cursor: pointer;
            font-size: 0.8125rem; font-weight: 600; line-height: 1;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }
        .pe-ls__trigger:hover { background: rgb(241 245 249); color: rgb(15 23 42); }
        .pe-ls__globe { width: 1rem; height: 1rem; opacity: .8; }
        .pe-ls__chevron { width: .85rem; height: .85rem; opacity: .6; transition: transform .15s ease; }
        .pe-ls[data-open="true"] .pe-ls__chevron { transform: rotate(180deg); }
        .pe-ls__menu {
            position: absolute; top: calc(100% + 0.5rem); z-index: 50;
            min-width: 11rem; padding: 0.375rem;
            background: #fff; border: 1px solid rgb(226 232 240);
            border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(2, 6, 23, 0.12);
        }
        .pe-ls__menu--end { inset-inline-end: 0; }
        .pe-ls__menu--start { inset-inline-start: 0; }
        .pe-ls__item {
            display: flex; align-items: center; gap: 0.5rem;
            width: 100%; padding: 0.5rem 0.625rem; border-radius: 0.5rem;
            font-size: 0.875rem; color: rgb(51 65 85); text-decoration: none;
            transition: background-color .12s ease;
        }
        .pe-ls__item:hover { background: rgb(241 245 249); }
        .pe-ls__item[aria-current="true"] { color: rgb(5 150 105); font-weight: 600; }
        .pe-ls__check { width: 1rem; height: 1rem; flex: none; }
        .pe-ls__check--hidden { visibility: hidden; }
        .pe-ls__native { flex: 1; }
        .pe-ls__code { font-size: 0.6875rem; letter-spacing: .04em; color: rgb(148 163 184); text-transform: uppercase; }

        /* Dark mode (host toggles .dark on <html>). */
        .dark .pe-ls__trigger { border-color: rgb(51 65 85); color: rgb(203 213 225); }
        .dark .pe-ls__trigger:hover { background: rgb(30 41 59); color: rgb(241 245 249); }
        .dark .pe-ls__menu { background: rgb(15 23 42); border-color: rgb(51 65 85); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .dark .pe-ls__item { color: rgb(203 213 225); }
        .dark .pe-ls__item:hover { background: rgb(30 41 59); }
        .dark .pe-ls__item[aria-current="true"] { color: rgb(52 211 153); }
        .dark .pe-ls__code { color: rgb(100 116 139); }
    </style>
@endonce

<div class="pe-ls"
     x-data="{ open: false }"
     x-bind:data-open="open.toString()"
     @keydown.escape="open = false">
    <button type="button"
            class="pe-ls__trigger"
            @click="open = !open"
            x-bind:aria-expanded="open.toString()"
            aria-haspopup="true"
            aria-label="{{ __('Change language') }}">
        <svg class="pe-ls__globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
            <path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/>
        </svg>
        <span>{{ strtoupper($currentLocale) }}</span>
        <svg class="pe-ls__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div class="pe-ls__menu pe-ls__menu--{{ $align }}"
         x-show="open"
         x-cloak
         x-transition.origin.top
         @click.outside="open = false"
         role="menu">
        @foreach ($locales as $code => $locale)
            <a href="{{ route($switchRoute, $code) }}"
               class="pe-ls__item"
               role="menuitem"
               lang="{{ $code }}"
               dir="{{ $locale['dir'] }}"
               @if ($code === $currentLocale) aria-current="true" @endif>
                <svg class="pe-ls__check {{ $code === $currentLocale ? '' : 'pe-ls__check--hidden' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0L4.296 10.71a1 1 0 011.42-1.42L8.5 12.07l6.79-6.78a1 1 0 011.414 0z"/>
                </svg>
                <span class="pe-ls__native">{{ $locale['native'] }}</span>
                <span class="pe-ls__code">{{ $code }}</span>
            </a>
        @endforeach
    </div>
</div>
