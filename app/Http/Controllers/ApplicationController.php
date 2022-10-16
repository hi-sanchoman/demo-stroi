<?php

namespace App\Http\Controllers;

use App\Exports\ApplicationExport;
use App\Exports\ContractApplicationExport;
use App\Exports\PaymentsExport;
use App\Exports\PaymentsPositionsExport;
use App\Exports\PaymentsToBePaidExport;
use App\Exports\TableExport;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationEquipment;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\ApplicationService;
use App\Models\Construction;
use App\Models\Contract;
use App\Models\EquipmentOffer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOffer;
use Maatwebsite\Excel\Excel;
use Storage;

class ApplicationController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $applications = Application::get();

        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        return view('applications.index');
    }

    public function edit()
    {
        return view('applications.index');
    }


    public function uploadFile(Request $request)
    {
        $file = request()->file('file');

        $path = time() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('/uploads'), $path);

        if ($request->kind == 'product') {
            $offer = ApplicationOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        } else if ($request->kind == 'service') {
            $offer = ServiceOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        } else if ($request->kind == 'equipment') {
            $offer = EquipmentOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        }

        return [
            'data' => [
                'file' => $path,
            ],
        ];
    }


    public function uploadOneFile(Request $request)
    {        
        $positionIds = explode(',', $request->positions);

        $file = request()->file('file');
        $path = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads'), $path);

        if ($request->kind == 'product') {
            $appProducts = ApplicationProduct::with('offers')->whereIn('id', $positionIds)->get();
            $offerIds = [];

            foreach ($appProducts as $item) {
                foreach ($item->offers as $offer) {
                    $offerIds[] = $offer->id;
                }
            }

            ApplicationOffer::whereIn('id', $offerIds)->update([
                'file' => $path
            ]);
        } else if ($request->kind == 'service') {
            $appServices = ApplicationService::with('offers')->whereIn('id', $positionIds)->get();
            $offerIds = [];

            foreach ($appServices as $item) {
                foreach ($item->offers as $offer) {
                    $offerIds[] = $offer->id;
                }
            }

            ServiceOffer::whereIn('id', $offerIds)->update([
                'file' => $path
            ]);
        } else if ($request->kind == 'equipment') {
            $appEquipments = ApplicationEquipment::with('offers')->whereIn('id', $positionIds)->get();
            $offerIds = [];
            
            foreach ($appEquipments as $item) {
                foreach ($item->offers as $offer) {
                    $offerIds[] = $offer->id;
                }
            }

            EquipmentOffer::whereIn('id', $offerIds)->update([
                'file' => $path
            ]);
        }

        return [
            'data' => [
                'file' => $path,
            ],
        ];
    }


    public function uploadContract(Request $request)
    {
        $file = request()->file('file');
        $path = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads'), $path);

        $contract = Contract::findOrFail($request->contract_id);

        if ($request->has('type')) {
            $contract->file_singed = $path;
        } else {
            $contract->file_contract = $path;
        }

        $contract->save();

        return [
            'data' => [
                'file' => $path,
            ],
        ];
    }


    public function parse()
    {
        return;
        $file = public_path('goods.tsv');
        $lines = collect([]);

        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $lines->push(trim(preg_replace('/\s\s+/', ' ', $line)));
            }

            fclose($handle);
        }

        // dd($lines->unique());
        // Product::truncate();

        $data = [];

        foreach ($lines->unique() as $line) {
            $data[] = [
                'name' => $line,
                'price' => 1,
            ];
        }

        Product::insert($data);

        return 'done';
    }

    public function exportApplication($id) {
        $application = Application::query()
            ->with(['construction', 'applicationApplicationProducts'])
            ->find($id);
        $appID = $application->num ? $application->num : $application->id;
        return (new ApplicationExport($application))->download('Заявка #' . $appID . '.xlsx');
    }

    public function exportContracts($id) {
        $contract = Contract::query()
            ->with(['owner'])
            ->find($id);
        // dd($contract);

        return (new ContractApplicationExport($contract))->download('Бланк заявки на договор #' . $contract->id . '.xlsx');
    }

    public function exportTable(Request $request) {
        $data = json_decode($request->data);
        return (new TableExport($data->data, $data->user))->download('Экспорт заявок.xlsx');
    }

    public function exportPayments(Request $request, $id) {
        $payments = Payment::query()
            ->with([
                'application', 'application.construction',
                'company',
                'productOffers', 'productOffers.applicationProduct', 'productOffers.applicationProduct.product', 'productOffers.applicationProduct.category', 'productOffers.applicationProduct.unit',
                'equipmentOffers', 'equipmentOffers.applicationEquipment', 'equipmentOffers.applicationEquipment.unit', 'equipmentOffers.applicationEquipment.equipment',
                'serviceOffers', 'serviceOffers.applicationService',
            ])
            ->where('status', 'completed')
            ->get();
        // dd($payments->toArray());

        $data = [];

        foreach ($payments as $payment) {
            // if ($payment->application->status == 'in_progress') {
            if ($payment->amount > 0 && $payment->company_id != null) {
                if ($payment->application->construction_id === intval($id)) {
                    $data[] = $payment;
                }
            }
            // }
        }

        // dd($data);

        if ($request->has('to_be_paid')) {
            // dd($request->has('to_be_paid'));
            return (new PaymentsToBePaidExport($data))->download('Реестр платежей - на оплате.xlsx');
        }

        if ($request->has('positions')) {
            // dd($request->has('to_be_paid'));
            return (new PaymentsPositionsExport($data))->download('Реестр платежей - позиции.xlsx');
        }

        return (new PaymentsExport($data))->download('Реестр платежей.xlsx');
    }

    
}
