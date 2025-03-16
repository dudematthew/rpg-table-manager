<?php

namespace App\Controllers;

use App\Models\Project;
use App\Models\DiceTable;
use App\Models\TableEntry;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TableController extends Controller
{
    public function create(Request $request, Response $response, array $args): Response
    {
        $project = Project::findOrFail($args['projectId']);
        return $this->render($response, 'tables/new', ['project' => $project]);
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $project = Project::findOrFail($args['projectId']);
        
        $table = $project->tables()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'dice_expression' => $data['dice_expression']
        ]);

        return $this->redirect($response, "/projects/{$project->id}");
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::with(['entries' => function($query) {
            $query->orderBy('min_value');
        }])->findOrFail($args['id']);

        return $this->render($response, 'tables/view', ['table' => $table]);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::findOrFail($args['id']);
        return $this->render($response, 'tables/edit', ['table' => $table]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::findOrFail($args['id']);
        $data = $request->getParsedBody();

        $this->logger->info('Updating table', [
            'table_id' => $table->id,
            'data' => $data
        ]);

        $table->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'dice_expression' => $data['dice_expression']
        ]);

        return $this->redirect($response, "/tables/{$table->id}");
    }

    public function updateEntries(Request $request, Response $response, array $args): Response
    {
        try {
            $rawBody = (string) $request->getBody();
            $this->logger->debug('Raw request body', ['body' => $rawBody]);
            
            $contentType = $request->getHeaderLine('Content-Type');
            $this->logger->debug('Request headers', [
                'Content-Type' => $contentType,
                'Accept' => $request->getHeaderLine('Accept')
            ]);

            $data = $request->getParsedBody();
            $this->logger->debug('Parsed request body', ['data' => $data]);

            if (empty($data) && $rawBody) {
                $data = json_decode($rawBody, true);
                $this->logger->debug('JSON decoded body', ['data' => $data]);
            }
            
            if (!isset($data['entries']) || !is_array($data['entries'])) {
                $this->logger->error('Invalid request data', [
                    'table_id' => $args['id'],
                    'raw_body' => $rawBody,
                    'parsed_data' => $data
                ]);
                return $this->json($response, [
                    'error' => 'Invalid request data: entries array is required'
                ], 400);
            }
            
            $table = DiceTable::findOrFail($args['id']);
            
            // Delete existing entries
            $table->entries()->delete();
            
            // Create new entries
            $entries = collect($data['entries'])->map(function ($entry) use ($table) {
                if (!isset($entry['min_value'], $entry['max_value'], $entry['result'])) {
                    $this->logger->warning('Invalid entry data', [
                        'entry' => $entry,
                        'table_id' => $table->id
                    ]);
                    return null;
                }
                
                return new TableEntry([
                    'table_id' => $table->id,
                    'min_value' => $entry['min_value'],
                    'max_value' => $entry['max_value'],
                    'result' => $entry['result']
                ]);
            })->filter();
            
            $table->entries()->saveMany($entries);
            
            $savedEntries = $table->entries()->orderBy('min_value')->get();
            
            $this->logger->info('Successfully updated table entries', [
                'table_id' => $table->id,
                'entry_count' => $savedEntries->count()
            ]);
            
            return $this->json($response, [
                'entries' => $savedEntries
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error updating table entries', [
                'table_id' => $args['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->json($response, [
                'error' => 'Failed to update entries: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportMarkdown(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::with('entries')->findOrFail($args['id']);
        
        $response = $response
            ->withHeader('Content-Type', 'text/markdown')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $table->name . '.md"');
            
        $response->getBody()->write($table->toMarkdown());
        
        return $response;
    }

    public function exportCsv(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::with('entries')->findOrFail($args['id']);
        
        $response = $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $table->name . '.csv"');
            
        $response->getBody()->write($table->toCsv());
        
        return $response;
    }

    public function iframe(Request $request, Response $response, array $args): Response
    {
        $table = DiceTable::with(['entries' => function($query) {
            $query->orderBy('min_value');
        }])->findOrFail($args['id']);

        // Get the current URL for embedding
        $currentUrl = (string) $request->getUri();

        // If this is an iframe request (detected by query parameter), show minimal view
        if ($request->getQueryParams()['embed'] ?? false) {
            return $this->render($response, 'tables/embed', ['table' => $table]);
        }

        // Otherwise show the iframe tester page
        return $this->render($response, 'tables/iframe_tester', [
            'table' => $table,
            'currentUrl' => $currentUrl . '?embed=1'
        ]);
    }

    public function clone(Request $request, Response $response, array $args): Response
    {
        $originalTable = DiceTable::with('entries')->findOrFail($args['id']);

        $this->logger->info('Cloning table', [
            'original_table_id' => $originalTable->id
        ]);

        // Create new table with "(Copy)" suffix
        $newTable = DiceTable::create([
            'project_id' => $originalTable->project_id,
            'name' => $originalTable->name . ' (Copy)',
            'description' => $originalTable->description,
            'dice_expression' => $originalTable->dice_expression
        ]);

        // Clone all entries
        foreach ($originalTable->entries as $entry) {
            $newTable->entries()->create([
                'min_value' => $entry->min_value,
                'max_value' => $entry->max_value,
                'result' => $entry->result
            ]);
        }

        $this->logger->info('Table cloned successfully', [
            'original_table_id' => $originalTable->id,
            'new_table_id' => $newTable->id
        ]);

        return $this->redirect($response, "/tables/{$newTable->id}");
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $table = DiceTable::findOrFail($args['id']);

            $this->logger->info('Deleting table', [
                'table_id' => $table->id,
                'table_name' => $table->name
            ]);

            // Delete table (entries will be deleted via cascade)
            $table->delete();

            return $this->json($response, ['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting table', [
                'table_id' => $args['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->json($response, [
                'error' => 'Failed to delete table: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle oEmbed API requests
     * This allows platforms like Trello to display rich previews of our tables
     */
    public function oembed(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        
        // Check if URL parameter is provided
        if (!isset($params['url'])) {
            return $this->json($response, [
                'error' => 'URL parameter is required'
            ], 400);
        }
        
        $url = $params['url'];
        $format = $params['format'] ?? 'json';
        $maxWidth = isset($params['maxwidth']) ? (int)$params['maxwidth'] : 500;
        $maxHeight = isset($params['maxheight']) ? (int)$params['maxheight'] : 300;
        
        // Only support JSON format
        if ($format !== 'json') {
            return $this->json($response, [
                'error' => 'Only JSON format is supported'
            ], 400);
        }
        
        try {
            // Extract table ID from URL
            // Expected format: /tables/{id} or /tables/{id}/view
            if (preg_match('#/tables/(\d+)(?:/[a-z]+)?$#', $url, $matches)) {
                $tableId = $matches[1];
                $table = DiceTable::with('entries')->findOrFail($tableId);
                
                // Get table project
                $project = Project::findOrFail($table->project_id);
                
                // Build oEmbed response
                $oembedResponse = [
                    'version' => '1.0',
                    'type' => 'rich',
                    'provider_name' => 'RPG Table Manager',
                    'provider_url' => $request->getUri()->getScheme() . '://' . $request->getUri()->getHost(),
                    'title' => $table->name,
                    'width' => min(500, $maxWidth),
                    'height' => min(300, $maxHeight)
                ];
                
                // Add description if available
                if ($table->description) {
                    $oembedResponse['description'] = $table->description;
                } else {
                    $oembedResponse['description'] = "Dice table for {$table->dice_expression} in project {$project->name}";
                }
                
                // Generate HTML for embedding
                $iframeUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . 
                             $this->config->get('app.base_path') . "/tables/{$table->id}/iframe";
                
                $oembedResponse['html'] = "<iframe src=\"{$iframeUrl}\" width=\"{$oembedResponse['width']}\" " .
                                         "height=\"{$oembedResponse['height']}\" frameborder=\"0\" " .
                                         "allowtransparency=\"true\"></iframe>";
                
                return $this->json($response, $oembedResponse);
            } else {
                // URL doesn't match expected pattern
                return $this->json($response, [
                    'error' => 'Invalid URL format'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error generating oEmbed response', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return $this->json($response, [
                'error' => 'Failed to generate oEmbed response'
            ], 500);
        }
    }
} 