<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $result->student?->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ink: #1a1a2e;
            --ink-light: #4a4a6a;
            --border: #c8c8d8;
            --accent: #1a3a6e;
            --accent-light: #e8eef8;
            --pass: #166534;
            --fail: #991b1b;
            --surface: #f8f8fc;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 11pt;
            color: var(--ink);
            background: #fff;
            line-height: 1.4;
        }

        /* ─── Screen wrapper ─── */
        .page-wrapper {
            max-width: 210mm;
            margin: 20px auto;
            background: #fff;
            border: 1px solid var(--border);
            padding: 0;
        }

        /* ─── No-print controls ─── */
        .no-print {
            display: flex;
            gap: 10px;
            padding: 12px 20px;
            background: var(--accent);
            justify-content: flex-end;
        }
        .no-print a, .no-print button {
            font-family: system-ui, sans-serif;
            font-size: 13px;
            padding: 7px 18px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-print { background: #fff; color: var(--accent); font-weight: 600; }
        .btn-back  { background: transparent; color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.4) !important; }

        /* ─── Report card content ─── */
        .rc {
            padding: 16mm 18mm;
        }

        /* ─── School header ─── */
        .school-header {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px solid var(--accent);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .school-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: contain;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }
        .school-logo-placeholder {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--accent-light);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
            color: var(--accent);
        }
        .school-name {
            font-size: 17pt;
            font-weight: bold;
            color: var(--accent);
            letter-spacing: .3px;
            line-height: 1.2;
        }
        .school-meta {
            font-size: 9pt;
            color: var(--ink-light);
            margin-top: 3px;
        }
        .school-motto {
            font-style: italic;
            font-size: 10pt;
            color: var(--accent);
            margin-top: 2px;
        }
        .report-title-block {
            text-align: center;
            margin-bottom: 14px;
        }
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 5px 0;
            margin: 0 auto;
        }

        /* ─── Info grid ─── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid var(--border);
            margin-bottom: 14px;
        }
        .info-row {
            display: flex;
            border-bottom: 1px solid var(--border);
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            width: 38%;
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: bold;
            color: var(--ink-light);
            background: var(--surface);
            border-right: 1px solid var(--border);
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .info-value {
            flex: 1;
            padding: 5px 8px;
            font-size: 10pt;
            color: var(--ink);
        }
        .info-panel {
            display: flex;
            border-bottom: 1px solid var(--border);
        }
        .info-half {
            flex: 1;
            border-right: 1px solid var(--border);
        }
        .info-half:last-child { border-right: none; }

        /* ─── Subject table ─── */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 3px;
            margin-bottom: 6px;
            margin-top: 14px;
        }
        table.scores {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        table.scores th {
            background: var(--accent);
            color: #fff;
            font-size: 8.5pt;
            font-weight: 600;
            text-align: center;
            padding: 5px 4px;
            letter-spacing: .2px;
        }
        table.scores th.left { text-align: left; padding-left: 8px; }
        table.scores td {
            border: 1px solid var(--border);
            padding: 4px 4px;
            text-align: center;
            font-size: 9.5pt;
        }
        table.scores td.subject-name {
            text-align: left;
            padding-left: 8px;
            font-weight: 500;
        }
        table.scores tr:nth-child(even) td { background: var(--surface); }
        table.scores td.pass  { color: var(--pass); font-weight: bold; }
        table.scores td.fail  { color: var(--fail); font-weight: bold; }
        table.scores td.grade { font-weight: 900; font-size: 11pt; }
        table.scores tfoot td {
            background: var(--accent-light);
            font-weight: bold;
            font-size: 9pt;
        }

        /* ─── Performance summary ─── */
        .perf-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 1px solid var(--border);
            margin-top: 14px;
            margin-bottom: 14px;
        }
        .perf-cell {
            text-align: center;
            padding: 8px 4px;
            border-right: 1px solid var(--border);
        }
        .perf-cell:last-child { border-right: none; }
        .perf-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--ink-light);
            margin-bottom: 3px;
        }
        .perf-value {
            font-size: 14pt;
            font-weight: bold;
            color: var(--accent);
            line-height: 1;
        }
        .perf-value.good  { color: var(--pass); }
        .perf-value.weak  { color: var(--fail); }

        /* ─── Grading key ─── */
        .grade-key {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0;
            border: 1px solid var(--border);
            font-size: 8pt;
            margin-bottom: 14px;
        }
        .grade-key-cell {
            text-align: center;
            padding: 4px 2px;
            border-right: 1px solid var(--border);
        }
        .grade-key-cell:last-child { border-right: none; }
        .grade-key-header {
            background: var(--surface);
            font-weight: bold;
            letter-spacing: .2px;
        }

        /* ─── Comments ─── */
        .comments {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }
        .comment-box {
            border: 1px solid var(--border);
            border-radius: 3px;
        }
        .comment-label {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 4px 8px;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: var(--ink-light);
        }
        .comment-text {
            padding: 7px 8px;
            font-size: 10pt;
            min-height: 44px;
            color: var(--ink);
            font-style: italic;
        }
        .comment-sig {
            border-top: 1px solid var(--border);
            padding: 6px 8px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        .sig-line {
            flex: 1;
            border-bottom: 1px solid var(--ink-light);
            margin-bottom: 2px;
        }
        .sig-label {
            font-size: 8pt;
            color: var(--ink-light);
            white-space: nowrap;
        }

        /* ─── Status strip ─── */
        .status-strip {
            margin-top: 14px;
            border-top: 1px solid var(--border);
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            color: var(--ink-light);
        }

        /* ─── Print styles ─── */
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .page-wrapper { border: none; margin: 0; max-width: 100%; }
            .rc { padding: 10mm 14mm; }
        }

        @page { size: A4 portrait; margin: 0; }
    </style>
