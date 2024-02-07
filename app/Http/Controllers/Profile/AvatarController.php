<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Str;


class AvatarController extends Controller
{
    public function update(UpdateAvatarRequest $request)
    {
        // $path = $request->file('avatar')->store('avatars', 'public');
        // Below should be more stable using the Storage facade
        $path = Storage::disk('public')->put('avatars', $request->file('avatar'));

        $oldAvatar = $request->user()->avatar;
        if ($oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        auth()->user()->update(['avatar' =>  $path]);

        return redirect(route('profile.edit'))->with('message', 'Avatar is updated');
    }

    function generate(Request $request)
    {

        $result = OpenAI::images()->create([
            "prompt" => "Create a funny animated user avatar",
            "n" => 1,
            "size" => "256x256"
        ]);

        $imageUrl = $result->data[0]->url;

        $contents = file_get_contents($imageUrl);

        $filename = Str::random(25);

        $path =  Storage::disk('public')->put("avatars/$filename.jpg", $contents);

        $oldAvatar = $request->user()->avatar;
        if ($oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }
        auth()->user()->update(['avatar' =>  "avatars/$filename.jpg"]);


        return redirect(route('profile.edit'))->with('message', 'Avatar is updated');
    }
}
