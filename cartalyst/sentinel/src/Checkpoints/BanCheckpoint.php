<?php namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Bans\BanRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;

class BanCheckpoint implements CheckpointInterface
{
    use AuthenticatedCheckpoint;

    /**
     * The ban repository.
     *
     * @var \Cartalyst\Sentinel\Bans\BanRepositoryInterface
     */
    protected $bans;

    /**
     * Create a new ban checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Bans\BanRepositoryInterface  $bans
     * @return void
     */
    public function __construct(BanRepositoryInterface $bans)
    {
        $this->bans = $bans;
    }

    /**
     * {@inheritDoc}
     */
    public function login(UserInterface $user)
    {
        return $this->checkBan($user);
    }

    /**
     * {@inheritDoc}
     */
    public function check(UserInterface $user)
    {
        return $this->checkBan($user);
    }

    /**
     * Checks the ban status of the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     * @throws \Cartalyst\Sentinel\Checkpoints\BannedException
     */
    protected function checkBan(UserInterface $user)
    {
        $isBanned = $this->bans->isBanned($user);

        if ($isBanned) {
            $exception = new BannedException('You are banned.');

            $exception->setUser($user);

            throw $exception;
        }
    }
}