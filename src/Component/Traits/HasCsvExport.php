<?php

namespace DigitalNode\Larafields\Component\Traits;

use Illuminate\Support\Facades\Response;

trait HasCsvExport
{
    /**
     * Export form data as CSV
     */
    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $this->generateCsvFilename();
        $flattenedData = $this->flattenFormData();

        return Response::streamDownload(function () use ($flattenedData) {
            $handle = fopen('php://output', 'w');
            
            // Write CSV headers
            if (!empty($flattenedData)) {
                fputcsv($handle, array_keys($flattenedData[0]));
                
                // Write data rows
                foreach ($flattenedData as $row) {
                    fputcsv($handle, $row);
                }
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate CSV filename based on form context
     */
    private function generateCsvFilename(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $formName = $this->getFormName();
        
        return "{$formName}_export_{$timestamp}.csv";
    }

    /**
     * Get form name for filename
     */
    private function getFormName(): string
    {
        // Try to get form name from schema
        if (!empty($this->availablePropertiesSchema)) {
            $firstField = reset($this->availablePropertiesSchema);
            if (isset($firstField['label'])) {
                return $this->sanitizeFilename($firstField['label']);
            }
        }

        // Fallback to object type and name
        $name = $this->groupObjectType;
        if ($this->groupObjectName) {
            $name .= '_' . $this->groupObjectName;
        }

        return $this->sanitizeFilename($name);
    }

    /**
     * Sanitize filename for CSV export
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and replace spaces with underscores
        $filename = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = trim($filename, '_');
        
        return strtolower($filename) ?: 'form_export';
    }

    /**
     * Flatten form data for CSV export
     */
    private function flattenFormData(): array
    {
        $flattenedRows = [];
        $maxRepeaterRows = $this->getMaxRepeaterRows();

        // If no repeater fields, create a single row
        if ($maxRepeaterRows === 0) {
            $flattenedRows[] = $this->flattenSingleRow();
            return $flattenedRows;
        }

        // Create rows for each repeater index
        for ($i = 0; $i < $maxRepeaterRows; $i++) {
            $flattenedRows[] = $this->flattenRowAtIndex($i);
        }

        return $flattenedRows;
    }

    /**
     * Get the maximum number of rows across all repeater fields
     */
    private function getMaxRepeaterRows(): int
    {
        $maxRows = 0;
        
        foreach ($this->availablePropertiesSchema as $field) {
            if ($field['type'] === 'repeater') {
                $fieldData = $this->availablePropertiesData[$field['name']] ?? [];
                $maxRows = max($maxRows, count($fieldData));
            }
        }

        return $maxRows;
    }

    /**
     * Flatten a single row (for forms without repeaters)
     */
    private function flattenSingleRow(): array
    {
        $row = [];
        
        foreach ($this->availablePropertiesSchema as $field) {
            if ($field['type'] === 'repeater') {
                // Handle repeater fields
                $row = array_merge($row, $this->flattenRepeaterField($field, 0));
            } else {
                // Handle regular fields
                $value = $this->availablePropertiesData[$field['name']] ?? '';
                $row[$field['label']] = $this->formatFieldValue($value);
            }
        }

        return $row;
    }

    /**
     * Flatten a row at a specific index
     */
    private function flattenRowAtIndex(int $index): array
    {
        $row = [];
        
        foreach ($this->availablePropertiesSchema as $field) {
            if ($field['type'] === 'repeater') {
                // Handle repeater fields
                $row = array_merge($row, $this->flattenRepeaterField($field, $index));
            } else {
                // Handle regular fields (same value for all rows)
                $value = $this->availablePropertiesData[$field['name']] ?? '';
                $row[$field['label']] = $this->formatFieldValue($value);
            }
        }

        return $row;
    }

    /**
     * Flatten a repeater field at a specific index
     */
    private function flattenRepeaterField(array $field, int $index): array
    {
        $row = [];
        $fieldData = $this->availablePropertiesData[$field['name']] ?? [];
        $rowData = $fieldData[$index] ?? [];

        foreach ($field['subfields'] as $subfield) {
            if ($subfield['type'] === 'repeater') {
                // Handle nested repeaters
                $row = array_merge($row, $this->flattenNestedRepeater($subfield, $rowData, $index));
            } else {
                // Handle regular subfields
                $columnName = $field['label'] . ' - ' . $subfield['label'];
                $value = $rowData[$subfield['name']] ?? '';
                $row[$columnName] = $this->formatFieldValue($value);
            }
        }

        return $row;
    }

    /**
     * Flatten nested repeater fields
     */
    private function flattenNestedRepeater(array $subfield, array $parentRowData, int $parentIndex): array
    {
        $row = [];
        $nestedData = $parentRowData[$subfield['name']] ?? [];

        // For nested repeaters, we'll concatenate all rows into a single cell
        // or create separate columns for each nested row (configurable)
        $nestedValues = [];
        
        foreach ($nestedData as $nestedIndex => $nestedRow) {
            $nestedRowValues = [];
            
            foreach ($subfield['subfields'] as $nestedSubfield) {
                $value = $nestedRow[$nestedSubfield['name']] ?? '';
                $nestedRowValues[] = $nestedSubfield['label'] . ': ' . $this->formatFieldValue($value);
            }
            
            $nestedValues[] = implode('; ', $nestedRowValues);
        }

        $columnName = $subfield['label'] . ' (Nested)';
        $row[$columnName] = implode(' | ', $nestedValues);

        return $row;
    }

    /**
     * Format field value for CSV export
     */
    private function formatFieldValue($value): string
    {
        if (is_array($value)) {
            // Handle empty arrays - convert to empty string
            if (empty($value)) {
                return '';
            }
            
            // Handle array values (multiselect, etc.)
            return implode(', ', array_map(function ($item) {
                if (is_string($item) && $this->isJson($item)) {
                    $decoded = json_decode($item, true);
                    return $decoded['label'] ?? $item;
                }
                return is_array($item) ? json_encode($item) : (string) $item;
            }, $value));
        }

        if (is_string($value) && $this->isJson($value)) {
            // Handle JSON values (select fields with objects)
            $decoded = json_decode($value, true);
            
            // Check if decoded value is an empty array
            if (is_array($decoded) && empty($decoded)) {
                return '';
            }
            
            return $decoded['label'] ?? $value;
        }

        // Handle string representation of empty arrays
        if ($value === '[]') {
            return '';
        }

        return (string) $value;
    }

    /**
     * Check if a string is valid JSON
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
