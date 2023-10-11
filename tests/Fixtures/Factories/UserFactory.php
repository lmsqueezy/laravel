<?php

namespace Tests\Fixtures\Factories;

use Orchestra\Testbench\Factories\UserFactory as OrchestraUserFactory;
use Tests\Fixtures\User;

class UserFactory extends OrchestraUserFactory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;
}
