<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Info;
use App\User;
use Illuminate\Support\Facades\Validator;


class InfoController extends Controller
{

    /**
     * Create a new Info.
     *
     * @return json
     */
    public function insertInfo(Request $request)
    {
        //Log::info('Creating new Info: ' . $request);

        $validator = Validator::make($request->all(), [
                 'country'  => 'required|string',
                 'passport_image' => 'required|mimes:jpeg,pdf',
                 'driving_license' => 'required|mimes:jpeg,pdf',
                 'bank_statement_image' => 'required|mimes:jpeg,pdf',
                 'formation_doc' => 'mimes:jpeg,pdf',
                 'mandatory_request' => 'required|mimes:jpeg,pdf',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        
        $info = new Info();

        $info->country = $request->input('country');
        $info->passport_image_path = self::uploadImage($request->file('passport_image'));
        $info->driving_license_path = self::uploadImage($request->file('driving_license'));
        $info->bank_statement_path = self::uploadImage($request->file('bank_statement_image'));
        $info->formation_doc_path = self::uploadImage($request->file('formation_doc'));
        $info->mandatory_request_path = self::uploadImage($request->file('mandatory_request'));
        // $info->person_image_path = self::uploadImage($request->file('person_image'));

        $info->save();

        if($request->wantsJson()) {
             return response()->json($info, 201);
         }

        Auth::user()->update(['flag' => '1', 'info_id' => $info->id]);

        return response(view('kyc.conformation'));
    }

    private function uploadImage($image)
    {
        if($image) {
            $filename = uniqid() . $image->getClientOriginalName();

            $path = "info/";
            $imagePath = $path . $filename;

            Storage::disk('local')->put("public/" . $imagePath, file_get_contents($image));

            return $imagePath;
        }
        
        return null;
    }

    /**
     * Get Info.
     *
     * @return Info
     */
    public function getInfo(Info $info)
    {
        $info->passport_image_path = Storage::url($info->passport_image_path);

        return $info;
    }

    /**
     * Get Info.
     *
     * @return Info
     */
    public function getUnverifiedInfo(Info $info)
    {
        $info = Info::where('flag', '=', 1)->get();

        return $info;
    }

    /**
     * Update Info.
     *
     * @return Info
     */
    public function updateInfo(Request $request, Info $info)
    {
        $info->update($request->all());

        return response()->json($info, 200);
    }

    /**
     * Delete Info.
     *
     * @return json
     */
    public function deleteInfo(Info $info)
    {
        $info->delete();

        return response()->json(null, 204);
    }
}
