<?php

return [
    'exports' => [
        'chunk_size' => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
            'output_encoding' => '',
            'test_auto_detect' => true,
        ],
        'properties' => [
            'creator' => 'PageTurner',
            'lastModifiedBy' => 'PageTurner',
            'title' => 'Book Export',
            'description' => 'Exported Books Data',
            'subject' => 'Books',
            'keywords' => 'books,export,spreadsheet',
            'category' => 'Data',
            'manager' => 'PageTurner Admin',
            'company' => 'PageTurner',
        ],
    ],

    'imports' => [
        'heading_row' => [
            'formatter' => 'slug',
        ],
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],
        'chunk_size' => 1000,
    ],

    'extension_detector' => [
        'xlsx' => 'Xlsx',
        'xlsm' => 'Xlsm',
        'xltx' => 'Xltx',
        'xltm' => 'Xltm',
        'xls' => 'Xls',
        'xlt' => 'Xlt',
        'ods' => 'Ods',
        'ots' => 'Ots',
        'slk' => 'Slk',
        'xml' => 'Xml',
        'gnumeric' => 'Gnumeric',
        'htm' => 'Html',
        'html' => 'Html',
        'csv' => 'Csv',
        'tsv' => 'Csv',
        'pdf' => 'Dompdf',
    ],

    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        'driver' => 'memory',
        'batch' => [
            'memory_limit' => 60000,
        ],
    ],

    'temporary_files' => [
        'local_path' => storage_path('framework/cache/laravel-excel'),
        'remote_disk' => null,
        'remote_prefix' => null,
        'force_resync_remote' => null,
    ],
];