</head>
<body>

<div class="page-wrapper">
    {{-- Screen-only toolbar --}}
    <div class="no-print">
        <a href="{{ route('results.show', $result) }}" class="btn-back">← Back</a>
        <button class="btn-print" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="rc">
        {{-- School header --}}
        <div class="school-header">
            @if ($school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="school-logo">
            @else
                <div class="school-logo-placeholder">🏫</div>
            @endif
            <div>
                <div class="school-name">{{ $school->name }}</div>
                @if ($school->address || $school->city)
                    <div class="school-meta">
                        {{ implode(', ', array_filter([$school->address, $school->city, $school->state])) }}
                        @if ($school->phone) · Tel: {{ $school->phone }} @endif
                        @if ($school->email) · {{ $school->email }} @endif
                    </div>
                @endif
                @if ($school->motto)
                    <div class="school-motto">{{ $school->motto }}</div>
                @endif
            </div>
        </div>

        {{-- Report title --}}
        <div class="report-title-block">
            <div class="report-title">
                Terminal Report — {{ $result->term?->name }} &nbsp;·&nbsp; {{ $result->academicYear?->name }}
            </div>
        </div>

        {{-- Student info grid --}}
        <div class="info-grid">
            <div>
                <div class="info-row">
                    <div class="info-label">Student</div>
                    <div class="info-value" style="font-weight:bold;">{{ $result->student?->full_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Student No.</div>
                    <div class="info-value">{{ $result->student?->student_number ?? $result->student?->admission_number ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Class</div>
                    <div class="info-value">{{ $result->schoolClass?->full_name ?? '—' }}</div>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ ucfirst($result->student?->gender ?? '—') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $result->student?->date_of_birth?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Term</div>
                    <div class="info-value">{{ $result->term?->name }} &nbsp; ({{ $result->academicYear?->name }})</div>
                </div>
            </div>
        </div>

        {{-- Subject scores --}}
        <div class="section-title">Academic Performance</div>

        @if ($result->subjectScores->isEmpty())
            <p style="font-size:10pt; color: var(--ink-light); font-style:italic;">No subject scores recorded for this term.</p>
        @else
        <table class="scores">
            <thead>
                <tr>
                    <th class="left" style="width:28%">Subject</th>
                    <th style="width:9%">CA<br><span style="font-weight:400;font-size:7.5pt;">(/40)</span></th>
                    <th style="width:9%">Exam<br><span style="font-weight:400;font-size:7.5pt;">(/60)</span></th>
                    <th style="width:9%">Total<br><span style="font-weight:400;font-size:7.5pt;">(/100)</span></th>
                    <th style="width:8%">Grade</th>
                    <th style="width:9%">Remark</th>
                    <th style="width:9%">Position</th>
                    <th style="width:10%">Class Avg</th>
                    <th style="width:10%">Highest</th>
                    <th style="width:10%">Lowest</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result->subjectScores as $score)
                @php
                    $total = $score->total_score;
                    $cls   = is_null($total) ? '' : ($total >= 50 ? 'pass' : 'fail');
                @endphp
                <tr>
                    <td class="subject-name">{{ $score->subject?->name ?? '—' }}</td>
                    <td>{{ is_null($score->ca_score) ? '—' : number_format($score->ca_score, 1) }}</td>
                    <td>{{ is_null($score->exam_score) ? '—' : number_format($score->exam_score, 1) }}</td>
                    <td class="{{ $cls }}">{{ is_null($total) ? '—' : number_format($total, 1) }}</td>
                    <td class="grade {{ $cls }}">{{ $score->grade ?? '—' }}</td>
                    <td style="font-size:8.5pt; color: var(--ink-light);">{{ $score->remark ?? '—' }}</td>
                    <td>{{ $score->position ?? '—' }}</td>
                    <td style="color: var(--ink-light);">{{ $score->class_average ? number_format($score->class_average, 1) : '—' }}</td>
                    <td style="color: var(--pass);">{{ $score->highest_score ? number_format($score->highest_score, 1) : '—' }}</td>
                    <td style="color: var(--fail);">{{ $score->lowest_score ? number_format($score->lowest_score, 1) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="left" colspan="3" style="text-align:left; padding-left:8px;">
                        Subjects Offered: {{ $result->subjects_offered ?? $result->subjectScores->count() }}
                    </td>
                    <td>{{ $result->total_score ? number_format($result->total_score, 1) : '—' }}</td>
                    <td colspan="6"></td>
                </tr>
            </tfoot>
        </table>
        @endif

        {{-- Performance summary ─── --}}
        @php
            $avg    = $result->average_score;
            $avgCls = $avg !== null ? ($avg >= 50 ? 'good' : 'weak') : '';
        @endphp
        <div class="perf-grid">
            <div class="perf-cell">
                <div class="perf-label">Average Score</div>
                <div class="perf-value {{ $avgCls }}">
                    {{ $avg !== null ? number_format($avg, 1) . '%' : '—' }}
                </div>
            </div>
            <div class="perf-cell">
                <div class="perf-label">Position</div>
                <div class="perf-value">
                    {{ $result->position ? $result->position . ' / ' . $result->class_size : '—' }}
                </div>
            </div>
            <div class="perf-cell">
                <div class="perf-label">Overall Grade</div>
                <div class="perf-value {{ $avgCls }}">
                    {{ $result->overall_grade ?? ($avg !== null ? ($avg >= 80 ? 'A' : ($avg >= 70 ? 'B' : ($avg >= 60 ? 'C' : ($avg >= 50 ? 'D' : 'F')))) : '—') }}
                </div>
            </div>
            <div class="perf-cell">
                <div class="perf-label">Remark</div>
                <div class="perf-value" style="font-size:10pt; color: var(--ink);">
                    {{ $result->overall_remark ?? ($avg !== null ? ($avg >= 80 ? 'Excellent' : ($avg >= 70 ? 'Very Good' : ($avg >= 60 ? 'Good' : ($avg >= 50 ? 'Satisfactory' : 'Needs Improvement')))) : '—') }}
                </div>
            </div>
        </div>

        {{-- Grading key --}}
        <div class="grade-key">
            <div class="grade-key-cell grade-key-header">Grade</div>
            <div class="grade-key-cell grade-key-header">A</div>
            <div class="grade-key-cell grade-key-header">B</div>
            <div class="grade-key-cell grade-key-header">C</div>
            <div class="grade-key-cell grade-key-header">D</div>
            <div class="grade-key-cell grade-key-header">F</div>

            <div class="grade-key-cell grade-key-header">Range</div>
            <div class="grade-key-cell">80 – 100</div>
            <div class="grade-key-cell">70 – 79</div>
            <div class="grade-key-cell">60 – 69</div>
            <div class="grade-key-cell">50 – 59</div>
            <div class="grade-key-cell" style="color:var(--fail);">0 – 49</div>

            <div class="grade-key-cell grade-key-header">Remark</div>
            <div class="grade-key-cell">Excellent</div>
            <div class="grade-key-cell">Very Good</div>
            <div class="grade-key-cell">Good</div>
            <div class="grade-key-cell">Satisfactory</div>
            <div class="grade-key-cell" style="color:var(--fail);">Fail</div>
        </div>

        {{-- Comments --}}
        <div class="section-title">Remarks</div>
        <div class="comments">
            <div class="comment-box">
                <div class="comment-label">Class Teacher's Comment</div>
                <div class="comment-text">{{ $result->class_teacher_comment ?: 'No comment provided.' }}</div>
                <div class="comment-sig">
                    <div class="sig-line"></div>
                    <div class="sig-label">Class Teacher's Signature</div>
                </div>
            </div>
            <div class="comment-box">
                <div class="comment-label">Head Teacher / Principal's Comment</div>
                <div class="comment-text">{{ $result->principal_comment ?: 'No comment provided.' }}</div>
                <div class="comment-sig">
                    <div class="sig-line"></div>
                    <div class="sig-label">Head Teacher's Signature</div>
                </div>
            </div>
        </div>

        {{-- Status strip --}}
        <div class="status-strip">
            <span>
                Status: <strong>{{ ucfirst($result->status) }}</strong>
                @if ($result->approved_at)
                    &nbsp;·&nbsp; Approved by {{ $result->approvedBy?->name ?? '—' }} on {{ $result->approved_at->format('d M Y') }}
                @endif
            </span>
            <span>Printed: {{ now()->format('d M Y, g:ia') }}</span>
        </div>
    </div>
</div>

</body>
</html>
