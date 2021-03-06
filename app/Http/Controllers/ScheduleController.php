<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceModel;
use Illuminate\Http\Request;
use App\Models\ScheduleModel;
use App\Notifications\SendMail;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterScheduleRequest;
use App\Models\TimeScheduleModel;

class ScheduleController extends Controller
{
    private $schedule;
    private $service;
    private $user;
    private $time;
    public function __construct(ScheduleModel $schedule, ServiceModel $service, User $user, TimeScheduleModel $time)
    {
        $this->schedule = $schedule;
        $this->service = $service;
        $this->user = $user;
        $this->time = $time;
    }
    public function getRegisterSchedule()
    {
        $service = $this->service->all();
        return view('Schedule.register_schedual', compact('service'));
    }
    public function postRegisterSchedule(RegisterScheduleRequest $request)
    {
        $data = $this->schedule;
        $data->user_id = Auth::user()->id;
        $data->home_address = $request->home_address;
        $data->service_id = $request->service_id;
        $data->room_patients = $request->room_patients;
        $data->reason = $request->reason;
        $data->date = $request->date;
        $data->time_id = $request->time_id;
        $data->save();
        $user = $this->user::find(Auth::user()->id);
        $user->notify(new SendMail());
        return redirect('/user/get_register_schedule')->with('mail', 'Thông báo xác nhận lịch khám đã được gửi đến mail của bạn');
    }

    public function detail()
    {
        $data = $this->schedule->where('user_id', Auth::user()->id)->latest()->first();
        // dd($data);
        if ($data) {
            return view('Schedule.detail_schedual', compact('data'));
        }
        return back()->with('null', 'Bạn không có lịch khám nào');
    }

    public function list()
    {
        $userSchedules  = $this->schedule->where('user_id', Auth::user()->id)->latest()->get();
        return view('Schedule.list', compact('userSchedules'));
    }

    public function getDetail($id)
    {
        $rs['data'] = $this->schedule->find($id);
        $rs['service'] = $this->service->all();
        $rs['time'] = $this->time->all();
        return view('Schedule.edit', $rs);
    }

    public function postEdit( $id, Request $request)
    {
        $data = $this->schedule->find($id);
        // dd($data);
        $data->update($request->all());
        return redirect('/admin/appointments')->with('update', 'Chỉnh sửa thành công');
    }
}
