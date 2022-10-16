<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContractOffer;
use App\Models\ContractProduct;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\ContractOfferResource;
use App\Models\ContractComment;
use App\Models\ContractLog;
use App\Models\Payment;
use DB;

class ContractCommentApiController extends Controller
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
      $comment = ContractComment::create($request->all());

      // ContractLog::create([
      //   'contract_id' => $comment->contract_id,
      //   'user_id' => $comment->user_id,
      //   'log' => $request->user()->name . ' написал комментарий "'. $comment->comment .'" к договору №' . $comment->contract->num,
      // ]);

      return ContractComment::query()
        ->with(['user'])
        ->where('contract_id', $request->contract_id)
        ->orderBy('created_at', 'desc')
        ->get();
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      
    }
}
