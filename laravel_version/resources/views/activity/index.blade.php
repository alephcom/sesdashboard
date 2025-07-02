@extends('layouts.master')


@section('site-title')
    Activity
@endsection

@section('h1')
    <h1 class="h2">Activity</h1>
@endsection

@section('page-content')

    @if($projects->isNotEmpty())
        <div id="app"></div>
    @else
        <div class="alert alert-info">No project created yet. <a href="">Create new</a></div>
    @endif

@endsection

@section('scripts')
  <script>
    window.dashboardProjectId = '{{ $projects->first()->id ?? '' }}';
    window.APP_EXPORT_URL = '{{ url('activity/export')}}';
  </script>
  <script src="/js/activity.js"></script>

@endsection