<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Storage;

use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\StoreAdmin\SelfRequest;
use App\Http\Requests\StoreAdmin\GetStoreAdminRequest;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $stores = new Store;
        $stores = $stores->with(['products', 'user']);

        $user = $request->user();

        $stores = $stores->orderBy('created_at', 'DESC');

        if ($request->has('full_data')) {
            $stores = $stores->get();
        } else {
            $stores = $stores->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetch record.', 200, $stores);
    }

    public function storeAdmin(GetStoreAdminRequest $request)
    {
        $stores = new Store;
        $stores = $stores->with(['products', 'user']);

        $user = $request->user();

        $stores = $stores->where('user_id', $user->id);

        $stores = $stores->orderBy('created_at', 'DESC');

        if ($request->has('full_data')) {
            $stores = $stores->get();
        } else {
            $stores = $stores->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetch record.', 200, $stores);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateStoreRequest $request)
    {
        $store = new Store;

        $user = $request->user();

        $image = null;

        if ($request->hasFile('image')) {
            $image = $this->uploadable($request->file('image'), $store);
        }

        $store->name = $request->get('name', null);
        $store->slug = \Str::slug($request->get('name', null), '_');
        $store->description = $request->get('description', null);
        $store->image = $image;
        $store->user_id = $user->id;

        $store->save();
        $store = $store->fresh();

        return $this->generateResponse('Successfully created store.', 200, $store);
    }

    public function self(SelfRequest $request)
    {
        $user = $request->user();

        $stores = new Store;

        $stores = $stores->where('user_id', $user->id);
        $stores = $stores->get();

        return $this->generateResponse('Successfully fetched stores.', 200, $stores);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show(Store $store)
    {
        $store->load(['user']);
        return $this->generateResponse('Successfully fetched record.', 200, $store);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        // $image = null;

        // if ($request->hasFile('image')) {
        //     $image = $this->uploadable($request->file('image'), $store);
        // }

        // $store->name = $request->get('name', null);
        // $store->slug = \Str::slug($request->get('name', null), '_');
        // $store->description = $request->get('description', null);
        // $store->image = $image;
        // $store->user_id = $user->id;

        // $store->save();
        // $store = $store->fresh();

        return $this->generateResponse('Successfully updated record.', 200, $store);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        //
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
