<?php

namespace DTOs;

use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

abstract readonly class DTO
{
    public function all(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    /**
     * @throws NestedValidationException
     */
    public static function fromObject(object $object): static
    {
        return new static(...get_object_vars($object));
    }

    public function validate()
    {
        return $this->rules()->assert($this);
    }

    abstract public function rules(): Validatable;
}