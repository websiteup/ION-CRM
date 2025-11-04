<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Board;

class PublicBoardComponent extends Component
{
    public $hash;
    public $board;

    public function mount($hash)
    {
        $this->hash = $hash;
        $this->board = Board::with(['columns.tasks' => function($query) {
            $query->orderBy('position');
        }, 'columns.tasks.assignedUser', 'columns.tasks.labels'])
            ->where('public_hash', $hash)
            ->where('is_public', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.public.public-board-component', [
            'board' => $this->board,
        ])->layout('layouts.public');
    }
}

