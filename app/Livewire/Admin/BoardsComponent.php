<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Project;
use App\Models\Board;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BoardsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $projectFilter = '';
    public $boardId = null;
    public $boardName = '';
    public $projectId = '';
    public $isPublic = false;
    public $showModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }
    }

    public function openModal($id = null)
    {
        if ($id) {
            $board = Board::findOrFail($id);
            $this->boardId = $id;
            $this->boardName = $board->name;
            $this->projectId = $board->project_id;
            $this->isPublic = $board->is_public;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->boardId = null;
        $this->boardName = '';
        $this->projectId = '';
        $this->isPublic = false;
        $this->resetValidation();
    }

    public function saveBoard()
    {
        $rules = [
            'boardName' => 'required|string|max:255',
            'projectId' => 'required|exists:projects,id',
        ];

        if ($this->boardId) {
            $rules['boardName'] = 'required|string|max:255';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->boardName,
            'project_id' => $this->projectId,
            'is_public' => $this->boardId ? $this->isPublic : false, // Board-urile noi sunt întotdeauna private
        ];

        // Only admin can make board public (only when editing)
        if ($this->boardId && $this->isPublic && !Auth::user()->hasRole('admin')) {
            $this->isPublic = false;
            $data['is_public'] = false;
        }

        if ($this->boardId) {
            $board = Board::findOrFail($this->boardId);
            $data['updated_by'] = Auth::id();
            $board->update($data);
            
            // Generate hash if making public
            if ($this->isPublic && !$board->public_hash) {
                $board->generatePublicHash();
            }
            
            session()->flash('message', 'Board actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            $board = Board::create($data);
            
            // Board-urile noi sunt întotdeauna private, deci nu generăm hash
            
            // Add creator as admin member
            $board->members()->attach(Auth::id(), ['role' => 'admin']);
            
            // Create default columns
            $defaultColumns = ['To Do', 'In Progress', 'Done'];
            foreach ($defaultColumns as $index => $columnName) {
                $board->columns()->create([
                    'name' => $columnName,
                    'position' => $index,
                    'color' => $index === 0 ? '#6c757d' : ($index === 1 ? '#0d6efd' : '#198754'),
                ]);
            }
            
            session()->flash('message', 'Board creat cu succes!');
        }

        $this->closeModal();
    }

    public function deleteBoard($id)
    {
        Board::findOrFail($id)->delete();
        session()->flash('message', 'Board șters cu succes!');
    }

    public function togglePublic($id)
    {
        $board = Board::findOrFail($id);
        if (!Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Doar administratorii pot face board-uri publice!');
            return;
        }

        $board->is_public = !$board->is_public;
        if ($board->is_public && !$board->public_hash) {
            $board->generatePublicHash();
        }
        $board->save();
        session()->flash('message', 'Status board actualizat cu succes!');
    }

    public function render()
    {
        $query = Board::with(['project', 'project.client']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->projectFilter) {
            $query->where('project_id', $this->projectFilter);
        }

        $boards = $query->orderBy('created_at', 'desc')->paginate(10);
        $projects = Project::with('client')->orderBy('name')->get();

        return view('livewire.admin.boards-component', [
            'boards' => $boards,
            'projects' => $projects,
        ])->layout('layouts.app');
    }
}

