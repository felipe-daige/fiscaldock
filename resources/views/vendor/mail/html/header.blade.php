@props(['url', 'logo' => null])
{{--
    Faixa de marca. Presa em 600px e centrada (mesma largura do card do corpo e do
    rodapé) — senão o navy vaza pra largura inteira do e-mail e vira um banner gigante
    descolado do conteúdo. Navy inline no <td>: mesmo com imagem bloqueada o topo
    continua sendo "FiscalDock em navy". Fio dourado embaixo = assinatura de marca.
--}}
<tr>
<td align="center" style="padding: 0;">
<table class="brand" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width: 600px; margin: 0 auto;">
<tr>
<td style="background-color: #102c4d; background-image: linear-gradient(#102c4d, #102c4d); padding: 20px 40px; text-align: left; border-radius: 6px 6px 0 0;">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
@if ($logo)
<td style="padding-right: 11px; vertical-align: middle;">
<img src="{{ $logo }}" width="30" height="30" alt="" style="display: block; width: 30px; height: 30px; border: 0; border-radius: 4px;">
</td>
@endif
<td style="vertical-align: middle; text-align: left;">
<span style="font-size: 19px; font-weight: 700; letter-spacing: 0.02em; color: #ffffff;">Fiscal</span><span style="font-size: 19px; font-weight: 700; letter-spacing: 0.02em; color: #e7c473;">Dock</span>
</td>
</tr>
</table>
</a>
</td>
</tr>
<tr>
<td style="height: 3px; line-height: 3px; font-size: 0; background-color: #d19a2e; background-image: linear-gradient(#d19a2e, #d19a2e);">&nbsp;</td>
</tr>
</table>
</td>
</tr>
