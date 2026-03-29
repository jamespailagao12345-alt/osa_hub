<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressManagementController extends Controller
{
    /**
     * Display address management dashboard
     */
    public function index()
    {
        $provinces = Province::withCount(['cities' => function($query) {
            $query->withCount('barangays');
        }])->orderBy('name')->get();
        
        // Get user addresses from addresses table
        $userAddresses = \App\Models\Address::where('addressable_type', \App\Models\User::class)
            ->with(['addressable' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'email');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'addresses_page');
        
        return view('admin.addresses.index', compact('provinces', 'userAddresses'));
    }

    /**
     * Show provinces management
     */
    public function provinces()
    {
        $provinces = Province::withCount('cities')->orderBy('name')->paginate(20);
        return view('admin.addresses.provinces', compact('provinces'));
    }

    /**
     * Store a new province
     */
    public function storeProvince(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:provinces,code',
            'name' => 'required|string|max:255|unique:provinces,name',
        ]);

        Province::create($validated);

        return redirect()->route('admin.addresses.provinces')
            ->with('success', 'Province created successfully.');
    }

    /**
     * Update a province
     */
    public function updateProvince(Request $request, Province $province)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:provinces,code,' . $province->id,
            'name' => 'required|string|max:255|unique:provinces,name,' . $province->id,
        ]);

        try {
            $province->update($validated);

            return redirect()->route('admin.addresses.provinces')
                ->with('success', 'Province "' . $province->name . '" updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update province: ' . $e->getMessage());
        }
    }

    /**
     * Delete a province
     */
    public function destroyProvince(Province $province)
    {
        if ($province->cities()->count() > 0) {
            return redirect()->route('admin.addresses.provinces')
                ->with('error', 'Cannot delete province. It has ' . $province->cities()->count() . ' city/cities.');
        }

        $province->delete();

        return redirect()->route('admin.addresses.provinces')
            ->with('success', 'Province deleted successfully.');
    }

    /**
     * Show cities for a province
     */
    public function cities(Province $province)
    {
        $cities = City::where('province_id', $province->id)
            ->withCount('barangays')
            ->orderBy('name')
            ->paginate(20);
        
        return view('admin.addresses.cities', compact('province', 'cities'));
    }

    /**
     * Store a new city
     */
    public function storeCity(Request $request, Province $province)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zip_code' => 'nullable|string|max:10',
        ]);

        // Check for duplicate city name in same province
        $exists = City::where('province_id', $province->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'City already exists in this province.'])->withInput();
        }

        City::create([
            'province_id' => $province->id,
            'name' => $validated['name'],
            'zip_code' => $validated['zip_code'],
        ]);

        return redirect()->route('admin.addresses.cities', $province)
            ->with('success', 'City created successfully.');
    }

    /**
     * Update a city
     */
    public function updateCity(Request $request, Province $province, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zip_code' => 'nullable|string|max:10',
        ]);

        // Check for duplicate city name in same province (excluding current city)
        $exists = City::where('province_id', $province->id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $city->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'City already exists in this province.'])->withInput();
        }

        $city->update($validated);

        return redirect()->route('admin.addresses.cities', $province)
            ->with('success', 'City updated successfully.');
    }

    /**
     * Delete a city
     */
    public function destroyCity(Province $province, City $city)
    {
        if ($city->barangays()->count() > 0) {
            return redirect()->route('admin.addresses.cities', $province)
                ->with('error', 'Cannot delete city. It has ' . $city->barangays()->count() . ' barangay/barangays.');
        }

        $city->delete();

        return redirect()->route('admin.addresses.cities', $province)
            ->with('success', 'City deleted successfully.');
    }

    /**
     * Show barangays for a city
     */
    public function barangays(Province $province, City $city)
    {
        $barangays = Barangay::where('city_id', $city->id)
            ->orderBy('name')
            ->paginate(20);
        
        return view('admin.addresses.barangays', compact('province', 'city', 'barangays'));
    }

    /**
     * Store a new barangay
     */
    public function storeBarangay(Request $request, Province $province, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for duplicate barangay name in same city
        $exists = Barangay::where('city_id', $city->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Barangay already exists in this city.'])->withInput();
        }

        Barangay::create([
            'city_id' => $city->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.addresses.barangays', [$province, $city])
            ->with('success', 'Barangay created successfully.');
    }

    /**
     * Update a barangay
     */
    public function updateBarangay(Request $request, Province $province, City $city, Barangay $barangay)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check for duplicate barangay name in same city (excluding current barangay)
        $exists = Barangay::where('city_id', $city->id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $barangay->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Barangay already exists in this city.'])->withInput();
        }

        $barangay->update($validated);

        return redirect()->route('admin.addresses.barangays', [$province, $city])
            ->with('success', 'Barangay updated successfully.');
    }

    /**
     * Delete a barangay
     */
    public function destroyBarangay(Province $province, City $city, Barangay $barangay)
    {
        $barangay->delete();

        return redirect()->route('admin.addresses.barangays', [$province, $city])
            ->with('success', 'Barangay deleted successfully.');
    }
}
