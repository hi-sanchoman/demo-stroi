<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\ApplicationOfferResource;
use App\Models\Payment;
use DB;

class ApplicationOfferApiController extends Controller
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
        $applicationOffer = ApplicationOffer::create($request->all());

        $applicationProduct = ApplicationProduct::with(['application', 'offers', 'offers.company'])
            ->where('id', $applicationOffer->application_product_id)
            ->firstOrFail();

        return ['data' => [
            'applicationId' => $applicationProduct->application_id,
            'offers' => $applicationProduct->offers,
            'offer' => $applicationOffer,
        ]];

        // return (new ApplicationOfferResource($applicationOffer))
        //     ->response()
        //     ->setStatusCode(Response::HTTP_CREATED);
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

        $offer = ApplicationOffer::with(['applicationProduct'])->whereId($id)->firstOrFail();
        $offer->update($input);

        // update payment
        $payment = Payment::firstOrCreate([
            'company_id' => $offer->company_id,
            'application_id' => $offer->applicationProduct->application_id,
        ]);

        $offer->payment_id = $payment->id;
        $offer->save();

        // update payment total amount
        $newAmount = 0;

        $offers = ApplicationOffer::where('payment_id', $payment->id)->get();
        foreach ($offers as $offer) {
            $newAmount += $offer->price * $offer->quantity;
        }

        $payment->amount = $newAmount;
        $payment->save();

        if ($newAmount == 0) {
            return ['error' => 'ОШИБКА: укажите сумму'];
        }

        DB::commit();

        return (new ApplicationOfferResource($offer))
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

        $offer = ApplicationOffer::whereId($id)->firstOrFail();
        $offer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
