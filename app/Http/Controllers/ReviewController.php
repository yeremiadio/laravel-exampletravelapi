<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Review::query();
            $id = $request->input('id');
            $user_id = $request->input('user_id');
            $rating = $request->input('rating');
            if ($id) {
                $query->where('id', $id);
            }
            if ($user_id) {
                $query->where('user_id', $user_id);
            }
            if ($rating) {
                $query->where('rating', '>=', $rating);
            }

            $data = $query->get();

            return $this->responseSuccess('Reviews by Authenticated User', $data, 200);
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
    public function store(Request $request, $destinationId)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'description' => 'required|string',
            'rating' => 'nullable|min:0|max:5|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }
        $destination = Destination::where('id', $destinationId)->first();
        if (!$destination) {
            return $this->responseFailed('Data not found', null, 404);
        }

        try {
            $review = new Review();
            $review->description = $request->input('description');
            $review->rating = $request->input('rating');
            $review->user_id = Auth::id();

            $destination->reviews()->save($review);

            return $this->responseSuccess('Review added successfully', $review, 201);
        } catch (\Exception $e) {
            return $this->responseFailed('Internal Server Error', $e, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $review = Review::where('id', $id)->first();
        if (!$review) return $this->responseFailed('Data not found', '', 404);

        return $this->responseSuccess('Review detail', $review);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $userId = Auth::id(); // Get the authenticated user's ID
        $review = Review::where([
            'id' => $id,
            'user_id' => $userId
        ])->first();
        if (!$review) return $this->responseFailed('User not allowed to review', '', 422);
        $validator = Validator::make($input, [
            'description' => 'required|string',
            'rating' => 'nullable|numeric|min:0|max:5'
        ]);
        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }
        try {
            $review->update([
                'description' => $input['description'],
                'rating' => $request->input('rating') ?? null
            ]);

            return $this->responseSuccess('Review updated successfully', $review, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Internal Server Error', $e, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $review = Review::where('id', $id)->first();
        if (!$review) return $this->responseFailed('Data not found', '', 404);

        try {
            $review->delete();
            return $this->responseSuccess('Data has been deleted');
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', '', 500);
        }
    }
}
