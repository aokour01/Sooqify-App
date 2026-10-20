<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request, Ad $ad)
    {
        abort_unless($ad->status === Ad::STATUS_ACTIVE, 404);
        abort_if($ad->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'reason'  => ['required', Rule::in(array_keys(Report::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $report = Report::firstOrCreate(
            ['ad_id' => $ad->id, 'user_id' => $request->user()->id],
            $data
        );
        $message = $report->wasRecentlyCreated
            ? 'Thanks, your report was sent to the moderators.'
            : 'You have already reported this ad. The moderators will review it.';

        return back()->with('status', $message);    }
}
