<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\ApplicationOfferResource;

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

        $applicationProduct = ApplicationProduct::with(['application', 'offers'])
            ->where('id', $applicationOffer->application_product_id)
            ->firstOrFail();

        return ['data' => [
            'applicationId' => $applicationProduct->application_id,
            'offers' => $applicationProduct->offers,
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
        $offer = ApplicationOffer::whereId($id)->firstOrFail();
        $offer->update($request->all());

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
