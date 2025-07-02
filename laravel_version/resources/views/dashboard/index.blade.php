@extends('layouts.master')


@section('site-title')
    Dashboard
@endsection

@section('h1')
    <h1 class="h2">Dashboard</h1>
@endsection

@section('page-content')

  <div id="app"></div>

@endsection

@section('scripts')
  <script>
    window.dashboardEndpoint = '{{ route('dashboard.api') }}';
    window.dashboardProjectId ='{{ $project->id??'' }}'
  </script>
  <script src="/js/dashboard.js"></script>
@endsection