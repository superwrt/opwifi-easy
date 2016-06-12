<li id="menu-{{$item['name']}}" class="dropdown {{$menusel[0]==$item['name']?'current':''}}">
	<a id="ml-{{$item['name']}}" href="{{$item['url']}}" class="menu-top"
	@if (isset($item['sub']))
	data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
	@endif
	>
		<div class="ow-menu-arrow"><div></div></div>
		<div class="ow-menu-icon dashicons-before"><span class="{{$item['iconClass']}}" aria-hidden="true"></span></div>
		<div class="ow-menu-name">{{$item['string']}}
		@if (isset($item['badge']))
			<span class="badge">{{$item['badge']}}</span>
		@endif
		</div>
	</a>
	@if (isset($item['sub']))
	<ul class="ow-submenu {{$menusel[0]==$item['name']?'current':'dropdown-menu dropdown-menu-left'}}" aria-labelledby="ml-{{$item['name']}}">
		<li class="ow-submenu-head">{{$item['string']}}
		@if (isset($item['badge']))
			<span class="badge">{{$item['badge']}}</span>
		@endif
		</li>
		@foreach ($item['sub'] as $k => $subitem)
		<li class="{{(isset($menusel[1]) && $menusel[1]==$subitem['name'])?'current':''}}"><a href="{{$subitem['url']}}">{{$subitem['string']}}</a>
		@if (isset($subitem['badge']))
			<span class="badge" title="{{$subitem['badge']}}">{{$subitem['badge']}}</span>
		@endif
		</li>
		@endforeach
	</ul>
	@endif
</li>