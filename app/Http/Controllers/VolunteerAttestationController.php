<?php

namespace App\Http\Controllers;

use App\Models\VolunteerAttestation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VolunteerAttestationController extends Controller
{
    private const TEMPLATE_PATH = 'attestation-benevolat-2026.pdf';

    private const ATTESTATIONS_DIRECTORY = 'attestations';

    public function template(): StreamedResponse
    {
        return Storage::disk('local')->response(self::TEMPLATE_PATH);
    }

    public function store(Request $request): Response
    {
        if ($request->user()->volunteerAttestation()->exists()) {
            abort(409, 'Une attestation a déjà été enregistrée.');
        }

        $validated = $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $validated['pdf']->storeAs(
            self::ATTESTATIONS_DIRECTORY,
            $request->user()->id.'.pdf',
            'local',
        );

        $request->user()->volunteerAttestation()->create([
            'disk_path' => $path,
            'submitted_at' => now(),
        ]);

        return response()->noContent();
    }

    public function downloadMine(Request $request): StreamedResponse
    {
        $attestation = $request->user()->volunteerAttestation()->firstOrFail();

        return Storage::disk('local')->download($attestation->disk_path, 'attestation-benevolat.pdf');
    }

    public function download(VolunteerAttestation $attestation): StreamedResponse
    {
        Gate::authorize('view', $attestation);

        return Storage::disk('local')->download($attestation->disk_path, 'attestation-benevolat.pdf');
    }
}
