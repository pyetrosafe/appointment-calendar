<?php

declare(strict_types=1);

namespace DTOs;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

readonly class TaskDTO extends DTO
{
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public ?string $description = '',
        public ?string $status = 'pending',
        public ?string $due_date = '',
        public ?string $created_at = '',
        public ?string $updated_at = '',
    ) {
    }

    public function inputRules(): Validatable
    {
        $rules = array(
            'id' => v::integerType()
        );

        return $this->rules($rules);
    }

    public function rules(array $extraRules = []): Validatable
    {
        $rules = v::attribute('title', v::stringType()->notEmpty())
            ->attribute('description', v::stringType(), false)
            ->attribute('status', v::stringType()->in(['pending', 'in_progress', 'completed']), false)
            ->attribute('due_date', v::dateTime('Y-m-d H:i:s'), false);

        foreach($extraRules as $k => $v) {
            $rules->attribute($k, $v);
        }

        return $rules;
    }
}
