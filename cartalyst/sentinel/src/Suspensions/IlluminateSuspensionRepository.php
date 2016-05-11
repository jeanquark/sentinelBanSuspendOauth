<?php namespace Cartalyst\Sentinel\Suspensions;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

use App\User;
use DateTime;
use Session;

class IlluminateSuspensionRepository implements SuspensionRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Eloquent activation model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Suspensions\EloquentSuspension';

    /**
     * The activation expiration time, in seconds.
     *
     * @var int
     */
    protected $expires = 259200;


    /**
     * The activation expiration time, in seconds.
     *
     * @var int
     */
    //public static $flag = false;

    /**
     * Suspensions time in minutes.
     *
     * @var int
     */
    protected static $suspensionTime = 15;


    /**
     * Create a new Illuminate activation repository.
     *
     * @param  string  $model
     * @param  int  $expires
     * @return void
     */
    public function __construct($model = null, $expires = null)
    {
        if (isset($model)) {
            $this->model = $model;
        }

        if (isset($expires)) {
            $this->expires = $expires;
        }
    }


    /**
     * Return all the registered users
     *
     * @return Collection
     */
    public function all()
    {
        //$users = $this->sentry->findAllUsers();
        $users = User::all();

        foreach ($users as $user) {
            if ($user->isActivated()) {
                $user->status = "Active";
            } else {
                $user->status = "Not Active";
            }

            //Pull Suspension & Ban info for this user
            $throttle = $this->throttleProvider->findByUserId($user->id);

            //Check for suspension
            if ($throttle->isSuspended()) {
                // User is Suspended
                $user->status = "Suspended";
            }

            //Check for ban
            if ($throttle->isBanned()) {
                // User is Banned
                $user->status = "Banned";
            }
        }

        return $users;
    }

    /**
     * {@inheritDoc}
     */
    public function create(UserInterface $user)
    {
        $suspension = $this->createModel();

        //$code = $this->generateActivationCode();

        //$suspension->fill(compact('code'));

        $suspension->user_id = $user->getUserId();

        $suspension->save();

        return $suspension;
    }

    /**
     * {@inheritDoc}
     */
    //public function exists(UserInterface $user, $code = null)
    public function exists(UserInterface $user)
    {
        //$expires = $this->expires();

        $suspension = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId());
            //->where('suspended', false)
            //->where('created_at', '>', $expires);

        /*if ($code) {
            $suspension->where('code', $code);
        }*/

        return $suspension->first() ?: false;
    }

    /**
     * {@inheritDoc}
     */
    //public function suspend(UserInterface $user, $code)
    public function suspend(UserInterface $user)
    {
        /*$expires = $this->expires();

        $suspension = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            //->where('code', $code)
            ->where('suspended', false)
            ->where('created_at', '>', $expires)
            ->first();

        if ($suspension === null) {
            return false;
        }

        $suspension->fill([
            'suspended'    => true,
            'suspended_at' => Carbon::now(),
        ]);

        $suspension->save();

        return true;*/

        //dd($this->getRemainingSuspensionTime($user));

        $exists = $this->exists($user);
        //dd($exists);
        //dd($user->getUserId());

        if ($exists) {
            $suspend = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                //->where('code', $code)
                ->where('suspended', false)
                ->first();

            if ($suspend === null) {
                return false;
            }

            $suspend->fill([
                'suspended'    => true,
                'suspended_at' => Carbon::now(),
            ]);

            $suspend->save();
            return true;

        } else {
            //dd($exists);
            $suspend = $this
                ->createModel();

            $suspend->fill([
                'user_id' => $user->getUserId(),
                'suspended'    => true,
                'suspended_at' => Carbon::now(),
            ]);

            $suspend->save();

            return true;
        }
    }

    /**
     * Unsuspend the user.
     *
     * @return void
     */
    public function unsuspend(UserInterface $user)
    {
        /*if ($this->suspended($user)) 
        {
            $suspend = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                ->first();

            if ($suspend === null) {
                return false;
            }

            $suspend->fill([
                'suspended' => false,
                'suspended_at' => null,
            ]);

            $suspend->save();
            return true;
        }*/

            $suspend = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                ->where('suspended', true)
                ->whereNotNull('suspended_at')
                ->first();

            if ($suspend === null) {
                return false;
            }

            $suspend->fill([
                'suspended'    => false,
                'suspended_at' => null,
            ]);

            $suspend->save();
            //dd('user has been unsuspended!');
            return true;
    }


    /**
     * {@inheritDoc}
     */
    public function suspended(UserInterface $user)
    {
        /*$suspension = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('suspended', true)
            ->first();

        return $suspension ?: false;*/

        $suspended = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('suspended', true)
            ->first();

        //return $suspended ?: false;
        //return false;
        //dd($suspended);
        //$suspended = $suspended->suspended;
        //$suspended_at = $suspended->suspended_at;
        //dd($suspended);
        //dd($suspended_at);

        if ($suspended !== null)
        {
            $this->removeSuspensionIfAllowed($user);
            return (bool) $this->suspended($user);
            //return false;
        } else {
            return false;
        }

        //return false;
    }


    /**
     * {@inheritDoc}
     */
    public function isSuspended(UserInterface $user)
    {
        //$flag = false;

        $isSuspended = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('suspended', true)
            ->first();

        //return $suspend ?: false;
        if ($isSuspended !== null) {
            //return true;
            //$this->removeSuspensionIfAllowed($user);
            //return $this->isSuspended($user);

            $remove = $this->removeSuspensionIfAllowed($user);
            //dd($remove);
            //dd($flag);
            return $remove;
            //return $this->isSuspended($user);
            /*if ($remove == null) {
                dd('$remove == null. It means that the process went through the removeSuspensionIfAllowed function.');
                //return true;
                return $this->isSuspended($user);
            } else {
                dd('$remove !== null');
                //return $this->isSuspended($user);
                //return Suspension::checksuspension($user);
                return Session::store();
                //return false;
            }*/

        } else {
            //dd('isSuspended == false');
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function remove(UserInterface $user)
    {
        $suspension = $this->suspended($user);

        if ($suspension === false) {
            return false;
        }

        return $suspension->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function removeExpired()
    {
        $expires = $this->expires();

        return $this
            ->createModel()
            ->newQuery()
            ->where('suspended', false)
            ->where('created_at', '<', $expires)
            ->delete();
    }

    /**
     * Returns the expiration date.
     *
     * @return \Carbon\Carbon
     */
    protected function expires()
    {
        return Carbon::now()->subSeconds($this->expires);
    }

    /**
     * Return a random string for an activation code.
     *
     * @return string
     */
    protected function generateSuspensionCode()
    {
        return str_random(32);
    }





    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        return array_merge(parent::getDates(), array('suspended_at', 'banned_at'));
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if (isset($result['suspended']))
        {
            $result['suspended'] = $this->getSuspendedAttribute($result['suspended']);
        }
        if (isset($result['banned']))
        {
            $result['banned'] = $this->getBannedAttribute($result['banned']);
        }
        if (isset($result['suspended_at']) and $result['suspended_at'] instanceof DateTime)
        {
            $result['suspended_at'] = $result['suspended_at']->format('Y-m-d H:i:s');
        }

        return $result;
    }


    /**
     * Set suspension time.
     *
     * @param  int  $minutes
     */
    public static function setSuspensionTime($minutes)
    {
        static::$suspensionTime = (int) $minutes;
    }

    /**
     * Get suspension time.
     *
     * @return  int
     */
    public static function getSuspensionTime()
    {
        return static::$suspensionTime;
    }


    /**
     * Get suspension time.
     *
     * @return  int
     */
    public function getSuspendedTime($user)
    {
            $suspendedAt = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                ->where('suspended', true)
                ->whereNotNull('suspended_at')
                ->first();

            if ($suspendedAt === null) {
                return false;
            }

            return $suspendedAt->suspended_at;
            //return false;

            /*$suspensionTime = $this->getSuspensionTime();
            $suspendedAt = $suspendedAt->suspended_at;
            $unsuspendAt = $suspendedAt + $suspensionTime;
            //$now = Carbon::now();
            $unsuspendAt = new DateTime($unsuspendAt);
            $now = new DateTime();
            $timeLeft = $now->diff($unsuspendAt);

            $minutesLeft = ($timeLeft->s != 0 ?
                        ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i) + 1 :
                        ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i));
            return $minutesLeft;*/
    }


    /**
     * Inspects to see if the user can become unsuspended
     * or not, based on the suspension time provided. If so,
     * unsuspends.
     *
     * @return bool
     */
    public function removeSuspensionIfAllowed($user)
    {
        //$suspended_at = clone $this->suspended_at;
        //$suspended_at = $this->getSuspended_atTime();
        $flag = false;

        $suspensionTime = $this->getSuspensionTime();
        //$suspensionTime = date('H:i:s', strtotime(15));
        $suspendedAt = new DateTime($this->getSuspendedTime($user));
        //dd($suspendedAt);
        $unsuspendAt = $suspendedAt->modify("+{$suspensionTime} minutes");
        //dd($unsuspendAt);
        //$now            = Carbon::now();
        $now = new DateTime();
        //dd($now);
        if ($unsuspendAt <= $now)
        {
            //dd('go to unsuspend($user)');
            $this->unsuspend($user);

            unset($suspended);
            unset($unsuspendAt);
            unset($now);

            return false;
            //$flag = true;
        } else {
            unset($suspended);
            unset($unsuspendAt);
            unset($now);

            return true;
        }
        //return false;
        //dd('not allowed to remove');
    }

    /**
     * Get the remaining time on a suspension in minutes rounded up. Returns
     * 0 if user is not suspended.
     *
     * @return int
     */
    public function getRemainingSuspensionTime($user)
    {
        //if(!$this->isSuspended($user))
        //    return 0;

        $suspensionTime = $this->getSuspensionTime();
        //$suspensionTime = date('H:i:s', strtotime(15));
        //dd($suspensionTime);
        $suspendedAt = new DateTime($this->getSuspendedTime($user));
        //dd($suspendedAt);
        //$unsuspendAt = $suspendedAt + $suspensionTime;
        $unsuspendAt = $suspendedAt->modify("+{$suspensionTime} minutes");
        //dd($unsuspendAt);
        //$unsuspendAt = new DateTime($unsuspendAt);
        $now = new DateTime();
        //dd($now);
        $timeLeft = $now->diff($unsuspendAt);
        //dd($timeLeft);

        $minutesLeft = ($timeLeft->s != 0 ?
                    ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i) + 1 :
                    ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i));
        return $minutesLeft;

        /*$lastAttempt = clone $this->last_attempt_at;

        $suspensionTime  = static::$suspensionTime;
        $clearAttemptsAt = $lastAttempt->modify("+{$suspensionTime} minutes");
        $now             = $this->freshTimestamp();

        $timeLeft = $clearAttemptsAt->diff($now);

        $minutesLeft = ($timeLeft->s != 0 ?
                        ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i) + 1 :
                        ($timeLeft->days * 24 * 60) + ($timeLeft->h * 60) + ($timeLeft->i));*/

        //return $minutesLeft;
    }
}