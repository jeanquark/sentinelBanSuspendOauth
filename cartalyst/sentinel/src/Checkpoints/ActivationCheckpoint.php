<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.9
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;

use Redirect;
use Hashids\Hashids;

class ActivationCheckpoint implements CheckpointInterface
{
    use AuthenticatedCheckpoint;

    /**
     * The activation repository.
     *
     * @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * Create a new activation checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
     * @return void
     */
    public function __construct(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * {@inheritDoc}
     */
    public function login(UserInterface $user)
    {
        return $this->checkActivation($user);
    }

    /**
     * {@inheritDoc}
     */
    public function check(UserInterface $user)
    {
        return $this->checkActivation($user);
    }

    /**
     * Checks the activation status of the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     * @throws \Cartalyst\Sentinel\Checkpoints\NotActivatedException
     */
    protected function checkActivation(UserInterface $user)
    {
        $completed = $this->activations->completed($user);

        if (!$completed) {
            $hashids = new Hashids('sentinel_oauth', 8, 'abcdefghij1234567890');
            $hash = $hashids->encode($user->id);
            //dd($hash);
            //$exception = new NotActivatedException("You have not yet activated this account. <a href='reactivate' class='alert-link'>Resend Activation Email?</a> {{ $user->id }}  {{ $user }}" );
            //$exception = new NotActivatedException("You have not yet activated this account. <a href=':url' class='alert-link'>Resend Activation Email?</a>");
            $exception = new NotActivatedException("You have not yet activated this account. <a href='reactivate/{$hash}' class='alert-link'>Resend Activation Email?</a>");

            $exception->setUser($user);

            throw $exception;
            //return Redirect::Route('login')->with('error', 'You have not yet activated this account.')->with('user', $user);
        }
    }
}
