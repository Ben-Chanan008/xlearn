<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'student']);
    }

    private function notify(User $user, array $data = []): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['title' => 'Test title', 'body' => 'Test body', 'url' => route('my-courses'), 'icon' => 'fa-bell', ...$data],
        ]);
    }

    public function test_the_bell_shows_the_unread_count(): void
    {
        $this->notify($this->user);
        $this->notify($this->user);
        $this->notify($this->user)->markAsRead();

        $this->actingAs($this->user)->get(route('my-courses'))
            ->assertOk()
            ->assertSeeInOrder(['data-unread-count', '2'], false)
            ->assertSee('Test title');
    }

    public function test_the_bell_caps_the_count_at_9_plus(): void
    {
        foreach (range(1, 10) as $i)
            $this->notify($this->user);

        $this->actingAs($this->user)->get(route('my-courses'))->assertSee('9+');
    }

    public function test_the_bell_has_no_badge_when_everything_is_read(): void
    {
        $this->notify($this->user)->markAsRead();

        $this->actingAs($this->user)->get(route('my-courses'))->assertDontSee('data-unread-count', false);
    }

    public function test_opening_a_notification_marks_it_read_and_redirects_to_its_url(): void
    {
        $notification = $this->notify($this->user, ['url' => route('dashboard')]);

        $this->actingAs($this->user)->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_open_someone_elses_notification(): void
    {
        $notification = $this->notify(User::factory()->create());

        $this->actingAs($this->user)->get(route('notifications.open', $notification->id))->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_read_all_clears_the_unread_count(): void
    {
        $this->notify($this->user);
        $this->notify($this->user);
        $other = $this->notify(User::factory()->create());

        $this->actingAs($this->user)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $this->user->unreadNotifications()->count());
        // Only the user's own notifications are touched
        $this->assertNull($other->fresh()->read_at);
    }
}
