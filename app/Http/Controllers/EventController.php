<?php

namespace App\Http\Controllers;

use App\Event;
use App\Exports\Export;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param null $class Used to filler event results to those of the specified class
     * @return Application|Factory|Response|View|BinaryFileResponse
     */
    public function index(Request $request, $class = null)
    {
        if (in_array(strtolower($class), ['acquisition', 'production', 'manipulation', 'observation'])) {
            $events = Event::$class()->orderBy('id', 'asc')->paginate(1000);
        } else {
            $events = Event::orderBy('id', 'asc')->paginate(1000);
        }

        switch ($request->prefers(['text/html', 'application/rdf+xml', 'application/xml', 'text/csv'])) {
            case 'application/rdf+xml':
                $people = $events->pluck('people')->flatten()->unique('id');
                $places = $events->pluck('places')->flatten()->unique('id');
                $objects = $events->pluck('items')->flatten()->unique('id');

                return response()
                    ->view('export.rdf.index', compact('events', 'people', 'places', 'objects'))
                    ->header('Content-Type', 'application/rdf+xml');
                break;
            case 'application/xml':
                $export = new Export($events);
                return response($export->toXML())->header('Content-Type', 'application/xml');
                break;
            case 'text/csv':
                $export = new Export($events);
                if ($request->has('csv_format') && ($request->get('csv_format') == 'graph')) {
                    return response()->download($export->toCSVGraph('ZIP'));
                } else {
                    return response()->download($export->toCSV());
                }
                break;
        }

        return view('event.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $event->items()->detach();
        $event->people()->detach();
        $event->places()->detach();
        $event->delete();

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $format
     * @return BinaryFileResponse|Response|void
     */
    public function export(Request $request, $format)
    {
        $export = new Export();

        switch (strtolower($format)) {
            case 'xml':
                return response($export->toXML())->header('Content-Type', 'application/xml');
                break;
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
        }
        return;
    }
}
