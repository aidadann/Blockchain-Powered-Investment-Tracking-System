<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaucetController extends Controller
{
    /**
     * Auto-fund a user's wallet with Sepolia test ETH from the system wallet.
     * 
     * This endpoint triggers a Node.js script (fund-wallet.js) that uses ethers.js
     * to send a small amount of test ETH from the pre-funded system wallet
     * to the authenticated user's connected MetaMask wallet address.
     * 
     * Rate-limited: 1 request per 24 hours per wallet address.
     */
    public function autoFund(Request $request)
    {
        $user = $request->user();

        // Validate wallet is connected
        if (!$user->wallet_address) {
            return response()->json([
                'message' => 'No wallet address linked to your account. Please connect MetaMask first.'
            ], 400);
        }

        // Check 24-hour cooldown
        if ($user->last_funded_at) {
            $lastFunded = \Carbon\Carbon::parse($user->last_funded_at);
            $cooldownEnd = $lastFunded->addHours(24);
            
            if (now()->lt($cooldownEnd)) {
                $remainingMinutes = now()->diffInMinutes($cooldownEnd);
                $remainingHours = floor($remainingMinutes / 60);
                $remainingMins = $remainingMinutes % 60;

                return response()->json([
                    'message' => 'Cooldown active. You can request again in ' . $remainingHours . 'h ' . $remainingMins . 'm.',
                    'cooldown_active' => true,
                    'cooldown_ends_at' => $cooldownEnd->toISOString(),
                ], 429);
            }
        }

        // Build the Node.js script command
        $scriptPath = base_path('scripts/fund-wallet.cjs');
        $walletAddress = escapeshellarg($user->wallet_address);
        $amount = '0.05';

        $command = "node " . escapeshellarg($scriptPath) . " {$walletAddress} {$amount} 2>&1";

        Log::info('Faucet auto-fund initiated', [
            'user_id' => $user->id,
            'wallet' => $user->wallet_address,
            'command' => $command,
        ]);

        // Execute the funding script
        $output = shell_exec($command);

        if (!$output) {
            Log::error('Faucet script returned no output');
            return response()->json([
                'message' => 'Funding script failed to execute. Please try manual mining.',
            ], 500);
        }

        $result = json_decode($output, true);

        if (!$result) {
            Log::error('Faucet script returned invalid JSON', ['output' => $output]);
            return response()->json([
                'message' => 'Funding script returned invalid response.',
            ], 500);
        }

        if ($result['success'] === true) {
            // Update cooldown timestamp
            $user->last_funded_at = now();
            $user->save();

            Log::info('Faucet auto-fund successful', [
                'user_id' => $user->id,
                'tx_hash' => $result['tx_hash'],
            ]);

            return response()->json([
                'message' => 'Successfully funded your wallet with ' . $amount . ' ETH!',
                'tx_hash' => $result['tx_hash'],
                'amount' => $amount,
                'etherscan_url' => 'https://sepolia.etherscan.io/tx/' . $result['tx_hash'],
            ], 200);
        }

        // Script executed but returned an error
        Log::warning('Faucet auto-fund failed', [
            'user_id' => $user->id,
            'error' => $result['error'] ?? 'Unknown error',
        ]);

        return response()->json([
            'message' => $result['error'] ?? 'Funding failed. Please try manual mining.',
        ], 500);
    }

    /**
     * Get the user's faucet funding status (cooldown info).
     */
    public function status(Request $request)
    {
        $user = $request->user();

        $cooldownActive = false;
        $cooldownEndsAt = null;

        if ($user->last_funded_at) {
            $cooldownEnd = \Carbon\Carbon::parse($user->last_funded_at)->addHours(24);
            $cooldownActive = now()->lt($cooldownEnd);
            $cooldownEndsAt = $cooldownActive ? $cooldownEnd->toISOString() : null;
        }

        return response()->json([
            'wallet_address' => $user->wallet_address,
            'cooldown_active' => $cooldownActive,
            'cooldown_ends_at' => $cooldownEndsAt,
            'last_funded_at' => $user->last_funded_at,
        ]);
    }
}
