<?php

namespace App\Support;

use ZipArchive;

class XlsxWriter
{
    private const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public static function mime(): string
    {
        return self::MIME;
    }

    public static function create(string $sheetName, array $columns, array $rows): string
    {
        $zip = new ZipArchive();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::worksheet($columns, $rows));

        $zip->close();

        $content = file_get_contents($path);
        unlink($path);

        return $content;
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . htmlspecialchars($sheetName) . '" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDDEBF7"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private static function worksheet(array $columns, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        $rows = array_merge([$columns], array_values($rows));
        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="' . ($rowIndex + 1) . '">';
            foreach (array_values($row) as $colIndex => $value) {
                $ref = self::columnRef($colIndex) . ($rowIndex + 1);
                $style = $rowIndex === 0 ? ' s="0"' : ' s="1"';

                if (is_int($value) || is_float($value)) {
                    $xml .= '<c r="' . $ref . '"' . $style . '><v>' . $value . '</v></c>';
                } else {
                    $text = htmlspecialchars((string) $value);
                    $xml .= '<c r="' . $ref . '"' . $style . ' t="inlineStr"><is><t>' . $text . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private static function columnRef(int $index): string
    {
        $ref = '';
        while ($index >= 0) {
            $ref = chr(65 + ($index % 26)) . $ref;
            $index = (int) ($index / 26) - 1;
        }
        return $ref;
    }
}