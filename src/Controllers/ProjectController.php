<?php

namespace App\Controllers;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProjectController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $projects = Project::with('tables')->get();
        return $this->render($response, 'projects/list', ['projects' => $projects]);
    }

    public function create(Request $request, Response $response): Response
    {
        return $this->render($response, 'projects/new');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);

        return $this->redirect($response, "/projects/{$project->id}");
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $project = Project::findOrFail($args['id']);
        $tables = $project->tables()->with('entries')->get();

        return $this->render($response, 'projects/view', [
            'project' => $project,
            'tables' => $tables
        ]);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $project = Project::findOrFail($args['id']);
        return $this->render($response, 'projects/edit', ['project' => $project]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $project = Project::findOrFail($args['id']);
        $data = $request->getParsedBody();

        $this->logger->info('Updating project', [
            'project_id' => $project->id,
            'data' => $data
        ]);

        $project->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);

        return $this->redirect($response, "/projects/{$project->id}");
    }

    public function clone(Request $request, Response $response, array $args): Response
    {
        $originalProject = Project::with('tables.entries')->findOrFail($args['id']);

        $this->logger->info('Cloning project', [
            'original_project_id' => $originalProject->id
        ]);

        // Create new project with "(Copy)" suffix
        $newProject = Project::create([
            'name' => $originalProject->name . ' (Copy)',
            'description' => $originalProject->description
        ]);

        // Clone all tables and their entries
        foreach ($originalProject->tables as $table) {
            $newTable = $newProject->tables()->create([
                'name' => $table->name,
                'description' => $table->description,
                'dice_expression' => $table->dice_expression
            ]);

            // Clone all entries
            foreach ($table->entries as $entry) {
                $newTable->entries()->create([
                    'min_value' => $entry->min_value,
                    'max_value' => $entry->max_value,
                    'result' => $entry->result
                ]);
            }
        }

        $this->logger->info('Project cloned successfully', [
            'original_project_id' => $originalProject->id,
            'new_project_id' => $newProject->id
        ]);

        return $this->redirect($response, "/projects/{$newProject->id}");
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $project = Project::findOrFail($args['id']);

        $this->logger->info('Deleting project', [
            'project_id' => $project->id,
            'project_name' => $project->name
        ]);

        // Delete project (tables and entries will be deleted via cascade)
        $project->delete();

        return $this->json($response, ['success' => true]);
    }
} 