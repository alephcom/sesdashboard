<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->view('project.list', compact('projects'));
    }

    public function add(Request $request)
    {
        $project = Project::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->first();


        if ($project) {
            return new \LogicException('Only one project available yet.');
        }

        if($request->has('submit')){
            $project_new = new Project();
            $project_new->user_id = auth()->id();
            $project_new->name = $request->name;
            $project_new->token = generateToken();
            $project_new->save();
        }

        return response()->view('project.edit', compact('project'));

    }

    public function edit(Request $request)
    {
        $project = Project::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->first();

        if($request->has('submit')){
            $project->user_id = auth()->id();
            $project->name = $request->name;
            $project->save();
        }

        return response()->view('project.edit', compact('project'));

    }

}
