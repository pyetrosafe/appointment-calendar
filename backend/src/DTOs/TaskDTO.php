<?php

declare(strict_types=1);

namespace DTOs;

readonly class TaskDTO
{
	public function __construct(
		public int $id,
		public string $title,
		public ?string $description,
		public string $status,
		public ?string $due_date,
	) {
	}
}
