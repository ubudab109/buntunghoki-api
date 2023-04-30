<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemberController extends BaseController
{
    public function memberBank()
    {
        $data = DB::table('member_banks')
        ->select('member_banks.*', 'bp.name as bank_name')
        ->where('member_banks.member_id', Auth::user()->id)
        ->join('payment_types as pt' ,'member_banks.payment_type_id', 'pt.id')
        ->join('bank_payments as bp', 'member_banks.bank_payment_id', 'bp.id')
        ->paginate('15');
        $paginate = new PaginationResource($data);
        return $this->sendResponse($paginate, 'Fetched');
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_type_id' => 'required',
            'bank_payment_id' => 'required',
            'account_number'  => 'required',
        ]);

        $member = DB::table('members')->where('id', Auth::user()->id)->first();

        DB::table('member_banks')->insert([
            'member_id'       => $member->id,
            'payment_type_id' => $request->payment_type_id,
            'bank_payment_id' => $request->bank_payment_id,
            'account_name'    => $member->fullname,
            'account_number'  => $request->account_number
        ]);

        return $this->sendResponse(array('success' => 1), 'Created successfully');

    }
}
