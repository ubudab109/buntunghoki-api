<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DatasetController extends BaseController
{
    public function adminBank(Request $request)
    {
        $firstData = DB::table('user_banks')
        ->select('user_banks.*', 'by.name as bank_name', 'by.logo as logo', 'by.status as bank_status', 'pt.name as payment_type_name')
        ->join('bank_payments as by', 'user_banks.bank_payment_id', 'by.id')
        ->join('payment_types as pt', 'user_banks.payment_type_id', 'pt.id')
        ->where('by.name', 'LIKE', '%'. $request->name . '%')
        ->get()->toArray();

        $dataSecond = DB::table('user_banks')
        ->select('user_banks.*', 'by.name as bank_name', 'by.logo as logo', 'by.status as bank_status', 'pt.name as payment_type_name')
        ->join('bank_payments as by', 'user_banks.bank_payment_id', 'by.id')
        ->join('payment_types as pt', 'user_banks.payment_type_id', 'pt.id')
        ->get()->toArray();

        if (count($firstData) > 0) {
            $data = array_merge($firstData, $dataSecond);
        } else {
            $data = $dataSecond;
        }

        return $this->sendResponse($data, 'Fetched Successfully');
    }

    public function paymentType()
    {
        $data = DB::table('payment_types')->get();
        return $this->sendResponse($data, 'Fetched Successfully');
    }

    public function getBankPayment(Request $request)
    {
        $data = DB::table('bank_payments')
        ->when($request->has('payment_type') && $request->payment_type != null, function ($query) use ($request) {
            $query->where('payment_type_id', $request->payment_type);
        })
        ->get();
        return $this->sendResponse($data, 'Fetched');
    }

    public function userBank(Request $request)
    {
        $userBank = DB::table('member_banks')
        ->select('member_banks.*', 'pt.*', 'bp.name as bank_name')
        ->where('member_id', Auth::user()->id)
        ->join('payment_types as pt', 'member_banks.payment_type_id', 'pt.id')
        ->join('bank_payments as bp', 'member_banks.bank_payment_id', 'bp.id')
        ->when($request->has('payment_type') && $request->payment_type != '', function ($query) use ($request) {
            $query->where('pt.name', $request->payment_type);
        })
        ->get();

        return $this->sendResponse($userBank, 'Fetched Successfully');
    }
}
