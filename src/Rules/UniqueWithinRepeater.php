<?php

namespace DigitalNode\Larafields\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueWithinRepeater implements Rule
{
    private array $repeaterData;

    private string $fieldName;

    private int $currentIndex;

    private string $message;

    public function __construct(array $repeaterData, string $fieldName, int $currentIndex, string $message = 'This value already exists.')
    {
        $this->repeaterData = $repeaterData;
        $this->fieldName = $fieldName;
        $this->currentIndex = $currentIndex;
        $this->message = $message;
    }

    public function passes($attribute, $value)
    {
        // Skip validation for empty values (let required rule handle that)
        if (empty($value)) {
            return true;
        }

        // Case-insensitive uniqueness check with trimming
        $normalizedValue = strtolower(trim($value));

        foreach ($this->repeaterData as $index => $row) {
            if ($index !== $this->currentIndex) {
                $existingValue = strtolower(trim($row[$this->fieldName] ?? ''));
                if ($existingValue === $normalizedValue && ! empty($existingValue)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
