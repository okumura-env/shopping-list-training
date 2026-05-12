<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return Item::orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        $item = Item::create($request->all());

        return response()->json($item, 201);
    }

    public function show(Item $item)
    {
        return $item;
    }

    public function update(Request $request, Item $item)
    {
        $item->update($request->all());

        return $item;
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->noContent();
    }
}
