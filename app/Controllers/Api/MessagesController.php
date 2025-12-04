<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Libraries\ValkeyClient;

class MessagesController extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/tickets/{id}/messages
     * List messages for a ticket
     */
    public function index($ticketId = null)
    {
        if ($ticketId === null) {
            return $this->failValidationErrors('Ticket ID is required');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('messages');
        $messages = $builder->where('ticket_id', $ticketId)
                            ->orderBy('created_at', 'ASC')
                            ->get()
                            ->getResultArray();

        return $this->respond($messages);
    }

    /**
     * POST /api/tickets/{id}/messages
     * Store a new message
     */
    public function create($ticketId = null)
    {
        if ($ticketId === null) {
            return $this->failValidationErrors('Ticket ID is required');
        }

        $data = $this->request->getJSON(true);

        if (empty($data['body'])) {
            return $this->failValidationErrors('Message body is required');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('messages');

        $messageData = [
            'ticket_id'  => $ticketId,
            'sender_id'  => $data['sender_id'] ?? 1, // Default sender if not provided
            'body'       => $data['body'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $builder->insert($messageData);
        $messageId = $db->insertID();

        // Publish to Valkey for real-time updates
        $valkey = new ValkeyClient();
        $valkey->publish("ticket:{$ticketId}", array_merge($messageData, ['id' => $messageId]));

        $messageData['id'] = $messageId;

        return $this->respondCreated($messageData);
    }
}
