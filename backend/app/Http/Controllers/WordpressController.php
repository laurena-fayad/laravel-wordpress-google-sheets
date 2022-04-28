<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheet;

use Http;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;


class WordpressController extends Controller
{
    public function getPluginDetails(){

        $spreadSheetId =  env(key: 'GOOGLE_SHEET_ID');
    
        function getDimensions($spreadSheetId){
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('credentials.json'));
            $client->addScope("https://www.googleapis.com/auth/spreadsheets");    
            $googleSheetService = new Google_Service_Sheets($client);
            $rowDimensions = $googleSheetService->spreadsheets_values->batchGet(
                $spreadSheetId,
                ['ranges' => 'Datasheet!A:A', 'majorDimension' => 'COLUMNS']
            );

        //if data is present at nth row, it will return array till nth row
        //if all column values are empty, it returns null
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (!$rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Datasheet!1:1', 'majorDimension' => 'ROWS']
        );

        //if data is present at nth col, it will return array till nth col
        //if all column values are empty, it returns null
        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (!$colMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        return [
            'error' => false,
            'rowCount' => count($rowMeta[0]),
            'colCount' => colLengthToColumnAddress(count($colMeta[0]))
        ];
    }

    function colLengthToColumnAddress($number) {
        if ($number <= 0) return null;

        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }

    function saveDataToSheet(array $data){
        $spreadSheetId =  env(key: 'GOOGLE_SHEET_ID');
        $dimensions = getDimensions($spreadSheetId);

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->addScope("https://www.googleapis.com/auth/spreadsheets");    
        $googleSheetService = new Google_Service_Sheets($client);

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $data
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
        ];

        $range = "A" . ($dimensions['rowCount'] + 1);

        return $googleSheetService
            ->spreadsheets_values
            ->update($spreadSheetId, $range, $body, $params);
    }

    $plugin_info = Http::get('https://api.wordpress.org/plugins/info/1.0/simply-schedule-appointments.json');
    $json_result = json_decode($plugin_info);
    $installations = $json_result->downloaded;
    $version = $json_result->version;
    $ratings = $json_result->ratings;
    $five_star_ratings = $ratings->{5};
    $other_ratings = $ratings->{4} + $ratings->{3} + $ratings->{2} + $ratings->{1};
    $date = date('Y-m-d');

    $values = [
        [$date, $version, $installations, $five_star_ratings, $other_ratings]
    ];

    saveDataToSheet ($values);
    
    // return [$five_star_ratings,$other_ratings,$installations,$version, $date];





}
}