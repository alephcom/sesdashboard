@extends('layouts.master')


@section('site-title')
    Edit Project
@endsection

@section('h1')
  <h1 class="h2">
    @if($project)
      Edit project <small>(ID: {{ $project->id??'' }})</small>
    @else
      Create new project
    @endif
  </h1>
@endsection

@section('page-content')

<form method="post">
  @csrf
    <div class="row">
      <div class="col-sm-4">
        <label class="form-label">Project Name</label>
        <input type="text" name="name" class="form-control" placeholder="Enter project name" value="{{ $project->name ?? '' }}" required>
      </div>
    </div>

    @if($project->id??false)
      <button type="submit" name="submit" class="btn btn-primary mt-1">Save</button>
    @else
      <button type="submit" name="submit" class="btn btn-primary mt-1">Create and get configuration</button>
    @endif

 </form>

  @if($project->token??false)
    <div class="card mt-5">
      <div class="card-header">
        <a href="" class="btn btn-danger btn-sm float-right">Refresh</a>
        <h5>WebHook</h5>
      </div>
      <div class="card-body">
        <p class="card-text"><samp>{{url('/webhook/'. $project->token??'')}}</samp></p>
      </div>
    </div>

    <div class="card mt-5">
      <div class="card-header">
        <h5>Configure AWS Simple Email Service</h5>
      </div>
      <div class="card-body">
        <p class="card-text">
          Follow Configuration instructions:
          <a href="https://sesdashboard.readthedocs.io/en/latest/configuration.html" target="_blank">https://sesdashboard.readthedocs.io/en/latest/configuration.html</a>
        </p>
      </div>
    </div>
  @endif

@endsection
