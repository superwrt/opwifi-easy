@extends('installer.layouts.master')

@section('title', trans('installer.environment.title'))
@section('container')
    @if (session('message'))
    <p class="alert">{{ session('message') }}</p>
    @endif
    <form method="post" action="{{ route('Installer::configsSave') }}">
        {!! csrf_field() !!}
        @foreach ($configs as $c)
        <div class="group">
        <label>{{$c['title']}}</label>
        <input type="text" name="{{$c['name']}}" value="{{$c['value']}}">
        </div>
        @endforeach
   
    @if(!isset($environment['errors']))
    <div class="buttons">
        <button type="submit" class="button" href="{{ route('Installer::requirements') }}">
            {{ trans('installer.next') }}
        </button>
    </div>
    @endif
    </form>
@stop