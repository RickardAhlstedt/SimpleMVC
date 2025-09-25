<?php

namespace SimpleMVC\Debug;

class DebugToolbar
{
    private array $collectors = [];
    private bool $enabled = false;
    private string $storageDir;
    private int $maxFiles;

    public function __construct(string $storageDir, int $maxFiles = null)
    {
        $this->storageDir = $storageDir;
        $this->enabled = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        $this->maxFiles = $maxFiles ?? (int)($_ENV['DEBUG_MAX_FILES'] ?? 25);
        
        // Create storage directory if it doesn't exist
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
    }

    public function addCollector(DataCollectorInterface $collector): void
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    public function getCollector(string $name): ?DataCollectorInterface
    {
        return $this->collectors[$name] ?? null;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function collect(string $requestId): array
    {
        if (!$this->enabled) {
            return [];
        }

        $data = [
            'request_id' => $requestId,
            'timestamp' => (int)microtime(true),
            'collectors' => []
        ];

        foreach ($this->collectors as $collector) {
            $data['collectors'][$collector->getName()] = $collector->collect();
        }

        // Store for later viewing
        $this->store($requestId, $data);
        
        // Cleanup old files
        $this->cleanupOldFiles();

        return $data;
    }

    private function store(string $requestId, array $data): void
    {
        $file = $this->storageDir . '/' . $requestId . '.json';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function cleanupOldFiles(): void
    {
        $files = glob($this->storageDir . '/*.json');
        
        if (count($files) <= $this->maxFiles) {
            return;
        }

        // Sort by modification time (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) <=> filemtime($b);
        });

        // Remove oldest files to keep only maxFiles
        $filesToRemove = array_slice($files, 0, count($files) - $this->maxFiles);
        
        foreach ($filesToRemove as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function getStoredProfiles(int $limit = 50): array
    {
        $files = glob($this->storageDir . '/*.json');
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $profiles = [];
        $files = array_slice($files, 0, $limit);

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $profiles[] = [
                    'id' => $data['request_id'],
                    'timestamp' => $data['timestamp'],
                    'date' => date('Y-m-d H:i:s', (int)$data['timestamp']),
                    'collectors' => array_keys($data['collectors']),
                    'file' => basename($file),
                ];
            }
        }

        return $profiles;
    }

    public function getProfile(string $requestId): ?array
    {
        $file = $this->storageDir . '/' . $requestId . '.json';
        
        if (!file_exists($file)) {
            return null;
        }

        return json_decode(file_get_contents($file), true);
    }
}