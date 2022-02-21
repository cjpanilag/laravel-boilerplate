<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authenticatedUser = $request->user();
        $data              = new OrderDetail;
        $data              = $data->with(['order']);

        if ($authenticatedUser->user_type == 'store_admin') {
            $data = $data->whereHas('order', function($q) use ($authenticatedUser) {
                $q->whereHas('store', function($q) use ($authenticatedUser) {
                    $q->where('user_id', $authenticatedUser->id);
                });
            });
        }

        return $this->generateResponse('Successfully fetched record', 200, $data->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function show(OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Simplify response 
     * 
     * @param String $message
     * @param Integer $code
     * @param Array $data 
     * 
     * @return \Illuminate\Http\Response
     */
    private function generateResponse($message, $code, $data = [])
    {
        $success = $code === 200 ?  true : false;

        $pagination = [];
        $newData = [];

        if ($data instanceof Paginator) {
            // convert pagination to array
            $pagination = $data->toArray();
            $newData = $pagination['data'];
            unset($pagination['data']);
        } else {
            $newData = $data;
        }

        return response()->json([
            'success' => $success,
            'code'    => $code,
            'message' => $message,
            'slug'    => \Str::slug($message, '_'),
            'data'    => $newData,
            'pagination' => $pagination
        ], $code);
    }
}
