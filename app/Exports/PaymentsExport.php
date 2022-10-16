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

class PaymentsExport implements FromView, WithColumnWidths, WithDefaultStyles {
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
    ];
  }

  public function view(): View
  {
    // dd(($this->data));

    return view('exports.payments', [
      'data' => $this->data,
      // 'num' => 10,
      'totalAmount' => $this->_getTotal(),
      'totalPaid' => $this->_getPaid(),
    ]);
  }

  private function _getTotal() {
    $total = 0; 

    foreach ($this->data as $payment) {
      $total += $payment->amount;
    }

    return $total;
  }

  private function _getPaid() {
    $total = 0; 

    foreach ($this->data as $payment) {
      $total += $payment->paid;
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