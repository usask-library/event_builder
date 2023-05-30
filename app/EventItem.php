<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
 * The EventItem model is used to facilitate joining the Item identifiers stored as part of the Event
 * to the Artefact/Objects to which those item identifiers may refer.  It makes the many-to-many-to-many
 * relationship defined in the Artefact model easier to implement.
 */
class EventItem extends Model
{
    protected $table = 'event_item';
}
