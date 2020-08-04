<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BoardTrello;
use GuzzleHttp\Client;
class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $boards = BoardTrello::all();
            return response()->json($boards);
        }catch(\Exception $e){
            \Log::info($e);
            return response()->json(['status'=>'error','message'=>'Get boards Failed!']);
        }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            //Check duplicate url
            $check = BoardTrello::where('url',$request->url)->count();
            if($check > 0){
                return response()->json(['status'=>'warning','message'=>'This Url has Existed!']);
            }
            //Find idBoard Trello
            $url = $request->url;
            $str_rev = strrev($url);
            $pos = strpos($url, '/');
            $new_string =  strrev(substr($str_rev,$pos+2))."reports.json";

            $client = new Client;
            $response = $client->request('GET',$new_string."?key="."054d67263f0716f7d49178686fc67888&token="."1a14e59b944dafc50f9ac3e1b6d78090ce3443c258df51272708e85799538b5a");
            $body = (string)$response->getBody();

            $board_info = json_decode($body);

            //Save info to database
            $board_arr = [
                'id_board' => $board_info->id,
                'name' => $board_info->name,
                'url' => $url
            ];

            $board = BoardTrello::create($board_arr);
            // return $pos;
            return response()->json(['status'=>'Success','board'=>$board]);
        }
        catch(\Exception $e){
            \Log::info($e);
            return response()->json(['status'=>'error','message'=>'Falied!']);
        }
            
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
