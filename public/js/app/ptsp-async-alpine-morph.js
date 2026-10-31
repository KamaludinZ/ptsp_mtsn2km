/**
 * Filament's SPA mode (/cp) loads table and form components only once they
 * come into view (x-load="visible"); until then async-alpine keeps each one
 * out of Alpine with x-ignore. Two things went wrong on a phone, where the
 * register table and its filters are below the fold:
 *
 * - The page's action modals are rendered inside the table, so "Minta Nomor
 *   SK" never opened its modal while the table waited to be scrolled to.
 *   Tables are therefore loaded at once, as Filament does outside SPA mode:
 *   on the first page, and on every page reached through wire:navigate
 *   before Alpine starts on it.
 * - The server's HTML has no x-ignore, so a Livewire update took it away from
 *   a component still waiting and Alpine ran code that was not loaded yet
 *   ("table is not defined", "selectFormComponent is not defined",
 *   "textareaFormComponent is not defined"). During an update, a component
 *   async-alpine has not loaded yet keeps its x-ignore; async-alpine removes
 *   it itself once the code is in.
 *
 * Filament's select also asks Livewire for its options again whenever its
 * state changes. Closing a modal takes its fields off the page, and a select
 * asking then goes through $wire on a removed element, which Livewire answers
 * with undefined instead of a list ("Cannot read properties of undefined
 * (reading 'map')" after Escape on Minta Nomor SK). Such a select gets an
 * empty list instead.
 */
(() => {
    if (window.ptspAsyncAlpineMorph) {
        return
    }
    window.ptspAsyncAlpineMorph = true

    const LAZY_TABLE = '[x-data="table"][x-load="visible"]'

    const loadTablesAtOnce = (root) => {
        root.querySelectorAll(LAZY_TABLE).forEach((table) => table.setAttribute('x-load', 'eager'))
    }

    const keepWaitingComponentsIgnored = () => {
        window.Livewire.hook('morph.updating', ({ el, toEl }) => {
            if (!(toEl instanceof Element)) {
                return
            }

            if (toEl.matches(LAZY_TABLE)) {
                toEl.setAttribute('x-load', 'eager')
            }

            if (el._x_async && el._x_async !== 'loaded' && el.hasAttribute('x-ignore') && !toEl.hasAttribute('x-ignore')) {
                toEl.setAttribute('x-ignore', '')
            }
        })
    }

    const SELECT_LISTS = ['getOptionsUsing', 'getOptionLabelsUsing', 'getSearchResultsUsing']

    const answerRemovedSelectsWithEmptyLists = () => {
        const data = window.Alpine.data

        window.Alpine.data = function (name, callback) {
            if (name !== 'selectFormComponent') {
                return data.call(this, name, callback)
            }

            return data.call(this, name, function (config, ...rest) {
                const lists = Object.fromEntries(SELECT_LISTS.map((key) => [key, async (...args) => (await config[key](...args)) ?? []]))

                return callback.call(this, { ...config, ...lists }, ...rest)
            })
        }
    }

    const watchLivewire = () => {
        keepWaitingComponentsIgnored()
        answerRemovedSelectsWithEmptyLists()
    }

    // This script runs at the end of the body, before Livewire starts Alpine.
    loadTablesAtOnce(document)
    document.addEventListener('livewire:navigating', (event) => {
        event.detail.onSwap(() => loadTablesAtOnce(document.body))
    })

    if (window.Livewire) {
        watchLivewire()
    } else {
        document.addEventListener('livewire:init', watchLivewire)
    }
})()
