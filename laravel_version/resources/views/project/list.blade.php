@extends('layouts.master')


@section('site-title')
    Projects
@endsection

@section('h1')
    <h1 class="h2">Projects</h1>
@endsection

@section('page-content')

  @if($projects->isNotEmpty())
    <div class="alert alert-light">There is only one project available at the moment.</div>
    <div class="row">
      @foreach($projects as $project)
        <div class="col-sm-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ $project->name }}</h5>
              <a href="{{ route('project.edit') }}" class="btn btn-primary">Edit & config</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
   @else
    <div class="alert alert-info">No projects created.</div>

    <div class="row">
      <div class="col-sm-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Add new</h5>
            <a href="{{ route('project.add') }}" class="btn btn-primary">Add</a>
          </div>
        </div>
      </div>
    </div>
  @endif

@endsection

