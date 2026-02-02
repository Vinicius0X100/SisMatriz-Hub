<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use App\Models\Onibus;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OnibusPdfController extends Controller
{
    public function downloadManifest(Excursao $excursao, Onibus $onibus)
    {
        $this->authorizeAccess($excursao);
        
        $onibus->load(['assentosVendidos' => function ($query) {
            $query->orderBy('poltrona');
        }]);

        $pdf = Pdf::loadView('modules.excursoes.onibus.pdf.manifest', compact('excursao', 'onibus'));
        
        return $pdf->download('manifesto_onibus_' . $onibus->numero . '.pdf');
    }

    public function downloadTickets(Excursao $excursao, Onibus $onibus)
    {
        $this->authorizeAccess($excursao);

        $onibus->load(['assentosVendidos' => function ($query) {
            $query->orderBy('poltrona');
        }]);

        // Generate QR Codes for each ticket
        foreach ($onibus->assentosVendidos as $assento) {
            // QR Code content: Ticket validation URL or simple data
            // For now, let's put a JSON with ID and Name to simulate validation
            $qrData = json_encode([
                'id' => $assento->id,
                'excursao' => $excursao->id,
                'onibus' => $onibus->numero,
                'poltrona' => $assento->poltrona,
                'passageiro' => $assento->passageiro_nome
            ]);
            
            $assento->qr_code = base64_encode(QrCode::format('svg')->size(100)->generate($qrData));
        }

        $pdf = Pdf::loadView('modules.excursoes.onibus.pdf.tickets', compact('excursao', 'onibus'));
        
        return $pdf->download('passagens_onibus_' . $onibus->numero . '.pdf');
    }

    public function downloadTicket(Excursao $excursao, Onibus $onibus, $assentoId)
    {
        $this->authorizeAccess($excursao);

        $assento = $onibus->assentosVendidos()->findOrFail($assentoId);
        
        // Use a collection to reuse the same view
        $onibus->setRelation('assentosVendidos', collect([$assento]));

        $qrData = json_encode([
            'id' => $assento->id,
            'excursao' => $excursao->id,
            'onibus' => $onibus->numero,
            'poltrona' => $assento->poltrona,
            'passageiro' => $assento->passageiro_nome
        ]);
        
        $assento->qr_code = base64_encode(QrCode::format('svg')->size(100)->generate($qrData));

        $pdf = Pdf::loadView('modules.excursoes.onibus.pdf.tickets', compact('excursao', 'onibus'));
        
        return $pdf->download('passagem_poltrona_' . $assento->poltrona . '.pdf');
    }

    public function downloadTicketsPrintable(Excursao $excursao, Onibus $onibus)
    {
        $this->authorizeAccess($excursao);

        $onibus->load(['assentosVendidos' => function ($query) {
            $query->orderBy('poltrona');
        }]);

        foreach ($onibus->assentosVendidos as $assento) {
            $qrData = json_encode([
                'id' => $assento->id,
                'poltrona' => $assento->poltrona,
            ]);
            $assento->qr_code = base64_encode(QrCode::format('svg')->size(80)->generate($qrData));
        }

        $pdf = Pdf::loadView('modules.excursoes.onibus.pdf.tickets_printable', compact('excursao', 'onibus'));
        
        return $pdf->download('passagens_recorte_onibus_' . $onibus->numero . '.pdf');
    }

    private function authorizeAccess(Excursao $excursao)
    {
        $user = auth()->user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
    }
}
