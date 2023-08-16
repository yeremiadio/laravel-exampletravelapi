<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $id = $request->input('id');
            $title = $request->input('title');
            $rating = $request->input('rating');
            $limitPage = $request->input('limit');

            $query = Destination::query();

            if ($id) {
                $query->where('id', $id);
            }
            if ($title) {
                $query->where('title', 'LIKE', "%$title%");
            }

            if ($rating) {
                $query->where('average_rating', '>=', $rating);
            }

            $perPage = 10; // Number of items per page
            $data = $query->with(['reviews'])->paginate($limitPage ?? $perPage);

            // Calculate the average rating for each destination
            $data->getCollection()->each(function ($destination) {
                $destination->updateRating();
            });

            return $this->responseSuccess('Successfully Get Destination', $data, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', $e, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        if ($request->hasFile('thumbnail')) {
            $uploadedFileUrl = $this->uploadFileImageKit('thumbnail');
        }

        try {
            $destination = new Destination([
                'title' => $input['title'],
                'description' => $input['description'],
                'thumbnail' => $uploadedFileUrl ?? null,
            ]);
            $destination->created_by = Auth::id();
            $destination->save();
            return $this->responseSuccess('Destination created Successfully', $destination, 201);
        } catch (\Exception $e) {
            return $this->responseFailed('Internal Server Error', $e, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $destination = Destination::where('id', $id)->with(['reviews'])->first();
        if (!$destination) return $this->responseFailed('Data not found', '', 404);

        // Calculate the average rating for each destination
        $destination->updateRating();

        return $this->responseSuccess('Destination detail', $destination);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destination $destination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $destination = Destination::where([
            'id' => $id,
            'created_by' => Auth::id()
        ])->first();
        if (!$destination) return $this->responseFailed('Data not found', '', 404);
        $validator = Validator::make($input, [
            'title' => 'required|string',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }
        $oldThubmanil = $destination->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $input['thumbnail'] =  $this->uploadFileImageKit('thumbnail');
        } else {
            $input['thumbnail'] = $oldThubmanil;
        }
        try {
            $destination->title = $input['title'];
            $destination->description = $input['description'];
            $destination->thumbnail = $input['thumbnail'];
            $destination->update();

            return $this->responseSuccess('Destination updated successfully', $destination, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Internal Server Error', $e, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $destination = Destination::where('id', $id)->first();
        if (!$destination) return $this->responseFailed('Data not found', '', 404);
        try {
            $destination->delete();
            return $this->responseSuccess('Data has been deleted');
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', $e, 500);
        }
    }
}
