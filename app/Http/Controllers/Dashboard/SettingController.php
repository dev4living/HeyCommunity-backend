<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\System;

class SettingController extends Controller
{
    /**
     *
     */
    public function getIndex()
    {
        return redirect('/dashboard/setting/system-info');
    }

    /**
     *
     */
    public function getSystemInfo()
    {
        $assign['system'] = System::findOrFail(1);
        return view('dashboard.setting.system-info', $assign);
    }

    /**
     *
     */
    public function getEditSystemInfo()
    {
        $assign['system'] = System::findOrFail(1);
        return view('dashboard.setting.edit-system-info', $assign);
    }

    /**
     *
     */
    public function postUpdateSystemInfo(Request $request)
    {
        $this->validate($request, [
            'community_name'         =>      'required|min:2',
        ]);

        $system = System::findOrFail(1);
        $system->community_name = $request->community_name;

        if ($system->save()) {
            return redirect('/dashboard/setting/system-info');
        } else {
            return back()->withInput();
        }
    }

    /**
     *
     */
    public function getWechatPa()
    {
        $assign['system'] = System::findOrFail(1);
        return view('dashboard.setting.wechat-pa', $assign);
    }

    /**
     *
     */
    public function getEditWechatPa()
    {
        $assign['system'] = System::findOrFail(1);
        return view('dashboard.setting.edit-wechat-pa', $assign);
    }

    /**
     *
     */
    public function postUpdateWechatPa(Request $request)
    {
        $this->validate($request, [
            'enable_wechat_pa'  =>  'required',
            'wx_app_id'         =>  'required_if:enable_wechat_pa,1|min:15',
            'wx_app_secret'     =>  'required_if:enable_wechat_pa,1|min:20',
            'wx_temp_notice_id' =>  'required_if:enable_wechat_pa,1|min:35',
            'wx_verify_file'    =>  'max:1'
        ]);

        $System = System::findOrFail(1);
        $System->enable_wechat_pa = $request->enable_wechat_pa;
        $System->wx_app_id = $request->wx_app_id;
        $System->wx_app_secret = $request->wx_app_secret;
        $System->wx_temp_notice_id = $request->wx_temp_notice_id;

        // save verify file
        if ($request->hasFile('wx_verify_file')) {
            $path = env('WECHAT_PA_VERIFY_FILE_PATH', base_path('../'));
            $file= $request->file('wx_verify_file');
            $file->move($path, $file->getClientOriginalName());
        }

        $System->save();
        return redirect('/dashboard/setting/wechat-pa');
    }

    /**
     *
     */
    public function getWechatNotice()
    {
        $assign['tenant'] = Auth::user()->user();
        return view('dashboard.setting.wechat-notice', $assign);
    }

    /**
     *
     */
    public function getEditWechatNotice()
    {
        $assign['tenant'] = Auth::user()->user();
        return view('dashboard.setting.edit-wechat-pa', $assign);
    }

    /**
     *
     */
    public function getAdministrator()
    {
        $assign['administrators'] = User::where(['is_admin' => 1])->get();
        return view('dashboard.setting.administrator', $assign);
    }

    /**
     *
     */
    public function getAddAdministrator()
    {
        return view('dashboard.setting.add-administrator');
    }

    /**
     *
     */
    public function anySearchAdministrator(Request $request)
    {
        $this->validate($request, [
            'search_key'        =>      'min:1',
        ]);

        $assign = [];
        if ($request->has('search_key')) {
            $request->flash();
            $nicknameStr = '%' . $request->search_key . '%';
            $assign['users'] = User::where(['id' => $request->search_key])->orWhere(['phone' => $request->search_key])->orWhere('nickname', 'like', $nicknameStr)->get();
        }
        return view('dashboard.setting.search-administrator', $assign);
    }

    /**
     *
     */
    public function postAddAdministrator(Request $request)
    {
        $this->validate($request, [
            'id'                =>      'required|min:1',
        ]);

        $User = User::findOrFail($request->id);
        $User->is_admin = true;
        $User->save();
        return redirect()->to('/dashboard/setting/administrator');
    }

    /**
     *
     */
    public function postDestroyAdministrator(Request $request)
    {
        $this->validate($request, [
            'id'                =>      'required|min:1',
        ]);

        $User = User::findOrFail($request->id);
        $User->is_admin = false;
        $User->save();
        return redirect()->to('/dashboard/setting/administrator');
    }
}
