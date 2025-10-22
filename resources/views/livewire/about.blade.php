<div>

    <flux:heading size="2xl" level="1"> {{ __('Leev Sync - Project') }}</flux:heading>
    <flux:subheading>{{ __('Open Source rsync tool.') }}</flux:subheading>

    <flux:separator class="my-6" variant="subtle" />

    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700 divide-y divide-gray-200 dark:divide-zinc-700">
        <details class="group">
            <summary class="cursor-pointer px-6 py-4 font-medium text-gray-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center justify-between">
                <span>{{ __('What is the purpose of this project?') }}</span>
                <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="px-6 py-4 text-gray-600 dark:text-gray-400">
                <p class="mb-3">{{ __('LeevSync is a lightweight and modern tool designed to simplify file synchronization and backup management using rsync.') }}</p>
                <p class="mb-3">{{ __('The goal of the project is to provide a simple, adaptable foundation built with modern frameworks such as Laravel and Livewire, making it easy to extend and customize for different use cases.') }}</p>
                <p>{{ __('With LeevSync, you can easily create, run synchronization tasks, review execution and history — all from a clean and intuitive interface.') }}</p>
            </div>

        </details>

        <details class="group">
            <summary class="cursor-pointer px-6 py-4 font-medium text-gray-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center justify-between">
                <span>{{ __('Why NativePHP?') }}</span>
                <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="px-6 py-4 text-gray-600 dark:text-gray-400">
                <p class="mb-3">{{ __('By building LeevSync with NativePHP, i aim to:') }}</p>
                <ul class="list-disc list-inside mb-3 space-y-2">
                    <li>{{ __('Explore the integration between Laravel and desktop application features') }}</li>
                    <li>{{ __('Test the performance and user experience of NativePHP-based applications') }}</li>
                    <li>{{ __('Demonstrate real-world use cases for NativePHP beyond simple examples') }}</li>
                </ul>
                <p>{{ __('This project is both a practical tool and a learning experience, pushing the boundaries of what\'s possible with PHP in the desktop application space.') }}</p>
            </div>
        </details>

        <details class="group">
            <summary class="cursor-pointer px-6 py-4 font-medium text-gray-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center justify-between">
                <span>{{ __('Open Source') }}</span>
                <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="px-6 py-4 text-gray-600 dark:text-gray-400">
                <p>{{ __('This project is licensed under the MIT License.') }}</p>
                <a class="underline font-bold cursor-pointer" wire:click="openExternal('https://github.com/cbuzas/leevsync/blob/main/LICENSE')">
                    Know more about the license
                </a>
            </div>
        </details>

        <details class="group">
            <summary class="cursor-pointer px-6 py-4 font-medium text-gray-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center justify-between">
                <span>{{ __('Support') }}</span>
                <svg class="w-5 h-5 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="px-6 py-4 text-gray-600 dark:text-gray-400">
                <p class="mb-4 font-semibold text-gray-700 dark:text-gray-300">{{ __('Love Leev Sync? ⭐ Star this project!') }}</p>
                <p class="mb-4">{{ __('If you find Leev Sync useful and enjoy using it, please consider giving it a star on GitHub! Your support helps the project grow and motivates continued development. Every star counts and is greatly appreciated!') }}</p>
                <a wire:click="openExternal('https://www.buymeacoffee.com/cbuzas')"  class="cursor-pointer inline-block mr-2">
                    <img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" class="h-7">
                </a>
                <a wire:click="openExternal('https://github.com/cbuzas/leevsync')" class="cursor-pointer inline-block">
                    <img src="https://img.shields.io/badge/⭐_Star_on-GitHub-black?style=for-the-badge&logo=github" alt="Star on GitHub" class="h-7">
                </a>
            </div>
        </details>
    </div>


</div>
