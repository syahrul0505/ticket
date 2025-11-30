<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;

class OtherSettingController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:other-setting', ['only' => ['getModal','update']]);
    }

    public function getModal()
    {
        $other_settings = OtherSetting::orderBy('id', 'ASC')->get()->first();

        if (!$other_settings) {
            $other_settings = [];
        }

        return View::make('admin.other-setting.modal')->with([
            'other_setting' => $other_settings
        ]);
    }

    public function update(Request $request, $otherSettingId)
    {
        $validate = $request->validate([
            'pb01'                  => 'nullable|integer|min:0|max:100|regex:/[0-9]/',
            'layanan'               => 'nullable|integer|min:0|max:100|regex:/[0-9]/',
            'name_brand'            => 'nullable',
            'address'               => 'nullable',
            'second_address'        => 'nullable',
            'name_footer'           => 'nullable',
            'name_footer_product'   => 'nullable',
            'time_start'            => 'nullable',
            'time_close'            => 'nullable',
            'regular_day_salary'    => 'nullable',
            'holiday_salary'        => 'nullable',
        ]);

        try {
            if ($otherSettingId == '0') {
                $other = new OtherSetting();
            } else {
                $other = OtherSetting::findorFail($otherSettingId);
            }

            $other->pb01                = (int) str_replace('.', '', $validate['pb01']);
            $other->layanan             = (int) str_replace('.', '', $validate['layanan']);
            $other->name_brand          = $validate['name_brand'];
            $other->address             = $validate['address'];
            $other->second_address      = $validate['second_address'];
            $other->name_footer         = $validate['name_footer'];
            $other->name_footer_product = $validate['name_footer_product'];
            $other->time_start          = $validate['time_start'];
            $other->time_close          = $validate['time_close'];
            $other->regular_day_salary  = $validate['regular_day_salary'];
            $other->holiday_salary      = $request->holiday_salary;

            // dd($request->all());
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $imageName = uniqid() . '' . time() . '.webp';

                // Resize and compres image
                $resizedImage = Image::make($image)
                    ->resize(90, 90, function ($constraint) {
                        $constraint->aspectRatio();
                    })
                    ->encode('webp', 80); // Kompresi kualitas 80%

                // Save iamge after resize, compres, and change format to webp format
                $resizedImage->save(public_path('images/products/' . $imageName));
                $other->logo = $imageName;
            }

            $other->save();

            $request->session()->flash('success', "Update data other setting successfully!");
            return redirect()->back();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $request->session()->flash('failed', "Failed to update data other setting!");
            return redirect()->back();
        }
    }
}
