<?php

namespace App\Http\Controllers;

use App\Event;
use App\Http\Requests\OrganizerRequest;
use App\Repository\UserRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrganizerController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Event
     */
    private $event;

    /**
     * OrganizerController constructor.
     */
    public function __construct(UserRepository $userRepository, User $user, Event $event)
    {
        $this->userRepository = $userRepository;
        $this->user = $user;
        $this->event = $event;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organizers = $this->userRepository->getOrganizer();
        return view('admin.organizer.index',compact('organizers'));
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
    public function store(OrganizerRequest $request)
    {
        try {
            $password = str_random(6);
            $request['password'] = bcrypt($password);

            $request['type'] = 'org';
            $request['email_verified_at'] = Carbon::now();

            $user = $this->user->create($request->all());
            if ($user) {
                if ($user)
                    Mail::send('admin.email.addUser', ['userName' => $request->name, 'password' => $password], function ($m) use ($request) {
                        $m->to($request->email)->subject('User Registration Information');
                    });
                session()->flash('success', 'Organizer Successfully Created!');
                return back();

            } else {
                session()->flash('success', 'Organizer could not be Create!');
                return back();
            }


        } catch (\Exception $e) {
            $e = $e->getMessage();
            session()->flash('error', 'Exception :',$e);
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $id = (int)$id;
            $edits = $this->userRepository->findById($id);
            if ($edits->count() > 0) {
                $organizers = $this->userRepository->getOrganizer();

                return view('admin.organizer.index', compact('organizers', 'edits'));
            } else {
                session()->flash('error', 'Id could not be obtained!');
                return back();
            }

        } catch (\Exception $e) {
            $exception = $e->getMessage();
            session()->flash('error', 'EXCEPTION :' . $exception);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(OrganizerRequest $request, $id)
    {
        $id = (int)$id;
        try {
            $user = $this->userRepository->findById($id);
            $oldValue = $this->userRepository->findById($id);

            if ($user) {
                $request['type'] = 'org';
                $request['email_verified_at'] = Carbon::now();
                $update = $user->fill($request->all())->save();
                if ($update) {
                    session()->flash('success', 'Organizer Successfully updated!');
                    return redirect(route('organizer.index'));
                } else {
                    session()->flash('error', 'Organizer could not be update!');
                    return back();
                }
            }

        } catch (\Exception $e) {
            $exception = $e->getMessage();
            session()->flash('error', 'EXCEPTION:' . $exception);
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = (int)$id;
        try {
            $organizer = $this->userRepository->findById($id);
            if ($organizer) {
                $organizer->delete();
                session()->flash('success', 'User successfully deleted!');
                return back();
            } else {
                session()->flash('error', 'User could not be delete!');
                return back();
            }

        } catch (\Exception $e) {
            $exception = $e->getMessage();
            session()->flash('error', 'EXCEPTION' . $exception);
            return back();

        }
    }


    public function events()
    {
        $events = $this->event->get();
        return view('admin.events', compact('events'));
    }

    public function eventStatus($id)
    {
        try {
            $id = (int)$id;
            $event = $this->event->find($id);
            if ($event->status == 'active') {
                $event->status = 'inactive';
                $event->save();
                session()->flash('success', 'Event has been Activated!');
                return back();
            } else {
                $event->status = 'active';
                $event->save();
                session()->flash('success', 'Event has been Deactivated!');
                return back();
            }

        } catch (\Exception $e) {
            $exception = $e->getMessage();
            session()->flash('error', 'EXCEPTION : Something Went Wrong!' );
        }
    }
}
