<?php namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Suspensions\SuspensionRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;

class SuspensionCheckpoint implements CheckpointInterface
{
    use AuthenticatedCheckpoint;

    /**
     * The activation repository.
     *
     * @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
     */
    protected $suspensions;

    /**
     * Create a new activation checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
     * @return void
     */
    public function __construct(SuspensionRepositoryInterface $suspensions)
    {
        $this->suspensions = $suspensions;
    }

    /**
     * {@inheritDoc}
     */
    public function login(UserInterface $user)
    {
        return $this->checkSuspension($user);
    }

    /**
     * {@inheritDoc}
     */
    public function check(UserInterface $user)
    {
        return $this->checkSuspension($user);
    }

    /**
     * Checks the activation status of the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     * @throws \Cartalyst\Sentinel\Checkpoints\NotActivatedException
     */
    protected function checkSuspension(UserInterface $user)
    {
        //$suspended = $this->suspensions->suspended($user);
        $suspended = $this->suspensions->isSuspended($user);
        //dd($suspended);
        if ($suspended) {
            //$exception = new NotActivatedException('You are suspended.');
            $exception = new SuspendedException('You have been suspended for ' . $this->suspensions->getSuspensionTime() .' minutes. There remains ' . $this->suspensions->getRemainingSuspensionTime($user) . ' minute(s).');
            //dd('abc');
            $exception->setUser($user);

            throw $exception;
        }


        /*if ($suspended)
        {
            throw new NotActivatedException(sprintf(
                'User [%s] has been suspended.',
                $this->getUser()->getLogin()
            ));
        }*/

        //return true;
    }
}