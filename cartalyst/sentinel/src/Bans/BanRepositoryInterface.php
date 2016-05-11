<?php namespace Cartalyst\Sentinel\Bans;

use Cartalyst\Sentinel\Users\UserInterface;

interface BanRepositoryInterface
{
    /**
     * Create a new activation record and code.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Bans\BanInterface
     */
    public function create(UserInterface $user);

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Bans\BanInterface|bool
     */
    public function exists(UserInterface $user);

    /**
     * Completes the activation for the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     */
    public function ban(UserInterface $user);

    /**
     * Checks if a valid activation has been completed.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Bans\BanInterface|bool
     */
    public function isBanned(UserInterface $user);

    /**
     * Remove an existing activation (deactivate).
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool|null
     */
    public function remove(UserInterface $user);

}

