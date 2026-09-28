@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/logo-mail.png') }}" class="logo" width="160" height="80" alt="{{ config('app.name') }}">
<span class="header-title">{!! $slot !!}</span></a>
</td>
</tr>
