<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'color',
        'vin',
        'brand',
        'model',
        'year',
    ];

    public function getCarData ( $vin )
    {
//        $vin = '5NPE24AFXFH183476';
//        $vin = '1FMCU9J94FUA44289';

        $curl = curl_init ();

        curl_setopt_array ( $curl, array(
            CURLOPT_URL => 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/' . $vin . '?format=json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ) );

        $response = curl_exec ( $curl );
        curl_close ( $curl );

        $data = json_decode ( $response, true );

        $clear_data = [];

        foreach ( $data['Results'] as $record ) {
            if ( !empty ( $record['Value'] ) ) {
                $clear_data [$record['Variable']] = $record['Value'];
            }
        }

        $add_car_info ['brand'] = array_key_exists ( 'Make', $clear_data ) ? $clear_data ['Make'] : '';
        $add_car_info ['model'] = array_key_exists ( 'Model', $clear_data ) ? $clear_data ['Model'] : '';
        $add_car_info ['year'] = array_key_exists ( 'Model Year', $clear_data ) ? $clear_data ['Model Year'] : '';

        return $add_car_info;
    }

}
