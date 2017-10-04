<?php
namespace Tests\Admin;

use App\Models\Post;

/**
 * Class PostsTest
 * Test posts as admin
 *
 * @author Mathrix Team
 * @package Tests\Admin
 */
class PostsTest extends AdminTestCase
{
    /*
    |--------------------------------------------------------------------------
    | POST /posts
    |--------------------------------------------------------------------------
    */
    /**
     * Test add post
     * Expect HTT 200 success
     */
    public function testAdd()
    {
        $data = factory(Post::class)->raw();
        unset($data['created_at']);

        // API Call
        $this->call('post', '/posts', $data);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('posts', $data);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /posts/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testEdit()
    {
        /** @var Post $post */
        $post = Post::inRandomOrder()->get(['id'])->first();
        $newData = factory(Post::class)->raw();
        unset($newData['created_at']);

        $this->call('patch', '/posts/' . $post->id, $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('posts', $newData);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE /posts/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testDeleteDoesntHaveComments()
    {
        /** @var Post $post */
        $post = Post::inRandomOrder()
            ->doesntHave('comments')
            ->get(['id'])
            ->first();

        $this->call('delete', '/posts/' . $post->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('posts', ['id' => $post->id]);
    }

    /**
     * Expect HTTP 200 success
     */
    public function testDeleteHasComments()
    {
        /** @var Post $post */
        $post = Post::inRandomOrder()
            ->has('comments')
            ->get(['id'])
            ->first();

        $this->call('delete', '/posts/' . $post->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('posts', ['id' => $post->id]);
        $this->notSeeInDatabase('comments', [
            'commentable_id' => $post->id,
            'commentable_type' => 'post'
        ]);
    }
}