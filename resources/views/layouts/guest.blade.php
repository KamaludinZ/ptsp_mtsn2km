<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Vite Assets: Tailwind CSS and Font Awesome (Local - No CDN) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="tw-font-sans tw-text-gray-900 tw-antialiased" x-data="{ theme: localStorage.getItem('theme') || 'light' }" :data-theme="theme">
        <div class="tw-min-h-screen tw-flex tw-flex-col sm:tw-justify-center tw-items-center tw-pt-6 sm:tw-pt-0 tw-bg-gray-100 dark:tw-bg-gray-900">
            <div class="tw-w-full sm:tw-max-w-md tw-mt-6 tw-px-6 tw-py-4 tw-bg-white dark:tw-bg-gray-800 tw-shadow-md tw-overflow-hidden sm:tw-rounded-lg">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <!-- Theme toggle button -->
                    <button 
                        @click="theme = theme === 'light' ? 'dark' : 'light'; localStorage.setItem('theme', theme)" 
                        class="tw-p-2 tw-rounded-lg tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-800 dark:tw-text-gray-200 hover:tw-bg-gray-300 dark:hover:tw-bg-gray-600 tw-transition-colors"
                        aria-label="Toggle theme"
                    >
                        <i :class="theme === 'light' ? 'fas fa-moon' : 'fas fa-sun'" aria-hidden="true"></i>
                    </button>
                    
                    <!-- Language switcher -->
                    <div class="tw-relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="tw-p-2 tw-rounded-lg tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-800 dark:tw-text-gray-200 hover:tw-bg-gray-300 dark:hover:tw-bg-gray-600 tw-transition-colors"
                            aria-label="Change language"
                        >
                            <i class="fas fa-globe"></i>
                        </button>
                        
                        <div 
                            x-show="open"
                            @click.outside="open = false"
                            x-transition:enter="tw-transition tw-ease-out tw-duration-200"
                            x-transition:enter-start="tw-transform tw-opacity-0 tw-scale-95"
                            x-transition:enter-end="tw-transform tw-opacity-100 tw-scale-105"
                            x-transition:leave="tw-transition tw-ease-in tw-duration-75"
                            x-transition:leave-start="tw-transform tw-opacity-100 tw-scale-105"
                            x-transition:leave-end="tw-transform tw-opacity-0 tw-scale-95"
                            class="tw-absolute tw-right-0 tw-mt-2 tw-w-48 tw-rounded-md tw-shadow-lg tw-bg-white dark:tw-bg-gray-800 tw-ring-1 tw-ring-black dark:tw-ring-gray-700 tw-ring-opacity-5 tw-z-50"
                        >
                            <div class="tw-py-1">
                                <button 
                                    @click="changeLanguage('id'); open = false" 
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 dark:tw-text-gray-300 hover:tw-bg-gray-100 dark:hover:tw-bg-gray-700"
                                >
                                    <span class="tw-mr-2">🇮🇩</span> Indonesia
                                </button>
                                <button 
                                    @click="changeLanguage('en'); open = false" 
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 dark:tw-text-gray-300 hover:tw-bg-gray-100 dark:hover:tw-bg-gray-700"
                                >
                                    <span class="tw-mr-2">🇬🇧</span> English
                                </button>
                                <button 
                                    @click="changeLanguage('ar'); open = false" 
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 dark:tw-text-gray-300 hover:tw-bg-gray-100 dark:hover:tw-bg-gray-700"
                                >
                                    <span class="tw-mr-2">🇸🇦</span> العربية
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <a href="/">
                        <x-application-logo class="tw-w-20 tw-h-20 tw-fill-current tw-text-gray-500" />
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
        
        <script>
            // Language Toggle
            function changeLanguage(lang) {
                console.log('Language changed to:', lang);
                // Make an AJAX request to change the language
                fetch(`/set-locale/${lang}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // Reload the page to apply the new language
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error changing language:', error);
                });
            }
        </script>
    </body>
</html>
