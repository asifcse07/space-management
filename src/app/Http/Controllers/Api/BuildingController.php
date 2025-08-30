<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller {
    public function index() { return Building::with('floors.areas')->paginate(20); }
    public function store(Request $r) {
        $data = $r->validate(['name'=>'required','code'=>'required|unique:buildings,code','address'=>'nullable']);
        return Building::create($data);
    }
    public function show(Building $building) { return $building->load('floors.areas'); }
    public function update(Request $r, Building $building) {
        $data = $r->validate(['name'=>'required','code'=>'required|unique:buildings,code,'.$building->id,'address'=>'nullable']);
        $building->update($data); return $building;
    }
    public function destroy(Building $building) { $building->delete(); return response()->noContent(); }
}
