<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\PaymentCompleted;
use App\Mail\PaymentOrdered;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\Payment;
use Mail;

class PaymentApiController extends Controller
{
    public function payments()
    {
        $payments = Payment::with(['application', 'application.construction', 'company', 'offers', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.category'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($payments as $payment) {
            if ($payment->application->status == 'in_progress') {
                if ($payment->amount > 0 && $payment->company_id != null) {
                    $data[] = $payment;
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
        // update payments
        foreach ($request->data as $payment) {
            if (!isset($payment['to_be_paid'])) continue;

            Payment::where('id', $payment['id'])->update([
                'to_be_paid' => $payment['to_be_paid'],
            ]);
        }

        // notify accountant
        // TODO: hard-coded accountant email!
        Mail::to('aijanim_89@mail.ru')->send(new PaymentOrdered());
        // Mail::to('noreply.oks@yandex.kz')->send(new PaymentOrdered());


        return $this->payments();
    }



    public function paymentsToPay()
    {
        $payments = Payment::with(['application', 'application.construction', 'company', 'offers'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($payments as $payment) {
            if ($payment->application->status == 'in_progress') {
                if ($payment->to_be_paid > 0 && $payment->company_id != null) {
                    $data[] = $payment;
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
        $payment = Payment::with(['company', 'application'])->where('id', $id)->firstOrFail();

        // save to be paid value
        $toBePaid = $payment->to_be_paid;

        // update offer
        $payment->paid += $toBePaid;
        $payment->to_be_paid = 0;
        $payment->save();

        // notify CEO
        // TODO: hard-coded CEO email!
        Mail::to('kurtayev.meirzhan@gmail.com')->send(new PaymentCompleted($payment, $toBePaid));
        // Mail::to('noreply.oks@yandex.kz')->send(new PaymentCompleted($applicationOffer, $toBePaid));

        return 1;
    }








    // public function payments()
    // {

    //     $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
    //     // dd($applications->toArray());

    //     $data = [];

    //     foreach ($applications as $product) {
    //         if ($product->application != null && $product->application->status == 'in_progress') {
    //             foreach ($product->offers as $offer) {
    //                 // dd($product->offers);

    //                 if ($offer->price > 0 && $offer->company_id != null) {
    //                     $data[] = $offer;
    //                 }
    //             }
    //         }
    //     }

    //     return [
    //         'data' => $data,
    //     ];
    // }



    // public function updateBatch(Request $request)
    // {
    //     foreach ($request->data as $offer) {
    //         if (!isset($offer['to_be_paid'])) continue;

    //         ApplicationOffer::where('id', $offer['id'])->update([
    //             'to_be_paid' => $offer['to_be_paid'],
    //         ]);
    //     }

    //     // notify accountant
    //     // TODO: hard-coded accountant email!
    //     Mail::to('aijanim_89@mail.ru')->send(new PaymentOrdered());
    //     // Mail::to('noreply.oks@yandex.kz')->send(new PaymentOrdered());


    //     $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
    //     // dd($applications->toArray());

    //     $data = [];

    //     foreach ($applications as $product) {
    //         if ($product->application != null && $product->application->status == 'in_progress') {
    //             foreach ($product->offers as $offer) {
    //                 if ($offer->price > 0 && $offer->company_id != null) {
    //                     $data[] = $offer;
    //                 }
    //             }
    //         }
    //     }

    //     return [
    //         'data' => $data,
    //     ];
    // }


    // public function paymentsToPay()
    // {
    //     $applications = ApplicationProduct::with(['application', 'product', 'category', 'offers', 'offers.company', 'offers.applicationProduct', 'offers.applicationProduct.product', 'offers.applicationProduct.application', 'offers.applicationProduct.application.construction'])->get();
    //     // dd($applications->toArray());

    //     $data = [];

    //     foreach ($applications as $product) {
    //         if ($product->application != null && ($product->application->status == 'in_progress' || $product->application->status == 'signed')) {
    //             foreach ($product->offers as $offer) {
    //                 if ($offer->to_be_paid != null && $offer->to_be_paid > 0) {
    //                     $data[] = $offer;
    //                 }
    //             }
    //         }
    //     }

    //     return [
    //         'data' => $data,
    //     ];
    // }

    /**
     * Set payment request as paid
     */
    // public function setPaid(Request $request, $id)
    // {
    //     $applicationOffer = ApplicationOffer::with(['company', 'applicationProduct', 'applicationProduct.product'])->where('id', $id)->firstOrFail();

    //     // save to be paid value
    //     $toBePaid = $applicationOffer->to_be_paid;

    //     // update offer
    //     $applicationOffer->paidTotal += $applicationOffer->to_be_paid;
    //     $applicationOffer->to_be_paid = 0;
    //     $applicationOffer->save();

    //     // notify CEO
    //     // TODO: hard-coded CEO email!
    //     Mail::to('kurtayev.meirzhan@gmail.com')->send(new PaymentCompleted($applicationOffer, $toBePaid));
    //     // Mail::to('noreply.oks@yandex.kz')->send(new PaymentCompleted($applicationOffer, $toBePaid));

    //     return 1;
    // }
}
