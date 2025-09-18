<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketReplyResource;
use Exception;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TicketReplyController extends Controller
{
    public function store(Request $request, $code)
    {
        $data = $request->validate([
            "content" => "required|string|max:256",
            "status" => auth()->user()->role == "admin" ? "required|in:open,in_progress,resolved,rejected" : "nullable",
        ]);

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    "message" => "Tiket ini tidak boleh di akses",
                    "data" => null
                ]);
            }
            $ticket_reply = new TicketReply();

            $ticket_reply->ticket_id = $ticket->id;
            $ticket_reply->user_id = auth()->user()->id;
            $ticket_reply->content = $data['content'];
            $ticket_reply->save();

            if ($data['status']) {
                $ticket->status = $data['status'];
                if ($data['status'] == "resolved") {
                    $ticket->completed_at = now();
                }
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                "message" => "berhasil reply ticket",
                "data" =>  new TicketReplyResource($ticket_reply)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'gagal menambahkan reply ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
