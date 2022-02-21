<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use DB;

use App\Http\Requests\Order\CreateRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = new Order;
        $user = $request->user();

        if ($user->user_type == 'basic_user') {
            $orders = $orders->where('user_id', $user->id);
            $orders = $orders->with(['store']);
        } else {
            $orders = $orders->with(['user']);
        }

        if ($user->user_type == 'store_admin') {
            $orders = $orders->whereHas('store', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->has('filter')) {
            $orders = $orders->where('status', $request->filter);
        }

        $orders = $orders->with(['orderDetails.product']);

        $orders = $orders->orderBy('updated_at', 'DESC');

        // only for getting status options
        if ($request->has('status')) {
            if ($user->user_type != 'basic_user') $orders = $orders->withOut(['user']);
            $orders = $orders->withOut(['orderDetails']);
            $orders = $orders->get('status')->unique('status');
            return $this->generateResponse('Successfully fetched order.', 200, $orders);
        }

        if ($request->has('full_data')) {
            $orders = $orders->get();
        } else {
            $orders = $orders->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetched order.', 200, $orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        // $order = new Order;
        $user  = $request->user();
        $carts = $request->cart; // assume cart is always an array

        try {
            DB::beginTransaction();

            foreach ($carts as $cart) {
                // get all items in a cart 
                $foundCart = Cart::find($cart);

                $order           = new Order;
                $order->store_id = Product::find($foundCart->product_id)->store_id;
                $order->user_id  = $user->id;

                $order->save();

                //instantiate new order detail
                $orderDetails = new OrderDetail;

                $orderDetails->order_id   = $order->id;
                $orderDetails->product_id = $foundCart->product_id;
                $orderDetails->quantity   = $foundCart->quantity;
                
                // get the price of an item
                $product = Product::find($foundCart->product_id);
                $total   = $product->actual_price * $foundCart->quantity;

                $orderDetails->total = $total;

                // after order is made, delete cart footprint
                if ($orderDetails->save()) $foundCart->delete();
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }

        return $this->generateResponse('Successfully place order.', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        
        if ($user->id != $order->user_id) {
            return $this->generateResponse('Resources not found.', 404);
        }

        $order = $order->with(['orderDetails.product']);

        return $this->generateResponse('Successfully place order.', 200, $order->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        // restrict user Order cancellation if order is already been shipped or delivered
        if ($order->status != 'Shipping soon') {
            return $this->generateResponse("Order cancellation denied! Your order is already been {$order->status}.", 401);
        }

        $order->status = 'Canceled';
        $order->save();
        $order = $order->fresh();

        return $this->generateResponse('Successfully canceled order.', 200, $order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Order $order)
    {
        $user = $request->user();

        if ($order->user_id != $user->id) {
            return $this->generateResponse('Your are not authorized to modify or delete this record', 401);
        }
        
        $order->orderDetails()->delete();
        $order->delete();
        return $this->generateResponse('Successfully deleted order.', 200, $order);
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
