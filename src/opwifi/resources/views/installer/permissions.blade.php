@extends('installer.layouts.master')

@section('title', trans('installer.permissions.title'))
@section('container')

<ul class="list">
    @foreach($permissions['permissions'] as $permission)
    <li class="list__item list__item--permissions @if($permission['isSet']) success @else error @endif">
        {{ $permission['folder'] }}<span>{{ $permission['permission'] }}</span>
        </li>
    @endforeach
</ul>

@if(!isset($permissions['errors']))
<div class="buttons">
    <a class="button" href="{{ route('Installer::database') }}">
        {{ trans('installer.next') }}
    </a>
</div>
@endif

@stop