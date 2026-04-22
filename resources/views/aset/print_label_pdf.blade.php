<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Aset</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }
        /* Grid 2 kolom */
        .grid-table {
            width: auto;
            border-collapse: separate;
            border-spacing: 3mm;
        }
        .grid-table td {
            width: 70mm;
            vertical-align: top;
            padding: 0;
        }
        /* Label container */
        .label-container {
            width: 70mm;
            height: 25mm;
            position: relative;
            background-color: #ffffff;
            border-radius: 8px;
            box-sizing: border-box;
            border: 1px solid #777;
            overflow: hidden;
        }
        /* Style untuk Text */
        .info-title {
            font-size: 7px;
            font-weight: bold;
            color: #555;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 8.5px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .asset-tag-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
            margin-top: 0px;
        }
        .asset-kode {
            font-size: 7px;
            font-weight: bold;
            white-space: nowrap;
        }
        /* Vertical rotation logic */
        .rotated-left {
            position: absolute;
            top: 90px;
            left: 3px;
            width: 85px;
            transform: rotate(-90deg);
            transform-origin: 0 0;
            text-align: center;
        }
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
                <div class="label-container">

                    <!-- Left Panel: Logo Vertikal -->
                    <div style="position: absolute; left: 0; top: 0; width: 16%; height: 100%; border-right: 1.5px solid #333; text-align: center;">
                        @if($base64Logo)
                            <div class="rotated-left">
                                <div style="text-align: center; line-height: 1;">
                                    <span style="font-size: 7.5px; color: #000; letter-spacing: 0.5px;">PROPERTY OF:</span>
                                </div>
                                <div style="text-align: center; line-height: 1; margin-top: 2px;">
                                    <img src="{{ $base64Logo }}" style="height: 20px;">
                                </div>
                            </div>
                        @else
                            <div class="rotated-left" style="font-size: 11px; font-weight: bold; color: #333;">
                                <span style="font-size: 8px; font-weight:normal; letter-spacing: 1px; display:inline-block; margin-right:4px;">PROPERTY OF:</span>
                                REKA<span style="color:#e30613;">INKA</span> Group
                            </div>
                        @endif
                    </div>

                    <!-- Middle Panel: Info -->
                    <div style="position: absolute; left: 18%; top: 50%; width: 36%; transform: translateY(-50%);">
                        <div class="info-title">SITES LOCATION</div>
                        <div class="info-value" style="white-space: nowrap; font-size: 7.5px;">PT Rekaindo Global Jasa</div>

                        <div class="info-title">HELPDESK CONTACT</div>
                        <div class="info-value" style="margin-bottom:0;">WA : 0819 0475 7690</div>
                    </div>

                    <!-- Right Panel: QR Code & Nomor Aset -->
                    <div style="position: absolute; left: 54%; right: 3px; top: 0; height: 100%;">
                        <div style="position: absolute; top: 3px; left: 0; width: 100%; text-align: left;">
                            <div class="asset-tag-title">Asset Tag</div>
                        </div>
                        <div style="position: absolute; top: 56%; left: 0; width: 100%; transform: translateY(-50%); text-align: left;">
                            <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(62)->generate(route('aset.show', $aset->id))) !!}" alt="QR" style="display: block;">
                            <div class="asset-kode" style="margin-top: 3px;">{{ $aset->nomor_aset }}</div>
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
