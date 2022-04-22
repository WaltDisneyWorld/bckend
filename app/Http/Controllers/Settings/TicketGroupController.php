<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Master\MaTicketStatus;
use App\Models\QueueSetting;
use App\Models\Ticket;
use App\Models\TicketGroup;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketGroupController extends Controller
{
    private $ticketStatus = [];

    function __construct()
    {
        $ticketStatus = TicketStatus::all()->mapWithKeys(function ($status) {
            return [$status['code'] => $status['id']];
        });

        $this->ticketStatus = $ticketStatus;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        try {
            $ticketGroup = TicketGroup::whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->whereUserId($user->id);
            })->orderBy('ticket_group_prefix', 'ASC')->get();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }

        return $this->sendOkResponse($ticketGroup);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $queueSetting = QueueSetting::whereUserId($user->id)->first();

        if (!$queueSetting) {
            return $this->sendBadResponse(null, 'Queue Setting not found');
        }

        $validator = Validator::make($request->all(), [
            'ticket_group_prefix' => ['required', 'string'],
            'description' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        // Check if duplicate
        $ticketGroup = TicketGroup::whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })
            ->where('ticket_group_prefix', $request->input('ticket_group_prefix', ''))
            ->get();

        if (count($ticketGroup) >= 1) {
            $validator = Validator::make($request->all(), [
                'ticket_group_prefix' => ['unique:ticket_group,ticket_group_prefix']
            ]);
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $code_prefix = (string)$queueSetting->id;
        $ticketGroup = new TicketGroup();
        $ticketGroup->queue_setting_id = $queueSetting->id;
        $ticketGroup->ticket_group_code = uniqid($code_prefix);
        $ticketGroup->ticket_group_prefix = $request->input('ticket_group_prefix', '');
        $ticketGroup->description = $request->input('description', '');

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Saved');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
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
        $user = Auth::user();

        try {
            $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->whereUserId($user->id);
            })->first();

            if ($ticketGroup) {
                return $this->sendOkResponse($ticketGroup, 'Ticket Group Found');
            }
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $validator = Validator::make($request->all(), [
            'ticket_group_prefix' => ['required', 'string'],
            'description' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $ticketGroup->ticket_group_prefix = $request->input('ticket_group_prefix', '');
        $ticketGroup->description = $request->input('description', '');

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Saved');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
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
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        try {
            $result = $ticketGroup->delete();
            if ($result) {
                return $this->sendOkResponse($result, 'Ticket Group Deleted');
            }
            return $this->sendBadResponse(false, "Cannot Delete Ticket Group");
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function ticketGroupActive($id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $ticketGroup->active = 1;
        $ticketGroup->active_count++;

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Activated');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function ticketGroupInactive($id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $ticketGroup->active = 0;

        try {
            $ticketGroup->save();
            $this->ticketLostUpdate($ticketGroup);
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Inactivated');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    private function ticketLostUpdate(TicketGroup $ticketGroup)
    {
        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];
        $remainTickets = Ticket::whereTicketGroupId($ticketGroup->id)
            ->whereTicketGroupActiveCount($ticketGroup->ticket_group_active_count)
            ->whereIn('status', $status)
            ->get();

        if ($remainTickets == 0) {
            return true;
        }

        try {
            $now = Carbon::now();
            DB::beginTransaction();
            foreach ($remainTickets as $ticket) {
                if ($ticket->is_postpone == 1) {
                    // Lost
                    $ticket->status = $this->ticketStatus['LOST'];
                    $ticket->lost_time = $now->toDateTimeString();
                } else {
                    // Reject
                    $ticket->status = $this->ticketStatus['REJECT'];
                    $ticket->reject_time = $now->toDateTimeString();
                }
                $ticket->save();
            }
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('ticketLostUpdate: Cannot update Lost Ticket');
            DB::rollback();
            return false;
        }

        return true;
    }
}
