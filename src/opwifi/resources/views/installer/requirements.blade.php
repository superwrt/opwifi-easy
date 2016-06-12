@extends('installer.layouts.master')

@section('title', trans('installer.requirements.title'))
@section('container')

<ul class="list">
    @foreach($requirements['requirements'] as $extention => $enabled)
    <li class="list__item @if($enabled) success @else error @endif">{{ $extention }}</li>
    @endforeach
</ul>

@if(!isset($requirements['errors']))
    <div class="buttons">
        <a class="button" href="{{ route('Installer::permissions') }}">
        {{ trans('installer.next') }}
        </a>
    </div>
@endif

@stop