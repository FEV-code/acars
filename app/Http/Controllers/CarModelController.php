<?php

namespace App\Http\Controllers;

use App\Models\CarModel;

use Illuminate\Http\Request;

class CarModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ()
    {
        return CarModel::with ( 'carBrand' )->get ();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store ( Request $request )
    {
        if ( !$request->isJson () ) {
            return response ()->json ( [ 'error' => 'Invalid format Input. Use JSON format.' ], 400 );
        }

        $validated = $request->validate ( [
            'name' => 'required|string|max:50|unique:car_models',
            'car_brand_id' => 'required|numeric|exists:car_brands,id',
        ] );

        return CarModel::create ( $validated );
    }

    /**
     * Display the specified resource.
     */
    public function show ( Request $request, $id )
    {
        if ( !preg_match ( '/^[0-9]+$/', $id ) ) {
            return response ()->json ( [ 'error' => 'Invalid ID format' ], 400 );
        }

        $carModel = CarModel::with ( 'carBrand' )->find ( $id );

        if ( !$carModel ) {
            return response ()->json ( [ 'message' => 'Data not found' ], 404 );
        } else {
            return response ()->json ( $carModel );

        }
    }

}
