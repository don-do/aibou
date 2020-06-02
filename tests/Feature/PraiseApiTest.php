<?php

namespace Tests\Feature;

use App\Photo;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PraiseApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        factory(Photo::class)->create();
        $this->photo = Photo::first();
    }

    /**
     * @test
     */
    public function should_グッジョブを追加できる()
    {
        $response = $this->actingAs($this->user)
            ->json('PUT', route('photo.praise', [
                'id' => $this->photo->id,
            ]));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'photo_id' => $this->photo->id,
            ]);

        $this->assertEquals(1, $this->photo->praises()->count());
    }

    /**
     * @test
     */
    public function should_2回同じ写真にグッジョブしても1個しかグッジョブがつかない()
    {
        $param = ['id' => $this->photo->id];
        $this->actingAs($this->user)->json('PUT', route('photo.praise', $param));
        $this->actingAs($this->user)->json('PUT', route('photo.praise', $param));

        $this->assertEquals(1, $this->photo->praises()->count());
    }

    /**
     * @test
     */
    public function should_グッジョブを解除できる()
    {
        $this->photo->praises()->attach($this->user->id);

        $response = $this->actingAs($this->user)
            ->json('DELETE', route('photo.praise', [
                'id' => $this->photo->id,
            ]));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'photo_id' => $this->photo->id,
            ]);

        $this->assertEquals(0, $this->photo->praises()->count());
    }
}
