<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Programme Booklet - {{ $programme->code }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
        }
        .booklet-content {
            width: 100%;
        }
        .booklet-cover {
            border-bottom: 1px solid #9ca3af;
            margin-bottom: 14px;
            padding-bottom: 12px;
        }
        .booklet-kicker {
            color: #92400e;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .booklet-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .booklet-subtitle {
            color: #4b5563;
            font-size: 10px;
            margin: 4px 0 0;
        }
        .booklet-meta-table,
        .booklet-table {
            width: 100%;
            border-collapse: collapse;
        }
        .booklet-meta-table {
            margin-top: 12px;
        }
        .booklet-meta-table th,
        .booklet-meta-table td,
        .booklet-table th,
        .booklet-table td {
            border: 1px solid #9ca3af;
            padding: 5px 6px;
            vertical-align: top;
        }
        .booklet-meta-table th {
            background: #f3f4f6;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .booklet-stats {
            width: 100%;
            margin-top: 12px;
        }
        .booklet-stat {
            display: inline-block;
            width: 23%;
            margin-right: 1%;
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
            padding: 8px;
            box-sizing: border-box;
            background: #fffaf0;
        }
        .booklet-stat-label {
            display: block;
            color: #92400e;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .booklet-stat strong {
            font-size: 16px;
        }
        .booklet-section {
            margin-top: 16px;
        }
        .booklet-section-head {
            margin-bottom: 6px;
        }
        .booklet-section-head h2 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }
        .booklet-section-head p {
            color: #4b5563;
            font-size: 9px;
            margin: 3px 0 0;
        }
        .booklet-table thead th {
            background: #e5e7eb;
            font-size: 8px;
            text-transform: uppercase;
        }
        .booklet-table tbody td,
        .booklet-table tfoot th {
            font-size: 8px;
        }
        .booklet-table tfoot th {
            background: #f3f4f6;
        }
        .booklet-empty {
            border: 1px dashed #9ca3af;
            padding: 8px;
            color: #4b5563;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .keep-together {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    @include('cdc.programmes.partials.booklet-content')
</body>
</html>
