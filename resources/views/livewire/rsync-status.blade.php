<div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <flux:icon name="rocket-launch" class="size-8 mr-4 text-leev-secondary-400" />
            <h3 class="text-xl font-normal underline underline-offset-6  flex items-center">

                Sync tool health check
            </h3>

        </div>

        <div class="flex space-x-2">
            <flux:button icon="arrow-path" wire:click="refreshInfo" />
        </div>

    </div>

    <flux:separator class="my-6" variant="subtle" />

    @if($loading)
        <div class="flex items-center justify-center py-8">
            <svg class="animate-spin h-6 w-6 mr-3 text-blue-600" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
            </svg>
            <span class="text-gray-600">Work in progress...</span>
        </div>

    @elseif($error)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Detection error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ $error }}</p>

                    @if(!empty($syncInfo['install_commands']))
                        <div class="mt-3">
                            <p class="text-sm text-red-700 font-medium mb-2">Recommended installation:</p>
                            @foreach($syncInfo['install_commands'] as $command)
                                <code class="block text-xs bg-red-100 text-red-800 px-2 py-1 rounded mt-1 font-mono">{{ $command }}</code>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @else
        <div class="">
            {{-- Status principal --}}
            <div class="">
                <div class="flex  items-center gap-5">
                    @if($syncInfo['recommended'])
                       <flux:icon name="check-circle" class="size-7 text-green-600" />
                    @else
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-yellow-100">
                            <svg class="h-4 w-4 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <h4 class="text-sm font-medium {{ $syncInfo['recommended'] ? '' : 'text-yellow-800' }}">
                            {{ $syncInfo['tool'] }}
                        </h4>
                        @if(!empty($syncInfo['message']))
                            <p class="text-xs italic  {{ $syncInfo['recommended'] ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $syncInfo['message'] }}
                            </p>
                        @endif
                    </div>

                </div>


            </div>

            <flux:separator class="my-6" variant="subtle" />

            {{-- Détails techniques --}}
            @if(!empty($syncInfo['details']))
                <details class="group mt-4">
                    <summary class="cursor-pointer text-sm text-leev-secondary-400 hover:text-leev-secondary-600 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1 transform group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Technical details
                    </summary>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-zinc-700 mt-6 p-4">
                        @foreach($syncInfo['details'] as $toolName => $toolInfo)
                            @if($toolInfo['available'])
                                <div class="">
                                    @if($toolName === 'openrsync')
                                        <flux:badge color="lime" class="">OpenRsync</flux:badge>
                                    @else
                                        <flux:badge color="blue" class="">Rsync</flux:badge>
                                    @endif


                                    <dl class="mt-4 grid grid-cols-1 divide-y divide-gray-100 dark:divide-white/20 gap-1 text-sm">
                                        <div class="flex justify-between py-2">
                                            <dt class="font-thin text-xs uppercase ">Version:</dt>
                                            <dd class="font-mono">{{ $toolInfo['version'] }}</dd>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <dt class="font-thin text-xs uppercase  ">Path:</dt>
                                            <dd class="text-gray-900 font-mono text-xs">
                                                <flux:badge color="gray"  size="sm" class="">{{ $toolInfo['path'] }}</flux:badge>

                                            </dd>
                                        </div>
                                        @if(isset($toolInfo['protocol']))
                                            <div class="flex justify-between py-2">
                                                <dt class="font-thin text-xs uppercase ">Protocol:</dt>
                                                <dd class=" font-bold font-mono">{{ $toolInfo['protocol'] }}</dd>
                                            </div>
                                        @endif
                                        @if(isset($toolInfo['is_system']) && $toolInfo['is_system'])
                                            <div class="flex justify-between items-center pt-2">
                                                <dt class="font-thin text-xs uppercase ">Type:</dt>
                                                <dd>
                                                    <flux:badge color="lime"  class="">macOS System</flux:badge>
                                                </dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif

</div>
