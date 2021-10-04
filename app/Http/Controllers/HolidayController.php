<?php

namespace App\Http\Controllers;

use App\Holiday;
use App\Http\Requests\HolidayRequest;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(){
        return view('pages.holidays.index');
    }

    public function getHolidayList(Request $request){
        return response()->json(Holiday::search($request->search));
    }
    
    public function edit(Holiday $holiday){
        return response()->json($holiday);

    }

    public function update(Holiday $holiday, HolidayRequest $request){
        $holiday->update([
                'name' => $request->name,
                'date' => $request->date,
                'office_id' => $request->office_id
        ]);

        return response()->json(['msg' => 'Holiday Successfully Updates',200]);
    }

    public function createHoliday(HolidayRequest $request){
        // dd($request);
        Holiday::create($request->all());

        return response()->json(['msg' => 'Holiday Successfully Created']);
    }

    public function delete(Holiday $holiday){
        $holiday->delete();

        return response()->json(['msg' => 'Holiday has been deleted.'], 200);
    }


}
