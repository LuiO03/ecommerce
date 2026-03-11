@props(['url'])
<tr>
    <td>
        <table class="header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <a href="{{ $url }}" style="display: inline-block;">
                        @if (trim($slot) === 'Geckommerce')
                            <div class="logo-container">
                                <img src="https://luio03.github.io/muniyauyos.github.io/imagen/logo-geckommerce.png" class="logo" alt="Geckommerce Logo">
                                <div class="logo-texto"><strong>Gecko</strong><span>merce</span></div>
                            </div>
                        @else
                            {!! $slot !!}
                        @endif
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
