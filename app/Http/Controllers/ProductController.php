<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Storage;

use App\Http\Requests\Product\CreateRequest;
use App\Http\Requests\Product\UpdateRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = new Product;
        $products = $products->with(['store']);

        if ($request->has('full_data')) {
            $products = $products->get();
        } else {
            $products = $products->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetched products.', 200, $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        $product = new Product;

        $actualPrice = $request->get('actual_price', null);
        $image = $request->file('image', null);

        // check request if has actual price
        if (! $request->has('actual_price')) {
            // if actual price is empty or null. set actual price as an original price.
            $actualPrice = $request->get('original_price');
        }

        if ($request->hasFile('image')) {
            $image = $this->uploadable($request->file('image'), $product);
        }

        $product->name = $request->get('name', null);
        $product->original_price = $request->get('original_price', null);
        $product->actual_price = $actualPrice;
        $product->image = $image;
        $product->description = $request->get('description', null);
        $product->category = $request->get('category', null);
        $product->unit = $request->get('unit');
        $product->quantity = $request->get('quantity', 0);
        $product->store_id = $request->get('store_id');

        $product->save();
        $product = $product->fresh();

        return $this->generateResponse('Successfully created record.', 200, $product);
    }

    public function getStoreAdminProduct(Request $request, Store $store)
    {
        $user = $request->user();

        if ($store->user_id != $user->id) {
            return $this->generateResponse('unable to fetch record.', 400);
        }

        $product = new Product;

        $product = $product->where('store_id', $store->id)
                        ->orderBy('updated_at', 'DESC')
                        ->get();

        return $this->generateResponse('Successfully fetched record.', 200, $product);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $this->generateResponse('Successfully fetched record.', 200, $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Product $product)
    {
        $actualPrice = $request->get('actual_price', $product->actual_price);

        // this will return a URL 
        $image = $product->image;
        // deconstruct URL into an array
        $image = explode('/', $image);
        // get only the last element and the next element for the full file path
        // this will become <foldername/filename>
        $image = $image[count($image) - 2].'/'.$image[count($image) - 1];

        // checking before updating new image
        // dont update when image is empty
        if ($request->hasFile('image')) {
            // delete existing image in storage
            if (Storage::delete($image)) {
                // replace image in storage
                $image = $this->uploadable($request->file('image'), $product);
            }
        }

        // update also actual price if original price will be updated
        if (! $request->has('actual_price') && $request->has('original_price')) {
            $actualPrice = $request->original_price;
        }

        $product->name           = $request->get('name', $product->name);
        $product->original_price = $request->get('original_price', $product->original_price);
        $product->description    = $request->get('description', $product->description);
        $product->category       = $request->get('category', $product->category);
        $product->unit           = $request->get('unit', $product->unit);
        $product->quantity       = $request->get('quantity', $product->quantity);
        $product->actual_price   = $actualPrice;
        $product->image          = $image;
        // making store id as immutable to prevent changing product ownership
        $product->store_id       = $product->store_id; 

        $product->save();
        $product = $product->fresh();

        return $this->generateResponse('Successfully updated record.', 200, $product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if (is_null($product->image) === false) {
            // deconstruct URL into an array
            $image = explode('/', $product->image);
            // get only the last element and the next element for the full file path
            // this will become <foldername/filename>
            $image = $image[count($image) - 2].'/'.$image[count($image) - 1];

            Storage::delete($image);
        }

        $product->delete();

        return $this->generateResponse('Successfully deleted record.', 200);
    }

    private function uploadable($file, $model)
    {
        $uploadedFile = null;

        if ($file instanceof UploadedFile) {
            // hash the table name to use as folder name
            // this is also not to expose table name while maintaining a foldered structure
            $tableHash = md5($model->getTable());

            // the filename will be the unixtimestamp along with the image hash
            $filename = Carbon::now()->format('U') . '_' . $file->hashName();

            $uploadedFile  = $file->storeAs($tableHash, $filename);
        }

        if ($uploadedFile !== false) {
            return $uploadedFile;
        } else {
            return null;
        }
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
