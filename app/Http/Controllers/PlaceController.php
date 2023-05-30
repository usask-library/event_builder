<?php

namespace App\Http\Controllers;

use App\Exports\Export;
use App\Http\Requests\StorePlace;
use App\Http\Requests\UpdatePlace;
use App\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlaceController extends Controller
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
     * Display a list of all places
     *
     * @return View
     */
    public function index()
    {
        $places = Place::all();

        return view('place.index', compact('places'));
    }

    /**
     * Display the form for creating a new place
     *
     * @return View
     */
    public function create()
    {
        $place = new Place();

        return view('place.create', compact('place'));
    }

    /**
     * Save the newly created place
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(StorePlace $request)
    {
        $place = Place::create($request->validated());

        return redirect()->route('place.index')
            ->with('success', 'Added place <strong>' . $place->name . '</strong>');
    }

    /**
     * Display the specified place and their associated events.
     *
     * @param Place $place
     * @return View
     */
    public function show(Place $place)
    {
        $export = new Export($place->events);
        $csvData = $export->toCSVGraph('STRING');
        return view('place.show', ['place' => $place, 'nodes' => $csvData['nodes'], 'links' => $csvData['edges']]);
    }

    /**
     * Display the form for editing the specified person
     *
     * @param Place $place
     * @return View
     */
    public function edit(Place $place)
    {
        return view('place.edit', compact('place'));
    }

    /**
     * Update the specified person.
     *
     * @param Request $request
     * @param Place $place
     * @return RedirectResponse
     */
    public function update(UpdatePlace $request, Place $place)
    {
        $place->update($request->validated());

        return redirect()->route('place.show', $place)
            ->with('success', 'Updated <strong>' . $place->name . '</strong>');
    }

    /**
     * Remove the specified place.
     *
     * We err on the side of caution, and will not delete places with associated events.
     * Places should first be removed from all associated events and then deleted.
     *
     *
     * @param Place $place
     * @return RedirectResponse
     */
    public function destroy(Place $place)
    {
        // Do not delete a person if they are involved in any events
        if ($place->events->count()) {
            return redirect()->route('place.show', $place)
                ->with('error', '<strong>' . $place->name . '</strong> is involved in ' .
                    $place->events->count() . ' event(s), and must be removed from those events before it can be deleted.');
        } else {
            $place->delete();

            return redirect()->route('place.index')
                ->with('success', '<strong>' . $place->name . '</strong> was deleted');
        }
    }

    /**
     * @param Request $request
     * @param Place $place
     * @param $format
     * @return BinaryFileResponse|Response
     */
    public function export(Request $request, Place $place, $format)
    {
        $export = new Export($place->events);

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
