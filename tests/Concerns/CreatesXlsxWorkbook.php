<?php

namespace Tests\Concerns;

use ZipArchive;

trait CreatesXlsxWorkbook
{
    /**
     * @param array<int, array<int, string|int|float|null>> $rows
     */
    protected function createXlsxWorkbook(array $rows, string $sheetName = 'Oktober 2025'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'snack_eclat_xlsx_');
        unlink($path);
        $path .= '.xlsx';

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $sharedStrings = [];
        $cellRows = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = [];

            foreach ($row as $columnIndex => $value) {
                $column = $this->excelColumn($columnIndex);
                $stringValue = (string) $value;
                $sharedStrings[$stringValue] ??= count($sharedStrings);
                $cells[] = '<c r="'.$column.$rowNumber.'" t="s"><v>'.$sharedStrings[$stringValue].'</v></c>';
            }

            $cellRows[] = '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$this->xml($sheetName).'" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.implode('', $cellRows).'</sheetData></worksheet>');
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">'.implode('', array_map(fn (string $value): string => '<si><t>'.$this->xml($value).'</t></si>', array_keys($sharedStrings))).'</sst>');
        $zip->close();

        return $path;
    }

    private function excelColumn(int $index): string
    {
        return chr(65 + $index);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
