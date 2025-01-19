<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ()
    {
        return CarBrand::with ( 'carModels' )->get ();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store ( Request $request )
    {
        if ( !$request->isJson () ) {
            return response ()->json ( [ 'error' => 'Invalid format Input. Use JSON format.' ], 400 );
        }

        $request->validate ( [
            'name' => 'required|string|max:50|unique:car_brands'
        ] );

        return CarBrand::create ( $request->all () );
    }

    /**
     * Display the specified resource.
     */
    public function show ( Request $request, string $id )
    {
        if ( !preg_match ( '/^[0-9]+$/', $id ) ) {
            return response ()->json ( [ 'error' => 'Invalid ID format' ], 400 );
        }

        return CarBrand::with ( 'carModels' )->findOrFail ( $id );
    }

//This method expects input data as array in JSON format like:
//{
//    "BrandName1": [ "ModelName1","ModelName2", ... ],
//    "BrandName2": [ "ModelName1","ModelName2", ... ],
//    ...
//}

    public function update ( Request $request )
    {

        if ( !$request->isJson () ) {
            return response ()->json ( [ 'error' => 'Invalid format Input. Use JSON format.' ], 400 );
        }

        $addedBrands = [];
        $skippedBrands = [];

        $addedModels = [];
        $skippedModels = [];

        $data = $request->all ();

        $validator = Validator::make ( $data, [
            '*' => 'required|array',
            '*.*' => 'required|string|min:1|max:50',
        ] );

        if ( $validator->fails () ) {
            return response ()->json ( [
                'errors' => $validator->errors (),
            ], 422 );
        }

        foreach ( $data as $carBrandName => $carModels ) {
            if ( $responseBrand = CarBrand::select ( 'id' )->where ( 'name', '=', $carBrandName )->first () ) {
                $brandId = $responseBrand->getAttribute ( 'id' );
                $skippedBrands [] = $carBrandName;
            } else {
                CarBrand::create ( [ 'name' => $carBrandName ] );
                $addedBrands [] = $carBrandName;
            }

            foreach ( $carModels as $carModelName ) {
                if ( CarModel::select ( 'id' )->where ( 'name', '=', $carModelName )->first () ) {
                    $skippedModels [] = $carModelName;
                } else {
                    CarModel::create ( [ 'name' => $carModelName, 'car_brand_id' => $brandId ] );
                    $addedModels [] = $carModelName;
                }
            }
        }

        return response ()->json ( [
            'Added Brands' => $addedBrands,
            'Skipped Brands' => $skippedBrands,
            'Added Models' => $addedModels,
            'Skipped Models' => $skippedModels,
        ],
            200 );
    }

}
