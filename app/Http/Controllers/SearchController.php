<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'all');
        $results = [];

        if (strlen($query) >= 2) {
            $user = $request->user();
            $term = '%' . $query . '%';

            if (in_array($type, ['all', 'projects'])) {
                $projects = $user->ownedProjects()
                    ->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('description', 'like', $term))
                    ->limit(10)
                    ->get(['id', 'name', 'color', 'status'])
                    ->map(fn ($p) => ['type' => 'project', 'id' => $p->id, 'title' => $p->name, 'subtitle' => $p->status, 'color' => $p->color, 'url' => "/projects/{$p->id}"]);
                $results = array_merge($results, $projects->toArray());
            }

            if (in_array($type, ['all', 'tasks'])) {
                $tasks = $user->tasks()
                    ->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('description', 'like', $term))
                    ->with('project:id,name,color')
                    ->limit(10)
                    ->get(['id', 'title', 'status', 'priority', 'project_id'])
                    ->map(fn ($t) => ['type' => 'task', 'id' => $t->id, 'title' => $t->title, 'subtitle' => "{$t->status} · {$t->priority}", 'color' => $t->project?->color, 'url' => "/projects/{$t->project_id}"]);
                $results = array_merge($results, $tasks->toArray());
            }

            if (in_array($type, ['all', 'notes'])) {
                $notes = $user->notes()
                    ->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('body', 'like', $term))
                    ->limit(10)
                    ->get(['id', 'title'])
                    ->map(fn ($n) => ['type' => 'note', 'id' => $n->id, 'title' => $n->title, 'subtitle' => 'Note', 'url' => "/notes/{$n->id}"]);
                $results = array_merge($results, $notes->toArray());
            }

            if (in_array($type, ['all', 'contacts'])) {
                $contacts = $user->contacts()
                    ->where(fn ($q) => $q->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('company', 'like', $term))
                    ->limit(10)
                    ->get(['id', 'first_name', 'last_name', 'company', 'email'])
                    ->map(fn ($c) => ['type' => 'contact', 'id' => $c->id, 'title' => trim("{$c->first_name} {$c->last_name}"), 'subtitle' => $c->company ?? $c->email, 'url' => "/contacts/{$c->id}"]);
                $results = array_merge($results, $contacts->toArray());
            }

            if (in_array($type, ['all', 'goals'])) {
                $goalQuery = $user->ownedProjects()->with(['goals' => fn ($q) => $q->where('title', 'like', $term)->limit(10)])->get();
                foreach ($goalQuery as $project) {
                    foreach ($project->goals as $goal) {
                        $results[] = ['type' => 'goal', 'id' => $goal->id, 'title' => $goal->title, 'subtitle' => $project->name, 'color' => $project->color, 'url' => "/projects/{$project->id}"];
                    }
                }
            }
        }

        return Inertia::render('Search/Index', [
            'results' => $results,
            'query' => $query,
            'type' => $type,
        ]);
    }
}
