<?php
namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpParser\ErrorHandler\Collecting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Style;

class PaymentsPositionsExport implements FromView, WithColumnWidths, WithDefaultStyles {
  use Exportable;

  private $data;

  public function __construct($data) 
  {
    $this->data = $data;
  }

  public function columnWidths(): array
  {
    return [
      'A' => 20,
      'B' => 20,            
      'C' => 35,            
      'D' => 20,            
      'E' => 20,            
      'F' => 20,            
      'G' => 20,             
      'H' => 20,     
    ];
  }

  public function view(): View
  {
    // dd(($this->data));
    $items = [];

    foreach ($this->data as $payment) {
      foreach ($payment->productOffers as $offer) {
        $items[] = [
          'application' => $payment->application,
          'construction' => $payment->application->construction,
          'company' => $payment->company,
          'category' => $offer->applicationProduct->category->name,
          'name' => $offer->applicationProduct->product->name,
          'quantity' => $offer->quantity,
          'price' => $offer->price,
        ];
      }

      foreach ($payment->equipmentOffers as $offer) {
        $items[] = [
          'application' => $payment->application,
          'construction' => $payment->application->construction,
          'company' => $payment->company,
          'category' => $offer->applicationEquipment->category->name,
          'name' => $offer->applicationEquipment->equipment->name,
          'quantity' => $offer->quantity,
          'price' => $offer->price,
        ];
      }
      
      foreach ($payment->serviceOffers as $offer) {
        $items[] = [
          'application' => $payment->application,
          'construction' => $payment->application->construction,
          'company' => $payment->company,
          'category' => $offer->applicationService->category,
          'name' => $offer->applicationService->service,
          'quantity' => $offer->quantity,
          'price' => $offer->price,
        ];
      }
    }

    return view('exports.payments_positions', [
      'data' => $items,
      // 'num' => 10,
      'totalAmount' => $this->_getTotal(),
    ]);
  }

  private function _getTotal() {
    $total = 0; 

    foreach ($this->data as $payment) {
      $total += $payment->amount;
    }

    return $total;
  }

  public function defaultStyles(Style $defaultStyle)
  {
    // Configure the default styles
    // return $defaultStyle->getAlignment()->setWrapText(true);

    return [
      'alignment' => [
        'wrapText' => true,
        'vertical' => 'top', 
          // 'startColor' => ['argb' => Color::RED],
      ],
  ];
  }
}