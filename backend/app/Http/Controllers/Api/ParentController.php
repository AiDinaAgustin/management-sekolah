<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $query = ParentModel::query()
            ->select(['id', 'nama_ayah', 'nama_ibu'])
            ->orderBy('nama_ayah');

        if ($request->boolean('simple')) {
            return response()->json([
                'data' => $query->get()->map(function (ParentModel $parent) {
                    return [
                        'id' => $parent->id,
                        'label' => trim($parent->nama_ayah.' / '.$parent->nama_ibu),
                        'nama_ayah' => $parent->nama_ayah,
                        'nama_ibu' => $parent->nama_ibu,
                    ];
                }),
            ]);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
    public function store() { return response()->json(['message' => 'Not implemented'], 201); }
    public function show($id) { return response()->json(['id' => $id]); }
    public function update($id) { return response()->json(['message' => 'Not implemented', 'id' => $id]); }
    public function destroy($id) { return response()->json([], 204); }
}
