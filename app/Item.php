<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

class Item extends Model
{
    // Table names used here more closely mimic the original database used for the Digital ARK project.
    // Events are linked using the identifier names, which exist in all the XML documents, even though the actual
    // items/objects to which they refer may not be cataloged in that application.
    protected $table = 'item_identifier';

    // Fields that can be mass assigned
    protected $fillable = ['identifier', 'item_id'];

    // Make sure the name is always available in the model
    protected $appends = ['name'];


    /**
     * Set the name of the current item.
     *
     * Items are document level identifiers, and as such do not have a "name" of their own.
     * The identifiers may be reference an Artefact, which does have a name field.  If a related Artefact does exist,
     * the "name" attribute of this item will use that value, otherwise it will be labelled as "not described"
     *
     * @return mixed|string
     */
    public function getNameAttribute() {
        // The name of the object is stored in a related table
        $object = $this->object;
        if (empty($object) || empty($object->name)) {
            return 'not described';
        }
        return $object->name;
    }

    /**
     * Get a list of all other "aliases"
     *
     * Items are document level identifiers (or "aliases") which may reference an Artefact object.
     * This method returns the list of all identifiers/aliases for the referenced Artefact
     *
     * @return mixed
     */
    public function getAliasesAttribute()
    {
        return Item::where('item_id', $this->item_id)->whereNotNull('item_id')->get();
    }

    /**
     * Returns the Artefact (object) to which this Item refers
     *
     * @return BelongsTo
     */
    public function object()
    {
        return $this->belongsTo('App\Artefact', 'item_id', 'id');
    }

    // All events this item is involved in
    public function events()
    {
        return $this->belongsToMany('App\Event', 'event_item', 'item_identifier', 'event_id', 'identifier');
    }

    // Acquisition events in which this item is involved
    public function acquisitions()
    {
        return $this->events()->acquisition();
    }

    // Production events in which this item is involved
    public function productions()
    {
        return $this->events()->production();
    }

    // Manipulation events in which this item is involved
    public function manipulations()
    {
        return $this->events()->manipulation();
    }

    // Observation events in which this item is involved
    public function observations()
    {
        return $this->events()->observation();
    }

}
