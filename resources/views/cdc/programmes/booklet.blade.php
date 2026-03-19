@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <style>
        .booklet-page {
            background: linear-gradient(180deg, #f8f5ec 0%, #ffffff 18%);
            border: 1px solid #d7d2c5;
            box-shadow: 0 18px 40px rgba(30, 41, 59, 0.08);
            border-radius: 24px;
            overflow: hidden;
        }
        .booklet-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }
        .booklet-toolbar h1 {
            font-size: 1.85rem;
            line-height: 1.1;
            font-weight: 700;
            color: #14213d;
            margin: 0;
        }
        .booklet-toolbar p {
            color: #6b7280;
            margin: 6px 0 0;
            font-size: 0.95rem;
        }
        .booklet-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .booklet-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
        }
        .booklet-btn-primary {
            background: #9a3412;
            color: #fff;
        }
        .booklet-btn-secondary {
            background: #fff;
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .booklet-shell {
            padding: 32px;
        }
        .booklet-content {
            color: #1f2937;
        }
        .booklet-cover {
            border-bottom: 2px solid #d6d3d1;
            margin-bottom: 24px;
            padding-bottom: 20px;
        }
        .booklet-kicker {
            color: #92400e;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .booklet-title {
            color: #111827;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.12;
            margin: 0;
        }
        .booklet-subtitle {
            color: #6b7280;
            font-size: 1rem;
            margin: 8px 0 0;
        }
        .booklet-meta-table,
        .booklet-table {
            width: 100%;
            border-collapse: collapse;
        }
        .booklet-meta-table {
            margin-top: 18px;
        }
        .booklet-meta-table th,
        .booklet-meta-table td {
            border: 1px solid #d6d3d1;
            padding: 10px 12px;
            text-align: left;
        }
        .booklet-meta-table th {
            width: 18%;
            background: #f5f5f4;
            color: #44403c;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .booklet-stats {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            margin-top: 18px;
        }
        .booklet-stat {
            background: #fffaf0;
            border: 1px solid #f2d7b5;
            border-radius: 16px;
            padding: 14px 16px;
        }
        .booklet-stat-label {
            display: block;
            color: #9a3412;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .booklet-stat strong {
            font-size: 1.5rem;
            color: #111827;
        }
        .booklet-section {
            margin-top: 28px;
        }
        .booklet-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }
        .booklet-section-head h2 {
            margin: 0;
            color: #14213d;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .booklet-section-head p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .booklet-table th,
        .booklet-table td {
            border: 1px solid #d6d3d1;
            padding: 8px 10px;
            vertical-align: top;
        }
        .booklet-table thead th {
            background: #efe7d6;
            color: #1f2937;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .booklet-table tbody td,
        .booklet-table tfoot th {
            font-size: 0.88rem;
        }
        .booklet-table tbody tr:nth-child(even) {
            background: #fcfcfb;
        }
        .booklet-table-compact th,
        .booklet-table-compact td {
            padding: 6px 7px;
        }
        .booklet-table-compact thead th,
        .booklet-table-compact tbody td,
        .booklet-table-compact tfoot th {
            font-size: 0.82rem;
        }
        .booklet-table-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }
        .booklet-table-wrap .booklet-table {
            min-width: max-content;
        }
        .glance-table .glance-credits-col,
        .glance-table .glance-marks-col {
            min-width: 92px;
            white-space: nowrap;
        }
        .glance-table .glance-marks-col {
            min-width: 88px;
        }
        .booklet-table tfoot th {
            background: #f5f5f4;
            color: #111827;
        }
        .booklet-empty {
            border: 1px dashed #d6d3d1;
            border-radius: 14px;
            padding: 16px;
            color: #6b7280;
            background: #fff;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        @media (max-width: 900px) {
            .booklet-shell {
                padding: 20px;
            }
            .booklet-title {
                font-size: 1.7rem;
            }
            .booklet-table-wrap {
                -webkit-overflow-scrolling: touch;
            }
            .booklet-table {
                white-space: nowrap;
            }
            .glance-table .glance-credits-col,
            .glance-table .glance-marks-col {
                position: sticky;
                right: 0;
                background: inherit;
                z-index: 1;
            }
            .glance-table .glance-credits-col {
                right: 88px;
                box-shadow: -1px 0 0 #d6d3d1;
            }
            .glance-table .glance-marks-col {
                box-shadow: -1px 0 0 #d6d3d1;
            }
            .glance-table thead .glance-credits-col,
            .glance-table thead .glance-marks-col {
                background: #efe7d6;
                z-index: 2;
            }
        }
    </style>

    <div class="booklet-toolbar">
        <div>
            <h1>Booklet Preview</h1>
            <p>{{ $programme->name }} · {{ $programme->code }} · {{ $programme->academic_year }}</p>
        </div>
        <div class="booklet-actions">
            <a href="{{ route('cdc.programmes.show', $programme) }}" class="booklet-btn booklet-btn-secondary">Back to Programme</a>
            <a href="{{ route('cdc.programmes.booklet.download-pdf', $programme) }}" class="booklet-btn booklet-btn-primary">Download PDF</a>
        </div>
    </div>

    <div class="booklet-page">
        <div class="booklet-shell">
            @include('cdc.programmes.partials.booklet-content')
        </div>
    </div>
</div>
@endsection
