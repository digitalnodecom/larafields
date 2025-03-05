<?php

namespace DigitalNode\Larafields\DTOs;

class GetFormDTO
{
    public ?string $objectId;
    public ?string $objectName;
    public ?string $fieldKey;

    public function __construct(?string $objectId = null, ?string $objectName = null, ?string $fieldKey = null)
    {
        $this->objectId = $objectId;
        $this->objectName = $objectName;
        $this->fieldKey = $fieldKey;
    }

    public static function fromRequest($request): self
    {
        return new self(
            $request->input('object_id'),
            $request->input('object_name'),
            $request->input('field_key')
        );
    }

    public function isValid(): bool
    {
        // At least one of these parameters must be provided
        return !is_null($this->objectId) || !is_null($this->objectName) || !is_null($this->fieldKey);
    }
}
