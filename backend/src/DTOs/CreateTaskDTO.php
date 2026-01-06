<?php

declare(strict_types=1);

namespace DTOs;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

readonly class CreateTaskDTO extends DTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $due_date,
    ) {
    }

    /**
     * @throws NestedValidationException
     */
    /* public static function fromObject(\stdClass $data): self
    {
        $taskValidator = v::attribute('title', v::stringType()->notEmpty())
            ->attribute('description', v::stringType(), false)
            ->attribute('due_date', v::dateTime('Y-m-d H:i:s'), false);

        $taskValidator->assert($data);

        return new self(
            title: $data->title,
            description: $data->description ?? null,
            due_date: $data->due_date ?? null,
        );
    } */

    public function rules(): Validatable
    {
        return v::attribute('title', v::stringType()->notEmpty())
            ->attribute('description', v::stringType()->nullable())
            ->attribute('due_date', v::dateTime('Y-m-d H:i:s')->nullable());
    }
}
