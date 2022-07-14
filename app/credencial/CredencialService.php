<?php
// require('../../tfpdf/tfpdf.php');
require('../../fpdf/fpdf.php');


class CredencialService
{
    public function sumarFechasEjeY($original)
    {
        $valor = $original + 4;
        return $valor;
    }

    public function crearCredencial($params)
    {
        $pdf = new FPDF();

        $xDatosFront = 38;
        $yDatosFront = 25;
        $xFechasFront = 40;
        $yFechasFront = 40;

        $pdf->SetLeftMargin(15);
        $pdf->SetTopMargin(15);
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0);
        // $pdf->SetFillColor(10, 103, 177);
        $pdf->SetFillColor(255);
        $pdf->AddPage();
        // $pdf->BasicTable($header, $data);


        $pdf->Cell(85, 55, "", 1, 0, 'C', false);

        if (isset($params['tituloCredencial'])) {
            $pdf->SetFont('Arial', '', 17);
            $pdf->SetXY(60, 19);
            $pdf->Cell(15, 0, $params['tituloCredencial'], 0, 0, 'C', true);
        }
        //CUADRO DORSO
        // $pdf->Cell(85, 55, "", 1, 0, 'C', false);
        $pdf->Image('../assets/banner-muni-transparent.png', 41, 60, 58, 9, 'PNG');

        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $response = file_get_contents($params['urlFoto'], false, stream_context_create($arrContextOptions));

        // $pic = "data:image/png;base64," . base64_encode($response);
        file_put_contents("test.jpg", $response);
        $pdf->Image('test.jpg', 16, 16, 20, 25);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY($xDatosFront, $yDatosFront);
        $pdf->Cell(0, 0,  'Numero Carnet: ' . $params['numeroCredencial'], 0, 0, 'L', false);
        $pdf->SetXY($xDatosFront, $yDatosFront = sumarEjeDatosY($yDatosFront));
        $pdf->Cell(0, 0,  $params['razonSocial'], 0, 0, 'L', false);
        $pdf->SetXY($xDatosFront, $yDatosFront = sumarEjeDatosY($yDatosFront));
        $pdf->Cell(0, 0,  'DNI: ' . $params['documento'], 0, 0, 'L', false);

        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        $fechaNacimiento = date("d-m-Y ", strtotime($params['fechaNacimiento']));
        $pdf->Cell(0, 0, "Fecha Nacimiento: $fechaNacimiento", 0, 0, 'L', false);
        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        $pdf->Cell(0, 0, "Otorgado: $params[fechaOtorgado]", 0, 0, 'L', false);
        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        $pdf->Cell(0, 0, "Vencimiento: $params[fechavencimiento]", 0, 0, 'L', false);

        if (isset($params['urlQR'])) {
            $url = "https://chart.googleapis.com/chart?chs=250x250&chco=006BB1&cht=qr&chld=H|0&chl=" . $params['urlQR'];
            //QR Credencial
            $pdf->Image($url, 16, 43, 25, 25, 'PNG');
        }

