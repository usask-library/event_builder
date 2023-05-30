<?php

namespace App\Http\Controllers;

use App\Artefact;
use App\Http\Requests\StoreArtefact;
use App\Http\Requests\UpdateArtefact;
use App\Item;
use App\Exports\Export;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArtefactController extends Controller
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
     * Display a list of all artefacts/objects
     *
     * @return View
     */
    public function index()
    {
        $objects = Artefact::all();
        return view('artefact.index', compact('objects'));
    }

    /**
     * Show the form for creating a new artefact/object.
     *
     * @return View
     */
    public function create()
    {
        $object = new Artefact();

        return view('artefact.create', compact('object'));
    }

    /**
     * Store a newly created artefact/object
     *
     * @param StoreArtefact $request
     * @return RedirectResponse
     */
    public function store(StoreArtefact $request)
    {
        $object = Artefact::create($request->validated());

        return redirect()->route('object.index')
            ->with('success', 'Added object <strong>' . $object->name . '</strong>');
    }

    /**
     * Display the specified artefact/object.
     *
     * @param Artefact $object
     * @return View
     */
    public function show(Artefact $object)
    {
        $export = new Export($object->events);
        $csvData = $export->toCSVGraph('STRING');

        return view('artefact.show', ['object' => $object, 'nodes' => $csvData['nodes'], 'links' => $csvData['edges']]);
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
     * Show the form for editing the specified artefact/object.
     *
     * @param Artefact $object
     * @return View
     */
    public function edit(Artefact $object)
    {
        return view('artefact.edit', compact('object'));
    }

    /**
     * Update the specified artefact/object
     *
     * @param UpdateArtefact $request
     * @param Artefact $object
     * @return RedirectResponse
     */
    public function update(UpdateArtefact $request, Artefact $object)
    {
        // Cannot update the ID if this object is involved in existing events
        $validated = $request->validated();
        if ($object->events->count() && ($validated['id'] != $object->id)) {
            return redirect()->back()
                ->with('error', 'The ID assigned to <strong>' . $object->name . '</strong> cannot be changed because it is involved in ' .
                    $object->events->count() . ' event(s).');
        } else {
            $object->update($validated);

            return redirect()->route('object.index')
                ->with('success', '<strong>' . $object->name . '</strong> was updated');
        }
    }

    /**
     * Remove the specified artefact/object.
     *
     * @param Artefact $object
     * @return RedirectResponse
     */
    public function destroy(Artefact $object)
    {
        // Do not delete an item if it is involved in any events
        if ($object->events->count()) {
            return redirect()->back()
                ->with('error', '<strong>' . $object->name . '</strong> is involved in ' .
                    $object->events->count() . ' event(s), and must be removed from those events before they can be deleted.');
        } else {
            $object->delete();

            return redirect()->back()
                ->with('success', '<strong>' . $object->name . '</strong> was deleted');
        }
    }

    /**
     * @param Request $request
     * @param Artefact $object
     * @param $format
     * @return BinaryFileResponse|Response|void
     */
    public function export(Request $request, Artefact $object, $format)
    {
        $export = new Export($object->events);

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
