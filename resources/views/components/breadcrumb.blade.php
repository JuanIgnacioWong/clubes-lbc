@props(['items' => []])

<nav class="text-xs text-slate-500">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach($items as $item)
            <li class="flex items-center gap-2">
                @if(!$loop->first)<span>/</span>@endif
                @if(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-brand-700">{{ $item['label'] }}</a>
                @else
                    <span class="font-semibold text-slate-700">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
