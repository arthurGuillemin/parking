<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class TimeRange
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    public function __construct(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        if ($end <= $start) {
            throw new InvalidArgumentException('End date must be strictly after start date.');
        }

        $this->start = $start;
        $this->end   = $end;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    
    public function contains(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->start && $instant < $this->end;
    }

    public function durationInMinutes(): int
    {
        $seconds = $this->end->getTimestamp() - $this->start->getTimestamp();

        return (int) floor($seconds / 60);
    }
}
