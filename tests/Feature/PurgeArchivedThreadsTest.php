<?php

use App\Actions\CreateThreadWithMessage;
use App\Livewire\ThreadShow;
use App\Models\Thread;
use App\Models\ThreadKeyGrant;
use App\Models\ThreadMessage;
use App\Models\User;
use Carbon\CarbonInterface;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // ThreadShow::shareableUsers() lists parents, professeurs and
    // administrateurs — User::role() requires the role to exist even when no
    // user holds it yet (see ThreadShowTest.php for the same pattern).
    Role::firstOrCreate(['name' => 'administrateur']);
});

/**
 * @return array{thread: Thread, parent: User}
 */
function createArchivedThreadForTest(?CarbonInterface $archivedAt): array
{
    $parent = User::factory()->parent()->create();

    $result = app(CreateThreadWithMessage::class)->execute(
        senderName: 'Jean Dupont',
        senderEmail: 'jean.dupont@example.com',
        message: 'Ceci est un message de test suffisamment long.',
        recipientType: 'group',
        recipientUserId: null,
    );

    $thread = $result['thread'];
    $thread->update(['status' => 'archive', 'archived_at' => $archivedAt]);

    return ['thread' => $thread, 'parent' => $parent];
}

test('a dossier archived more than 6 months ago is deleted along with its messages and grants', function () {
    ['thread' => $thread] = createArchivedThreadForTest(now()->subMonths(6)->subDay());
    $threadId = $thread->id;

    $this->artisan('app:purge-archived-threads');

    expect(Thread::find($threadId))->toBeNull()
        ->and(ThreadMessage::where('thread_id', $threadId)->count())->toBe(0)
        ->and(ThreadKeyGrant::where('thread_id', $threadId)->count())->toBe(0);
});

test('a dossier archived less than 6 months ago is kept', function () {
    ['thread' => $thread] = createArchivedThreadForTest(now()->subMonths(6)->addDay());

    $this->artisan('app:purge-archived-threads');

    expect(Thread::find($thread->id))->not->toBeNull();
});

test('a dossier that is not archived is kept regardless of age', function () {
    ['thread' => $thread] = createArchivedThreadForTest(null);
    $thread->update(['status' => 'en_cours', 'archived_at' => null, 'updated_at' => now()->subYear()]);

    $this->artisan('app:purge-archived-threads');

    expect(Thread::find($thread->id))->not->toBeNull();
});

test('a dossier reopened after being archived survives the purge, even long after the original archiving', function () {
    ['thread' => $thread, 'parent' => $parent] = createArchivedThreadForTest(now()->subMonths(12));

    Livewire::actingAs($parent)
        ->test(ThreadShow::class, ['thread' => $thread])
        ->call('updateStatus', 'en_cours');

    $this->artisan('app:purge-archived-threads');

    expect(Thread::find($thread->id))->not->toBeNull();
});
