<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Aset</title>
    <style>
        {!! file_get_contents(public_path('assets/css/print_label_pdf.css')) !!}
    </style>
</head>
<body>
    @php
        $pathLogo = public_path('assets/img/logo-reka.png');
        $base64Logo = '';
        if(file_exists($pathLogo)) {
            $base64Logo = 'data:image/png;base64,' . base64_encode(file_get_contents($pathLogo));
        }
        // aset menjadi baris 2 kolom
        $rows = array_chunk($asets->all(), 2);
    @endphp

    <table class="grid-table">
        @foreach ($rows as $row)
        <tr>
            @foreach ($row as $aset)
            <td>
                @php
                    $warnaLabel = $aset->kategoriAset->jenisKategori->warna_label ?? '#FF5E9B';
                @endphp
                <div class="label-container" style="border-color: {{ $warnaLabel }};">
                    
                    <!-- Top Banner -->
                    <div class="top-banner" style="background-color: {{ $warnaLabel }};">
                        @if($base64Logo)
                            <img src="{{ $base64Logo }}" alt="Logo">
                        @else
                            <div class="banner-text">
                                REKA<span style="color:#ffffff;">INKA</span> GROUP
                            </div>
                        @endif
                    </div>

                    <!-- Asset Number -->
                    <div class="asset-number">
                        {{ $aset->nomor_aset }}
                    </div>

                    <!-- Left Info -->
                    <div class="info-container">
                        <div class="info-label">SITES LOCATION</div>
                        <div class="info-value-bold">PT Rekaindo Global Jasa</div>
                    </div>

                    <!-- Right QR -->
                    <div class="qr-frame">
                        <div class="bracket tl" style="border-color: {{ $warnaLabel }};"></div>
                        <div class="bracket tr" style="border-color: {{ $warnaLabel }};"></div>
                        <div class="bracket bl" style="border-color: {{ $warnaLabel }};"></div>
                        <div class="bracket br" style="border-color: {{ $warnaLabel }};"></div>
                        <div class="qr-bg">
                            <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(60)->margin(0)->generate(route('aset.show', $aset->id))) !!}" alt="QR" class="qr-image">
                        </div>
                    </div>

                </div>
            </td>
            @endforeach
            @if(count($row) == 1)
            <td></td>
            @endif
        </tr>
        @endforeach
    </table>
</body>
</html>
