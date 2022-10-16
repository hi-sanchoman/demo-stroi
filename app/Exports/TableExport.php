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
use Auth;

class TableExport implements FromView, WithColumnWidths, WithDefaultStyles {
  use Exportable;

  private $data;
  private $user;

  public function __construct($data, $user) 
  {
    $this->data = $data;
    $this->user = $user;
  }

  public function columnWidths(): array
  {
    return [
      'A' => 20,
      'B' => 20,            
      'C' => 20,            
      'D' => 20,            
      'E' => 20,            
      'F' => 40,     
    ];
  }

  public function view(): View
  {
    // dd(($this->data));

    foreach ($this->data as $item) {
      $item->kindLabel = $this->_getKind($item->kind);
      $item->inners = $this->_getInners($item);
      $item->statusLabel = $this->_getStatus($item);
   
      // dd($item);
    }


    return view('exports.table', [
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
        'align' => 'left'
          // 'startColor' => ['argb' => Color::RED],
      ],
    ];
  }

  private function _getKind($kind) {
    $kinds = [
      'product' => 'заявка на товар',
      'equipment' => 'заявка на спец. технику',
      'service' => 'заявка на услугу'
    ];
    return $kinds[$kind];
  }

  private function _getInners($item) {

  }

  private function _getStatus($item) {
    if ($item->status == 'completed') {
      return 'закрыта';
    }

    if ($item->is_signed == 1) {
      return 'материально исполнена';
    }

    if ($item->status == 'draft') {
      return 'черновик';
    }

    $user = $this->user;
    $toBeSignedByMe = collect($item->application_application_statuses)->first(function($s) use ($user) {
      if ($s->application_path != null && $user->id == $s->application_path->responsible->id && $s->status == 'incoming') {
        return true;
      }

      return false;
    });

    if ($toBeSignedByMe) {
      return 'на подпись';
    }

    $signedByNext = collect($item->application_application_statuses)->first(function($s) {
      if ($s->status == 'incoming' && $s->application_path != null) {
        return true;
      }

      return false;
    });

    if ($signedByNext) {
      return 'В процессе исполнения: ' . $signedByNext->application_path->responsible->name;
    }

    return '-';
  }
}