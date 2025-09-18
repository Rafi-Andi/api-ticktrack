<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Resources\TicketResource;
use App\Http\Resources\UserResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|min:4|max:16',
            'description' => 'required|string|min:8|max:256',
            'priority' => 'in:high,low,medium|required|string',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $ticket = new Ticket;
            $ticket->user_id = $user->id;
            $ticket->code = "TIC-" . rand(1000, 9999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];
            $ticket->save();

            DB::commit();

            return response()->json([
                "message" => "berhasil menambahkan ticket",
                "data" => new TicketResource($ticket)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'gagal menambahkan ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Ticket::query();

            $query->orderBy('created_at', 'desc');

            if (auth()->user()->role == "user") {
                $query->where("user_id", auth()->user()->id);
            }
            if ($request->search) {
                $query->where("code", "like", "%" . $request->search . "%")->orWhere("title", "like", "%" . $request->search . "%");
            }
            if ($request->status) {
                $query->where("status", $request->status);
            }
            if ($request->priority) {
                $query->where("priority", $request->priority);
            }

            $tickets = $query->get();

            return response()->json([
                "message" => "Data tiket berhasil ditampilkan",
                "data" => TicketResource::collection($tickets)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'gagal menambahkan ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $ticket = Ticket::query()->where('code', $code)->first();

            if(!$ticket){
                return response()->json([
                    "message" => "Tiket tidak ditemukan",
                    "data" => null
                ]);
            }

            if(auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id){
                return response()->json([
                    "message" => "Tiket ini tidak boleh di akses",
                    "data" => null
                ]);
            }

            return response()->json([
                "message" => "tiket berhasil ditampilkan",
                "data" => new TicketResource($ticket)
            ]);


        } catch (Exception $e) {
            return response()->json([
                'message' => 'gagal menampilkan tiket ' . $code . ' ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