        $pdfFile = $pdf->Output("S", "temp.pdf", true);
        $base64File = "data:application/pdf;base64," . base64_encode($pdfFile);
        // $base64String = chunk_split(base64_encode($pdfFile));
        unlink('test.jpg');
        return ["file" => $base64File];
    }
    public function crearCredencialDorso($params)
    {
        $pdf = new FPDF();

        $xDatosFront = 38;
        $yDatosFront = 25;
        $xDatosDorso = 86;
        $yDatosDorso = 16;
        $xFechasFront = 40;
        $yFechasFront = 40;

        $pdf->SetLeftMargin(15);
        $pdf->SetTopMargin(15);
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0);
        // $pdf->SetFillColor(10, 103, 177);
        $pdf->SetFillColor(255);
        $pdf->AddPage();
        // $pdf->BasicTable($header, $data);

        //Frente
        $pdf->Cell(85, 55, "", 1, 0, 'C', false);
        //Dorso
        $pdf->Cell(85, 55, "", 1, 0, 'C', false);

        // foreach($params['datosFrente'] as $frente){}

        $pdf->SetFont('Arial', '', 17);
        $pdf->SetXY(60, 19);
        $pdf->Cell(15, 0, $params['datosFrente']['tituloCredencial'], 0, 0, 'C', true);


        $pdf->Image('../assets/banner-muni-transparent.png', 41, 60, 58, 9, 'PNG');

        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $response = file_get_contents($params['datosFrente']['urlFoto'], false, stream_context_create($arrContextOptions));

        file_put_contents("test.jpg", $response);
        $pdf->Image('test.jpg', 16, 16, 20, 25);

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($xDatosFront, $yDatosFront);
        $pdf->Cell(0, 0,  'Numero Carnet: ' . $params['datosFrente']['numeroCredencial'], 0, 0, 'L', false);
        $pdf->SetXY($xDatosFront, $yDatosFront = sumarEjeDatosY($yDatosFront));
        // $pdf->Cell(0, 0,  utf8_decode($params['datosFrente']['razonSocial']), 0, 0, 'L', false);
        $pdf->Cell(0, 0,  $params['datosFrente']['razonSocial'], 0, 0, 'L', false);
        $pdf->SetXY($xDatosFront, $yDatosFront = sumarEjeDatosY($yDatosFront));
        $pdf->Cell(0, 0,  'DNI: ' . $params['datosFrente']['documento'], 0, 0, 'L', false);

        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        // $fechaNacimiento = date("d-m-Y ", strtotime($params['datosFrente']['fechaNacimiento']));
        $pdf->Cell(0, 0, "Fecha Nacimiento: " . $params['datosFrente']['fechaNacimiento'], 0, 0, 'L', false);
        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        $pdf->Cell(0, 0, "Otorgado: " . $params['datosFrente']['fechaOtorgado'], 0, 0, 'L', false);
        $pdf->SetXY($xFechasFront, $yFechasFront = sumarEjeDatosY($yFechasFront));
        $pdf->Cell(0, 0, "Vencimiento: " . $params['datosFrente']['fechavencimiento'], 0, 0, 'L', false);

        if (isset($params['datosFrente']['urlQR'])) {
            $url = "https://chart.googleapis.com/chart?chs=250x250&chco=006BB1&cht=qr&chld=H|0&chl=" . $params['datosFrente']['urlQR'];
            //QR Credencial
            $pdf->Image($url, 16, 43, 25, 25, 'PNG');
        }

        //DORSO
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetXY($xDatosDorso, $yDatosDorso);
        $pdf->Cell(0, 5, utf8_decode("MUNICIPALIDAD DE NEUQUÉN"), 0, 0, 'C', false);
        $pdf->SetFont('Arial', '', 13);
        $pdf->SetXY($xDatosDorso, $yDatosDorso = sumarEjeDatosY($yDatosDorso));
        $pdf->Cell(0, 5,  utf8_decode("Provincia De Neuquén"), 0, 0, 'C', false);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(101, $yDatosDorso = sumarEjeDatosY($yDatosDorso+4));
        $pdf->Cell(0, 5, "Numero Recibo: " . $params['datosDorso']['numeroRecibo'], 0, 0, 'L', false);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(101, $yDatosDorso = sumarEjeDatosY($yDatosDorso+1));
        $pdf->MultiCell(84, 5, "Observaciones: " . $params['datosDorso']['observaciones'], 0, 'L', false);
        // $pdf->SetFont('Arial', '', 10);
        // $pdf->SetXY($xDatosDorso, $yDatosDorso = sumarEjeY($yDatosDorso));
        // $pdf->Cell(0, 5, "BLA BLA BLA", 0, 0, 'L', false);

        $pdfFile = $pdf->Output("S", "temp.pdf", true);
        $base64File = "data:application/pdf;base64," . base64_encode($pdfFile);
        // $base64String = chunk_split(base64_encode($pdfFile));
        unlink('test.jpg');
        return ["file" => $base64File];
    }
}
