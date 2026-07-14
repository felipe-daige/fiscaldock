{{-- Marca d'água diagonal única (não ladrilhada) — estampada em todo PDF gerado para o plano
     Free. O gate vive no layout (`$marcaDagua`); este partial é só o desenho.
     dompdf 3.x suporta transform:rotate + opacity. z-index abaixo do .pdf-conteudo (z-index:2). --}}
<div style="position:fixed; top:0; left:0; width:100%; height:100%; z-index:1;">
    <div style="position:absolute; top:42%; left:0; width:100%; text-align:center;
                transform:rotate(-32deg); opacity:0.07;">
        <div style="font-size:86px; font-weight:bold; color:#1f2937; letter-spacing:.06em;">FISCALDOCK</div>
        <div style="font-size:15px; color:#1f2937; letter-spacing:.18em; margin-top:4px; text-transform:uppercase;">Plano gratuito · assine para remover</div>
    </div>
</div>
