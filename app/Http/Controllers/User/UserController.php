<?php

namespace App\Http\Controllers\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Helpers\FileUploadHelper;

class UserController extends Controller
{
    public function getUser(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function updateUser(UpdateUserRequest $request): UserResource
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            $validated['profile_image_url'] = FileUploadHelper::uploadImage(
                $request->file('profile_image'),
                'profile_images'
            );
        }

        $user->update($validated);

        return new UserResource($user);
    }
}
