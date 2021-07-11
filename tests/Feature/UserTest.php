<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function testUserSuccessLogIn()
    // {
    //     Session::start();
    //     $user = factory(User::class)->create([
    //         'password' => bcrypt('sv9h4pld'),
    //     ]);
       
    //     $response = $this->from('/login')->post('/login', [
    //         'email' => $user->email,
    //         'password' => 'sv9h4pld',
    //     ]);
        
    //     $response->assertRedirect('/dashboard');
    //     $this->assertEquals(302, $response->getStatusCode());

        
    // }

    public function testUserRegistration(){
        Session::start();
        $credentials = [
            'email' => 'ashbee.morgado@icloud.com',
            'password' => bcrypt('sv94hpld')
        ];

        $response = $this->post('/register', $credentials);

        $response->assertRedirect('/dashboard');
        $this->assertEquals(302, $response->getStatusCode());

    }


}
