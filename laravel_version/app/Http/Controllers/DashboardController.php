<?php

namespace App\Http\Controllers;

use App\Enums\EmailEventEnums;
use App\Models\EmailEvent;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        
        $project = Project::where('user_id', auth()->id())->first();

        if (!$project) {
            session()->flash('alert', 'No project created yet');
        }

        return view('dashboard.index', compact('project'));
    }


    public function jsApi(Request $request)
    {
        $project = Project::where('id', $request->get('projectId'))
            ->where('user_id', auth()->id())
            ->first();

        if (!$project) {
            return response()->json(['error' => 'No project found!'], 404);
        }

        try {
            $dateFrom = Carbon::parse($request->get('dateFrom'));
            $dateTo = Carbon::parse($request->get('dateTo'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Wrong range date!'], 400);
        }


        // counters
        $eventsCount = EmailEvent::selectRaw('event, COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('event')
            ->get()
            ->toArray();

        foreach ($eventsCount as $counter) {
            $counters[$counter['event']] = $counter['count'];
        }


        $notDelivered = ($counters[EmailEventEnums::EVENT_FAILURE] ?? 0)
            + ($counters[EmailEventEnums::EVENT_COMPLAINT] ?? 0)
            + ($counters[EmailEventEnums::EVENT_BOUNCE] ?? 0)
            + ($counters[EmailEventEnums::EVENT_REJECT] ?? 0);

        $counterResults = [
            'sent' => $counters[EmailEventEnums::EVENT_SEND] ?? 0,
            'delivered' => $counters[EmailEventEnums::EVENT_DELIVERY] ?? 0,
            'opens' => $counters[EmailEventEnums::EVENT_OPEN] ?? 0,
            'clicks' => $counters[EmailEventEnums::EVENT_CLICK] ?? 0,
            'notDelivered' => $notDelivered,
        ];

        
        // chart data
        $countersByDate = DB::table('email_events as e')->select('e.event',DB::raw("COUNT(e.id) as count"),DB::raw("DATE_FORMAT(CONVERT_TZ(e.created_at, '+00:00', ?), '%Y-%m-%d') as daygroup"))
        ->whereBetween('e.created_at', [$dateFrom, $dateTo])
        ->addBinding(timezoneOffsetFormatter($request->tzOffset), 'select')
        ->groupBy('daygroup', 'e.event')
        ->orderBy('daygroup', 'ASC')
        ->get();

        $labels = [];
        $datasets = [];
        foreach ($countersByDate as $counter) {
            $labels[$counter->daygroup] = $counter->daygroup;

            if (empty($datasets[$counter->event])) {
                $datasets[$counter->event] = [
                    'label' => ucfirst($counter->event),
                    'data' => [],
                ];
            }

            $datasets[$counter->event]['data'][] = $counter->count;
        }

        $chartData = [
            'labels' => array_values($labels),
            'datasets' => array_values($datasets),
        ];

        return response()->json([
            'counters' => $counterResults,
            'chartData' => $chartData,
        ]);
    }
}
