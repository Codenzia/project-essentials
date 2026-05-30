/**
 * Responsive Tabs Alpine.js Component
 *
 * A responsive tab component that automatically folds overflow tabs into a "More"
 * dropdown when there isn't enough space to display all tabs.
 *
 * Usage:
 * Pair with resources/views/components/responsive-tabs.blade.php
 *
 * Registration:
 * - Registers on alpine:init for fresh page loads
 * - Registers on livewire:navigated for SPA navigation
 * - Registers immediately if Alpine already loaded
 *
 * Alpine Data Properties:
 * - activeTab: Currently selected tab ID
 * - moreOpen: Whether the overflow dropdown is open
 * - visibleCount: Number of tabs visible before overflow
 * - tabs: Array of tab definitions
 *
 * Public Methods:
 * - select(index): Select a tab by index
 * - getTab(id): Get tab definition by ID
 * - getCurrentTab(): Get currently active tab definition
 * - getParams(id?): Get params for tab (current if no ID)
 * - isVisible(index): Check if tab at index is visible
 *
 * Events Dispatched:
 * - change (component): { id, label, icon, badge, params, index }
 * - responsive-tabs:change (window): { id, label, icon, badge, params, index }
 */
(function () {
  let registered = false;

  function registerResponsiveTabs() {
    if (typeof Alpine === "undefined" || registered) return;
    registered = true;

    Alpine.data(
      "responsiveTabs",
      ({ activeTabInit, tabs, persistKey = null }) => ({
        activeTab: null,
        moreOpen: false,
        visibleCount: tabs.length,
        tabs: tabs,
        tabWidths: [],
        resizeObserver: null,
        persistKey: persistKey,
        _initialized: false,
        _resizeTimeout: null,
        _initAttempts: 0,
        _maxInitAttempts: 50, // ~2.5 seconds max

        init() {
          // Restore from storage or use default
          this.activeTab = this.getInitialTab(activeTabInit);

          this.$nextTick(() => {
            const index = this.tabs.findIndex((t) => t.id === this.activeTab);
            if (index !== -1 && this.persistKey) {
              this.dispatchChange(this.tabs[index], index);
            }
            this._initialized = true;
          });

          // Watch for storage persistence
          if (this.persistKey) {
            this.$watch("activeTab", (value, oldValue) => {
              if (this._initialized && value !== oldValue && value) {
                localStorage.setItem(this.getStorageKey(), value);
              }
            });
          }

          // Delayed initialization with max attempts
          this.tryInit();

          // Setup resize observer when nav is available
          this.$nextTick(() => this.setupResizeObserver());
        },

        destroy() {
          clearTimeout(this._resizeTimeout);
          this.resizeObserver?.disconnect();
        },

        getInitialTab(init) {
          // Check URL first (highest priority)
          const urlParams = new URLSearchParams(window.location.search);
          const urlTab = urlParams.get("tab");
          if (urlTab && this.tabs.some((t) => t.id === urlTab)) {
            return urlTab;
          }

          // Then localStorage if persist is enabled
          if (this.persistKey) {
            const stored = localStorage.getItem(this.getStorageKey());
            if (stored && this.tabs.some((t) => t.id === stored)) {
              return stored;
            }
          }

          // Then use init value (handles both string and entangled proxy)
          const initValue =
            typeof init === "string" ? init : init?.toString?.() || init;
          if (initValue && this.tabs.some((t) => t.id === initValue)) {
            return initValue;
          }

          // Fallback to first tab
          return this.tabs[0]?.id ?? null;
        },

        getStorageKey() {
          return `responsive-tabs:${this.persistKey}`;
        },

        tryInit() {
          this._initAttempts++;

          if (this._initAttempts > this._maxInitAttempts) {
            console.warn("ResponsiveTabs: Max init attempts reached");
            return;
          }

          const nav = this.$refs.nav;
          if (!nav || nav.offsetWidth === 0) {
            requestAnimationFrame(() => this.tryInit());
            return;
          }

          this.measureTabs();
          this.calculate();
        },

        setupResizeObserver() {
          if (!this.$refs.nav || this.resizeObserver) return;

          this.resizeObserver = new ResizeObserver(() => {
            clearTimeout(this._resizeTimeout);
            this._resizeTimeout = setTimeout(() => this.calculate(), 50);
          });
          this.resizeObserver.observe(this.$refs.nav);
        },

        measureTabs() {
          const nav = this.$refs.nav;
          if (!nav) return;

          const tabEls = nav.querySelectorAll("[data-tab-item]");
          if (!tabEls.length) return;

          // Batch DOM operations to minimize reflows
          const measurements = [];

          // First pass: unhide all
          tabEls.forEach((el) => {
            el.style.cssText =
              "display: inline-flex !important; visibility: hidden !important;";
          });

          // Single reflow - read all widths
          tabEls.forEach((el) => {
            measurements.push(el.getBoundingClientRect().width + 8);
          });

          // Second pass: restore styles
          tabEls.forEach((el) => {
            el.style.cssText = "";
          });

          this.tabWidths = measurements;
        },

        calculate() {
          const nav = this.$refs.nav;
          if (!nav || this.tabWidths.length === 0) return;

          const navWidth = nav.offsetWidth;
          if (navWidth === 0) return;

          const moreWidth = this.$refs.moreBtn?.offsetWidth || 140;
          const totalAllWidth = this.tabWidths.reduce((a, b) => a + b, 0);

          // All tabs fit - show all
          if (totalAllWidth <= navWidth) {
            this.visibleCount = this.tabs.length;
            return;
          }

          // Calculate how many fit
          let totalWidth = 0;
          let count = 0;

          for (let i = 0; i < this.tabWidths.length; i++) {
            const tabWidth = this.tabWidths[i];
            const isLast = i === this.tabs.length - 1;
            const reserved = isLast ? 0 : moreWidth;

            if (totalWidth + tabWidth + reserved <= navWidth) {
              totalWidth += tabWidth;
              count++;
            } else {
              break;
            }
          }

          this.visibleCount = Math.max(1, count);
        },

        dispatchChange(tab, index) {
          const eventData = { ...tab, index };
          this.$dispatch("change", eventData);
          window.dispatchEvent(
            new CustomEvent("responsive-tabs:change", { detail: eventData })
          );
        },

        isVisible(index) {
          return index < this.visibleCount;
        },

        get hasOverflow() {
          return this.visibleCount < this.tabs.length;
        },

        get overflowCount() {
          return this.tabs.length - this.visibleCount;
        },

        get isActiveInOverflow() {
          const activeIndex = this.tabs.findIndex(
            (t) => t.id === this.activeTab
          );
          return activeIndex >= this.visibleCount;
        },

        getTab(id) {
          return this.tabs.find((t) => t.id === id);
        },

        getCurrentTab() {
          return this.getTab(this.activeTab);
        },

        getParams(id = null) {
          return this.getTab(id ?? this.activeTab)?.params ?? {};
        },

        select(index) {
          const tab = this.tabs[index];
          if (!tab || tab.disabled) return;

          this.activeTab = tab.id;
          this.moreOpen = false;
          this.dispatchChange(tab, index);
        },
      })
    );
  }

  document.addEventListener("alpine:init", registerResponsiveTabs);
  document.addEventListener("livewire:navigated", () => {
    // Re-register for SPA navigation if needed
    if (typeof Alpine !== "undefined") {
      registerResponsiveTabs();
    }
  });

  if (typeof Alpine !== "undefined") {
    registerResponsiveTabs();
  }
})();
