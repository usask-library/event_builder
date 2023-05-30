<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerson;
use App\Http\Requests\UpdatePerson;
use App\Person;
use App\Exports\Export;
use App\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersonController extends Controller
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
     * Display a list of all people
     *
     * @return View
     */
    public function index()
    {
        $people = Person::all();

        return view('person.index', compact('people'));
    }

    /**
     * Display the form for creating a new person
     *
     * @return View
     */
    public function create()
    {
        $person = new Person();
        $roles = Role::orderBy('name')->get();

        return view('person.create', compact('person', 'roles'));
    }

    /**
     * Save the newly created Person
     *
     * @param StorePerson $request
     * @return RedirectResponse
     */
    public function store(StorePerson $request)
    {
        $person = Person::create($request->validated());
        $person->roles()->sync($request->validated()['roles']);

        return redirect()->route('person.index');
    }

    /**
     * Display the specified person and their associated events.
     *
     * @param Person $person
     * @return View
     */
    public function show(Person $person)
    {
        $export = new Export($person->events);
        $csvData = $export->toCSVGraph('STRING');

        return view('person.show', ['person' => $person, 'nodes' => $csvData['nodes'], 'links' => $csvData['edges']]);
    }

    /**
     * Display the form for editing the specified person
     *
     * @param Person $person
     * @return View
     */
    public function edit(Person $person)
    {
        $roles = Role::orderBy('name')->get();
        return view('person.edit', compact('person', 'roles'));
    }

    /**
     * Update the specified person and their roles.
     *
     * @param UpdatePerson $request
     * @param Person $person
     * @return RedirectResponse
     */
    public function update(UpdatePerson $request, Person $person)
    {
        $person->update($request->validated());
        $person->roles()->sync($request->validated()['roles']);

        return redirect()->route('person.show', $person)
            ->with('success', 'Updated <strong>' . implode(' ', [$person->first, $person->last]) . '</strong>');
    }

    /**
     * Remove the specified person.
     *
     * We err on the side of caution, and will not delete people with associated events.
     * Persons should first be removed from all associated events and then deleted.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy(Person $person)
    {
        // Do not delete a person if they are involved in any events
        if ($person->events->count()) {
            return redirect()->route('person.show', $person)
                ->with('error', '<strong>' . implode(' ', [$person->first, $person->last]) . '</strong> is involved in ' .
                    $person->events->count() . ' event(s), and must be removed from those events before they can be deleted.');
        } else {
            $person->roles()->detach();
            $person->delete();

            return redirect()->route('person.index')
                ->with('success', '<strong>' . implode(' ', [$person->first, $person->last]) . '</strong> was deleted');
        }
    }

    /**
     * Export all the events the specified person is involved in
     *
     * @param Request $request
     * @param Person $person
     * @param $format
     * @return BinaryFileResponse|Response
     */
    public function export(Request $request, Person $person, $format)
    {
        $export = new Export($person->events);

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
