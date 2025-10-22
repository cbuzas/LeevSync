<div>
    <div class="flex justify-between items-center">
        <flux:heading size="2xl" level="1">
            {{ __('Execution History') }}
        </flux:heading>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700 px-6 p-3">
        @if($taskRuns->count() === 0)
            <div class="text-center p-6">
                <p class="text-gray-400 font-light dark:text-white/30">
                    {{ __('No execution history available.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($taskRuns as $run)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-semibold text-black dark:text-white tracking-wider">
                                            {{ $run->task_name }}
                                        </span>
                                        @if($run->task)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                ID: {{ $run->task_id }}
                                            </span>
                                        @else
                                            <flux:badge color="red" size="sm">
                                                Task Deleted
                                            </flux:badge>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <flux:tooltip content="{{ $run->source }}" position="top">
                                        <span class="text-xs font-mono font-light text-gray-600 dark:text-white/70">
                                            {{ Str::limit($run->source, 30) }}
                                        </span>
                                    </flux:tooltip>
                                </td>

                                <td class="px-6 py-4">
                                    <flux:tooltip content="{{ $run->destination }}" position="top">
                                        <span class="text-xs font-mono font-light text-gray-600 dark:text-white/70">
                                            {{ Str::limit($run->destination, 30) }}
                                        </span>
                                    </flux:tooltip>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs">
                                        <div class="text-gray-600 dark:text-white/70">
                                            {{ $run->started_at->diffForHumans() }}
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">
                                            {{ $run->started_at->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-600 dark:text-white/70">
                                        @if($run->completed_at)
                                            {{ $run->started_at->diffInSeconds($run->completed_at) }}s
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400 italic">In progress...</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'running' => ['color' => 'blue', 'icon' => 'arrow-path', 'text' => 'Running'],
                                            'completed' => ['color' => 'green', 'icon' => 'check', 'text' => 'Completed'],
                                            'failed' => ['color' => 'red', 'icon' => 'x-mark', 'text' => 'Failed'],
                                        ];
                                        $config = $statusConfig[$run->status] ?? ['color' => 'gray', 'icon' => 'question-mark-circle', 'text' => 'Unknown'];
                                    @endphp
                                    <flux:badge color="{{ $config['color'] }}" size="sm">
                                        <flux:icon name="{{ $config['icon'] }}" :class="'w-3 h-3' . ($run->status === 'running' ? ' animate-spin' : '')" />
                                        {{ $config['text'] }}
                                    </flux:badge>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($run->log_file_path)
                                            <flux:button
                                                icon="document-text"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="viewLog({{ $run->id }})"
                                            >
                                                View log
                                            </flux:button>
                                        @endif

                                        @if($run->output)
                                            <flux:tooltip content="Output available" position="left">
                                                <flux:badge color="blue" size="sm">
                                                    <flux:icon name="command-line" class="w-3 h-3" />
                                                </flux:badge>
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $taskRuns->links() }}
            </div>
        @endif
    </div>

    {{-- Log Modal --}}
    @if($selectedRun && $logContent)
        <flux:modal name="log-viewer" class="max-w-4xl" variant="flyout">
            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">
                        Log - {{ $selectedRun->task_name }}
                    </flux:heading>
                    <flux:subheading>
                        Executed on {{ $selectedRun->started_at->format('M j, Y \a\t H:i:s') }}
                    </flux:subheading>
                </div>

                <flux:separator variant="subtle" />

                <div class="bg-zinc-900 dark:bg-black rounded-lg p-4 overflow-auto max-h-96">
                    <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ $logContent }}</pre>
                </div>

                @if($selectedRun->error_message)
                    <flux:separator variant="subtle" />
                    <div>
                        <flux:heading size="sm" class="text-red-600 dark:text-red-400">
                            Error message
                        </flux:heading>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                            {{ $selectedRun->error_message }}
                        </p>
                    </div>
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button variant="ghost" wire:click="closeLogModal">
                        Close
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
