<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EquipmentOffer;
use App\Models\Equipment;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\EquipmentOfferResource;
use App\Models\ApplicationEquipment;
use App\Models\Payment;
use DB;

class EquipmentOfferApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: deni if no access
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $offer = EquipmentOffer::create($request->all());

        $applicationEquipment = ApplicationEquipment::with(['application', 'offers', 'offers.company'])
            ->where('id', $offer->application_equipment_id)
            ->firstOrFail();

        return ['data' => [
            'applicationId' => $applicationEquipment->application_id,
            'offers' => $applicationEquipment->offers,
            'offer' => $offer,
        ]];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();

        $input = $request->all();
        $input['company_id'] = $request->company['id'];

        $offer = EquipmentOffer::with(['applicationEquipment'])->whereId($id)->firstOrFail();
        $offer->update($input);

        // update payment
        $payment = Payment::firstOrCreate([
            'company_id' => $offer->company_id,
            'application_id' => $offer->applicationEquipment->application_id,
        ]);

        $offer->payment_id = $payment->id;
        $offer->save();

        // update payment total amount
        $newAmount = 0;

        $offers = EquipmentOffer::where('payment_id', $payment->id)->get();
        foreach ($offers as $offer) {
            $newAmount += $offer->price * $offer->quantity;
        }

        $payment->amount = $newAmount;
        $payment->save();

        if ($newAmount == 0) {
            return ['error' => 'ОШИБКА: укажите сумму'];
        }

        DB::commit();

        return (new EquipmentOfferResource($offer))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // TODO: abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $offer = EquipmentOffer::whereId($id)->firstOrFail();
        $offer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
