<?php

namespace DigitalNode\Larafields\DTOs;

class UpdateFormDTO
{
    public string $fieldKey;

    public string $fieldValue;

    public string $objectId;

    public string $objectName;

    public string $objectType;

    public function __construct(string $fieldKey, string $fieldValue, string $objectId, string $objectName, string $objectType)
    {
        $this->fieldKey = $fieldKey;
        $this->fieldValue = $fieldValue;
        $this->objectId = $objectId;
        $this->objectName = $objectName;
        $this->objectType = $objectType;
    }

    public static function fromRequest($request): self
    {
        return new self(
            $request->input('field_key'),
            $request->input('field_value'),
            $request->input('object_id'),
            $request->input('object_name'),
            $request->input('object_type'),
        );
    }
}
