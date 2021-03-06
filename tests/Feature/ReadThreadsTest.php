<?php

namespace Tests\Feature;

use Tests\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadThreadsTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();
        $this->thread = factory('App\Thread')->create();
    }
    /** @test */
    public function a_can_user_vie_all_threads()
    {
        $response = $this->get('/threads')
                    ->assertSee($this->thread->title);
    }
    /** @test */
    public function a_can_user_read_a_single_thread()
    {
        $response = $this->get($this->thread->path())
                    ->assertSee($this->thread->title);
    }

//    /** @test */
//    public function a_can_user_read_replies_that_are_associated_with_a_thread()
//    {
//        $reply= factory('App\Reply')->create(['thread_id'=>$this->thread->id]);
//        $this->get($this->thread->path())
//              ->assertSee($reply->body);
//    }

    /** @test */
    function a_user_can_filter_threads_according_to_a_channel()
    {
        $channel = create('App\Channel');
        $threadInChannel = create('App\Thread',['channel_id'=>$channel->id]);
        $threadNotInChannel = create('App\Thread');
        $this->get('/threads/'.$channel->slug)
            ->assertSee($threadInChannel->title)
            ->assertDontSee($threadNotInChannel->title);
    }

    /** @test */
    function a_user_can_filter_threads_by_any_username()
    {
        $this->signIn(create('App\User',['name'=>'AbedinRasel']));
        $threadByRasel = create('App\Thread',['user_id'=>auth()->id()]);
        $threadNotByRasel = create('App\Thread');
        $this->get('threads?by=AbedinRasel')
             ->assertSee($threadByRasel->title)
             ->assertDontSee($threadNotByRasel->title);
    }

    /** @test */
    function a_user_can_filter_threads_by_popularity()
    {
        $threadsWithTwoRelies= create('App\Thread');
        create('App\Reply',['thread_id'=>$threadsWithTwoRelies->id],2);
        $threadsWithThreeReplies = create('App\Thread');
        create('App\Reply',['thread_id'=>$threadsWithThreeReplies->id],3);
        $threadsWithNoRelies = $this->thread;

       $response = $this->getJson('threads?popular=1')->json();
       $this->assertEquals([3,2,0],array_column($response,'replies_count'));
    }

    /** @test */
    function a_user_can_request_all_replies_for_given_thread()
    {
        $thread = create('App\Thread');
        create('App\Reply',['thread_id'=>$thread->id],2);
        $res = $this->getJson($thread->path().'/replies')->json();
//        dd($res);
        $this->assertCount(2,$res['data']);
        $this->assertEquals(2,$res['total']);
    }
}
