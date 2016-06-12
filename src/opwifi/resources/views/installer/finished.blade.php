@extends('installer.layouts.master')

@section('title', trans('installer.final.title'))
@section('container')
    <p class="paragraph">{{ $message }}</p>
    <div class="buttons">
        <a href="{{ route('Installer::final') }}" class="button">{{ trans('installer.final.exit') }}</a>
    </div>
@stop