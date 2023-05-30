<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:refreshdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh person, place and object data from original database';

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
        // This extracts data directly from the source database via SQL queries
        // and populates the core event builder tables.

        // Import People
        $this->info('Importing people from primary database...');
        $people = DB::connection('pgsql2')->select('SELECT person_id, last, first FROM person');
        foreach ($people as $person) {
            DB::table('people')->updateOrInsert(
                ['id' => $person->person_id],
                ['last' => $person->last, 'first' => $person->first, 'updated_at' => now()]
            );
        }

        // Import Places
        $this->info('Importing locations from primary database...');
        $locations = DB::connection('pgsql2')->select('SELECT location_id, location_name FROM location');
        foreach ($locations as $location) {
            DB::table('places')->updateOrInsert(
                ['id' => $location->location_id],
                ['name' => $location->location_name, 'updated_at' => now()]
            );
        }

        // Import Objects
        $this->info('Importing objects from primary database...');
        $objects = DB::connection('pgsql2')->select('SELECT object_id, name FROM object');
        foreach ($objects as $object) {
            DB::table('items')->updateOrInsert(
                ['id' => $object->object_id],
                ['name' => $object->name, 'updated_at' => now()]
            );
        }

        // Roles and the people assigned to them come straight from the original database.
        // When importing, delete and data stored here and import data from the remote database.
        DB::table('person_role')->truncate();
        DB::table('roles')->truncate();

        // Import Roles
        $this->info('Importing roles from primary database...');
        $roles = DB::connection('pgsql2')->select('SELECT role_id, role_name FROM role');
        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'id' => $role->role_id,
                'name' => $role->role_name
            ]);
        }

        // Import Person Roles
        $this->info('Importing person roles from primary database...');
        $memberships = DB::connection('pgsql2')->select('SELECT person_id, role_id FROM person_role');
        foreach ($memberships as $membership) {
            DB::table('person_role')->insert([
                'person_id' => $membership->person_id,
                'role_id' => $membership->role_id,
            ]);
        }

        // Import Items
        //
        // Note that the original data has some integrity issues.
        // - The Identifiers in the object_matching table sometimes refer to multiple different objects in the objects table
        // - The XML documents only contain the identifier. In order to construct en event that involves specific people,
        //   places and objects, these identifiers can only refer to a single object
        //
        // For example, how can "BarCat00070" refer to both object 23 ("Large Hercules") and 24 ("Small Hercules Juvenis")
        //
        // The query used below to extract the object_matching data excludes these duplicates.  Ideally this
        // integrity issue would be fixed in the original database.
        $this->info('Importing object identifiers from primary database...');
        $identifiers = DB::connection('pgsql2')->select('SELECT DISTINCT * FROM object_matching WHERE identifier NOT IN (SELECT identifier FROM object_matching AS om INNER JOIN object AS o ON (o.object_id=om.object_id) GROUP BY om.identifier HAVING COUNT(om.identifier) > 1)');
        foreach ($identifiers as $identifier) {
            DB::table('item_identifier')->updateOrInsert(
                ['identifier' => $identifier->identifier],
                ['alias_id' => $identifier->object_matching_id, 'item_id' => $identifier->object_id, 'updated_at' => now()]
            );
//            DB::table('items')->updateOrInsert(
//                ['id' => $identifier->object_matching_id],
//                ['item_detail_id' => $identifier->object_id, 'identifier' => $identifier->identifier, 'updated_at' => now()]
//            );
        }
    }
}
