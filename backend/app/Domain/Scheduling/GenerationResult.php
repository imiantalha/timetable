<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use Illuminate\Support\Collection;

final readonly class GenerationResult
{
    public function __construct(
        public Collection $entries,
        public Collection $unplaced,
        public int $score,
    ) {}
}
