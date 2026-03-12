<?php
function getDocxText($filename) {
    if (!file_exists($filename)) return "File not found: $filename";
    $zip = new ZipArchive();
    if ($zip->open($filename) === true) {
        if (($index = $zip->locateName('word/document.xml')) !== false) {
            $data = $zip->getFromIndex($index);
            $zip->close();
            
            preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/', $data, $matches);
            return implode(" ", $matches[1]);
        }
        $zip->close();
    }
    return "Could not open $filename";
}

$files = [
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A0_Index.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A2_Struct_001.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A3_Lvl_011.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_CurI_2021_A4_L06_227.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A5_Anex_249.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A6_Cert_257.docx'
];

$output = "";
foreach ($files as $f) {
    $output .= "\n\n--- $f ---\n";
    $output .= getDocxText($f);
}

file_put_contents('c:/xampp/htdocs/syllabus/laravel/tmp/docx_output.txt', $output);
