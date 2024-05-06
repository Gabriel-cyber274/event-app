<?php

namespace App\Http\Controllers;

use App\Models\event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create_event(Request $request)
    {
        //
        $fields = Validator::make($request->all(),[
            'name'=> 'required|string',
            'date'=> 'required|string',
            'location'=> 'required|string',
            'sport_type'=> 'required|string',
            'description'=> 'required|string',
        ]);
        

        if($fields->fails()) {
            $response = [
                'errors'=> $fields->errors(),
                'success' => false
            ];

            return response($response);
        }
        
        $user = auth()->user();

        if(!$user->agent) {
            $response = [
                'errors'=> "you can't create an event",
                'success' => false
            ];
            return response($response);
        }else if($user->agent || $user->admin) {
            $event = event::create([
                'agent_id' => $user->id,
                'name'=> $request->name,
                'date'=> $request->date,
                'location'=> $request->location,
                'sport_type'=> $request->sport_type,
                'description'=> $request->description,
            ]);
            $response = [
                'event'=> $event,
                'message'=> 'event created successfully',
                'success' => true
            ];
            return response($response);
        }
        
    }


    public function get_all_created_events() {
        $user = auth()->user();
        $event = event::with(['Registrations'])->where('agent_id', $user->id)->get();

        
        if($user->agent || $user->admin) {
            $main_events = [];

            for ($i=0; $i < count($event); $i++) { 
                $registrations = [];

                for ($j=0; $j < count($event[$i]->registrations); $j++) { 
                    $userInfo = User::where('id', $event[$i]->registrations[$j]->user_id)->first();

                    $registrations[] = [
                        "id"=> $event[$i]->registrations[$j]->id,
                        "user_id"=> $event[$i]->registrations[$j]->user_id,
                        "event_id"=> $event[$i]->registrations[$j]->event_id,
                        "file_path"=> $event[$i]->registrations[$j]->file_path,
                        "user"=> $userInfo,
                        "created_at"=> $event[$i]->registrations[$j]->created_at,
                        "updated_at"=> $event[$i]->registrations[$j]->updated_at
                    ];
                }

                $main_events[]= [
                    "id"=> $event[$i]->id,
                    "agent_id"=> $event[$i]->agent_id,
                    "name"=> $event[$i]->name,
                    "date"=> $event[$i]->date,
                    "location"=> $event[$i]->location,
                    "sport_type"=> $event[$i]->sport_type,
                    "description"=> $event[$i]->description,
                    "created_at"=> $event[$i]->created_at,
                    "updated_at"=> $event[$i]->updated_at,
                    "registrations"=> $registrations,
                    "registered"=> count($registrations)
                ];
            }

            $response = [
                'event'=> $main_events,
                'message'=> 'events retrieved successfully',
                'success' => true
            ];

            return response($response);

        }else {
            $response = [
                'message'=> 'you are not an agent or admin',
                'success' => false
            ];
            return response($response);   
        }   
        
    }


    public function get_all_registered_events() {
        $user = auth()->user();
        $registrations = Registration::where('user_id', $user->id)->get();

        $eventids= [];

        foreach($registrations as $registration) {
            $eventids[]= $registration->event_id;
        }
        $events = event::find($eventids);


        $response = [
            'registered'=> $events,
            'message'=> 'registration retrieved successfully',
            'success' => true
        ];
        return response($response);

    }


    public function search_events($search) {

        $eventbyname = event::where('name', 'like', '%'.$search.'%')->get();
        $eventbydescription = event::where('description', 'like', '%'.$search.'%')->get();
        $eventbydate = event::where('date', 'like', '%'.$search.'%')->get();
        $eventbyLocation = event::where('location', 'like', '%'.$search.'%')->get();
        $eventbytype = event::where('sport_type', 'like', '%'.$search.'%')->get();

        $events = $eventbyname
            ->union($eventbydescription)
            ->union($eventbydate)
            ->union($eventbyLocation)
            ->union($eventbytype)
            ->unique();


        $response = [
            'searchResults'=> $events,
            'message'=> 'search retrieved successfully',
            'success' => true
        ];
        return response($response);


    }


    public function get_all_events() {
        $events= event::with('Registrations')->get();
        $user = auth()->user();

        
        if(!$user->admin) {
            $response = [
                'message'=> 'you are not an admin',
                'success' => false
            ];
            return response($response);

        }else {
            $response = [
                'searchResults'=> $events,
                'message'=> 'events successfully',
                'success' => true
            ];
            return response($response);
        }

    }


    public function register_for_event(Request $request) {
        $request->validate([
            'file' => 'required|mimes:jpg,png,pdf|max:2048',
            'event_id'=> 'required'
        ]);
    
        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $user = auth()->user();

        $event = event::where('agent_id', $user->id)->get();
        $registeredBefore = Registration::where('user_id', $user->id)->where('event_id', $request->event_id)->get();

        if(count($event) > 0) {
            $response = [
                'message'=> "you can't register for an event you created",
                'success' => false
            ];
            return response($response);

        }
        else if (count($registeredBefore) > 0) {
            $response = [
                'message'=> "you can't register for an event twice",
                'success' => false
            ];
            return response($response);
        }
        else {
            $registration = Registration::create([
                'user_id' => $user->id,
                'event_id'=> $request->event_id,
                'file_path'=> $path,
            ]);
    
    
            $response = [
                'registration'=> $registration,
                'message'=> 'registration successful',
                'success' => true
            ];
            return response($response);
        }

    
    }





    /**
     * Display the specified resource.
     */
    public function show(event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(event $event)
    {
        //
    }
}
