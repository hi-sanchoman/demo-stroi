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

class ApplicationExport implements FromView, WithColumnWidths, WithDefaultStyles {
  use Exportable;

  private $application;

  public function __construct(Application $application) 
  {
    $this->application = $application;
  }

  public function columnWidths(): array
  {
    return [
      'A' => 3,
      'B' => 20,            
      'C' => 35,            
      'D' => 7,            
      'E' => 7,            
      'F' => 20,            
    ];
  }

  public function view(): View
  {
    $items = [];

    if ($this->application->kind == 'product') {
      $items = $this->application->applicationApplicationProducts;
    } else if ($this->application->kind == 'service') {
      $items = $this->application->applicationServices;
    } else if ($this->application->kind == 'equipment') {
      $items = $this->application->applicationEquipments;
    }

    return view('exports.application', [
      'application' => $this->application,
      'items' => $items,
      // 'num' => 10,
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