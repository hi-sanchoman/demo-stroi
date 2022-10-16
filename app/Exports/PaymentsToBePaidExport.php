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

class PaymentsToBePaidExport implements FromView, WithColumnWidths, WithDefaultStyles {
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
    ];
  }

  public function view(): View
  {
    // dd(($this->data));

    return view('exports.payments_to_be_paid', [
      'data' => $this->data,
    ]);
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