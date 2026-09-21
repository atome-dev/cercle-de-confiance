<?php

use App\Models\User;
use App\Models\VolunteerAttestation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function fakePdfUpload(string $name = 'document.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n%%EOF");
}

test('the attestation page redirects guests without the access cookie', function () {
    $this->get(route('attestation-benevolat.show'))
        ->assertRedirect(route('access.show'));
});

test('a user with none of administrateur, parent or professeur gets a 403 on the attestation page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withCookies(withAccessCookie())
        ->get(route('attestation-benevolat.show'))
        ->assertForbidden();
});

test('a parent without a submitted attestation sees the editor, not the locked state', function () {
    $parent = User::factory()->parent()->create();

    $response = $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('attestation-benevolat.show'));

    $response->assertOk();
    $response->assertSee('id="pdf-canvas"', false);
    $response->assertDontSee('Attestation enregistrée');
});

test('submitting a pdf creates the attestation and stores the file', function () {
    Storage::fake('local');
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->post(route('attestation-benevolat.store'), ['pdf' => fakePdfUpload()])
        ->assertNoContent();

    expect(VolunteerAttestation::where('user_id', $parent->id)->exists())->toBeTrue();
    Storage::disk('local')->assertExists("attestations/{$parent->id}.pdf");
});

test('a second submission is rejected once an attestation already exists', function () {
    Storage::fake('local');
    $parent = User::factory()->parent()->create();
    $parent->volunteerAttestation()->create([
        'disk_path' => "attestations/{$parent->id}.pdf",
        'submitted_at' => now(),
    ]);

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->post(route('attestation-benevolat.store'), ['pdf' => fakePdfUpload()])
        ->assertConflict();
});

test('a parent with a submitted attestation sees the locked state instead of the editor', function () {
    $parent = User::factory()->parent()->create();
    $parent->volunteerAttestation()->create([
        'disk_path' => "attestations/{$parent->id}.pdf",
        'submitted_at' => now(),
    ]);

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('attestation-benevolat.show'))
        ->assertOk()
        ->assertSee('Attestation enregistrée');
});

test('the nav shows a dot next to Attestation until the member has submitted one', function () {
    $parent = User::factory()->parent()->create();

    $this->actingAs($parent)
        ->withCookies(withAccessCookie())
        ->get(route('home'))
        ->assertSee('Attestation non enregistrée');

    $parent->volunteerAttestation()->create([
        'disk_path' => "attestations/{$parent->id}.pdf",
        'submitted_at' => now(),
    ]);

    // ->fresh() : $parent->volunteerAttestation()->create() ne met pas à jour la
    // relation déjà mise en cache sur cette instance ; sans ça, la deuxième requête
    // réutiliserait le même objet $parent (via actingAs) avec sa relation encore
    // vide en cache, alors qu'une vraie requête HTTP résoudrait un utilisateur frais.
    $this->actingAs($parent->fresh())
        ->withCookies(withAccessCookie())
        ->get(route('home'))
        ->assertDontSee('Attestation non enregistrée');
});

test('a member cannot download another member\'s attestation', function () {
    Storage::fake('local');
    $owner = User::factory()->parent()->create();
    $other = User::factory()->professeur()->create();
    $attestation = $owner->volunteerAttestation()->create([
        'disk_path' => "attestations/{$owner->id}.pdf",
        'submitted_at' => now(),
    ]);
    Storage::disk('local')->put($attestation->disk_path, "%PDF-1.4\n%%EOF");

    $this->actingAs($other)
        ->withCookies(withAccessCookie())
        ->get(route('attestations-benevolat.download', $attestation))
        ->assertForbidden();
});

test('an administrateur sees the list of members and can download a submitted attestation', function () {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $parent = User::factory()->parent()->create(['name' => 'Jeanne Dupont']);
    $attestation = $parent->volunteerAttestation()->create([
        'disk_path' => "attestations/{$parent->id}.pdf",
        'submitted_at' => now(),
    ]);
    Storage::disk('local')->put($attestation->disk_path, "%PDF-1.4\n%%EOF");

    $this->actingAs($admin)
        ->withCookies(withAccessCookie())
        ->get(route('attestations-benevolat.index'))
        ->assertOk()
        ->assertSeeText('Jeanne Dupont');

    $this->actingAs($admin)
        ->withCookies(withAccessCookie())
        ->get(route('attestations-benevolat.download', $attestation))
        ->assertOk();
});
