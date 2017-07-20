<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Requests\TeamRequest;
use App\Http\Requests\UploadTicketRequest;
use App\Team;
use App\Registration;
use App\TeamMember;
use App\User;
use App\Event;
use App\Confirmation;
use Auth;
use Session;
use PDF;

class PagesController extends Controller
{
    function dashboard(){
        $events = Auth::user()->events;
        $teamEvents = Auth::user()->teamEvents();        
        return view('pages.dashboard')->with('events', $events)->with('teamEvents', $teamEvents);
    }
    function about(){
        return view('pages.about');
    }
    function events(){
        $registeredTeam = null;
        if(Auth::check()){
            $events = Event::all()->reject(function($event, $key){          
                return Auth::user()->hasRegisteredEvent($event->id);
            });
            $events = $events->all();
        }
        else{
            $events = Event::all();                 
        }
        return view('pages.events')->with('events', $events);
    }
    function register($id){
        $event = Event::find($id);
        $user = Auth::user();                                  
        $response = [];  
        $registered_events = $user->events;
        foreach($registered_events as $registered_event){
            // Date of the event to be registered
            $event_date = date_create($event->event_date);
            // Date of the registered event
            $registered_event_date = date_create($registered_event->event_date);
            // Start and end time of event being registered
            $start_time = strtotime($event->start_time);
            $end_time = strtotime($event->end_time);       
            //  Start and end time of event already registered
            $registered_start_time = strtotime($registered_event->start_time);
            $registered_end_time = strtotime($registered_event->end_time);                
            // Check whether they occur in parallel
            if($event_date == $registered_event_date){
                if(($registered_start_time <= $start_time && $start_time < $registered_end_time) || ($end_time > $registered_start_time && $end_time <= $registered_end_time)){
                    $response['error'] = true;
                    $response['message'] = "Sorry! you have registered a parallel event $registered_event->title";
                    return response()->json($response);                    
                }                    
            }
        }
        $user->events()->save($event);
        $response['error'] = false;
        return response()->json($response);
    }
    function unregister($id){
        $event = Event::find($id);
        $user = Auth::user();
        $event->users()->detach($user->id);        
        return  redirect()->route('pages.dashboard');
    }
    function createTeam($event_id){
        $team = new Team();
        return view('teams.create')->with('team', $team);
    }
    function registerTeam(TeamRequest $request, $event_id){
        $event  = Event::find($event_id);                 
        $input = Request::all();
        $team  = new Team($input);
        $team->user_id = Auth::user()->id;
        $team->save();
        $team_members_emails = explode(',', $input['team_members']);
        $team_members_users = User::all()->whereIn('email', $team_members_emails);
        foreach($team_members_users as $team_member_user){
            $team_member = new TeamMember();
            $team_member->team_id = $team->id;
            $team_member->user_id = $team_member_user->id;
            $team->teamMembers()->save($team_member);
        }
        $team->events()->save($event);
        Session::flash('success', 'Team registered and event added to dashboard!');
        return redirect()->route('pages.events');
    }
    function unregisterTeam($event_id, $id){
        $team = Team::find($id);
        $event  = Event::find($event_id);                         
        $event->teams()->detach($id);
        $team->teamMembers()->delete();
        Team::destroy($team->id);
        return  redirect()->route('pages.dashboard');
    }
    function getCollegeMates(){
        $user  = Auth::user();
        $userEmails = User::where('college_id', $user->college_id)->where('id', '<>', $user->id)->get(['email']);
        return response()->json($userEmails);
    }
    function confirm(){
        $confirmation = new Confirmation();
        Auth::user()->confirmation()->save($confirmation);
        return redirect()->route('pages.dashboard');
    }
    function downloadTicket(){
        $pdf = PDF::loadView('pages.ticket');
        return $pdf->download('ticket.pdf');
    }
    function uploadTicketImage(UploadTicketRequest $request){
        // Check if the student can upload ticket for approval
        if(!Auth::user()->needApproval()){
            Session::flash('success', 'Sorry! Your verification and payment will be done by one of your team leaders');
            return redirect()->route('pages.dashboard');            
        }
        $extension = $request->file('ticket')->getClientOriginalExtension();
        $filename = 'ticket_' . Auth::user()->id . '.' . $extension;
        $confirmation = Auth::user()->confirmation;
        $request->file('ticket')->move('uploads/tickets', $filename);
        $confirmation->file_name = $filename;
        $confirmation->save();
        return redirect()->route('pages.dashboard');
    }
}
