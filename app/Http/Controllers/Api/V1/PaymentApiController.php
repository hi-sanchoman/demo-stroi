<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;

class PaymentApiController extends Controller
{
    public function payments()
    {

        $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($applications as $product) {
            if ($product->application != null && $product->application->status == 'in_progress') {
                foreach ($product->offers as $offer) {
                    // dd($product->offers);

                    if ($offer->price > 0 && $offer->company_id != null) {
                        $data[] = $offer;
                    }
                }
            }
        }

        return [
            'data' => $data,
        ];
    }


    public function paymentsToPay()
    {
        $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($applications as $product) {
            if ($product->application != null && ($product->application->status == 'in_progress' || $product->application->status == 'signed')) {
                foreach ($product->offers as $offer) {
                    if ($offer->to_be_paid != null && $offer->to_be_paid > 0) {
                        $data[] = $offer;
                    }
                }
            }
        }

        return [
            'data' => $data,
        ];
    }


    /**
     * Update payments
     */
    public function updateBatch(Request $request)
    {
        foreach ($request->data as $offer) {
            if (!isset($offer['to_be_paid'])) continue;

            ApplicationOffer::where('id', $offer['id'])->update([
                'to_be_paid' => $offer['to_be_paid'],
            ]);
        }

        $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($applications as $product) {
            if ($product->application != null && $product->application->status == 'in_progress') {
                foreach ($product->offers as $offer) {
                    if ($offer->price > 0 && $offer->company_id != null) {
                        $data[] = $offer;
                    }
                }
            }
        }

        return [
            'data' => $data,
        ];
    }

    /**
     * Set payment request as paid
     */
    public function setPaid(Request $request, $id)
    {
        $applicationOffer = ApplicationOffer::where('id', $id)->firstOrFail();
        $applicationOffer->paidTotal += $applicationOffer->to_be_paid;
        $applicationOffer->to_be_paid = 0;
        $applicationOffer->save();

        return 1;
    }
}
