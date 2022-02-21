<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use DB;

use App\Http\Requests\Cart\IndexRequest;
use App\Http\Requests\Cart\CreateRequest;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        $user  = $request->user();
        $carts = new Cart;
        $carts = $carts->with(['product']);
        $carts = $carts->where('user_id', $user->id);

        if ($request->has('category')) {
            $category = $request->category;
            $carts = $carts->whereHas('product', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $carts = $carts->orderBy('updated_at', 'DESC');

        if ($request->has('full_data')) {
            $carts = $carts->get();
        } else {
            $carts = $carts->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetched record.', 200, $carts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        $user = $request->user();
        
        $items = $request->get('items', null);
        
        try {
            DB::beginTransaction();

            foreach ($items as $item) {
                $cart = new Cart;

                $cart->product_id = $item['product_id'];
                $cart->quantity   = $item['quantity'];
                $cart->user_id    = $user->id;
    
                $cart->save();

                DB::commit();
            }

            $cart = $cart->fresh();
        } catch(Exception $e) {
            DB::rollback();
        }

        return $this->generateResponse('Successfully added to cart.', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();
        return $this->generateResponse('Successfully removed from cart.', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
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
