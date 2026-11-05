<?php

namespace App\Http\Controllers;

use App\Models\KycProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    private const STEPS = ['wallet', 'id', 'selfie', 'address'];

    private function profile(): KycProfile
    {
        return KycProfile::firstOrCreate(['user_id' => Auth::id()], ['status' => 'draft']);
    }

    private function nextStep(string $step): string
    {
        $i = array_search($step, self::STEPS, true);

        return self::STEPS[$i + 1] ?? 'done';
    }

    public function show(Request $request)
    {
        $step = (string) $request->query('step', 'wallet');
        if (! in_array($step, array_merge(self::STEPS, ['done']), true)) {
            $step = 'wallet';
        }
        $profile = $this->profile();

        return view('kyc.show', [
            'step' => $step,
            'profile' => $profile,
            'done' => $profile->completedSteps(),
        ]);
    }

    public function save(Request $request, string $step)
    {
        abort_unless(in_array($step, self::STEPS, true), 404);

        $user = Auth::user();
        $profile = $this->profile();
        $message = 'Saved.';

        if ($step === 'wallet') {
            $data = $request->validate([
                'phonenumber' => ['nullable', 'string', 'regex:/^\+?[0-9]{10,14}$/', 'required_with:bvn'],
                'bvn' => ['nullable', 'digits:11', 'required_with:phonenumber'],
            ]);
            if (! empty($data['bvn']) && ! empty($data['phonenumber'])) {
                $profile->update(['phone' => $data['phonenumber']]);
                if (! $user->virtual_account_number) {
                    try {
                        app(AuthController::class)->trySetupVirtualAccountPublic($user, $data['phonenumber'], $data['bvn']);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                    $message = $user->fresh()->virtual_account_number
                        ? 'Your wallet account number is ready.'
                        : 'We could not create your wallet account yet. Check your details and try again later from your wallet page.';
                }
            }
        }

        if ($step === 'id') {
            $request->validate([
                'id_type' => ['nullable', 'in:national_id,passport,drivers_license,voters_card,residence_card'],
                'id_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'id_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            $update = [];
            if ($request->filled('id_type')) {
                $update['id_type'] = $request->input('id_type');
            }
            foreach (['id_front' => 'id_front_path', 'id_back' => 'id_back_path'] as $field => $col) {
                if ($request->hasFile($field)) {
                    if ($profile->$col) {
                        Storage::disk('local')->delete($profile->$col);
                    }
                    $update[$col] = $request->file($field)->store('kyc/' . $user->id, 'local');
                }
            }
            if ($update) {
                $profile->update($update);
            }
        }

        if ($step === 'selfie') {
            $request->validate([
                'selfie' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            if ($request->hasFile('selfie')) {
                if ($profile->selfie_path) {
                    Storage::disk('local')->delete($profile->selfie_path);
                }
                $profile->update(['selfie_path' => $request->file('selfie')->store('kyc/' . $user->id, 'local')]);
            }
        }

        if ($step === 'address') {
            $data = $request->validate([
                'address_line' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
            ]);
            $profile->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));
        }

        if ($profile->status === 'approved' && $profile->wasChanged()) {
            $profile->update(['status' => 'submitted', 'submitted_at' => now()]);
            $message = 'Saved. Your details changed, so they need to be reviewed again.';
        }
        return redirect()->route('kyc.show', ['step' => $this->nextStep($step)])->with('status', $message);
    }

    public function submit()
    {
        $profile = $this->profile();
        if (! $profile->id_front_path && ! $profile->selfie_path) {
            return redirect()->route('kyc.show', ['step' => 'done'])
                ->withErrors(['kyc' => 'Add your ID or a selfie before submitting for review.']);
        }
        $profile->update(['status' => 'submitted', 'submitted_at' => now()]);

        return redirect()->route('kyc.show', ['step' => 'done'])->with('status', 'Submitted. We will review your details.');
    }
}
