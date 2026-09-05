<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * Test that the homepage loads and renders the welcome screen.
     * The page shows a clock and conditionally a Login link (white on white bg).
     * We assert the clock element exists to confirm the page loads correctly.
     */
    public function test_homepage_loads_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathIs('/')
                    ->assertPresent('#clock');
        });
    }

    /**
     * Test that the login page has the correct title.
     */
    public function test_login_page_has_correct_title()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertTitleContains('Attendance Management System');
        });
    }

    /**
     * Test that the login form has the correct heading and input fields.
     */
    public function test_login_page_has_correct_elements()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitForText('Welcome Back!', 10)
                    ->assertSee('Welcome Back!')
                    ->assertSee('Sign in to continue to AMS Portal')
                    ->assertPresent('#email')
                    ->assertPresent('#password')
                    ->assertPresent('button[type="submit"]')
                    ->assertSeeIn('button[type="submit"]', 'Secure Log In');
        });
    }

    /**
     * Test that the login form accepts typed input.
     */
    public function test_login_form_accepts_input()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('#email', 10)
                    ->type('#email', 'admin@ams.com')
                    ->type('#password', 'secret123')
                    ->assertInputValue('#email', 'admin@ams.com');
        });
    }

    /**
     * Test that submitting invalid credentials redirects back to /login.
     * (The specific error message depends on the DB being available.)
     */
    public function test_login_with_invalid_credentials_redirects_back()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('#email', 10)
                    ->type('#email', 'invalid@example.com')
                    ->type('#password', 'wrongpassword')
                    ->click('button[type="submit"]')
                    ->waitForLocation('/login', 15)
                    ->assertPathIs('/login');
        });
    }
}
