<?php

declare(strict_types=1);

namespace DTOs;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

readonly class UpdateTaskDTO
{
    public function __construct(
        public ?string $title,
        public ?string $description,
        public ?string $status,
        public ?string $due_date
    ) {
    }

    /**
     * @throws NestedValidationException
     */
    public static function fromObject(\stdClass $data): self
    {
        $taskValidator = v::attribute('title', v::stringType()->notEmpty(), false)
            ->attribute('description', v::stringType(), false)
            ->attribute('due_date', v::dateTime('Y-m-d H:i:s'), false)
            ->attribute('status', v::in(['pending', 'in_progress', 'completed']), false);

        $taskValidator->assert($data);

        return new self(
            title: $data->title ?? null,
            description: $data->description ?? null,
            status: $data->status ?? null,
            due_date: $data->due_date ?? null
        );
    }

    /**
     * Converte o DTO para um array, removendo chaves com valores nulos.
     * Útil para garantir que apenas os campos fornecidos na requisição sejam atualizados.
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
