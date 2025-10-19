<?php

namespace App\Http\Controllers\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Property\Property;
use App\Http\Requests\V1\Property\CreatePropertyRequest;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $query = Property::with(['media', 'ameneties'])
                ->where('owner_id', $user->id);

            $query = $this->applyFilters($query, $request);

            $query->orderBy('created_at', 'desc');
            $perPage = $request->get('per_page', 3);
            $properties = $query->paginate($perPage);

            return response()->json([
                'message' => 'Properties fetched successfully.',
                'data' => $properties,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch properties.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply filters to the property query.
     */
    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            if (strtolower($request->status) === 'active') {
                $query->where('is_active', true);
            } elseif (strtolower($request->status) === 'inactive') {
                $query->where('is_active', false);
            }
        }
        return $query;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePropertyRequest $request)
    {
        $validated = $request->validated();

        $userId = $request->user()->id;
        $validated['owner_id'] = $userId;
        $validated['added_by'] = $userId;
        $validated['is_active'] = true;
        $validated['is_featured'] = false;

        DB::beginTransaction();

        try {

            $property = Property::create($validated);
            if($request->amenities)
            {
                $property->ameneties()->create([
                    'amenity_names' => json_encode($request->amenities)
                ]);
            }
            if ($request->hasFile('property_images')) {
                $files = $request->file('property_images');
                $imageUrls = [];
                foreach ($files as $index => $file) {
                    $url = FileUploadHelper::uploadImage($file, 'property_images');
                    $imageUrls[] = $url;
                    $property->media()->create([
                        'media_url'  => $url,
                        'media_type' => 'image',
                        'is_primary' => $index === 0,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Property created successfully.',
                'data' => $property->load('media'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create property.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        return response()->json([
            'message' => 'Property fetched successfully.',
            'data' => $property->load(['media', 'ameneties']),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreatePropertyRequest $request, Property $property)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $property->update($validated);

            if($request->amenities)
            {
                if($property->amenities)
                {
                    $property->ameneties()->update([
                        'amenity_names' => json_encode($request->amenities)
                    ]);
                } else {
                    $property->ameneties()->create([
                        'amenity_names' => json_encode($request->amenities)
                    ]);
                }
            }

            if ($request->hasFile('property_images')) {
                $files = $request->file('property_images');
                $imageUrls = [];
                foreach ($files as $index => $file) {
                    $url = FileUploadHelper::uploadImage($file, 'property_images');
                    $imageUrls[] = $url;
                    $property->media()->create([
                        'media_url'  => $url,
                        'media_type' => 'image',
                        'is_primary' => false,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Property updated successfully.',
                'data' => $property->load('media'),
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update property.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        try {
            $property->delete();

            return response()->json([
                'message' => 'Property deleted successfully.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete property.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function featuredProperties(Request $request)
    {
        try {
            $user = $request->user();
            $propertyType = $request->route('property_type'); 
            if($user)
            {
                $query = Property::with(['media', 'ameneties'])
                    ->where('owner_id', $user->id)
                    ->where('is_featured', true);
            }
            else
            {
                $query = Property::with(['media', 'ameneties'])
                ->where('is_featured', true);
            }

            // Filter by property_type if provided and valid
            $query->when(
                $propertyType && in_array($propertyType, Property::PROPERTY_TYPES),
                function ($q) use ($propertyType) {
                    $q->where('property_type', $propertyType);
                }
            );

            $query->orderBy('created_at', 'desc');
            $properties = $query->take(3)->get();

            return response()->json([
                'message' => 'Featured properties fetched successfully.',
                'data' => $properties,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch featured properties.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProperty(Property $property)
    {
        return response()->json([
            'message' => 'Property fetched successfully.',
            'data' => $property->load(['media', 'ameneties']),
        ], 200);
    }


    
    public function searchProperties(Request $request)
    {
        $filters = $request->all();
        $query = Property::query();

        $this->applySearchFilters($query, $filters);

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $properties = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Properties fetched successfully',
            'total' => $properties->total(),
            'current_page' => $properties->currentPage(),
            'per_page' => $properties->perPage(),
            'last_page' => $properties->lastPage(),
            'data' => $properties->items(),
        ]);
    }

    /**
     * Apply dynamic filters based on user input.
     */
    private function applySearchFilters($query, array $filters)
    {
        $directFilters = [
            'property_type',
            'listing_type',
            'property_label',
            'city',
            'state',
            'zip_code',
            'location',
            'rooms',
            'bedrooms',
            'bathrooms',
            'garages',
            'year_built',
            'is_featured',
            'is_active',
        ];

        foreach ($directFilters as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        // Keyword search
        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('address', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        // Price range
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Size range
        if (isset($filters['min_size'])) {
            $query->where('size', '>=', $filters['min_size']);
        }
        if (isset($filters['max_size'])) {
            $query->where('size', '<=', $filters['max_size']);
        }

        // Land area range (optional)
        if (isset($filters['min_land_area'])) {
            $query->where('land_area', '>=', $filters['min_land_area']);
        }
        if (isset($filters['max_land_area'])) {
            $query->where('land_area', '<=', $filters['max_land_area']);
        }
    }
}
