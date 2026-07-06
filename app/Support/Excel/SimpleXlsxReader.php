<?php

namespace App\Support\Excel;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SimpleXlsxReader
{
    private const SPREADSHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const OFFICE_REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @return array<int, array{sheet: string, row: int, values: array<string, string>}>
     */
    public function read(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->sharedStrings($zip);
            $sheets = $this->sheets($zip);
            $rows = [];

            foreach ($sheets as $sheet) {
                $rows = array_merge($rows, $this->sheetRows($zip, $sheet, $sharedStrings));
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $this->xml($zip, 'xl/sharedStrings.xml', false);

        if ($xml === null) {
            return [];
        }

        $xml->registerXPathNamespace('m', self::SPREADSHEET_NS);
        $strings = [];

        foreach ($xml->xpath('//m:si') ?: [] as $item) {
            $texts = [];
            $item->registerXPathNamespace('m', self::SPREADSHEET_NS);

            foreach ($item->xpath('.//m:t') ?: [] as $text) {
                $texts[] = (string) $text;
            }

            $strings[] = implode('', $texts);
        }

        return $strings;
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    private function sheets(ZipArchive $zip): array
    {
        $workbook = $this->xml($zip, 'xl/workbook.xml');
        $rels = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
        $relationships = [];

        foreach ($rels->children(self::REL_NS)->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $relationships[(string) $attributes['Id']] = (string) $attributes['Target'];
        }

        $sheets = [];
        $workbookSheets = $workbook->children(self::SPREADSHEET_NS)->sheets;

        foreach ($workbookSheets->children(self::SPREADSHEET_NS)->sheet as $sheet) {
            $attributes = $sheet->attributes();
            $relAttributes = $sheet->attributes(self::OFFICE_REL_NS);
            $target = $relationships[(string) $relAttributes['id']] ?? null;

            if (! $target) {
                continue;
            }

            $sheets[] = [
                'name' => (string) $attributes['name'],
                'path' => $this->workbookRelationshipPath($target),
            ];
        }

        return $sheets;
    }

    /**
     * @param array{name: string, path: string} $sheet
     * @param array<int, string> $sharedStrings
     * @return array<int, array{sheet: string, row: int, values: array<string, string>}>
     */
    private function sheetRows(ZipArchive $zip, array $sheet, array $sharedStrings): array
    {
        $xml = $this->xml($zip, $sheet['path']);
        $xml->registerXPathNamespace('m', self::SPREADSHEET_NS);
        $headers = [];
        $rows = [];

        foreach ($xml->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $rowNumber = (int) ($row->attributes()['r'] ?? 0);
            $values = $this->rowValues($row, $sharedStrings);

            if ($values === []) {
                continue;
            }

            if ($headers === []) {
                if (! in_array('nama_produk', array_map($this->normalizeHeader(...), $values), true)) {
                    continue;
                }

                foreach ($values as $column => $value) {
                    $headers[$column] = $this->normalizeHeader($value);
                }

                continue;
            }

            $payload = [];

            foreach ($headers as $column => $header) {
                if ($header === '') {
                    continue;
                }

                $payload[$header] = trim((string) ($values[$column] ?? ''));
            }

            if (array_filter($payload, fn (string $value): bool => $value !== '') === []) {
                continue;
            }

            $rows[] = [
                'sheet' => $sheet['name'],
                'row' => $rowNumber,
                'values' => $payload,
            ];
        }

        return $rows;
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<string, string>
     */
    private function rowValues(SimpleXMLElement $row, array $sharedStrings): array
    {
        $values = [];

        foreach ($row->children(self::SPREADSHEET_NS)->c as $cell) {
            $attributes = $cell->attributes();
            $reference = (string) ($attributes['r'] ?? '');
            $column = preg_replace('/\d+/', '', $reference) ?: '';

            if ($column === '') {
                continue;
            }

            $values[$column] = $this->cellValue($cell, $sharedStrings);
        }

        return $values;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $attributes = $cell->attributes();
        $type = (string) ($attributes['t'] ?? '');

        if ($type === 's') {
            $index = (int) $this->childText($cell, 'v');

            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            $texts = [];
            $cell->registerXPathNamespace('m', self::SPREADSHEET_NS);

            foreach ($cell->xpath('.//m:t') ?: [] as $text) {
                $texts[] = (string) $text;
            }

            return implode('', $texts);
        }

        return $this->childText($cell, 'v');
    }

    private function childText(SimpleXMLElement $node, string $name): string
    {
        return (string) ($node->children(self::SPREADSHEET_NS)->{$name} ?? '');
    }

    private function normalizeHeader(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?: '';

        return trim($normalized, '_');
    }

    private function workbookRelationshipPath(string $target): string
    {
        $target = ltrim(str_replace('\\', '/', trim($target)), '/');

        return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
    }

    private function xml(ZipArchive $zip, string $path, bool $required = true): ?SimpleXMLElement
    {
        $content = $zip->getFromName($path);

        if ($content === false) {
            if ($required) {
                throw new RuntimeException("Bagian Excel {$path} tidak ditemukan.");
            }

            return null;
        }

        $xml = simplexml_load_string($content);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException("Bagian Excel {$path} tidak valid.");
        }

        return $xml;
    }
}
