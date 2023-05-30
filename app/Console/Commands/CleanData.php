<?php

namespace App\Console\Commands;

use App\Person;
use App\Place;
use App\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:cleandata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete person, place and object data no longer present in the original database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Check for records that should be deleted -- data previously imported from the Ark database that
        // no longer exist there -- and delete them.

        // Look for people that can be deleted
        $deletionCandidate = DB::table('people')->whereRaw('updated_at < CURRENT_DATE')->get();
        foreach ($deletionCandidate as $item) {
            $person = Person::find($item->id);
            if (! DB::connection('pgsql2')->table('person')->where('person_id', $person->id)->exists()) {
                if ($person->events->count() > 0) {
                    $this->error('Person ' . $person->id . ' (' . implode(' ', [$person->first, $person->last]) . ') should be deleted but is involved in ' . $person->events->count() . ' events');
                } else {
                    DB::table('person_role')->where('person_id', $person->id)->delete();
                    DB::table('people')->where('id', $person->id)->delete();
                    $this->info('Person ' . $person->id . ' (' . implode(' ', [$person->first, $person->last]) . ') was deleted');
                }
            }
        }

        // Look for places that can be deleted
        $deletionCandidate = DB::table('places')->whereRaw('updated_at < CURRENT_DATE')->get();
        foreach ($deletionCandidate as $item) {
            $place = Place::find($item->id);
            if (! DB::connection('pgsql2')->table('location')->where('location_id', $place->id)->exists()) {
                if ($place->events->count() > 0) {
                    $this->error('Place ' . $place->id . ' (' . $place->name . ') should be deleted but is involved in ' . $place->events->count() . ' events');
                } else {
                    DB::table('places')->where('id', $place->id)->delete();
                    $this->info('Place ' . $place->id . ' (' . $place->name . ') was deleted');
                }
            }
        }

        // Look for items and related identifiers that can be deleted
        $deletionCandidate = DB::table('items')->whereRaw('updated_at < CURRENT_DATE')->get();
        foreach ($deletionCandidate as $object) {
            if (! DB::connection('pgsql2')->table('object')->where('object_id', $object->id)->exists()) {
                $hasEvents = [];
                $items = Item::where('item_id', $object->id)->get();
                foreach ($items as $item) {
                    if ($item->events->count() > 0) {
                        $this->error('Identifier ' . $item->id . ' (' . $item->identifier . ') should be deleted but is involved in ' . $item->events->count() . ' events');
                        foreach ($item->events as $event) {
                            $hasEvents[] = $item->id . '=>' . $event->id;
                        }
                    } else {
                        DB::table('item_identifier')->where('id', $item->id)->delete();
                        $this->info('Identifier ' . $item->id . ' (' . $item->identifier . ') was deleted');
                    }
                }
                if (! empty($hasEvents)) {
                    $this->error('Object ' . $object->id . ' (' . $object->name . ') should be deleted but is involved in these events: ' . implode('; ', $hasEvents));
                } else {
                    DB::table('items')->where('id', $object->id)->delete();
                    $this->info('Object ' . $object->id . ' (' . $object->name . ') was deleted');
                }
            }
        }

    }
}
