<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithNotifications;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RsyncStatus extends Component
{
    use WithNotifications;

    public $syncInfo = [];

    public $loading = true;

    public $error = null;

    public function mount()
    {
        $this->loadSyncInfo();
    }

    private function getSyncToolInfo()
    {
        $tools = [
            'openrsync' => ['available' => false],
            'rsync' => ['available' => false],
        ];

        try {
            // Find all rsync-type executables
            $executables = $this->findAllRsyncExecutables();

            foreach ($executables as $executablePath) {
                $toolInfo = $this->analyzeExecutable($executablePath);

                if ($toolInfo['type'] === 'openrsync') {
                    $tools['openrsync'] = [
                        'available' => true,
                        'path' => $executablePath,
                        'version' => $toolInfo['version'],
                        'is_system' => $executablePath === '/usr/bin/rsync',
                        'protocol' => $toolInfo['protocol'] ?? null,
                    ];
                } else {
                    $tools['rsync'] = [
                        'available' => true,
                        'path' => $executablePath,
                        'version' => $toolInfo['version'],
                        'is_system' => $executablePath === '/usr/bin/rsync',
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error detecting sync tools: '.$e->getMessage());
        }

        return $tools;
    }

    private function findAllRsyncExecutables()
    {
        $executables = [];

        // Possible paths to check
        $possiblePaths = [
            '/opt/homebrew/bin/openrsync',  // Homebrew Apple Silicon
            '/opt/homebrew/bin/rsync',
            '/usr/local/bin/openrsync',     // Homebrew Intel
            '/usr/local/bin/rsync',
            '/usr/bin/rsync',               // macOS system (often OpenRsync)
        ];

        // Check direct paths
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                $executables[] = $path;
            }
        }

        // Use which to find other instances
        $whichCommands = ['rsync', 'openrsync'];
        foreach ($whichCommands as $cmd) {
            $result = shell_exec("which -a $cmd 2>/dev/null");
            if ($result) {
                $paths = array_filter(array_map('trim', explode("\n", $result)));
                $executables = array_merge($executables, $paths);
            }
        }

        return array_unique(array_filter($executables));
    }

    private function analyzeExecutable($path)
    {
        $info = [
            'type' => 'rsync',
            'version' => 'unknown',
            'protocol' => null,
        ];

        try {
            // Test with -V (works for OpenRsync and modern rsync)
            $versionOutput = shell_exec("$path -V 2>&1");

            if ($versionOutput) {
                // Detect OpenRsync
                if (strpos($versionOutput, 'openrsync') !== false) {
                    $info['type'] = 'openrsync';

                    // Extract protocol number
                    if (preg_match('/protocol version (\d+)/', $versionOutput, $matches)) {
                        $info['protocol'] = $matches[1];
                        $info['version'] = 'protocol '.$matches[1];
                    }

                    // Look for rsync compatible version
                    if (preg_match('/rsync version ([0-9]+\.[0-9]+\.[0-9]+) compatible/', $versionOutput, $matches)) {
                        $info['version'] = $matches[1].' (OpenRsync)';
                    }
                }
                // Detect classic rsync
                elseif (preg_match('/rsync\s+version\s+([0-9]+\.[0-9]+\.[0-9]+)/', $versionOutput, $matches)) {
                    $info['type'] = 'rsync';
                    $info['version'] = $matches[1];
                }
            }

            // Fallback with --version for older versions
            if ($info['version'] === 'unknown') {
                $versionOutput = shell_exec("$path --version 2>&1 | head -1");
                if ($versionOutput && preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', $versionOutput, $matches)) {
                    $info['version'] = $matches[1];
                }
            }

        } catch (\Exception $e) {
            Log::error("Error analyzing $path: ".$e->getMessage());
        }

        return $info;
    }

    public function showSyncInfo()
    {
        $syncInfo = $this->getSyncToolInfo();

        // Handle case where no tool is available
        if (! $syncInfo || (! $syncInfo['openrsync']['available'] && ! $syncInfo['rsync']['available'])) {
            return [
                'tool' => 'No tool available',
                'recommended' => false,
                'details' => $syncInfo,
                'error' => 'Please install openrsync or rsync',
                'install_commands' => [
                    'brew install openrsync  # Recommended for macOS',
                    'brew install rsync      # Classic alternative',
                ],
                'message' => 'Installation required',
            ];
        }

        // Determine recommended tool
        if ($syncInfo['openrsync']['available']) {
            $tool = 'OpenRsync '.$syncInfo['openrsync']['version'];
            $recommended = true;
            $message = $syncInfo['openrsync']['is_system']
                ? 'macOS native tool (system)'
                : 'OpenRsync installed via Homebrew';
        } elseif ($syncInfo['rsync']['available']) {
            $tool = 'Rsync '.$syncInfo['rsync']['version'];
            $recommended = ! ($syncInfo['rsync']['is_system'] ?? false);
            $message = ($syncInfo['rsync']['is_system'] ?? false)
                ? 'System version (may be outdated)'
                : 'Modern version installed via Homebrew';
        }

        return [
            'tool' => $tool ?? 'Detection error',
            'recommended' => $recommended ?? false,
            'message' => $message ?? '',
            'details' => $syncInfo,
            'error' => null,
        ];
    }

    public function loadSyncInfo()
    {
        try {
            $this->loading = true;
            $this->syncInfo = $this->showSyncInfo();
            $this->error = $this->syncInfo['error'] ?? null;
        } catch (\Exception $e) {
            $this->error = 'Error loading: '.$e->getMessage();
            Log::error('Error in Livewire RsyncStatus: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());
        } finally {
            $this->loading = false;
        }
    }

    public function refreshInfo()
    {
        $this->loadSyncInfo();
        $this->notifySuccess('Updated information successfully', 3000);
    }

    public function render()
    {
        return view('livewire.rsync-status', [
            'syncInfo' => $this->syncInfo,
            'loading' => $this->loading,
            'error' => $this->error,
        ]);
    }
}
