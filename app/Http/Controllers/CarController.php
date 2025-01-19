<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CarExport;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ( Request $request )
    {

        if ( !preg_match ( "#application/json#", $request->header ( 'Accept' ) ) ) {
            return response ()->json ( [ 'error' => 'Use type application/json at your query (Header Accept).' ], 400 );
        }

        $validated = $request->validate ( [
            'page' => 'numeric|max_digits:3',
            'per_page' => 'numeric|max_digits:2',
            'sort_by' => 'string|max:6|in:name,number,color,vin,brand,model,year',
            'sort_order' => 'in:asc,desc',
            'search_field' => 'string|max:6|in:name,number,color,vin,brand,model,year',
            'search_value' => 'string|min:1|max:5',
            'brand' => 'string|min:1|max:50',
            'model' => 'string|min:1|max:50',
            'year' => 'numeric|digits:4',
        ],
            [
                'sort_by.in' => 'Parameter sort_by must be one from: name, number, color, vin, brand, model, year.',
                'sort_order.in' => 'Parameter sort_order must be one from: asc, desc.',
                'search_field.in' => 'Parameter search_field must be one from: name, number, color, vin, brand, model, year.',
            ] );

//        dd ( $request->all, $request->getContent (), $request->input (), $validated );

        $perPage = $request->query ( 'per_page', 5 );

        $sortBy = $request->query ( 'sort_by', 'id' );
        $sortOrder = $request->query ( 'sort_order', 'asc' );

        $whereArr = [];

        $searchField = $request->query ( 'search_field' );
        $searchValue = $request->query ( 'search_value' );

        if ( !empty( $searchField ) && !empty( $searchValue ) ) {
            $whereArr[] = [ $searchField, 'like', '%' . $searchValue . '%' ];
        }

        $filterBrand = $request->query ( 'brand' );
        $filterModel = $request->query ( 'model' );
        $filterYear = $request->query ( 'year' );

        if ( !empty( $filterBrand ) ) {
            $whereArr[] = [ 'brand', '=', $filterBrand ];
        }
        if ( !empty( $filterModel ) ) {
            $whereArr[] = [ 'model', '=', $filterModel ];
        }
        if ( !empty( $filterYear ) ) {
            $whereArr[] = [ 'year', '=', $filterYear ];
        }

//        return Car::where ( $searchField, 'like', '%' . $searchValue . '%' )->orderBy ( $sortBy, $sortOrder )->simplePaginate ( $perPage );
        return Car::select ( [ "id", "name", "number", "color", "vin", "brand", "model", "year" ] )
            ->where ( $whereArr )
            ->orderBy ( $sortBy, $sortOrder )
            ->simplePaginate ( $perPage );

    }

    /**
     * Store a newly created resource in storage.
     */

    public function store ( Request $request )
    {
        if ( !preg_match ( "#application/json#", $request->header ( 'Accept' ) ) ) {
            return response ()->json ( [ 'error' => 'Use type application/json at your query (Header Accept).' ], 400 );
        }

        if ( !$request->isJson () ) {
            return response ()->json ( [ 'error' => 'Invalid format Input. Use JSON format.' ], 400 );
        }

        $validated = $request->validate ( [
            'name' => 'required|string|max:50',
            'number' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'vin' => 'required|string|max:50',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|string|max:4',
        ] );

//        dd ( $request->all, $request->getContent (), $request->input (), $validated );

        $car = new Car();

        $addInfo = $car->getCarData ( $validated['vin'] );

        $validated ['brand'] = $addInfo['brand'];
        $validated ['model'] = $addInfo['model'];
        $validated ['year'] = $addInfo['year'];

        $data = Car::create ( $validated );

        return response ()->json ( $data, 201 );
    }

    /**
     * Display the specified resource.
     */
    public function show ( Request $request, string $id )
    {

        if ( !preg_match ( '/^[0-9]+$/', $id ) ) {
            return response ()->json ( [ 'error' => 'Invalid ID format' ], 400 );
        }

        $cars = Car::find ( $id );

        if ( !$cars ) {
            return response ()->json ( [ 'message' => 'Data not found' ], 404 );
        }

        return response ()->json ( $cars );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update ( Request $request, string $id )
    {

        if ( !preg_match ( '/^[0-9]+$/', $id ) ) {
            return response ()->json ( [ 'error' => 'Invalid ID format' ], 400 );
        }

        if ( !$request->isJson () ) {
            return response ()->json ( [ 'error' => 'Invalid format Input. Use JSON format.' ], 400 );
        }

        $validated = $request->validate ( [
            'name' => 'alpha_num|max:50',
            'number' => 'alpha_num|max:50',
            'color' => 'alpha_num|max:50',
            'vin' => 'alpha_num|max:50',
        ] );

//        dd ( $request->all, $request->getContent (), $request->input, $id, $validated );

        $cars = Car::find ( $id );

        if ( !$cars ) {
            return response ()->json ( [ 'message' => 'Data not found' ], 404 );
        }

        if ( !empty( $validated['vin'] ) ) {
            $car = new Car();
            $add_info = $car->getCarData ( $validated['vin'] );

            $validated ['brand'] = $add_info['brand'];
            $validated ['model'] = $add_info['model'];
            $validated ['year'] = $add_info['year'];
        }

        $cars->update ( $validated );

        return response ()->json ( $cars );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy ( string $id )
    {
        if ( !preg_match ( '/^[0-9]+$/', $id ) ) {
            return response ()->json ( [ 'error' => 'Invalid ID format' ], 400 );
        }

        $cars = Car::find ( $id );

        if ( !$cars ) {
            return response ()->json ( [ 'message' => 'Data not found' ], 404 );
        }

        $cars->delete ();

        return response ()->json ( [ 'message' => 'Car\'s data deleted successfully' ] );
    }

    public function export ( Request $request )
    {

        $validated = $request->validate ( [
            'sort_by' => 'string|max:6|in:name,number,color,vin,brand,model,year',
            'sort_order' => 'in:asc,desc',
            'search_field' => 'string|max:6|in:name,number,color,vin,brand,model,year',
            'search_value' => 'string|min:1|max:5',
            'brand' => 'string|min:1|max:50',
            'model' => 'string|min:1|max:50',
            'year' => 'numeric|digits:4',
        ],
            [
                'sort_by.in' => 'Parameter sort_by must be one from: name, number, color, vin, brand, model, year.',
                'sort_order.in' => 'Parameter sort_order must be one from: asc, desc.',
                'search_field.in' => 'Parameter search_field must be one from: name, number, color, vin, brand, model, year.',
            ] );

        $sortBy = $request->query ( 'sort_by', 'id' );
        $sortOrder = $request->query ( 'sort_order', 'asc' );

        $whereArr = [];

        $searchField = $request->query ( 'search_field' );
        $searchValue = $request->query ( 'search_value' );

        if ( !empty( $searchField ) && !empty( $searchValue ) ) {
            $whereArr[] = [ $searchField, 'like', '%' . $searchValue . '%' ];
        }

        $filterBrand = $request->query ( 'brand' );
        $filterModel = $request->query ( 'model' );
        $filterYear = $request->query ( 'year' );

        if ( !empty( $filterBrand ) ) {
            $whereArr[] = [ 'brand', '=', $filterBrand ];
        }
        if ( !empty( $filterModel ) ) {
            $whereArr[] = [ 'model', '=', $filterModel ];
        }
        if ( !empty( $filterYear ) ) {
            $whereArr[] = [ 'year', '=', $filterYear ];
        }

        $fileName = 'cars.xlsx';

        ( new CarExport( $sortBy, $sortOrder, $whereArr ) )->store ( $fileName );

        $filePath = storage_path ( '/app/private/' . $fileName );

        if ( file_exists ( $filePath ) ) {
            $fileContent = file_get_contents ( $filePath );
            $fileBase64Content = base64_encode ( $fileContent );

            return response ()->json ( [
                'file name' => $fileName,
                'base64 content' => $fileBase64Content
            ] );

//The response type by which the client receives the file upon request is not defined in TT!!!

//            return response()->download($filePath, $fileName);
        }
    }

}
