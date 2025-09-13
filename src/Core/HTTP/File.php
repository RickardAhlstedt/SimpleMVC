<?php

namespace SimpleMVC\Core\HTTP;

class File
{
    private string $name;
    private string $type;
    private int $size;
    private string $tmpName;
    private int $error;

    public function __construct(array $file)
    {
        $this->name = $file['name'] ?? '';
        $this->type = $file['type'] ?? '';
        $this->size = $file['size'] ?? 0;
        $this->tmpName = $file['tmp_name'] ?? '';
        $this->error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function moveTo(string $targetDir): ?string
    {
        if (!$this->isValid()) {
            return null;
        }
        $targetPath = rtrim($targetDir, '/') . '/' . basename($this->name);
        if (move_uploaded_file($this->tmpName, $targetPath)) {
            return $targetPath;
        }
        return null;
    }
}
