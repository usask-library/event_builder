<?php

namespace App\Http\Controllers;

use App\Artefact;
use App\Http\Requests\StoreItem;
use App\Http\Requests\UpdateItem;
use App\Item;
use App\Exports\Export;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemController extends Controller
{
    /**
     * Constructor.
     *
     * Ensure all methods that can create or modify data are behind authentication
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'export']);
    }

    /**
     * Show the form for creating a new item.
     *
     * @return View
     */
    public function index()
    {
        $items = Item::with('events')->get();
        return view('item.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $item = new Item();
        $objects = Artefact::orderBy('name')->get();

        return view('item.create', compact('item', 'objects'));
    }

    /**
     * Store a newly created item.
     *
     * @param StoreItem $request
     * @return RedirectResponse
     */
    public function store(StoreItem $request)
    {
        $item = Artefact::create($request->validated());

        return redirect()->route('item.index')
            ->with('success', 'Added object <strong>' . $object->name . '</strong>');
    }

    /**
     * Display the specified item.
     *
     * @param Item $item
     * @return View
     */
    public function show(Item $item)
    {
        $export = new Export($item->events);
        $csvData = $export->toCSVGraph('STRING');

        return view('item.show', ['item' => $item, 'nodes' => $csvData['nodes'], 'links' => $csvData['edges']]);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return View
     */
    public function display($id)
    {
        $collection = Item::where('item_id', $id)->with('events')->get()->groupBy(function ($item, $key) {
            return implode('|', [$item['item_id'], $item['name']]);
        });
        $events = [];
        $nodes = [];
        $links = [];
        foreach ($collection as $key => $subcollection) {
            foreach ($subcollection as $object) {
                foreach ($object->events as $event) {
                    $events[] = $event;
                }
            }
        }
        if (! empty($events)) {
            $export = new Export($events);
            $csvData = $export->toCSVGraph('STRING');
            $nodes = $csvData['nodes'];
            $links = $csvData['edges'];
        }

        if ($collection->isNotEmpty()) {
            return view('item.summary', compact('collection', 'nodes', 'links'));
        }
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param Item $item
     * @return View
     */
    public function edit(Item $item)
    {
        $objects = Artefact::orderBy('name')->get();

        return view('item.edit', compact('item', 'objects'));
    }

    /**
     * Update the specified item
     *
     * @param UpdateItem $request
     * @param Item $item
     * @return RedirectResponse
     */
    public function update(UpdateItem $request, Item $item)
    {
        // Cannot update the identifier if this item is involved in existing events
        $validated = $request->validated();
        if ($item->events->count() && ($validated['identifier'] != $item->identifier)) {
            return redirect()->back()
                ->with('error', 'The identifier assigned to <strong>' . $item->identifier . '</strong> cannot be changed because it is involved in ' .
                    $item->events->count() . ' event(s).');
        } else {
            $item->update($validated);

            return redirect()->route('item.show')
                ->with('success', '<strong>' . $item->identifier . '</strong> was updated');
        }
    }

    /**
     * Remove the specified item
     *
     * @param Item $item
     * @return RedirectResponse
     */
    public function destroy(Item $item)
    {
        // Do not delete an item if it is involved in any events
        if ($item->events->count()) {
            return redirect()->back()
                ->with('error', '<strong>' . $item->identifier . '</strong> is involved in ' .
                    $item->events->count() . ' event(s), and must be removed from those events before they can be deleted.');
        } else {
            $item->delete();

            return redirect()->back()
                ->with('success', '<strong>' . $item->identifier . '</strong> was deleted');
        }
    }

    /**
     * @param Request $request
     * @param Item $item
     * @param $format
     * @return BinaryFileResponse|Response|void
     */
    public function export(Request $request, Item $item, $format)
    {
        $export = new Export($item->events);

        switch (strtolower($format)) {
            case 'csv':
                if ($request->has('csv_format') && ($request->get('csv_format') == 'graph')) {
                    return response()->download($export->toCSVGraph('ZIP'));
                } else {
                    return response()->download($export->toCSV());
                }
                break;
            case 'graphviz':
                return response($export->toGraphviz())->header('Content-Type', 'text/plain]');
                break;
            case 'xml':
            default:
                return response($export->toXML())->header('Content-Type', 'application/xml');
                break;
        }
    }

}
