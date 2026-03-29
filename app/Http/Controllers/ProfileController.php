<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Update user profile image
     * Synchronizes image across User, Student, and Staff tables
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        try {
            // Delete old image if exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Upload new image
            $image = $request->file('image');
            $imageName = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('profile_images', $imageName, 'public');

            // Update User table
            $user->image = $imagePath;
            $user->save();

            // Synchronize to Student table if user is a student (role = 1)
            if ($user->role == 1) {
                $student = \App\Models\Student::where('user_id', $user->id)->first();
                if ($student) {
                    // Note: Student table uses personal_data_sheet_image for profile images
                    // Delete old student image if exists
                    if ($student->personal_data_sheet_image && Storage::disk('public')->exists($student->personal_data_sheet_image)) {
                        Storage::disk('public')->delete($student->personal_data_sheet_image);
                    }
                    $student->personal_data_sheet_image = $imagePath;
                    $student->saveQuietly(); // Use saveQuietly to avoid triggering events
                }
            }

            // Synchronize to Staff table if user is staff (role = 2)
            if ($user->role == 2) {
                // Update Staff table by email match
                $staff = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                if ($staff) {
                    // Delete old staff image if exists
                    if ($staff->image && Storage::disk('public')->exists($staff->image)) {
                        Storage::disk('public')->delete($staff->image);
                    }
                    $staff->image = $imagePath;
                    $staff->saveQuietly();
                }

                // Update Staff table if exists (using user_id)
                $staffProfile = \App\Models\Staff::where('user_id', $user->id)->first();
                if ($staffProfile) {
                    // Delete old staff profile image if exists
                    if ($staffProfile->image && Storage::disk('public')->exists($staffProfile->image)) {
                        Storage::disk('public')->delete($staffProfile->image);
                    }
                    $staffProfile->image = $imagePath;
                    $staffProfile->saveQuietly();
                }
            }

            // Synchronize to Assistant/User table if user is assistant (role = 3)
            // Note: Assistants use the User table's image field, which is already updated above
            // But we can also update Staff if it exists for assistants
            if ($user->role == 3) {
                $staffProfile = \App\Models\Staff::where('user_id', $user->id)->first();
                if ($staffProfile) {
                    // Delete old staff profile image if exists
                    if ($staffProfile->image && Storage::disk('public')->exists($staffProfile->image)) {
                        Storage::disk('public')->delete($staffProfile->image);
                    }
                    $staffProfile->image = $imagePath;
                    $staffProfile->saveQuietly();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'image_url' => Storage::disk('public')->url($imagePath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user profile image
     */
    public function deleteImage(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        try {
            // Delete image from storage if exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Update User table
            $user->image = null;
            $user->save();

            // Synchronize to Student table if user is a student (role = 1)
            if ($user->role == 1) {
                $student = \App\Models\Student::where('user_id', $user->id)->first();
                if ($student) {
                    if ($student->personal_data_sheet_image && Storage::disk('public')->exists($student->personal_data_sheet_image)) {
                        Storage::disk('public')->delete($student->personal_data_sheet_image);
                    }
                    $student->personal_data_sheet_image = null;
                    $student->saveQuietly();
                }
            }

            // Synchronize to Staff table if user is staff (role = 2)
            if ($user->role == 2) {
                $staff = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                if ($staff) {
                    if ($staff->image && Storage::disk('public')->exists($staff->image)) {
                        Storage::disk('public')->delete($staff->image);
                    }
                    $staff->image = null;
                    $staff->saveQuietly();
                }

                $staffProfile = \App\Models\Staff::where('user_id', $user->id)->first();
                if ($staffProfile) {
                    if ($staffProfile->image && Storage::disk('public')->exists($staffProfile->image)) {
                        Storage::disk('public')->delete($staffProfile->image);
                    }
                    $staffProfile->image = null;
                    $staffProfile->saveQuietly();
                }
            }

            // Synchronize to Assistant/Staff if user is assistant (role = 3)
            if ($user->role == 3) {
                $staffProfile = \App\Models\Staff::where('user_id', $user->id)->first();
                if ($staffProfile) {
                    if ($staffProfile->image && Storage::disk('public')->exists($staffProfile->image)) {
                        Storage::disk('public')->delete($staffProfile->image);
                    }
                    $staffProfile->image = null;
                    $staffProfile->saveQuietly();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }
}

