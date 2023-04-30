<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{
    public function index(Request $request)
    {
        if ($request->type == 'bet') {
            $data = DB::table('turnover_members')
            ->where('member_id', Auth::user()->id)
            ->orderBy('id', 'desc')
            ->paginate(15);
        } else if ($request->type == 'transaction') {
            if ($request->transaction_type == 'deposit') {
                $data = DB::table('transaction')
                ->select('transaction.*', 'mb.account_name as account_name', 'mb.account_number as account_number', 'admin_bank.account_name as admin_account_name', 'admin_bank.account_number as admin_bank_account_number', 'abp.name as admin_bank_name', 'mbp.name as member_bank_name')
                ->where('type', 'deposit')
                ->where('transaction.member_id', Auth::user()->id)
                ->join('member_banks as mb', 'transaction.member_bank_id', 'mb.id')
                ->join('user_banks as admin_bank', 'transaction.admin_bank_id', 'admin_bank.id')
                ->leftJoin('bank_payments as abp', 'admin_bank.bank_payment_id', 'abp.id')
                ->leftJoin('bank_payments as mbp', 'mb.bank_payment_id', 'mbp.id')
                ->orderBy('id', 'desc')
                ->paginate('15');
            } else if ($request->transaction_type == 'withdraw') {
                $data = DB::table('transaction')
                ->select('transaction.*', 'mb.account_name as account_name', 'mb.account_number as account_number', 'mbp.name as member_bank_name')
                ->where('type', 'withdraw')
                ->where('transaction.member_id', Auth::user()->id)
                ->join('member_banks as mb', 'transaction.member_bank_id', 'mb.id')
                ->leftJoin('bank_payments as mbp', 'mb.bank_payment_id', 'mbp.id')
                ->orderBy('id', 'desc')
                ->paginate('15');
            } else {
                $data = DB::table('transaction')
                ->where('transaction.member_id', Auth::user()->id)
                ->select('transaction.*', 'mb.account_name as account_name', 'mb.account_number as account_number', 'mbp.name as member_bank_name')
                ->where('type', 'withdraw')
                ->where('transaction.member_id', Auth::user()->id)
                ->join('member_banks as mb', 'transaction.member_bank_id', 'mb.id')
                ->leftJoin('bank_payments as mbp', 'mb.bank_payment_id', 'mbp.id')
                ->when($request->has('transaction_type') && $request->transaction_type != '', function ($query) use ($request) {
                    $query->where('type', $request->type);
                })
                ->orderBy('id', 'desc')
                ->paginate('15');
            }
        } else {
            $data = null;
        }
        $paginate = new PaginationResource($data);
        return $this->sendResponse($paginate, 'Fetched Successfully');
    }

    public function instanDeposit(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'amount'   => 'required',
            'admin_bank_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Erros', $validator->errors());
        }

        $checkExists = DB::table('transaction')->where('member_id', Auth::user()->id)
        ->where('type', 'deposit')
        ->where('status', 0)
        ->exists();

        if ($checkExists) {
            return $this->sendBadRequest('Failed', 'Terdapat permintaan deposit yang masih dalam antrian');
        }

        DB::beginTransaction();
        try {
            $adminBank = DB::table('user_banks')->where('id', $request->admin_bank_id)->first();
            $memberBank = DB::table('member_banks')->orderBy('id','asc')->first();
            if (!$memberBank) {
                return $this->sendBadRequest('Failed', 'Harap tambahkan akun anda terlebih dahulu');
            }
            DB::table('transaction')->insert([
                'member_id' => Auth::user()->id,
                'admin_id'  => $adminBank->user_id,
                'type'      => 'deposit',
                'remarks'   => json_encode([
                    'type'  => 'deposit',
                    'note'  => $request->note,
                ]),
                'amount'    => $request->amount,
                'admin_bank_id' => $adminBank->id,
                'member_bank_id' => $memberBank->id,
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ]);
            DB::commit();
            return $this->sendResponse(array('success' => 1), 'Deposit requested successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage(). $err->getLine());
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
    }

    public function deposit(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'amount'   => 'required',
            'admin_bank_id' => 'required',
            'member_bank_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Erros', $validator->errors());
        }

        $checkExists = DB::table('transaction')->where('member_id', Auth::user()->id)
        ->where('type', 'deposit')
        ->where('status', 0)
        ->exists();

        if ($checkExists) {
            return $this->sendBadRequest('Failed', 'Terdapat permintaan deposit yang masih dalam antrian');
        }

        DB::beginTransaction();
        try {
            $adminBank = DB::table('user_banks')->where('id', $request->admin_bank_id)->first();
            $memberBank = DB::table('member_banks')->where('id', $request->member_bank_id)->first();
            DB::table('transaction')->insert([
                'member_id' => Auth::user()->id,
                'admin_id'  => $adminBank->user_id,
                'type'      => 'deposit',
                'remarks'   => json_encode([
                    'type'  => 'deposit',
                    'note'  => $request->note,
                ]),
                'amount'    => $request->amount,
                'admin_bank_id' => $adminBank->id,
                'member_bank_id' => $memberBank->id,
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ]);
            DB::commit();
            return $this->sendResponse(array('success' => 1), 'Deposit requested successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
    }

    public function withdraw(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'amount'   => 'required',
            'member_bank_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Erros', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $memberBank = DB::table('member_banks')
            ->where('member_id', Auth::user()->id)
            ->orderBy('id', 'asc')
            ->first();
            $member = DB::table('members')->where('id', $memberBank->member_id);

            if ($request->amount > $member->first()->balance) {
                return $this->sendBadRequest('Failed', 'Insufficient Balance');
            }

            DB::table('transaction')->insert([
                'member_id' => Auth::user()->id,
                'type'      => 'withdraw',
                'remarks'   => json_encode([
                    'type'  => 'withdraw',
                    'note'  => $request->note,
                ]),
                'amount'    => $request->amount,
                'member_bank_id' => $memberBank->id,
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ]);
            $member->decrement('balance', $request->amount);
            DB::commit();
            return $this->sendResponse(array('success' => 1), 'Withdraw requested successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
    }
}
