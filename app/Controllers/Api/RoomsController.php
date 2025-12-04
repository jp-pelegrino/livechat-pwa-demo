<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class RoomsController extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/rooms
     * List all public rooms
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $rooms = $db->table('rooms')
                    ->where('is_public', true)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

        return $this->respond($rooms);
    }

    /**
     * GET /api/rooms/{id}
     * Get a specific room
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('Room ID is required');
        }

        $db = \Config\Database::connect();
        $room = $db->table('rooms')
                   ->where('id', $id)
                   ->get()
                   ->getRowArray();

        if (!$room) {
            return $this->failNotFound('Room not found');
        }

        return $this->respond($room);
    }

    /**
     * POST /api/rooms
     * Create a new room
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['name'])) {
            return $this->failValidationErrors('Room name is required');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('rooms');

        $roomData = [
            'name'        => htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
            'description' => isset($data['description']) ? htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8') : null,
            'is_public'   => $data['is_public'] ?? true,
            'created_by'  => isset($data['created_by']) ? htmlspecialchars($data['created_by'], ENT_QUOTES, 'UTF-8') : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $builder->insert($roomData);
        $roomId = $db->insertID();

        $roomData['id'] = $roomId;

        return $this->respondCreated($roomData);
    }

    /**
     * GET /api/rooms/{id}/messages
     * Get messages for a room
     */
    public function messages($roomId = null)
    {
        if ($roomId === null) {
            return $this->failValidationErrors('Room ID is required');
        }

        // Get 'since' parameter for polling (messages after a certain ID)
        $since = $this->request->getGet('since');
        $limit = $this->request->getGet('limit') ?? 100;

        $db = \Config\Database::connect();
        $builder = $db->table('messages');
        $builder->where('ticket_id', $roomId); // Using ticket_id as room_id for backward compatibility

        if ($since) {
            $builder->where('id >', $since);
        }

        $messages = $builder->orderBy('created_at', 'ASC')
                            ->limit($limit)
                            ->get()
                            ->getResultArray();

        return $this->respond($messages);
    }
}
