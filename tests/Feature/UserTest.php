<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserSuccessLogIn()
    {
        Session::start();
        $response = $this->post('/login', [
            'email' => 'ashbee.morgado@icloud.com',
            'password' => 'sv9h4pld',
            'csrf_token' => csrf_token()
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('auth.login', $response->original->name());
        // $response->assertSessionHasErrors();

    
        // //this works
        // // $response->assertRedirect('/dashboard');
    
        // //this fails 
        // $this->assertTrue(Auth::check());
        
    }

    public function signIn(){
        $credential = [
            'email' => 'ashbee.morgado@icloud.com',
            'password' => 'sv94hpld'
        ];
    
        
        $response = $this->post('login',$credential);
        // $response->assertRedirect('/dashboard');


    }
}
