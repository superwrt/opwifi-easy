@extends('installer.layouts.master')

@section('title', trans('installer.welcome.title'))
@section('container')
    <p class="paragraph">{{ trans('installer.welcome.message') }}</p>
    <div class="buttons">
        <a href="{{ route('Installer::configs') }}" class="button">{{ trans('installer.next') }}</a>
    </div>
@stop