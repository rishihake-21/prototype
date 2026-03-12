<?php
function getDocxText($filename) {
    if (!file_exists($filename)) return "File not found: $filename";
    $zip = new ZipArchive();
    if ($zip->open($filename) === true) {
        if (($index = $zip->locateName('word/document.xml')) !== false) {
            $data = $zip->getFromIndex($index);
            $zip->close();
            $xml = new DOMDocument();
            $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            return strip_tags($xml->saveXML());
        }
        $zip->close();
    }
    return "Could not open $filename";
}

$files = [
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A0_Index.docx',
    'c:/xampp/htdocs/syllabus/laravel/tests/sample_curricula/05_IF_Curi_2021_A2_Struct_001.docx'
];

foreach ($files as $f) {
    echo "\n\n--- $f ---\n";
    echo getDocxText($f);
}
