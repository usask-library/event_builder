<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Place extends Model
{
    protected $fillable = ['id', 'name'];


    /**
     * All events involving this place
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
     * Acquisition events involving this place
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
     * Production events involving this place
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
     * Manipulation events involving this place
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
     * Observation events involving this place
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
}
