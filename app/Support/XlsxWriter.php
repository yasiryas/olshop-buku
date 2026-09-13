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
        return self::createMulti([
            ['name' => $sheetName, 'columns' => $columns, 'rows' => $rows],
        ]);
    }

    /**
     * @param array<int, array{name: string, title?: string, columns: array, rows: array}> $sheets
     */
    public static function createMulti(array $sheets): string
    {
        $sheets = array_values($sheets);
        if (empty($sheets)) {
            throw new \RuntimeException('Tidak ada sheet untuk diekspor.');
        }

        $zip = new ZipArchive();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file Excel.');
        }

        $count = count($sheets);
        $zip->addFromString('[Content_Types].xml', self::contentTypes($count));
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook(array_column($sheets, 'name')));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels($count));
        $zip->addFromString('xl/styles.xml', self::styles());

        foreach ($sheets as $index => $sheet) {
            $zip->addFromString(
                'xl/worksheets/sheet' . ($index + 1) . '.xml',
                self::worksheet($sheet['columns'], $sheet['rows'], $sheet['title'] ?? null)
            );
        }

        $zip->close();

        $content = file_get_contents($path);
        unlink($path);

        return $content;
    }

    private static function contentTypes(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $overrides .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . $overrides
            . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(array $sheetNames): string
    {
        $sheets = '';
        foreach (array_values($sheetNames) as $index => $name) {
            $sheets .= '<sheet name="' . htmlspecialchars($name) . '" sheetId="' . ($index + 1) . '" r:id="rId' . ($index + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets></workbook>';
    }

    private static function workbookRels(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . ($sheetCount + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
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

    private static function worksheet(array $columns, array $rows, ?string $title = null): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        $rowIndex = 0;

        if ($title !== null) {
            $rowIndex = 1;
            $xml .= self::cellRow($rowIndex, [$title], '0');
            $rowIndex = 2;
        }

        $xml .= self::cellRow($rowIndex, $columns, '0');
        $rowIndex++;

        foreach (array_values($rows) as $r => $row) {
            $xml .= self::cellRow($rowIndex + $r, $row, '1');
        }

        return $xml . '</sheetData></worksheet>';
    }

    private static function cellRow(int $rowIndex, array $cells, string $style): string
    {
        $xml = '<row r="' . $rowIndex . '">';
        foreach (array_values($cells) as $colIndex => $value) {
            $ref = self::columnRef($colIndex) . $rowIndex;

            if (is_int($value) || is_float($value)) {
                $xml .= '<c r="' . $ref . '" s="' . $style . '"><v>' . $value . '</v></c>';
            } else {
                $text = htmlspecialchars((string) $value);
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t>' . $text . '</t></is></c>';
            }
        }
        return $xml . '</row>';
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