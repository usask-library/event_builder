<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * Each XML/TEI document will have a document specific "identifier" used to refer items in the text.
 * For historical reasons, those identifiers/witnesses/items are the Items in Event Builder.
 *
 * Each Item can reference exactly one actual object. "Object" is a reserved word in many programming languages,
 * including PHP, so these "objects" are referred to as "Artefacts" in the Event Builder code.
 *
 * The table names used in the database, for historical reasons, does not clearly line up with this terminology.
 *
 *  - Items/Identifiers are stored in the item_identifier table
 *  - Artefacts are stored in the items table
 *
 *  There is a many-to-one relationship between Items and Artefacts
 *
 */
class Artefact extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    protected $table = 'items';

    // Fields that can be mass assigned
    protected $fillable = ['id', 'name'];


    /**
     * Returns the Items that reference this Artefact
     *
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany('App\Item', 'item_id', 'id');
    }

    // All events this object is involved in
    public function events()
    {
        return $this->hasManyDeep(
            'App\Event',
            ['App\Item', 'App\EventItem'],
            [
                'item_id',
                'item_identifier',
                'id'
            ],
            [
                'id',
                'identifier',
                'event_id'
            ]
        );
    }

    // Acquisition events in which this object is involved
    public function acquisitions()
    {
        return $this->events()->acquisition();
    }

    // Production events in which this object is involved
    public function productions()
    {
        return $this->events()->production();
    }

    // Manipulation events in which this object is involved
    public function manipulations()
    {
        return $this->events()->manipulation();
    }

    // Observation events in which this object is involved
    public function observations()
    {
        return $this->events()->observation();
    }

}
