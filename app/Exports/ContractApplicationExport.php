<?php
namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpParser\ErrorHandler\Collecting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Style;

class ContractApplicationExport implements FromView, WithColumnWidths, WithDefaultStyles {
  use Exportable;

  private $contract;

  public function __construct(Contract $contract) 
  {
    $this->contract = $contract;
  }

  public function columnWidths(): array
  {
    return [
      'A' => 100,
      'B' => 100,        
    ];
  }

  public function view(): View
  {
    return view('exports.contract', [
      'contract' => $this->contract
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