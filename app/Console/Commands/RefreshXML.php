<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RefreshXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:refreshxml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download fresh copies of the XML documents';

    protected $xmlFiles = [
        '1695Bra',
        '1695JP',
        '1695VC',
        'AMS2',
        'AMS7',
        'AMS8',
        'AMS10',
        'AMS18',
        'BarCat',
        'BarGen',
        'BarLeg',
        'BarLiC',
        'BarMin',
        'BarMla',
        'BarMli',
        'BarMpa',
        'BarSched',
        'BritCur',
        'CopeJulius',
        'CopePlat',
        'Courten',
        'Evelyn',
        'Grew',
        'Hub1664',
        'Hub1665',
        'Hub1669',
        'Mundy',
        'OxAnBen',
        'OxAnBor',
        'OxAnBorIt',
        'OxAnDonor',
        'OxAnFon',
        'OxAnPoin',
        'OxAnUffA',
//        'OxAnUff',
        'PastIN',
        'PetGaz',
        'PetMus',
        'Rarities',
        'RawlC865a',
        'RawlC865b',
        'RawlC865c',
        'RawlD912a',
        'RawlD912b',
        'Saltero1731',
        'Saltero1737',
        'Saltero1775',
        'Stirn',
        'ThorMus',
        'ThorRelic',
        'ThorSale',
        'Trad1656',
        'TradAsh1131',
        'Uffenbach',
        'UffOx',
        'WalkA',
        'WilOrn',
        'Wood',
//        'Woodward',
        'YAS17',
        'YAS19b',
        'YAS19g',
        'YAS27',
    ];

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
        foreach ($this->xmlFiles as $xmlFile) {
            $xmlUrl = 'https://drc.usask.ca/projects/ark/XML/' . $xmlFile . '/' . $xmlFile . '.xml';
            $this->info('Fetching ' . $xmlFile . '.xml from ' . $xmlUrl);

            try {
                $xml = file_get_contents($xmlUrl);
                Storage::disk('xml')->put($xmlFile . '.xml', $xml);
            } catch (\Exception $exception) {
                $this->error('Failed to retrieve ' . $xmlFile . '.xml from ' . $xmlUrl);
            }
        }
    }
}
