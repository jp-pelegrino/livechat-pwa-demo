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

        // Get 'since' parameter for polling (messages after a certain ID)
        $since = $this->request->getGet('since');

        $db = \Config\Database::connect();
        $builder = $db->table('messages');
        $builder->where('ticket_id', $ticketId);
        
        if ($since) {
            $builder->where('id >', $since);
        }
        
        $messages = $builder->orderBy('created_at', 'ASC')
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

        if (empty($data['username'])) {
            return $this->failValidationErrors('Username is required');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('messages');

        $messageData = [
            'ticket_id'  => $ticketId,
            'sender_id'  => $data['sender_id'] ?? 0,
            'username'   => htmlspecialchars($data['username'], ENT_QUOTES, 'UTF-8'),
            'body'       => htmlspecialchars($data['body'], ENT_QUOTES, 'UTF-8'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $builder->insert($messageData);
        $messageId = $db->insertID();

        // Publish to Valkey for real-time updates
        try {
            $valkey = new ValkeyClient();
            $valkey->publish("ticket:{$ticketId}", array_merge($messageData, ['id' => $messageId]));
        } catch (\Exception $e) {
            // Log but don't fail if Valkey is unavailable
            log_message('error', 'Valkey publish failed: ' . $e->getMessage());
        }

        $messageData['id'] = $messageId;

        return $this->respondCreated($messageData);
    }
}
