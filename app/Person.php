<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    protected $with = ['roles'];

    protected $fillable = ['id', 'last', 'first'];

    /**
     * All events involving this person
     *
     * @return BelongsToMany
     */
    public function events()
    {
        return $this->belongsToMany('App\Event')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps();
    }

    /**
     * Acquisition events involving this person
     *
     * @return mixed
     */
    public function acquisitions()
    {
        return $this->belongsToMany('App\Event')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps()
            ->acquisition();
    }

    /**
     * Production events involving this person
     *
     * @return mixed
     */
    public function productions()
    {
        return $this->belongsToMany('App\Event')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps()
            ->production();
    }

    /**
     * Manipulation events involving this person
     *
     * @return mixed
     */
    public function manipulations()
    {
        return $this->belongsToMany('App\Event')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps()
            ->manipulation();
    }

    /**
     * Observation events involving this person
     *
     * @return mixed
     */
    public function observations()
    {
        return $this->belongsToMany('App\Event')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps()
            ->observation();
    }

    /**
     * All roles assigned to this person
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
}
