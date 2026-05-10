@props([
    'icon' => 'fas fa-inbox',
    'message' => 'Nothing to show yet',
    'colspan' => null, // when used inside a <table>
])

@if($colspan)
    <tr>
        <td colspan="{{ $colspan }}" style="text-align:center; color:var(--muted); padding:28px;">
            <i class="{{ $icon }}" style="font-size:1.3rem; opacity:0.5;"></i>
            <div style="margin-top:8px;">{{ $message }}</div>
        </td>
    </tr>
@else
    <div style="text-align:center; color:var(--muted); padding:32px;">
        <i class="{{ $icon }}" style="font-size:1.6rem; opacity:0.5;"></i>
        <div style="margin-top:10px; font-size:0.9rem;">{{ $message }}</div>
    </div>
@endif
