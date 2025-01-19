<?php


$curl = curl_init ();

curl_setopt_array ( $curl, array(
    CURLOPT_URL => 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/5NPE24AFXFH183476?format=json',
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


$r = json_decode ($response,true );

$clear_data = [];

foreach ($r['Results'] as $record )
{
    if (!empty ($record['Value']))
    {
        $clear_data [$record['Variable']] = $record['Value'];
    }
}

$add_car_info ['brand'] = $clear_data ['Make'];
$add_car_info ['model'] = $clear_data ['Model'];
$add_car_info ['year'] = $clear_data ['Model Year'];

//return $add_car_info;

echo $response;
