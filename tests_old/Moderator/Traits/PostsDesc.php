<?php
namespace Tests\Moderator\Traits;

use App\Models\Post;

/**
 * Trait PostsDesc
 * Test posts for moderator and lower
 *
 * @author Mathrix Team
 * @package Tests\Moderator\Traits
 */
trait PostsDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /posts
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testAddUnauthorized()
    {
        $this->call('post', '/posts', factory(Post::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /posts/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testEditUnauthorized()
    {
        $post = Post::inRandomOrder()->first();
        $this->call('patch', '/posts/' . $post->id, factory(Post::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /posts/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testDeleteUnauthorized()
    {
        $post = Post::inRandomOrder()->first();
        $this->call('delete', '/posts/' . $post->id);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }
}