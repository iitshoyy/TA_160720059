@props([
    'title' => null,
])

{{--
    A card with an optional title and an optional `action` named slot.
    WHY a slot instead of a string prop: the action commonly contains HTML
    with quotes and a `>` character (e.g. an anchor tag), which mangles
    Blade's component-attribute parser when escaped into a string prop.
    Slots side-step that entirely.
--}}

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($action))
        <div class="section-card-head">
            @if($title)
                <h3 class="section-card-title">{{ $title }}</h3>
            @endif
            @isset($action)
                <div>{{ $action }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</div>
