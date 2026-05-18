<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\AuditLog;

class InvestmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtolower($user->role->name);

        if ($role === 'investor') {
            $investments = Investment::where('user_id', $user->id)->get();
        } else {
            $investments = Investment::all();
        }

        return response()->json($investments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'asset_name' => 'required|string|max:255',
        ]);

        $investment = Investment::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'asset_name' => $request->asset_name,
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Investment Submitted',
            'details' => 'Investor submitted investment ID: ' . $investment->id . ' for amount: ' . $investment->amount,
        ]);

        return response()->json(['message' => 'Investment submitted successfully', 'investment' => $investment], 201);
    }

    public function approve(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if ($investment->status !== 'pending') {
            return response()->json(['message' => 'Investment is already processed'], 400);
        }

        $investment->status = 'approved';
        $investment->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Investment Approved',
            'details' => 'Admin approved investment ID: ' . $investment->id . ' for amount: ' . $investment->amount,
        ]);

        return response()->json(['message' => 'Investment approved successfully', 'investment' => $investment]);
    }

    public function reject(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if ($investment->status !== 'pending') {
            return response()->json(['message' => 'Investment is already processed'], 400);
        }

        $investment->status = 'rejected';
        $investment->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Investment Rejected',
            'details' => 'Admin rejected investment ID: ' . $investment->id . ' for amount: ' . $investment->amount,
        ]);

        return response()->json(['message' => 'Investment rejected successfully', 'investment' => $investment]);
    }

    public function updateHash(Request $request, $id)
    {
        $request->validate([
            'blockchain_hash' => 'required|string',
        ]);

        $investment = Investment::findOrFail($id);
        $investment->blockchain_hash = $request->blockchain_hash;
        $investment->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Blockchain Hash Updated',
            'details' => 'Investment ID: ' . $investment->id . ' hash: ' . $request->blockchain_hash,
        ]);

        return response()->json(['message' => 'Hash updated', 'investment' => $investment]);
    }
}

