<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from addresses');
        DB::delete('delete from contacts');
        DB::delete('delete from users');
        DB::delete('delete from user_categories');
        DB::delete('delete from loans');
    }
}
