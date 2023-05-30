<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Event extends Model
{
    protected $fillable = ['class', 'type', 'year', 'document'];

    /**
     * Relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['people', 'places', 'items'];


    // First some scopes, to limit events based on their type
    public function scopeAcquisition($query)
    {
        return $query->where('class', '=', 'acquisition');
    }
    public function scopeProduction($query)
    {
        return $query->where('class', '=', 'production');
    }
    public function scopeManipulation($query)
    {
        return $query->where('class', '=', 'manipulation');
    }
    public function scopeObservation($query)
    {
        return $query->where('class', '=', 'observation');
    }

    // The People referenced in the event, along with their role and the text used to describe them
    public function people()
    {
        return $this->belongsToMany('App\Person')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps();
    }

    // The Places referenced in the event, along with their role and the text used to describe them
    public function places()
    {
        return $this->belongsToMany('App\Place')
            ->as('details')
            ->withPivot(['role', 'description'])
            ->withTimestamps();
    }

    // The Items referenced in the event, along with the text used to describe them
    public function items()
    {
        return $this->belongsToMany(
            'App\Item',
            'event_item',
            'event_id',
            'item_identifier',
            'id',
            'identifier'
        )
            ->as('details')
            ->withPivot(['description'])
            ->withTimestamps();
    }
}
