<?php namespace Cartalyst\Sentinel\Bans;

use Illuminate\Database\Eloquent\Model;

class EloquentBan extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'throttle';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'suspended',
        'banned',
        'suspended_at',
        'banned_at'
    ];

    /**
     * Get mutator for the "suspended" attribute.
     *
     * @param  mixed  $suspended
     * @return bool
     */
    public function getisBannedAttribute($isBanned)
    {
        return (bool) $isBanned;
    }

    /**
     * Set mutator for the "suspended" attribute.
     *
     * @param  mixed  $suspended
     * @return void
     */
    public function setisBannedAttribute($isBanned)
    {
        $this->attributes['banned'] = (bool) $isBanned;
    }

}