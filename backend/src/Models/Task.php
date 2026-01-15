<?php

namespace Models;

use \DTOs\DTO;
use Exception;

use function PHPUnit\Framework\throwException;

class Task extends Model {

    /** @var int */
    public ?int $id = 0;

    /** @var string */
    public string $title = '';

    /** @var string|null */
    public ?string $description = '';

    /** @var 'pending'|'completed' */
    public string $status = 'pending';

    /** @var string|null */
    public ?string $due_date = '';

    /** @var string|null */
    public ?string $created_at = '';

    /** @var string|null */
    public ?string $updated_at = '';

    protected function table(): string
    {
        return 'tasks';
    }

    protected function fillable(): array
    {
        return [
            'title',
            'description',
            'status',
            'due_date',
        ];
    }
}
