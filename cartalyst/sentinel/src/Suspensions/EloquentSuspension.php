<?php namespace Cartalyst\Sentinel\Suspensions;

use Illuminate\Database\Eloquent\Model;

class EloquentSuspension extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'throttle';

    /**
     * Suspensions time in minutes.
     *
     * @var int
     */
    protected static $suspensionTime = 15;

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
    public function getSuspendedAttribute($suspended)
    {
        return (bool) $suspended;
    }

    /**
     * Set mutator for the "suspended" attribute.
     *
     * @param  mixed  $suspended
     * @return void
     */
    public function setSuspendedAttribute($suspended)
    {
        $this->attributes['suspended'] = (bool) $suspended;
    }

    /**
     * {@inheritDoc}
     */
    /*public function getCode()
    {
        return $this->attributes['suspended'];
    }*/
}