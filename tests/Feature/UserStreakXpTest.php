<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserStreakXpTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_xp_and_update_streak()
    {
        $user = User::factory()->create(['xp' => 0, 'daily_streak' => 0, 'last_practiced_at' => null]);

        $user->addXp(10);
        $this->assertEquals(10, $user->xp);

        $user->updatePracticeStreak();
        $user->refresh();
        $this->assertEquals(1, $user->daily_streak);
        $this->assertNotNull($user->last_practiced_at);
    }
}
