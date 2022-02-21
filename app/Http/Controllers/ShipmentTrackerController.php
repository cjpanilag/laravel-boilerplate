<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Http\Requests\ShipmentTracker\IndexRequest;

class ShipmentTrackerController extends Controller
{
    
    public function updateOrderStatus(IndexRequest $request, Order $order)
    {
        $order->status = $request->status;
        
        if ($order->save()) {   
            // update products if shipment action is not canceled or pending 
            if ($request->status == 'Shipping') {
                // map through order details item and get product_id & order quantity, used for updating product quanity
                $orderDetails = $order->orderDetails()->get(['product_id', 'quantity']);
                // decrement product quantity base on order items
                foreach ($orderDetails as $details) {
                    Product::find($details->product_id)->decrement('quantity', $details->quantity);
                }
            }
        }

        $order->fresh();

        return $this->generateResponse('Shipment status updated.', 200, $order);
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
