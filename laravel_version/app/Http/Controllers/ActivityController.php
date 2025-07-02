<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Project;
use App\Utils\ActivityExport\Report;
use App\Utils\ActivityExport\WriterFormatFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    public function index()
    {
        
       $projects = Project::where(column: 'user_id', operator: auth()->id())->get();

        return view('activity.index', compact('projects'));
    }

    public function listApi(Request $request)
    {
  

            $projects = Project::where(column: 'user_id', operator: auth()->id())->get()->pluck('id');
            if (!$projects) {
                return response()->json([
                    'error' => 'No project found',
                ]);
            }

            $filters = [];

            if ($request->get('search')) {
                $filters['search'] = $request->get('search');
            }

            if ($request->get('eventType')) {
                $filters['eventType'] = $request->get('eventType');
            }

            if ($dateFrom = $request->get('dateFrom')) {
              $filters['dateFrom']  = Carbon::parse($dateFrom);  
            }

            if ($dateTo = $request->get('dateTo')) {
                $filters['dateTo']  = Carbon::parse($dateTo);
            }

            // dd($filters);

        $email = Email::whereIn('project_id', $projects);

        if (!empty($filters['search'])){
            $email->where('emails.destination','like',$filters['search'])
                  ->orWhere('emails.subject', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $email->whereBetween('emails.created_at', [$filters['dateFrom'],$filters['dateTo']]);
        }


        if (!empty($filters['eventType'])) {
            $email->leftJoin('email_events','emails.id','=','email_events.email_id')
            ->where('email_events.event', $filters['eventType'])
                  ->select('emails.*');
        }   

        $results = $email->orderBy('created_at','desc')->paginate(10);

        return response()->json([
            'rows' => $results->items(),
            'totalRows' => $results->total(),
        ]);
    }


    public function detailsApi(Request $request)
    {
        $email = Email::find($request->id);
        return response()->json($email);
    }

    public function export(Request $request, WriterFormatFactory $writerFormatFactory)
    {
        $project = Project::where('user_id', auth()->id())->first();

        if (!$project) {
            abort(404, 'Project not found');
        }

        $filters = [];

        if ($request->get('search')) {
            $filters['search'] = $request->get('search');
        }

        if ($request->get('eventType')) {
            $filters['eventType'] = $request->get('eventType');
        }

        if ($dateFrom = $request->get('dateFrom')) {
            try {
                $filters['dateFrom']  = Carbon::parse($dateFrom);
            } catch (\Exception $e) {
                throw new \Exception('Wrong dateFrom parameter!');
            }
        }

        if ($dateTo = $request->get('dateTo')) {
            try {
                $filters['dateTo']  = Carbon::parse($dateTo);
            } catch (\Exception $e) {
                throw new \Exception('Wrong dateTo parameter!');
            }
        }


        $emails = Email::where('project_id', $project->id);

        if (!empty($filters['search'])){
            $emails->where('emails.destination','like',$filters['search'])
                  ->orWhere('emails.subject', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $emails->whereBetween('emails.created_at', [$filters['dateFrom'],$filters['dateTo']]);
        }


        if (!empty($filters['eventType'])) {
            $emails->leftJoin('email_events','emails.id','=','email_events.email_id')
            ->where('email_events.event', $filters['eventType'])
                  ->select('emails.*');
        } 


        $emails = $emails->get();
        $reports = [];
        foreach ($emails as $email) {
                $row = [
                    $email->status,
                    $email->subject,
                    $email->destination,
                    $email->created_at->format('m/d/Y H:i'),
                    $email->opens,
                    $email->clicks,
                ];
            $reports[] = $row;
        }

        try {
            $writer = $writerFormatFactory->get($request->get('format'));
        } catch (\Exception $e) {
           return response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }


        $response = new StreamedResponse(static function () use ($writer, $reports): void {
            $writer->openToFile('php://output');

            $writer->addRow(WriterEntityFactory::createRowFromArray([
                'Status',
                'Subject',
                'Destination',
                'Date UTC',
                'Opens',
                'Clicks',
            ]));

            foreach ($reports as $row) {
                $writer->addRow(WriterEntityFactory::createRowFromArray($row));
            }

            $writer->close();
        });

        // TODO Refactor
        if ($request->get('format') == 'csv') {
             $response->headers->set('Content-Type', 'text/csv');
             $response->headers->set('Content-Disposition', 'attachment; filename="activity_report.csv"');
        }
        else {
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment; filename="activity_report.xlsx"');
        }

        return $response;
    }


}
