<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Season;
use Illuminate\Http\JsonResponse;

class ApiDataController extends Controller
{
    public function divisions(string $season): JsonResponse
    {
        $seasonModel = Season::query()->where('slug', $season)->orWhere('year', $season)->firstOrFail();

        $divisions = Division::query()
            ->where('season_id', $seasonModel->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $divisions]);
    }

    public function clubs(Division $division): JsonResponse
    {
        $clubs = $division->clubs()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $clubs]);
    }
}
