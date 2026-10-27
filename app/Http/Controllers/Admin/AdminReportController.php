<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\AdEvent;

class AdminReportController extends Controller
{
    public function index()
    {
        $reports = Report::where('status', Report::STATUS_OPEN)
            ->with(['ad.images', 'ad.user', 'reporter'])
            ->oldest()
            ->paginate(15);

        return view('admin.reports.index', compact('reports'));
    }

    public function dismiss(Request $request, Report $report)
    {
        abort_unless($report->status === Report::STATUS_OPEN, 422);

        $report->forceFill([
            'status'     => Report::STATUS_DISMISSED,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ])->save();

        return back()->with('status', 'Report dismissed.');
    }

    public function takedown(Request $request, Report $report)
    {
        abort_unless($report->status === Report::STATUS_OPEN, 422);

        DB::transaction(function () use ($request, $report) {
            $ad = $report->ad;

            if ($ad && $ad->status !== Ad::STATUS_REJECTED) {
                $ad->forceFill([
                    'status'           => Ad::STATUS_REJECTED,
                    'rejection_reason' => 'Removed after a report: ' . Report::REASONS[$report->reason],
                ])->save();

                $ad->user->notify(new AdEvent(
                    $ad, 'rejected',
                    "Your ad \"{$ad->title}\" was removed after a report: " . Report::REASONS[$report->reason]
                ));
            }

            Report::where('ad_id', $report->ad_id)
                ->where('status', Report::STATUS_OPEN)
                ->update([
                    'status'     => Report::STATUS_RESOLVED,
                    'handled_by' => $request->user()->id,
                    'handled_at' => now(),
                ]);
        });

        return back()->with('status', 'Ad removed and reports resolved.');
    }
}
