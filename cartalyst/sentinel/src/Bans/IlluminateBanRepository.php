<?php namespace Cartalyst\Sentinel\Bans;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminateBanRepository implements BanRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Eloquent ban model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Bans\EloquentBan';

    /**
     * {@inheritDoc}
     */
    public function create(UserInterface $user)
    {
        $ban = $this->createModel();

        $ban->user_id = $user->getUserId();

        $ban->save();

        return $ban;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(UserInterface $user)
    {
        $ban = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId());

        return $ban->first() ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function ban(UserInterface $user)
    {
        $exists = $this->exists($user);

        if ($exists) {
            $ban = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                ->where('banned', false)
                ->first();

            if ($ban === null) {
                return false;
            }

            $ban->fill([
                'banned'    => true,
                'banned_at' => Carbon::now(),
            ]);

            $ban->save();
            return true;

        } else {
            $ban = $this
                ->createModel();

            $ban->fill([
                'user_id' => $user->getUserId(),
                'banned'    => true,
                'banned_at' => Carbon::now(),
            ]);

            $ban->save();
            return true;
        }

    }

    public function unban(UserInterface $user)
    {
        if ($this->isBanned($user))
        {
            $ban = $this
                ->createModel()
                ->newQuery()
                ->where('user_id', $user->getUserId())
                ->where('banned', true)
                ->first();

            if ($ban === null) {
                return false;
            }

            $ban->fill([
                'banned'    => false,
                'banned_at' => null,
            ]);

            $ban->save();
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isBanned(UserInterface $user)
    {
        $ban = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('banned', true)
            ->first();

        return $ban ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(UserInterface $user)
    {
        $ban = $this->banned($user);

        if ($ban === false) {
            return false;
        }

        return $ban->delete();
    }
}