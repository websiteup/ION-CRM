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

    public function getBackgroundStyle()
    {
        if (!$this->board->background) {
            return '';
        }
        
        $backgrounds = $this->getBackgroundsProperty();
        if (isset($backgrounds[$this->board->background])) {
            $bg = $backgrounds[$this->board->background];
            return 'background: ' . $bg['value'] . ';';
        }
        
        return '';
    }

    // Predefined backgrounds (same as BoardViewComponent)
    public function getBackgroundsProperty()
    {
        return [
            // Gradient backgrounds
            'gradient-1' => [
                'name' => 'Dark Blue Purple',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
                'icon' => 'bi-circle-fill'
            ],
            'gradient-2' => [
                'name' => 'Light Blue Cyan',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #74b9ff 0%, #00b894 100%)',
                'icon' => 'bi-snow'
            ],
            'gradient-3' => [
                'name' => 'Dark Blue Cloud',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
                'icon' => 'bi-cloud-lightning'
            ],
            'gradient-4' => [
                'name' => 'Purple Pink',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'icon' => 'bi-stars'
            ],
            'gradient-5' => [
                'name' => 'Purple Light',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'icon' => 'bi-rainbow'
            ],
            'gradient-6' => [
                'name' => 'Orange Dark',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)',
                'icon' => 'bi-circle'
            ],
            'gradient-7' => [
                'name' => 'Pink Red',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #ff6b9d 0%, #c44569 100%)',
                'icon' => 'bi-heart'
            ],
            'gradient-8' => [
                'name' => 'Teal Green',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%)',
                'icon' => 'bi-globe'
            ],
            'gradient-9' => [
                'name' => 'Dark Blue Grey',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)',
                'icon' => 'bi-moon-stars'
            ],
            'gradient-10' => [
                'name' => 'Red Brown',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #c94b4b 0%, #4b134f 100%)',
                'icon' => 'bi-fire'
            ],
            'gradient-11' => [
                'name' => 'Ocean Blue',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)',
                'icon' => 'bi-droplet'
            ],
            'gradient-12' => [
                'name' => 'Sunset',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'icon' => 'bi-sun'
            ],
            // Solid colors
            'solid-blue' => [
                'name' => 'Blue',
                'type' => 'solid',
                'value' => '#0d6efd',
                'icon' => null
            ],
            'solid-orange' => [
                'name' => 'Orange',
                'type' => 'solid',
                'value' => '#fd7e14',
                'icon' => null
            ],
            'solid-green' => [
                'name' => 'Green',
                'type' => 'solid',
                'value' => '#198754',
                'icon' => null
            ],
            'solid-red' => [
                'name' => 'Red',
                'type' => 'solid',
                'value' => '#dc3545',
                'icon' => null
            ],
            'solid-purple' => [
                'name' => 'Purple',
                'type' => 'solid',
                'value' => '#6f42c1',
                'icon' => null
            ],
            'solid-pink' => [
                'name' => 'Pink',
                'type' => 'solid',
                'value' => '#d63384',
                'icon' => null
            ],
            'solid-teal' => [
                'name' => 'Teal',
                'type' => 'solid',
                'value' => '#20c997',
                'icon' => null
            ],
            'solid-cyan' => [
                'name' => 'Cyan',
                'type' => 'solid',
                'value' => '#0dcaf0',
                'icon' => null
            ],
            'solid-grey' => [
                'name' => 'Grey',
                'type' => 'solid',
                'value' => '#6c757d',
                'icon' => null
            ],
            'solid-dark' => [
                'name' => 'Dark Grey',
                'type' => 'solid',
                'value' => '#212529',
                'icon' => null
            ],
        ];
    }

    public function render()
    {
        return view('livewire.public.public-board-component', [
            'board' => $this->board,
        ])->layout('layouts.public', [
            'boardBackground' => $this->getBackgroundStyle()
        ]);
    }
}

