<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\ApplicationOfferResource;
use App\Models\ApplicationComment;
use App\Models\ApplicationLog;
use App\Models\Payment;
use DB;

class ApplicationCommentApiController extends Controller
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
      $comment = ApplicationComment::create($request->all());

      ApplicationLog::create([
        'application_id' => $comment->application_id,
        'user_id' => $comment->user_id,
        'log' => $request->user()->name . ' написал комментарий "'. $comment->comment .'" к заявке №' . $comment->application->num,
      ]);

      return ApplicationComment::query()
        ->with(['user'])
        ->where('application_id', $request->application_id)
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
