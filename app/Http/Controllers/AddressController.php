<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class AddressController extends Controller
{
    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $provinces = Province::orderBy('name')->get(['id', 'code', 'name']);
        
        // Format for frontend compatibility
        return response()->json($provinces->map(function($province) {
            return [
                'id' => $province->id,
                'code' => $province->code,
                'name' => $province->name,
            ];
        }));
    }

    /**
     * Get address from addresses table by user ID
     * This helps fetch existing addresses stored in the addresses table
     */
    public function getAddressByUser(Request $request)
    {
        $userId = $request->input('user_id');
        $type = $request->input('type', 'home');
        
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        $address = \App\Models\Address::where('addressable_type', \App\Models\User::class)
            ->where('addressable_id', $userId)
            ->where('type', $type)
            ->first();
        
        if (!$address) {
            return response()->json([
                'province' => '',
                'city_municipality' => '',
                'barangay' => '',
                'street' => '',
                'zip_code' => '',
            ]);
        }
        
        return response()->json([
            'province' => $address->province ?? '',
            'city_municipality' => $address->city_municipality ?? '',
            'barangay' => $address->barangay ?? '',
            'street' => $address->street ?? '',
            'zip_code' => $address->zip_code ?? '',
        ]);
    }

    /**
     * Get cities/municipalities by province
     */
    public function getCities(Request $request)
    {
        $provinceCode = $request->input('province');
        
        if (!$provinceCode) {
            return response()->json([]);
        }
        
        // Find province by code or name (for backward compatibility with addresses table)
        $province = Province::where('code', $provinceCode)
            ->orWhere('name', $provinceCode)
            ->first();
        
        if (!$province) {
            return response()->json([]);
        }
        
        $cities = City::where('province_id', $province->id)
            ->orderBy('name')
            ->get(['id', 'name', 'zip_code']);
        
        return response()->json($cities->map(function($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'zip_code' => $city->zip_code,
            ];
        }));
    }

    /**
     * Get barangays by city/municipality
     */
    public function getBarangays(Request $request)
    {
        $cityName = $request->input('city');
        $provinceCode = $request->input('province');
        
        if (!$cityName) {
            return response()->json([]);
        }
        
        // Find province by code or name (for backward compatibility with addresses table)
        $province = Province::where('code', $provinceCode)
            ->orWhere('name', $provinceCode)
            ->first();
        
        if (!$province) {
            return response()->json([]);
        }
        
        // Find city by name (case-insensitive for better matching)
        $city = City::where('province_id', $province->id)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($cityName))])
            ->first();
        
        if (!$city) {
            return response()->json([]);
        }
        
        $barangays = Barangay::where('city_id', $city->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($barangays->map(function($barangay) {
            return [
                'id' => $barangay->id,
                'name' => $barangay->name,
            ];
        }));
    }

    /**
     * Get zip code by city
     */
    public function getZipCode(Request $request)
    {
        $cityName = $request->input('city');
        $provinceCode = $request->input('province');
        
        if (!$cityName || !$provinceCode) {
            return response()->json(['zip_code' => '']);
        }
        
        // Find province by code or name (for backward compatibility with addresses table)
        $province = Province::where('code', $provinceCode)
            ->orWhere('name', $provinceCode)
            ->first();
        
        if (!$province) {
            return response()->json(['zip_code' => '']);
        }
        
        // Find city by name (case-insensitive for better matching)
        $city = City::where('province_id', $province->id)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($cityName))])
            ->first();
        
        if ($city && $city->zip_code) {
            return response()->json(['zip_code' => $city->zip_code]);
        }
        
        return response()->json(['zip_code' => '']);
    }
}
